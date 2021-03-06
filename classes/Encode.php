<?php

namespace jct;

class Encode extends EncodedAsset {

    /**
     * @return Track
     */
    public function getParentTrack() {
        return $this->getParentPost(Track::class);
    }

    public function getParentMusicStoreProduct() {
        return $this->getParentTrack();
    }

    public function getShopifyProductVariantTitle() {
        return $this->getEncodeConfig()->getConfigName();
    }

    public function getShopifyAndFetchSKU() {
        return $this->getParentTrack()->getPostID() . ':' . $this->getEncodeConfig()->getConfigName();
    }

    public function getEncodedAssetConfig() {
        return $this->getEncodeConfig();
    }


    /**
     * @return EncodeConfig
     */
    public function getEncodeConfig() {
        return EncodeConfig::fromPersistableArray($this->getConfigPayloadArray());
    }

    public function setEncodeConfig(EncodeConfig $encodeConfig) {
        $this->setConfigPayloadArray($encodeConfig->toPersistableArray());
    }

    public function getAwsName() {
        return $this->getParentTrack()->getAlbum()->getFilenameFriendlyTitle() . '/' .
               $this->getEncodeConfig()->getEncodeFormat() . '/' .
               $this->getParentTrack()->getPublicFilename($this->getEncodeConfig()->getFileExtension());
    }

}

?>