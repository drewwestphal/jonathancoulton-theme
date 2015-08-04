<?php
/**
 * Created by PhpStorm.
 * User: DAM
 * Date: 7/31/15
 * Time: 17:16
 */
$encode_hash = $params['var'];
$encode_transient = get_transient($encode_hash);
if (!$encode_transient) {
    die("Can't find that encode hash!");
} else {
    $encode_details = explode("|", $encode_transient);
    $track_post_id = $encode_details[0];
    $encode_format = $encode_details[1];

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    } else {
        die("wp_handle_upload() doesn't exist!");
    }

    if (!isset($_FILES['file'])) {
        die("No file!");
    }

    //$uploadedfile = $_FILES['file']; // media_handle_upload() takes only the index to $_FILES

    $upload_overrides = array('test_form' => false);

    $attachment_id = media_handle_upload('file', $track_post_id, array(), $upload_overrides);

    if ( !is_wp_error($attachment_id) ) {
        echo "File is valid, and was successfully uploaded.\n";
        var_dump($movefile);

        /* This code block should be unnecessary, since everything should be handled by wp_handle_upload()
        // $filename should be the path to a file in the upload directory.
        $filename = $movefile['file'];

        // The ID of the post this attachment is for.
        $parent_post_id = $track_post_id;

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype( basename( $filename ), null );

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
            'post_mime_type' => $filetype['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        */

        update_post_meta($track_post_id, 'lastforsale_'.$encode_format.'_id', $attach_id);
        update_post_meta($track_post_id, 'lastforsale_'.$encode_format.'_hash', $encode_hash);
    } else {
        echo $attachment_id->get_error_message();;
    }
}