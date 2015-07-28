<?php

class EncodeTarget {


    protected $destFormatDesc;
    protected $fromWavFile;
    protected $nameBase;
    protected $destFileName;
    protected $destMetadata;
    protected $fromArtFile;
    protected $errorsFile;

    public function __construct($config, $fromWav, $fromNameBase, $fromArtFile) {
        // get some reference materials
        $outputFormats = require('ffmpeg-output-formats.php');
        $metadataKeys = require('ffmpeg-metadata-fields.php');

        $this->fromWavFile=$fromWav;
        $rowJSON = json_encode($config);
        $this->nameBase = $fromNameBase . md5($rowJSON);
        // for debugging & tracking
        file_put_contents($this->nameBase . '.txt', $rowJSON);
        $this->errorsFile = $this->nameBase.'.errors';

        $this->fromArtFile = $fromArtFile;

        // default to first format if illegal format given
        $this->destFormatDesc = isset($outputFormats[$config['encode_format']]) ?
            $outputFormats[$config['encode_format']] : $outputFormats[0];
        if(isset($config['ffmpeg_flags']) &&
           preg_match('/[^a-z0-9\ :-]/i', $config['ffmpeg_flags'])
        ) {
            // only allow subset of chars to be in flags
            $this->destFormatDesc['flags'] = $config['ffmpeg_flags'];
        }

        $this->destFileName = $this->nameBase . '.' . $this->destFormatDesc['file_ext'];

        $this->destMetadata = $config['metadata'];
        // second arg of array combine is meaningless
        // just need only legal metadata keys
        $this->destMetadata = array_intersect_key(
            $this->destMetadata, array_combine($metadataKeys, $metadataKeys));
    }

    public function getFFMpegMetadataFlags() {
        return implode(' ', array_map(function ($mdKey, $mdVal) {
            return sprintf('-metadata %s=%s', $mdKey, escapeshellarg($mdVal));
        }, array_keys($this->destMetadata), $this->destMetadata));
    }

    public function doEncode() {
        $output = [];
        $rv = 0;

        $cmd = sprintf('ffmpeg -y -i %s %s -c:a %s %s %s 2>&1',
                       escapeshellarg($this->fromWavFile),
                       $this->getFFMpegMetadataFlags(),
                       escapeshellarg($this->destFormatDesc['lib']),
                       $this->destFormatDesc['flags'],
                       escapeshellarg($this->destFileName)
        );
        error_log($cmd);
        exec($cmd, $output, $rv);

        if($rv != 0) {
            file_put_contents($this->errorsFile, implode("\n", $output));
            return false;
        }

        exec($this->addAlbumArtCommand(), $output,$rv);
        if($rv != 0) {
            file_put_contents($this->errorsFile, implode("\n", $output));
            return false;
        }

        return true;
    }

    private function addAlbumArtCommand() {
        switch($this->destFormatDesc['add_art']) {

            case 'ffmpeg':
                return sprintf('ffmpeg -i %s -i %s -map 0:0 -map 1:0 -c copy -id3v2_version 3 metadata:s:v title="Album cover" -metadata:s:v comment="Cover (Front)" %s',
                               escapeshellarg($this->fromWavFile),
                               escapeshellarg($this->fromArtFile),
                               escapeshellarg($this->fromWavFile)
                );

            case 'metaflac':
                return sprintf('metaflac --import-picture-from=%s %s',
                               escapeshellarg($this->fromArtFile),
                               escapeshellarg($this->fromWavFile)
                );
                break;

            case 'atomicparsley':
                return sprintf('AtomicParsley %s -artwork %s',
                               escapeshellarg($this->fromWavFile),
                               escapeshellarg($this->fromArtFile)
                );
                break;

            default:
                break;
        }
    }

}

?>