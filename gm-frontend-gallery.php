<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

add_action( 'init', ['gmFrontendGallery', 'createPostType']);
register_deactivation_hook(__FILE__, ['gmFrontendGallery', 'deactivate']);

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

    protected static function checkUserAbility()
    {
        return current_user_can('activate_plugins');
    }
}