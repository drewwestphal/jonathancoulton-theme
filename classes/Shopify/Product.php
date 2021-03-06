<?php

namespace jct\Shopify;

class Product extends Struct {
    //https://help.shopify.com/api/reference/product
    public
        // strings that i can POST and PUT
        $title,
        $body_html,
        $product_type,
        $vendor,
        $tags,

        // ProductVariant[]
        $variants,
        // ProductOption[]
        $options,
        // ProductImage[]
        $images,

        // Metafield[]
        $metafields = [],

        // unused (default values)
        $template_suffix,
        $published_scope,

        // not updateable (do not send to the mothership)
        //A human-friendly unique string for the Product automatically generated from its title. They are used by the Liquid templating language to refer to objects.
        $handle,
        // ProductImage // the first image
        $image,
        // in as string --> DateTime
        $created_at,
        $updated_at,
        $published_at;


    protected function postProperties() {
        return [
            'title', 'body_html', 'product_type', 'vendor', 'tags', 'variants', 'options', 'images', 'image',
            'metafields',
        ];
    }

    protected function putProperties() {
        return array_merge(['id'], $this->postProperties());
    }


    protected function setProperty($propertyName, $property) {
        switch($propertyName) {
            case 'variants':
                $property = ProductVariant::instancesFromArray($property, $this);
                break;
            case 'options':
                $property = ProductOption::instancesFromArray($property, $this);
                break;
            case 'image':
                $property = Image::instanceFromArray($property, $this);
                break;
            case 'images':
                $property = Image::instancesFromArray($property, $this);
                break;
        }

        parent::setProperty($propertyName, $property);
    }
}