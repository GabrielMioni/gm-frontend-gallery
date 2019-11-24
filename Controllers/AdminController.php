<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;
use WP_REST_Response;

class AdminController extends BaseController
{
    public function deleteGalleryPostById(WP_REST_Request $request)
    {
        $postId = $this->setRequestParams($request, 'postId');
        $permanent = (int) $this->setRequestParams($request, 'permanent') === 1;

        $userCanDeletePost = current_user_can('delete_posts', $postId);

        if (!$userCanDeletePost) {
            return $this->createWPError('invalid_request', 'Invalid user capabilities', 403);
        }

        $attachmentPaths = $permanent === true ? $this->getAttachmentImagePaths($postId) : [];

        $deletedPost = $permanent !== true ? wp_trash_post($postId) : wp_delete_post($postId, true);

        if (!$permanent && !is_a($deletedPost, 'WP_POST')) {
            $action = $permanent === true ? 'deleted' : 'trashed';
            return $this->createWPError('invalid_request', 'Post was not ' . $action, 500);
        }

        foreach ($attachmentPaths as $path) {
            if (!file_exists($path) || !$permanent) {
                continue;
            }
            unlink($path);
        }

        $postStatus = get_post_status($postId);

        $data = [];
        $data['postID'] = $postId;
        $data['postStatus'] = $postStatus;

        $response = new WP_REST_Response($data);
        $response->set_status(200);

        return $response;
    }

    public function deleteAttachmentById(WP_REST_Request $request)
    {
        $postId = $this->setRequestParams($request, 'postId');
        $attachId = $this->setRequestParams($request, 'attachmentId');

        $metaData = $this->getCompleteMetaData($postId, 'gm_gallery_attachment', $attachId);

        if (empty($metaData)) {
            return $this->createWPError('invalid_request', 'attach_id not found', 400);
        }

        $galleryAttachmentMeta = $metaData[0];

        $metaId = $galleryAttachmentMeta->meta_id;
        $attachId = $galleryAttachmentMeta->meta_value;

        $paths = $this->getPathsByAttachId($attachId);

        foreach ($paths as $path) {
            unlink($path);
        }

        delete_metadata_by_mid('post', $metaId);

        $response = new WP_REST_Response();
        $response->set_status(200);

        return $response;
    }

    public function setGalleryPostOrder(WP_REST_Request $request)
    {
        $postId = $this->setRequestParams($request, 'postId');
        $order = $this->setRequestParams($request, 'order');

        $postIdsGreaterThanOrder = $this->getOrderGreaterThan($order, $postId);

        update_post_meta($postId, 'gm_gallery_order', $order);

        foreach ($postIdsGreaterThanOrder as $currentPostId) {
            update_post_meta($currentPostId, 'gm_gallery_order', ++$order);
        }
    }

    public function setGalleryAttachmentOrder(WP_REST_Request $request)
    {
        $postId = $this->setRequestParams($request, 'postId');
        $order = $this->setRequestParams($request, 'order');

        var_dump($postId);
        var_dump($order);
    }

    protected function getOrderGreaterThan($order, $doNotIncludePostId = null)
    {
        global $wpdb;

        $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND cast(meta_value as unsigned) >= %d AND post_id != %d ORDER BY meta_value ASC";

        $metaQueryResult = $wpdb->get_results($wpdb->prepare($query, 'gm_gallery_order', $order, $doNotIncludePostId), 'ARRAY_N');
        $postIdsGreaterThanOrder = $this->flattenArray($metaQueryResult);

        return $postIdsGreaterThanOrder;
    }
}