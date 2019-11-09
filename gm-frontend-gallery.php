<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

require_once(ABSPATH . 'wp-admin/includes/image.php');

if (!defined('WP_TEST_RUNNING')) {
    add_action( 'init', ['gmFrontendGallery', 'createPostType']);
    add_action('rest_api_init', ['gmFrontendGallery', 'registerApiSubmitRoute']);
    register_deactivation_hook(__FILE__, ['gmFrontendGallery', 'deactivate']);
}

class gmFrontendGallery
{
    protected static $postType = 'gallery';
    protected static $postStatus = 'published';
    protected static $galleryIncompleteCode = 'gallery_incomplete';
    protected static $galleryAttachmentMetaKey = 'gm_gallery_attachment';

    protected static $orderByOptions = [
        'author',
        'title',
        'date'
    ];

    protected static $orderDirectionOptions = [
        'asc',
        'desc'
    ];

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
        register_rest_route( 'gm-frontend-gallery/v1', '(?P<postId>\d+)?(?:/(?P<permanent>\d+))?', [
            'methods' => 'DELETE',
            'callback' => [self::class, 'deleteGalleryPostById'],
            'args' => [
                'postId',
                'permanent'
            ]
        ]);
    }

    public static function deleteGalleryPostById(WP_REST_Request $request)
    {
        $postId = self::setRequestParams($request, 'postId');
        $permanent = (int) self::setRequestParams($request, 'permanent') === 1;

        $userCanDeletePost = current_user_can('delete_posts', $postId);

        if (!$userCanDeletePost) {
            return self::createWPError('invalid_request', 'Invalid user capabilities', 403);
        }

        $trashedPost = wp_trash_post($postId);

        if (!is_a($trashedPost, 'WP_POST')) {
            return self::createWPError('invalid_request', 'Post was not moved to trash', 500);
        }

        $postStatus = get_post_status($postId);

        $data = [];
        $data['postID'] = $postId;
        $data['postStatus'] = $postStatus;

        $response = new WP_REST_Response($data);
        $response->set_status(200);

        return $response;
    }

    public static function registerApiGetRoute()
    {
        register_rest_route( 'gm-frontend-gallery/v1', '/get(?:/(?P<orderBy>[a-zA-Z\s]+))?(?:/(?P<order>[a-zA-Z\s]+))?',
            self::setGetRouteArray([
                'orderBy',
                'order'
            ])
        );

        register_rest_route( 'gm-frontend-gallery/v1', '/get/(?P<orderBy>[a-zA-Z\s]+)/(?P<order>[a-zA-Z\s]+)',
            self::setGetRouteArray([
                'orderBy',
                'order',
            ])
        );

        register_rest_route( 'gm-frontend-gallery/v1', '/get/(?P<page>\d+)/(?P<results>\d+)(?:/(?P<orderBy>[a-zA-Z\s]+))?(?:/(?P<order>[a-zA-Z\s]+))?',
            self::setGetRouteArray([
                'page',
                'results',
                'orderBy',
                'order'
            ])
        );
    }

    protected static function setGetRouteArray(array $args)
    {
        return [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [self::class, 'retrieveGalleryPosts'],
            'args' => $args
        ];
    }

    public static function retrieveGalleryPosts(WP_REST_Request $request)
    {
        $postsPerPage = self::setRequestParams($request, 'page');
        $numberPosts  = self::setRequestParams($request, 'results');
        $orderData    = self::setOrderData($request);

        $isPaged = !is_null($postsPerPage) && !(is_null($numberPosts));

        $args = [
            'post_type' => self::$postType,
            'order'     => $orderData['order'],
            'orderby'   => $orderData['orderby'],
            'post_status' => self::$postStatus,
            'offset' => $isPaged === true ? $numberPosts * ($postsPerPage - 1) : -1,
            'numberposts'=> $isPaged === true ? $numberPosts : -1,
        ];

        $posts = get_posts($args);

        $data = [];

        foreach ($posts as $post) {

            $postVariables = self::multiPluck($post, [
                'ID',
                'post_author',
                'post_date',
                'post_content',
                'post_title'
            ]);

            $postVariables['images'] = self::retrieveGalleryImages($post);

            $data[] = $postVariables;
        }

        return $data;
    }

    protected static function retrieveGalleryImages(WP_Post $post)
    {
        $images = [];
        $sizes = ['thumbnail', 'medium', 'full'];

        $galleryAttachmentMeta = get_post_meta($post->ID, self::$galleryAttachmentMetaKey, false);
        
        foreach ($galleryAttachmentMeta as $metaItem) {
            $attachId = $metaItem['attach_id'];
            $sizedImages = [];

            foreach ($sizes as $size) {
                $postImage = wp_get_attachment_image_url($attachId, $size);
                $sizedImages[$size] = $postImage !== false ? $postImage : null;
            }

            $images[] = [
                'attach_id' => $attachId,
                'sized_images' => $sizedImages,
            ];
        }

        return $images;
    }

    protected static function setOrderData(WP_REST_Request $request) {
        $orderBy      = self::setRequestParams($request, 'orderBy');
        $order        = self::setRequestParams($request, 'order');

        return [
            'orderby' => in_array($orderBy, self::$orderByOptions) ? $orderBy : 'ID',
            'order'   => in_array($order, self::$orderDirectionOptions) ? strtoupper($order) : 'ASC'
        ];
    }

    public static function processGallerySubmission(WP_REST_Request $request)
    {
        $nonceParam   = self::setRequestParams($request, 'post_nonce');
        $nonceIsValid = wp_verify_nonce($nonceParam, 'gm_gallery_submit');

        if ($nonceIsValid === false) {
            return self::createWPError('invalid_request', 'Invalid nonce', 401);
        }

        $post_title   = self::setRequestParams($request, 'post_title');
        $post_content = self::setRequestParams($request, 'post_content');

        if (is_null($post_title) || is_null($post_content)) {
            return self::createWPError(self::$galleryIncompleteCode, 'Gallery submissions must include a title and content', 404);
        }

        $imageData = $request->get_file_params();

        if (empty($imageData)) {
            return self::createWPError(self::$galleryIncompleteCode, 'Gallery submissions must include an image', 404);
        }

        $postArray = [];
        $postArray['post_content'] = $post_content;
        $postArray['post_title']   = $post_title;
        $postArray['post_type']    = self::$postType;
        $postArray['post_status']  = self::$postStatus;

        $newPostId = wp_insert_post($postArray, true);

        $data = [];

        if (!is_wp_error($newPostId)) {
            $data['postID'] = $newPostId;
        }

        $attachmentIds = self::processImageAttachments($imageData, $newPostId);

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

    protected static function setRequestImage(WP_REST_Request $request)
    {
        $fileParams = $request->get_file_params();
//        file_put_contents(dirname(__FILE__) . '/log', print_r($fileParams, true), FILE_APPEND);
        return isset($fileParams['image']) ? $fileParams['image'] : null;
    }

    protected static function createWPError($code, $message, $status)
    {
        return new WP_Error($code, $message, ['status' => $status]);
    }

    protected static function checkUserAbility()
    {
        return current_user_can('activate_plugins');
    }

    protected static function processImageAttachments(array $imageData, $postId)
    {
        $attachmentIds = [];

        foreach ($imageData as $imageDatum) {
            $attachmentIds[] = self::createImageAttachment($imageDatum, $postId);
        }

        return $attachmentIds;

    }
    
    protected static function createImageAttachment(array $imageData, $postId)
    {
        $uploadData = wp_upload_bits($imageData['name'], null, $imageData['file']);

        $file_path = $uploadData['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name, null);
        $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
        $wp_upload_dir = wp_upload_dir();

        $post_info = [
            'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title'     => $attachment_title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment( $post_info, $file_path, $postId );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

        wp_update_attachment_metadata($attach_id, $attach_data);

        $attachmentMetaData = [
            'attach_id' => $attach_id,
        ];

        add_post_meta($postId, self::$galleryAttachmentMetaKey, $attachmentMetaData, false);

        return $attach_id;
    }

    protected static function multiPluck($input, array $indexKeys)
    {
        $out = [];

        if (is_object($input))
            $input = get_object_vars($input);

        foreach ($indexKeys as $indexKey) {
            $out[$indexKey] = $input[$indexKey];
        }

        return $out;
    }
}