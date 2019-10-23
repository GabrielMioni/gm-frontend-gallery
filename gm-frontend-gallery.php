<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

//require_once( ABSPATH . 'wp-admin/includes/image.php' );
//require_once( ABSPATH . 'wp-admin/includes/file.php' );
//require_once( ABSPATH . 'wp-admin/includes/media.php' );

if (!defined('WP_TEST_RUNNING')) {
    add_action( 'init', ['gmFrontendGallery', 'createPostType']);
    add_action('rest_api_init', ['gmFrontendGallery', 'registerApiSubmitRoute']);
    register_deactivation_hook(__FILE__, ['gmFrontendGallery', 'deactivate']);
}

class gmFrontendGallery
{
    protected static $postType = 'gallery';

    public static function createPostType()
    {
        $postType = self::$postType;

        $labels = [
            'name' => 'Gallery Images',
            'singular_name' => 'Gallery'
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => $postType],
        ];

        register_post_type($postType, $args);
    }

    public static function registerApiSubmitRoute()
    {
        register_rest_route( 'gm-frontend-gallery/v1', '/submit/', [
            'methods' => 'POST',
            'callback' => [self::class, 'processGallerySubmission'],
        ]);
    }

    public static function processGallerySubmission(WP_REST_Request $request)
    {
        $post_title   = self::setRequestParams($request, 'post_title');
        $post_content = self::setRequestParams($request, 'post_content');

        if (is_null($post_title) || is_null($post_content)) {
            return new WP_Error(
                'gallery_incomplete',
                'Gallery submissions must include a title and content',
                ['status' => 404]);
        }

        $postArray = [];
        $postArray['post_content'] = $post_content;
        $postArray['post_title']   = $post_title;

        $newPostId = wp_insert_post($postArray, true);

        $data = [];

        if (!is_wp_error($newPostId)) {
            $data['postID'] = $newPostId;
        }

//        $imageAttachment = self::createImageAttachment($request);
        $imageAttachment = self::createImageAttachment($request, $newPostId);
//        file_put_contents(dirname(__FILE__) . '/log', print_r($imageAttachment, true), FILE_APPEND);

        $response = new WP_REST_Response($data);
        $response->set_status(200);

        return $response;
    }

    public static function activate()
    {

    }

    public static function deactivate()
    {
        if (!self::checkUserAbility()) {
            return;
        }
        unregister_post_type(self::$postType);
    }

    public static function uninstall()
    {

    }

    protected static function setRequestParams(WP_REST_Request $request, $key)
    {
        $parameter = trim($request->get_param($key));
        return $parameter !== '' ? $parameter : null;
    }

    protected static function checkUserAbility()
    {
        return current_user_can('activate_plugins');
    }

    /*protected static function createImageAttachment(WP_REST_Request $request)
    {
        $fileParams = $request->get_file_params();
        $imageData = $fileParams['image'];

        $imageData = wp_upload_bits($imageData['name'], null, $imageData['file']);

        return $imageData;
    }*/
    
    protected static function createImageAttachment(WP_REST_Request $request, $postId)
    {
        $fileParams = $request->get_file_params();
        $imageData = $fileParams['image'];

        $imageData = wp_upload_bits($imageData['name'], null, $imageData['file']);

        $file_path = $imageData['file'];
        $file_name = basename( $file_path );
        $file_type = wp_check_filetype( $file_name, null );
        $attachment_title = sanitize_file_name( pathinfo( $file_name, PATHINFO_FILENAME ) );
        $wp_upload_dir = wp_upload_dir();

        $post_info = array(
            'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title'     => $attachment_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        );

        $attach_id = wp_insert_attachment( $post_info, $file_path, $postId );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
        wp_update_attachment_metadata( $attach_id,  $attach_data );
        return $attach_id;
    }
}