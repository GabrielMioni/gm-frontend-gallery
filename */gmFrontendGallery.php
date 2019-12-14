<?php

namespace GmFrontendGallery;

use GmFrontendGallery\Controller\AdminController;
use GmFrontendGallery\Controller\GalleryController;
use GmFrontendGallery\Controller\OptionsController;
use GmFrontendGallery\Controller\SubmitController;

use GmFrontendGallery\Controller\TestController;
use WP_REST_Server;

class gmFrontendGallery
{
    use definitionsTrait;

    public function activate()
    {
        $this->registerPostType();
        $this->createOptions();
    }

    public function registerPostType()
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
        add_option($this->pluginOptionName, $this->defaultOptions);
    }

    public function registerApiRoutes()
    {
        $submitController = new SubmitController();
        $adminController = new AdminController();
        $galleryController = new GalleryController();
        $optionsController = new OptionsController();
        $testController = new TestController();

        register_rest_route( $this->routeNameSpace, '/submit/', [
            'methods' => 'POST',
            'callback' => [$submitController, 'processGallerySubmission'],
        ]);

        register_rest_route( $this->routeNameSpace, '/update/(?P<postId>\d+)?', [
            'methods' => 'POST',
            'callback' => [$adminController, 'updateGalleryPostById'],
        ]);

        register_rest_route( $this->routeNameSpace, '/options/', [
            'methods' => 'POST',
            'callback' => [$optionsController, 'updateGalleryOptions'],
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

        register_rest_route($this->routeNameSpace, '/testData', [
            'methods' => 'GET',
            'callback' => [$testController, 'index'],
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

    public function registerVue()
    {
        $galleryUrlJS  = plugins_url() . '/gm-frontend-gallery/dist/gallery.js';
        $galleryUrlCSS = plugins_url() . '/gm-frontend-gallery/dist/gallery.css';

        wp_register_script('gm-frontend-gallery', $galleryUrlJS, [], '1.0.0');
    }

    public function mountVueApp()
    {
        wp_enqueue_script('gm-frontend-gallery');
        return '<div id="gm-frontend-gallery"></div>';
    }
}
