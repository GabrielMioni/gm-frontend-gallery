<?php

//require_once('../autoload.php');

use GmFrontendGallery\Controller\AdminController;
use GmFrontendGallery\Controller\GalleryController;
use GmFrontendGallery\Controller\OptionsController;
use GmFrontendGallery\Controller\SubmitController;

class gmFrontendGallery
{
    use definitionsTrait;

    public function activate()
    {
        $this->createPostType();
        $this->createOptions();
    }

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
        add_option($this->pluginOptionName, $this->defaultOptions);
    }

    public function registerApiRoutes()
    {
        $submitController = new SubmitController();
        $adminController = new AdminController();
        $galleryController = new GalleryController();
        $optionsController = new OptionsController();

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
