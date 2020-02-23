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

    protected $scriptHandleGallery = 'gm-frontend-gallery';
    protected $scriptHandleSubmit  = 'gm-frontend-submit';

    protected $galleryVersion = '1.0.0';

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

    public function registerGalleryVue()
    {
        $galleryUrlJS  = plugins_url() . '/gm-frontend-gallery/dist/gallery/app.js';
        $galleryUrlCSS = plugins_url() . '/gm-frontend-gallery/dist/gallery/app.css';

        wp_register_script($this->scriptHandleGallery, $galleryUrlJS, [], $this->galleryVersion);
        wp_register_style($this->scriptHandleGallery, $galleryUrlCSS, [], $this->galleryVersion);
    }

    public function mountVueGallery()
    {
        wp_enqueue_script($this->scriptHandleGallery);
        wp_enqueue_style($this->scriptHandleGallery);
        return '<div id="gm-frontend-gallery"></div>';
    }

    public function registerSubmitVue()
    {
        $submitUrlJS  = plugins_url() . '/gm-frontend-gallery/dist/submit/app.js';
        $submitUrlCSS = plugins_url() . '/gm-frontend-gallery/dist/submit/app.css';

        wp_register_script($this->scriptHandleSubmit, $submitUrlJS, [], $this->galleryVersion);
        wp_register_style($this->scriptHandleSubmit, $submitUrlCSS, [], $this->galleryVersion);
    }

    public function mountVueSubmit()
    {
        wp_enqueue_script($this->scriptHandleSubmit);
        wp_enqueue_style($this->scriptHandleSubmit);
        $nonce = wp_create_nonce($this->gallerySubmitNonce);
        $jsonEncodedOptions = $this->createOptionsJson();

        return '<div id="gm-frontend-submit" data-nonce="'.$nonce.'" data-options="'.$jsonEncodedOptions.'"></div>';
    }

    public function setGalleryPostVueMountElm($content)
    {
        global $post;

        if ($post->post_type === $this->galleryPostType) {
            $content = '<div id="gm-frontend-gallery-post-single"></div>';
        }
        return $content;
    }

    protected function createOptionsJson()
    {
        $options = get_option($this->pluginOptionName);

        $setOptions = [
            'maxAttachments' => $this->getOptionValue($options,'max_attachments'),
            'allowedMimes' => $this->getOptionValue($options,'allowed_mimes'),
            'maxContentLength' => $this->getOptionValue($options, 'max_content_length')
        ];

        return htmlspecialchars(json_encode($setOptions), ENT_QUOTES, 'UTF-8');
    }

    protected function getOptionValue(array $options, $optionType) {
        return isset($options[$optionType]) ? $options[$optionType] : $this->defaultOptions[$optionType];
    }
}
