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
    use definitionsTrait;

    public function createPostType()
    {
        $labels = [
            'name' => 'Gallery Images',
            'singular_name' => 'Gallery'
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => $this->galleryPostType],
        ];

        register_post_type($this->galleryPostType, $args);
    }

    public function createOptions()
    {
        add_option($this->pluginOptionName, [
            'user_required' => false,
            'admin_must_approve' => false,
            'allowed_mimes' => [
                'image/jpeg',
                'image/gif',
                'image/png',
            ],
        ]);
    }

    public function registerApiRoutes()
    {
        $submitController = new SubmitController();
        $adminController = new AdminController();
        $galleryController = new GalleryController();

        register_rest_route( $this->routeNameSpace, '/submit/', [
            'methods' => 'POST',
            'callback' => [$submitController, 'processGallerySubmission'],
        ]);

        register_rest_route( $this->routeNameSpace, '/update/(?P<postId>\d+)?', [
            'methods' => 'POST',
            'callback' => [$adminController, 'updateGalleryPostById'],
        ]);

        register_rest_route( $this->routeNameSpace, '(?P<postId>\d+)?(?:/(?P<permanent>\d+))?', [
            'methods' => 'DELETE',
            'callback' => [$adminController, 'deleteGalleryPostById'],
            'args' => [
                'postId',
                'permanent'
            ]
        ]);
        register_rest_route( $this->routeNameSpace, '/image/(?P<postId>\d+)?(?:/(?P<attachmentId>\d+))?', [
            'methods' => 'DELETE',
            'callback' => [$adminController, 'deleteAttachmentById'],
            'args' => [
                'postId',
                'attachmentId'
            ]
        ]);
        register_rest_route( $this->routeNameSpace, '/get(?:/(?P<orderBy>[a-zA-Z\s]+))?(?:/(?P<order>[a-zA-Z\s]+))?',
            $this->setGetRouteArray($galleryController, [
                'orderBy',
                'order'
            ])
        );

        register_rest_route( $this->routeNameSpace, '/get/(?P<orderBy>[a-zA-Z\s]+)/(?P<order>[a-zA-Z\s]+)',
            $this->setGetRouteArray($galleryController, [
                'orderBy',
                'order',
            ])
        );

        register_rest_route( $this->routeNameSpace, '/get/(?P<page>\d+)/(?P<results>\d+)(?:/(?P<orderBy>[a-zA-Z\s]+))?(?:/(?P<order>[a-zA-Z\s]+))?',
            $this->setGetRouteArray($galleryController, [
                'page',
                'results',
                'orderBy',
                'order'
            ])
        );

        register_rest_route($this->routeNameSpace, '(?P<postId>\d+)?', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$galleryController, 'retrieveGalleryPostSingle'],
            'args' => [
                'postId'
            ]
        ]);

        register_rest_route($this->routeNameSpace, '/order/post/(?P<postId>\d+)?/(?P<order>\d+)?', [
            'methods' => 'POST',
            'callback' => [$adminController, 'setGalleryPostOrder'],
            'args' => [
                'postId',
                'order',
            ]
        ]);

        register_rest_route($this->routeNameSpace, '/order/attachment/(?P<postId>\d+)?/(?P<attachId>\d+)/(?P<order>\d+)?', [
            'methods' => 'POST',
            'callback' => [$adminController, 'setGalleryAttachmentOrder'],
            'args' => [
                'postId',
                'attachId',
                'order',
            ]
        ]);
    }

    protected function setGetRouteArray(GalleryController $galleryController, array $args)
    {
        return [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$galleryController, 'retrieveGalleryPosts'],
            'args' => $args
        ];
    }
}
