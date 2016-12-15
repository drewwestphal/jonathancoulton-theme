<?php

namespace jct;

use jct\Shopify\Provider\MetafieldProvider;

class MusicStoreMetafieldProvider implements MetafieldProvider {
    const DEFAULT_NAMESPACE = 'global';

    private $key, $value;

    public function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }


    public function getProductMetafieldNamespace() {
        return self::DEFAULT_NAMESPACE;
    }

    public function getProductMetafieldKey() {
        return $this->key;
    }

    public function getProductMetafieldValue() {
        return $this->value;
    }

    public function getProductMetafieldValueType() {
        return is_string($this->value) ? 'string' : 'integer';
    }

    public static function getForProduct(ShopifyProduct $product) {
        $trackNumber = 0;
        if($product instanceof Track) {
            $trackNumber = $product->getTrackNumber();
        }

        return [
            new MusicStoreMetafieldProvider('track_number', $trackNumber),
            new MusicStoreMetafieldProvider('wiki_link', $product->getWikiLink()),
            new MusicStoreMetafieldProvider('music_link', $product->getMusicLink()),
        ];
    }
}