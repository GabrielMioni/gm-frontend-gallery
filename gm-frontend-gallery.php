<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

use GmFrontendGallery\Controller\BaseController;

if (!defined('WP_TEST_RUNNING')) {
    add_action( 'init', ['gmFrontendGallery', 'createPostType']);
    add_action('rest_api_init', ['gmFrontendGallery', 'registerApiSubmitRoute']);
    register_deactivation_hook(__FILE__, ['gmFrontendGallery', 'deactivate']);
}

class gmFrontendGalleryOne extends BaseController
{
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
}
