<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;
use WP_Error;
use WP_Post;

abstract class BaseController
{
    use \definitionsTrait;

    protected function setRequestParams(WP_REST_Request $request, $key)
    {
        $parameter = $request->get_param($key);
        if (is_string($parameter)) {
            $parameter = trim($parameter);
        }
        return $parameter !== '' ? $parameter : null;
    }

    /**
     * @param $code
     * @param $message
     * @param $status
     * @return WP_Error
     */
    protected function createWPError($code, $message, $status)
    {
        return new WP_Error($code, $message, ['status' => $status]);
    }

    protected function retrieveGalleryImages(WP_Post $post)
    {
        $images = [];
        $sizes = ['thumbnail', 'medium', 'full'];

        $attachmentIds = get_post_meta($post->ID, $this->galleryAttachmentMetaKey, false);

        foreach ($attachmentIds as $attachId) {
            $sizedImages = [];

            foreach ($sizes as $size) {
                $postImage = wp_get_attachment_image_url($attachId, $size);
                $sizedImages[$size] = $postImage !== false ? $postImage : null;
            }

            $images[] = [
                'attach_id' => $attachId,
                'sized_images' => $sizedImages,
                'order' => get_post_meta($attachId, $this->galleryAttachmentOrderKey, true),
            ];
        }

        return $images;
    }

    public function getAttachmentImagePaths($postId)
    {
        $attachmentIds = get_post_meta($postId, 'gm_gallery_attachment', false);

        $imageAttachmentPaths = [];

        foreach ($attachmentIds as $attachId) {
            $attachmentPaths = $this->getPathsByAttachId($attachId);
            $imageAttachmentPaths = array_merge($imageAttachmentPaths, $attachmentPaths);
        }

        return $imageAttachmentPaths;
    }

    protected function getPathsByAttachId($attachId)
    {
        $uploadDir = wp_upload_dir();
        $uploadBaseDir = $uploadDir['basedir'];

        $imageAttachmentPaths = [];

        $attachmentMetaData = wp_get_attachment_metadata($attachId);

        $origFilePath = $uploadBaseDir . '/' . $attachmentMetaData['file'];
        $fileBasename = basename($origFilePath);

        $imageRootPath = str_replace($fileBasename, '', $origFilePath);

        $imageAttachmentPaths[] = $origFilePath;

        foreach ($attachmentMetaData['sizes'] as $size) {
            $imageAttachmentPaths[] = $imageRootPath . $size['file'];
        }

        return $imageAttachmentPaths;
    }

    protected function getCompleteMetaData($postId, $metaKey, $attachmentId) {

        global $wpdb;
        $query = "SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s AND meta_value = %d";
        $metaData = $wpdb->get_results( $wpdb->prepare($query, $postId, $metaKey, $attachmentId) );

        if (empty($metaData)) {
            return false;
        }

        return $metaData;
    }

    protected function multiPluck($input, array $indexKeys)
    {
        $out = [];

        if (is_object($input))
            $input = get_object_vars($input);

        foreach ($indexKeys as $indexKey) {
            $out[$indexKey] = $input[$indexKey];
        }

        return $out;
    }

    protected function flattenArray(array $array)
    {
        $out = [];

        array_walk_recursive($array, function($a) use (&$out) {
            $out[] = $a;
        });

        return $out;
    }

    protected function currentUserIsAdmin()
    {
        $user = wp_get_current_user();
        return in_array('administrator', (array) $user->roles);
    }

    protected function getGalleryOption($optionKey = null)
    {
        $options = get_option($this->pluginOptionName);

        if ($optionKey === null) {
            return $options;
        }

        if (isset($options[$optionKey])) {
            return $options[$optionKey];
        }

        return $this->createWPError('invalid_request', 'No options found', 500);
    }
}