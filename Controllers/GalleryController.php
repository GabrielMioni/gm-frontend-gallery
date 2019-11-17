<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;

class GalleryController extends BaseController
{
    protected static $orderByOptions = [
        'author',
        'title',
        'date'
    ];

    protected static $orderDirectionOptions = [
        'asc',
        'desc'
    ];

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

    public static function retrieveGalleryPostSingle(WP_REST_Request $request)
    {
        $postId = self::setRequestParams($request, 'postId');
        $post = get_post($postId);

        $data = self::multiPluck($post, [
            'ID',
            'post_author',
            'post_content',
            'post_title',
            'post_excerpt',
            'post_name',
            'post_type',
        ]);

        $data['images'] = self::retrieveGalleryImages($post);
        return $data;
    }

    protected static function setOrderData(WP_REST_Request $request) {
        $orderBy      = self::setRequestParams($request, 'orderBy');
        $order        = self::setRequestParams($request, 'order');

        return [
            'orderby' => in_array($orderBy, self::$orderByOptions) ? $orderBy : 'ID',
            'order'   => in_array($order, self::$orderDirectionOptions) ? strtoupper($order) : 'ASC'
        ];
    }
}