<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class SubmitController extends BaseController
{
    public function processGallerySubmission(WP_REST_Request $request)
    {
        $userIsRequired = $this->userIsRequiredForGalleryPosts();

        if ($userIsRequired instanceof WP_Error) {
            return $userIsRequired;
        }

        if ($userIsRequired === false) {
            $nonceParam   = $this->setRequestParams($request, 'post_nonce');
            $nonceIsValid = wp_verify_nonce($nonceParam, 'gm_gallery_submit');

            if ($nonceIsValid === false) {
                return $this->createWPError('invalid_request', 'Invalid nonce', 401);
            }
        }

        $post_title   = $this->setRequestParams($request, 'post_title');
        $post_content = $this->setRequestParams($request, 'post_content');

        if (is_null($post_title) || is_null($post_content)) {
            return $this->createWPError($this->galleryIncompleteCode, 'Gallery submissions must include a title and content', 404);
        }

        $imageData = $request->get_file_params();

        if (empty($imageData)) {
            return $this->createWPError($this->galleryIncompleteCode, 'Gallery submissions must include an image', 404);
        }

        $postArray = [];
        $postArray['post_content'] = $post_content;
        $postArray['post_title']   = $post_title;
        $postArray['post_type']    = $this->galleryPostType;
        $postArray['post_status']  = $this->galleryPostStatus;

        $newPostId = wp_insert_post($postArray, true);

        $data = [];

        if (!is_wp_error($newPostId)) {
            $data['postID'] = $newPostId;
        }

        $this->setGalleryPostOrderMetaData($newPostId);

        $attachmentIds = $this->processImageAttachments($imageData, $newPostId);

        $response = new WP_REST_Response($data);
        $response->set_status(200);

        return $response;
    }

    protected function userIsRequiredForGalleryPosts()
    {
        $userIsRequired = $this->getGalleryOption('user_required');

        if ($userIsRequired === false) {
            return false;
        }

        if ($userIsRequired instanceof WP_Error) {
            return $userIsRequired;
        }

        if ($userIsRequired === true) {
            $currentUser = wp_get_current_user();
            if ($currentUser->ID > 0) {
                return true;
            }

            return $this->createWPError('invalid_request', 'User is required', 401);
        }

        return true;
    }

    protected function processImageAttachments(array $imageData, $postId)
    {
        $attachmentIds = [];
        $order = 0;

        foreach ($imageData as $imageDatum) {
            $attachmentIds[] = $this->createImageAttachment($imageDatum, $postId, $order);
            ++$order;
        }

        return $attachmentIds;

    }

    protected function createImageAttachment(array $imageData, $postId, $attachmentOrder)
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

        add_post_meta($postId, $this->galleryAttachmentMetaKey, $attach_id, false);
        add_post_meta($attach_id, $this->galleryAttachmentOrderKey, $attachmentOrder, true);

        return $attach_id;
    }

    protected function setGalleryPostOrderMetaData($postId)
    {
        global $wpdb;

        $querySelect = 'max(cast(meta_value as unsigned))';
        $metaQueryResult = $wpdb->get_results($wpdb->prepare("SELECT $querySelect FROM $wpdb->postmeta WHERE meta_key = %s", $this->galleryPostOrderKey), 'ARRAY_A');
        $metaQueryValue = $metaQueryResult[0][$querySelect];

        $maxOrder = (int) $metaQueryValue;
        $setOrder = $maxOrder +1;

        add_post_meta($postId, $this->galleryPostOrderKey, $setOrder, false);
    }
}