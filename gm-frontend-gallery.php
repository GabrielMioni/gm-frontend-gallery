<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

require_once('autoload.php');

use GmFrontendGallery\Controller\SubmitController;
use GmFrontendGallery\Controller\AdminController;
use GmFrontendGallery\Controller\GalleryController;

if (!defined('WP_TEST_RUNNING')) {
    add_action( 'init', ['gmFrontendGallery', 'createPostType']);
    add_action('rest_api_init', ['gmFrontendGallery', 'registerApiRoutes']);
//    register_deactivation_hook(__FILE__, ['gmFrontendGallery', 'deactivate']);
}

class gmFrontendGallery
{
    public static function createPostType()
    {
        $postType = 'gallery';

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

    public static function registerApiRoutes()
    {
        $submitController = new SubmitController();
        $adminController = new AdminController();
        $galleryController = new GalleryController();

        register_rest_route( 'gm-frontend-gallery/v1', '/submit/', [
            'methods' => 'POST',
            'callback' => [$submitController, 'processGallerySubmission'],
        ]);
        register_rest_route( 'gm-frontend-gallery/v1', '(?P<postId>\d+)?(?:/(?P<permanent>\d+))?', [
            'methods' => 'DELETE',
            'callback' => [$adminController, 'deleteGalleryPostById'],
            'args' => [
                'postId',
                'permanent'
            ]
        ]);
        register_rest_route( 'gm-frontend-gallery/v1', '/image/(?P<postId>\d+)?(?:/(?P<attachmentId>\d+))?', [
            'methods' => 'DELETE',
            'callback' => [$adminController, 'deleteAttachmentById'],
            'args' => [
                'postId',
                'attachmentId'
            ]
        ]);
        register_rest_route( 'gm-frontend-gallery/v1', '/get(?:/(?P<orderBy>[a-zA-Z\s]+))?(?:/(?P<order>[a-zA-Z\s]+))?',
            self::setGetRouteArray($galleryController, [
                'orderBy',
                'order'
            ])
        );

        register_rest_route( 'gm-frontend-gallery/v1', '/get/(?P<orderBy>[a-zA-Z\s]+)/(?P<order>[a-zA-Z\s]+)',
            self::setGetRouteArray($galleryController, [
                'orderBy',
                'order',
            ])
        );

        register_rest_route( 'gm-frontend-gallery/v1', '/get/(?P<page>\d+)/(?P<results>\d+)(?:/(?P<orderBy>[a-zA-Z\s]+))?(?:/(?P<order>[a-zA-Z\s]+))?',
            self::setGetRouteArray($galleryController, [
                'page',
                'results',
                'orderBy',
                'order'
            ])
        );

        register_rest_route('gm-frontend-gallery/v1', '(?P<postId>\d+)?', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$galleryController, 'retrieveGalleryPostSingle'],
            'args' => [
                'postId'
            ]
        ]);

        register_rest_route('gm-frontend-gallery/v1', '/order/post/(?P<postId>\d+)?/(?P<order>\d+)?', [
            'methods' => 'POST',
            'callback' => [$adminController, 'setGalleryPostOrder'],
            'args' => [
                'postId',
                'order',
            ]
        ]);
    }

    protected static function setGetRouteArray(GalleryController $galleryController, array $args)
    {
        return [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$galleryController, 'retrieveGalleryPosts'],
            'args' => $args
        ];
    }
}
