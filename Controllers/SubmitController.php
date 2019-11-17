<?php

namespace GmFrontendGallery\Controller;

require_once('BaseController.php');

use WP_REST_Request;
use WP_REST_Response;

class SubmitController extends BaseController
{
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
}