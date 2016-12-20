<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/views/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

namespace jct;

use Timber\Post;
use Timber\Timber;

$context = Timber::get_context();
$post = new Post();
$context['post'] = $post;

if($post->slug == "faq") {
    $context['faqs'] = Timber::get_posts('post_type=faq&numberposts=-1');
}

if($post->slug == "store") {
    $syncMan = new SyncManager(Util::get_shopify_api_client(), Util::get_fetch_api_client());

    //$context['store'] = include(__DIR__ . '/cache/cached_store_context.php');
    $context['store'] = file_exists(SyncManager::MUSIC_STORE_LOCK_FILE_LOCATION) ?
        json_decode(file_get_contents(SyncManager::MUSIC_STORE_LOCK_FILE_LOCATION), true) :
        false;


    $context['guest_at_store'] = true;
}

if($post->slug == 'news') {
    global $paged;
    if(!isset($paged) || !$paged) {
        $paged = 1;
    }
    $context['posts'] = Util::get_posts_cached([
                                                   'post_type'      => 'post',
                                                   'posts_per_page' => 4,
                                                   'paged'          => $paged,
                                               ], JCTPost::class);
    $context['pagination'] = Timber::get_pagination();

}
Timber::render(['page-' . $post->post_name . '.twig', 'page.twig'], $context);