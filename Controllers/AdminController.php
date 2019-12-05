<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;
use WP_REST_Response;

class AdminController extends BaseController
{
    public function updateGalleryPostById(WP_REST_Request $request)
    {
        $postId = $this->setRequestParams($request, 'postId');
        $postTitleKey = 'post_title';
        $postContentKey = 'post_content';

        $postTitle = $this->setRequestParams($request, $postTitleKey);
        $postContent = $this->setRequestParams($request, $postContentKey);

        $existentPost = get_post($postId, ARRAY_A);

        $updateData = [];

        if (!is_null($postTitle) && $existentPost[$postTitleKey] !== $postTitle) {
            $updateData[$postTitleKey] = $postTitle;
        }
        if (!is_null($postContent) && $existentPost[$postContentKey] !== $postContent) {
            $updateData['post_content'] = $postContent;
        }

        if (empty($updateData)) {
            $response = new WP_REST_Response([
                'updated' => false,
                'message' => 'Nothing to update'
            ]);
            $response->set_status(200);

            return $response;
        }

        $updatePost = wp_update_post([
            'ID' => $postId,
            $postTitleKey => $postTitle,
            $postContentKey => $postContent
        ], true);

        if (is_a($updatePost, 'WP_Error')) {
            return $updatePost;
        }

        $response = new WP_REST_Response($updateData);
        $response->set_status(200);

        return $response;
    }

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

        $userCanDeletePost = current_user_can('edit_posts', $postId);

        if (!$userCanDeletePost) {
            return $this->createWPError('invalid_request', 'Invalid user capabilities', 403);
        }

        $postIdsGreaterThanOrder = $this->getGalleryPostsWithOrderGreaterThan($order, $postId);

        update_post_meta($postId, $this->galleryPostOrderKey, $order);

        foreach ($postIdsGreaterThanOrder as $currentPostId) {
            update_post_meta($currentPostId, $this->galleryPostOrderKey, ++$order);
        }

        $response = new WP_REST_Response();
        $response->set_status(200);

        return $response;
    }

    public function setGalleryAttachmentOrder(WP_REST_Request $request)
    {
        $postId = $this->setRequestParams($request, 'postId');
        $attachId = $this->setRequestParams($request, 'attachId');
        $order = $this->setRequestParams($request, 'order');

        $attachIds = get_post_meta($postId, $this->galleryAttachmentMetaKey, false);
        $postIdQuery = implode(',', $attachIds);

        global $wpdb;

        $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND cast(meta_value as unsigned) >= %d AND post_id IN ($postIdQuery) AND post_id != %d ORDER BY meta_value ASC";
//        $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND cast(meta_value as unsigned) >= %d AND post_id IN ($postIdQuery) AND post_id != %d ORDER BY ABS(meta_value) ASC";
        $metaQueryResult = $wpdb->get_results($wpdb->prepare($query, $this->galleryAttachmentOrderKey, $order, $attachId), 'ARRAY_N');

        $attachIdsToBeUpdated = $this->flattenArray($metaQueryResult);

        update_post_meta($attachId, $this->galleryAttachmentOrderKey, $order);

        foreach ($attachIdsToBeUpdated as $currentAttachId) {
            update_post_meta($currentAttachId, $this->galleryAttachmentOrderKey, ++$order);
        }
    }

    protected function getGalleryPostsWithOrderGreaterThan($order, $doNotIncludePostId = null)
    {
        global $wpdb;

        $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND cast(meta_value as unsigned) >= %d AND post_id != %d ORDER BY ABS(meta_value) ASC";

        $metaQueryResult = $wpdb->get_results($wpdb->prepare($query, $this->galleryPostOrderKey, $order, $doNotIncludePostId), 'ARRAY_N');
        $postIdsGreaterThanOrder = $this->flattenArray($metaQueryResult);

        return $postIdsGreaterThanOrder;
    }
}