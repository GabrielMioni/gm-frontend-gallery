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

        /*$postNonce          = $this->setRequestParams($request, 'postNonce');
        if (!wp_verify_nonce($postNonce, $this->gallerySubmitNonce)) {
            return $this->createWPError('invalid_request', 'Invalid nonce', 401);
        }*/

        $mainTitle          = $this->setRequestParams($request, 'mainTitle');
        $attachmentContents = json_decode($this->setRequestParams($request, 'attachmentContents'));
        $files              = $request->get_file_params();
        $imageData          = $files['image_files'];

        if (is_null($mainTitle) || trim($mainTitle) === '') {
            return $this->createWPError($this->galleryIncompleteCode, 'Gallery submissions must include a title and content', 404);
        }

        $newPostId = wp_insert_post([
            'post_title' => $mainTitle,
            'post_content' => '',
            'post_type' => $this->galleryPostType,
            'post_status' => $this->setPostStatus(),
        ], true);

        $this->setGalleryPostOrderMetaData($newPostId);

        $attachmentIds = [];

        foreach ($attachmentContents as $key => $content) {
            $attachmentIds = $this->createImageAttachment($imageData, $key, $newPostId, count($attachmentIds), $content);
        }

        $response = new WP_REST_Response($newPostId);
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

        $mimesAreValid = $this->validateUploadMimes($imageData);

        if ($mimesAreValid instanceof WP_Error) {
            return $mimesAreValid;
        }

        $maxAttachments = $this->getSettingMaxAttachments();

        foreach ($imageData as $imageDatum) {
            $attachmentIds[] = $this->createImageAttachment($imageDatum, $postId, $order);
            ++$order;

            if (count($attachmentIds) >= $maxAttachments) {
                break;
            }
        }

        return $attachmentIds;
    }

    protected function validateUploadMimes(array $imageData)
    {
        $allowedMimes = (array) $this->getGalleryOption('allowed_mimes');

        foreach ($imageData as $imageDatum) {
            $path = $imageDatum['path'];
            $mimeContent = mime_content_type($path);
            if (!in_array($mimeContent, $allowedMimes)) {
                return $this->createWPError('invalid_mime', 'Invalid mime type', 400);
            }
        }

        return true;
    }

    protected function getSettingMaxAttachments()
    {
        $maxAttachments = $this->getGalleryOption('max_attachments');

        if ($maxAttachments instanceof WP_Error) {
            return $maxAttachments;
        }

        $maxAttachments = (int) $maxAttachments;

        if ($maxAttachments > $this->defaultOptions['max_attachments'])
            return $this->defaultOptions['max_attachments'];

        if ($maxAttachments < 1) {
            return 1;
        }

        return $maxAttachments;
    }

    protected function setPostStatus()
    {
        $userIsAdmin = $this->currentUserIsAdmin();

        if ($userIsAdmin === true) {
            return $this->galleryPostStatus;
        }

        $adminMustApprove = (bool) $this->getGalleryOption('admin_must_approve');

        if ($adminMustApprove === false) {
            return $this->galleryPostStatus;
        }

        return 'draft';
    }

    protected function createImageAttachment(array $imageData, int $key, int $postId, int $attachmentOrder, $content)
    {
        $name = $imageData['name'][$key];
        $tmp_name  = $imageData['tmp_name'][$key];
        $imageData = file_get_contents($tmp_name);

        $uploadData = wp_upload_bits($name, null, $imageData);

        $file_path = $uploadData['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name, null);
        $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
        $wp_upload_dir = wp_upload_dir();

        $post_info = [
            'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title'     => $attachment_title,
            'post_content'   => $content,
            'post_status'    => 'inherit',
        ];

        $attach_id = wp_insert_attachment( $post_info, $file_path, $postId );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );

        wp_update_attachment_metadata($attach_id, $attach_data);

        add_post_meta($postId, $this->galleryAttachmentMetaKey, $attach_id, false);
        add_post_meta($attach_id, $this->galleryAttachmentOrderKey, $attachmentOrder, true);

        return $attach_id;
    }

    /*protected function createImageAttachment2(array $imageData, $postId, $attachmentOrder)
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
    }*/

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