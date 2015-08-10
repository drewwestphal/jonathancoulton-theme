<?php

namespace jct;

class Album {


    private $postID, $albumTitle, $albumArtist, $albumYear, $albumGenre, $albumComment, $albumArtObject, $albumBonusAssetObject, $albumShow;
    // the parent post object
    private $wpPost;
    //
    private $albumTracks = array();

    /**
     * @param \WP_Post $postObject the post in the blog that forms the base of this
     * album. The post contains ACF fields and post_meta data that will define the
     * internal variables of this class
     **/
    public function __construct(\WP_Post $post) {
        $post_id = $post->ID;
        $this->postID = $post_id;
        // fill in private fields from post object/acf/postmeta
        $this->wpPost = $post;
        $this->albumTitle = $post->post_title;
        $this->albumArtist = get_field('album_artist',$post_id);
        $this->albumYear = get_field('album_year',$post_id);
        $this->albumGenre = get_field('album_genre',$post_id);
        $this->albumComment = get_field('album_comment',$post_id);
        $this->albumArtObject  = get_field('album_art',$post_id); // returns array with id, url, sizes, etc
        $this->albumBonusAssetObject = get_field('full_album_asset',$post_id);
        $this->albumShow = get_field('show_album_in_store',$post_id);
        $tracks = get_posts(array('post_type' => 'track', 'meta_key' => 'track_album', 'meta_value' => $post_id)); // Constructor probs shouldn't do this lookup
        foreach ($tracks as $track) {
            $this->albumTracks[get_field('track_number',$track->id)] = new Track($track,$this);
        }
    }

    public function isEncodeWorthy() {
        $worthy = false;
        if ($this->albumShow && $this->albumTitle && $this->albumArtist && $this->albumArtObject) {
            $worthy = true;
        }
        return $worthy;
    }

    public function getNeededEncodes() {
        if (!$this->isEncodeWorthy()) {
            return false;
        }
        $encodes = array();
        foreach ($this->albumTracks as $track) {
            $track_encodes = $track->getNeededEncodes();
            if ($track_encodes) {
                $encodes = array_merge($encodes,$track_encodes);
            }
        }
        if (count($encodes)) {
            return $encodes;
        } else {
            return false;
        }
    }

    public function getNumberOfAlbumTracks() {
        return count($this->albumTracks);
    }

    // @return array the album tracks IN ORDER
    public function getAlbumTracks() {
        return $this->albumTracks;
    }

    public function getPostID() {
        return $this->postID;
    }

    /**
     * @return mixed
     */
    public function getAlbumTitle() {
        return $this->albumTitle;
    }

    /**
     * @return mixed
     */
    public function getAlbumArtist() {
        return $this->albumArtist;
    }

    /**
     * @return mixed
     */
    public function getAlbumYear() {
        return $this->albumYear;
    }

    /**
     * @return mixed
     */
    public function getAlbumGenre() {
        return $this->albumGenre;
    }

    /**
     * @return mixed
     */
    public function getAlbumArtObject() {
        return $this->albumArtObject;
    }

    public function getAlbumArtURL() {
        $art_object = $this->getAlbumArtObject();
        return wp_get_attachment_url($art_object['id']);
    }

    public function getAlbumArtPath() {
        $art_object = $this->getAlbumArtObject();
        return get_attached_file($art_object['id']);
    }

    /**
     * @return mixed
     */
    public function getAlbumBonusAssetObject() {
        return $this->albumBonusAssetObject;
    }

    public function getAlbumBonusAssetURL() {
        $bonus_object = $this->getBonusAssetObject();
        return wp_get_attachment_url($bonus_object['id']);
    }

    public function getAlbumBonusAssetPath() {
        $bonus_object = $this->getAlbumBonusAssetObject();
        return get_attached_file($bonus_object['id']);
    }

    /**
     * @return mixed
     */
    public function getAlbumComment() {
        return $this->albumComment;
    }

}