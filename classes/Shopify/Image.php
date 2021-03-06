<?
namespace jct\Shopify;

class Image extends Struct {


    public
        // int
        $product_id,
        // url
        $src,
        $attachment,


        // date time
        $created_at,
        $updated_at,

        // programmatic--should increase
        $position,

        // default
        $variant_ids = [];


    protected function postProperties() {
        return ['src', 'attachment'];
    }

    protected function putProperties() {
        return ['id', 'src', 'attachment'];
    }

}
