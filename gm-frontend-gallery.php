<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

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
//        $fileData = $request->get_file_params();

        if (is_null($post_title) || is_null($post_content)) {
            return new WP_Error(
                'gallery_incomplete',
                'Gallery submissions must include a title and content',
                ['status' => 404]);
        }

        $postArray = [];
        $postArray['post_content'] = $post_content;
        $postArray['post_title']   = $post_title;

        $newPost = wp_insert_post($postArray, true);

        $data = [];

        if (!is_wp_error($newPost)) {
            $data['postID'] = $newPost;
        }

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

    protected static function setRequestParams(WP_REST_Request $request, $key){
        $parameter = trim($request->get_param($key));
        return $parameter !== '' ? $parameter : null;
    }

    protected static function checkUserAbility()
    {
        return current_user_can('activate_plugins');
    }
}