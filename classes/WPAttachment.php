<?php
/**
 * Created by PhpStorm.
 * User: DAM
 * Date: 8/13/15
 * Time: 12:01
 */

namespace jct;


class WPAttachment extends JCTPost {

    const META_CONTENT_HASH = 'jct_content_hash';
    const META_ATTACHMENT_LEAF_CLASS = 'jct_attachment_leaf_class';
    const META_ATTACHMENT_ROOT_CLASS = 'jct_attachment_root_class';


    public function __construct($id) {
        parent::__construct($id);

        // must construct parent so these methods know what the heck they are
        // operating on
        if(!($this->getAttachmentLeafClass() && $this->getAttachmentRootClass())) {
            $this->setAttachmentClassMetaVariables();
        }
    }

    public function getAttachmentID() { // must use this function instead of property because it gets redefined in KeyedWPAttachment
        return $this->getPostID();
    }

    public function getFilename() {
        return basename($this->getPath());
    }

    public function getCanonicalContentHash() {
        $hash = $this->get_field(self::META_CONTENT_HASH);
        if(!$hash) {
            return $this->setCanonicalContentHash();
        }
        return $hash;
    }

    protected function setCanonicalContentHash() {
        $this->update(self::META_CONTENT_HASH, $hash = md5_file($this->getPath()));
        return $hash;
    }

    public function getAttachmentLeafClass() {
        return $this->get_field(self::META_ATTACHMENT_LEAF_CLASS);
    }

    public function getAttachmentRootClass() {
        return $this->get_field(self::META_ATTACHMENT_ROOT_CLASS);
    }

    protected function setAttachmentClassMetaVariables() {
        $this->update(self::META_ATTACHMENT_LEAF_CLASS, static::class);
        $this->update(self::META_ATTACHMENT_ROOT_CLASS, self::class);
    }


    public function getPath() {
        /** @noinspection PhpUndefinedFunctionInspection */
        return get_attached_file($this->ID);
    }

    public function getURL() {
        /** @noinspection PhpUndefinedFunctionInspection */
        return wp_get_attachment_url($this->ID);
    }

    public function fileAssetExists() {
        return $this->ID && file_exists($this->getPath()) && filesize($this->getPath());
    }

    public function deleteAttachment($skipTrash = false) {
        /** @noinspection PhpUndefinedFunctionInspection */
        wp_delete_attachment($this->getPostID(), $skipTrash);
    }

    /**
     * @param $uniqueKey
     * @return Encode|null
     */
    public static function findByUniqueKey($uniqueKey, $prepop = null, $prepopNull = false) {
        $rv = Util::get_posts_cached([
                                         'post_type' => self::getPostType(),
                                         'name'      => $uniqueKey,
                                     ], static::class, $prepop, $prepopNull);
        if($rv && is_array($rv)) {
            return $rv[0];
        }
        if(!$rv) {
            return null;
        }
        return $rv;
    }

    public static function getPostType() {
        return 'attachment';
    }

    /**
     * @return static[]
     * @throws JCTException
     */
    public static function getAll() {

        $getRootClass = self::class === static::class;

        /** @var EncodedAsset[] $all */
        $all = Util::get_posts_cached([
                                          'post_type'      => static::getPostType(),
                                          'post_status'    => 'inherit',
                                          'posts_per_page' => -1,
                                          'meta_query'     => [
                                              // you'll notice an extra array in here
                                              // compared with elsewhere... this is a
                                              // wordpress bullshit thing from
                                              // http://wordpress.stackexchange.com/questions/181546/getting-attachments-by-meta-value
                                              [
                                                  'key'   => $getRootClass ?
                                                      self::META_ATTACHMENT_ROOT_CLASS :
                                                      self::META_ATTACHMENT_LEAF_CLASS,
                                                  'value' => str_replace('\\', '', static::class),
                                              ],
                                          ],
                                      ], static::class);

        $byUniqueKey = [];

        foreach($all as $encodedAsset) {
            // cache on the two class levels that we easily can
            // for the two key types we'll likely get asked about in the future
            static::getByID($encodedAsset->getPostID(), $encodedAsset);
            self::getByID($encodedAsset->getPostID(), $encodedAsset);

            static::findByUniqueKey($encodedAsset->getUniqueKey(), $encodedAsset);
            self::findByUniqueKey($encodedAsset->getUniqueKey(), $encodedAsset);

            // if there is no file it is garbage
            if($encodedAsset->fileAssetExists()) {
                $byUniqueKey[$encodedAsset->getUniqueKey()] = $encodedAsset;
            }
        }

        return $byUniqueKey;
    }


}