<?php

namespace GmFrontendGallery\Controller;

require_once('BaseController.php');

use WP_REST_Request;
use WP_REST_Response;

class AdminController extends BaseController
{
    public static function deleteGalleryPostById(WP_REST_Request $request)
    {
        $postId = self::setRequestParams($request, 'postId');
        $permanent = (int) self::setRequestParams($request, 'permanent') === 1;

        $userCanDeletePost = current_user_can('delete_posts', $postId);

        if (!$userCanDeletePost) {
            return self::createWPError('invalid_request', 'Invalid user capabilities', 403);
        }

        $attachmentPaths = $permanent === true ? self::getAttachmentImagePaths($postId) : [];

        $deletedPost = $permanent !== true ? wp_trash_post($postId) : wp_delete_post($postId, true);

        if (!$permanent && !is_a($deletedPost, 'WP_POST')) {
            $action = $permanent === true ? 'deleted' : 'trashed';
            return self::createWPError('invalid_request', 'Post was not ' . $action, 500);
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

    public static function deleteAttachmentById(WP_REST_Request $request)
    {
        $postId = self::setRequestParams($request, 'postId');
        $attachmentId = self::setRequestParams($request, 'attachmentId');

        $metaData = self::getCompleteMetaData($postId, 'gm_gallery_attachment', $attachmentId);

        if (!$metaData) {
            return self::createWPError('invalid_request', 'attach_id not found', 400);
        }

        $metaId = $metaData->meta_id;
        $attachmentId = $metaData->meta_value['attach_id'];

        $paths = self::getAttachmentPathsById($attachmentId);

        foreach ($paths as $path) {
            unlink($path);
        }

        delete_metadata_by_mid('post', $metaId);

        $response = new WP_REST_Response();
        $response->set_status(200);

        return $response;
    }
}