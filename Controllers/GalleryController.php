<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;

class GalleryController extends BaseController
{
    protected static $orderByOptions = [
        'author',
        'title',
        'date',
        'meta_value_num',
        'ID',
    ];

    protected static $orderDirectionOptions = [
        'asc',
        'desc'
    ];

    public function retrieveGalleryPosts(WP_REST_Request $request)
    {
        $postsPerPage = $this->setRequestParams($request, 'page');
        $numberPosts  = $this->setRequestParams($request, 'results');
        $orderData    = $this->setOrderData($request);

        $isPaged = !is_null($postsPerPage) && !(is_null($numberPosts));

        $args = [
            'post_type' => $this->galleryPostType,
            'order'     => $orderData['order'],
            'orderby'   => $orderData['orderby'],
            'meta_key'  => $orderData['meta_key'],
            'post_status' => $this->galleryPostStatus,
            'offset' => $isPaged === true ? $numberPosts * ($postsPerPage - 1) : -1,
            'numberposts'=> $isPaged === true ? $numberPosts : -1,
        ];
        
        $posts = get_posts($args);

        $data = [];

        foreach ($posts as $post) {

            $postVariables = $this->multiPluck($post, [
                'ID',
                'post_author',
                'post_date',
                'post_content',
                'post_title'
            ]);

            $postVariables['images'] = $this->retrieveGalleryImages($post);

            $data[] = $postVariables;
        }

        return $data;
    }

    public function retrieveGalleryPostSingle(WP_REST_Request $request)
    {
        $postId = $this->setRequestParams($request, 'postId');
        $post = get_post($postId);

        $data = $this->multiPluck($post, [
            'ID',
            'post_author',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_name',
            'post_type',
        ]);

        $data['images'] = $this->retrieveGalleryImages($post);
        return $data;
    }

    protected function setOrderData(WP_REST_Request $request) {
        $orderBy      = $this->setRequestParams($request, 'orderBy');
        $order        = $this->setRequestParams($request, 'order');

        return [
            'orderby' => in_array($orderBy, self::$orderByOptions) ? $orderBy : 'meta_value_num',
            'meta_key' => $this->galleryPostOrderKey,
            'order'   => in_array($order, self::$orderDirectionOptions) ? strtoupper($order) : 'ASC'
        ];
    }
}