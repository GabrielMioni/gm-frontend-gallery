<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;
use WP_Error;
use WP_Post;

abstract class BaseController
{
    protected static $postType = 'gallery';
    protected static $postStatus = 'published';
    protected static $galleryIncompleteCode = 'gallery_incomplete';
    protected static $galleryAttachmentMetaKey = 'gm_gallery_attachment';

    protected static function setRequestParams(WP_REST_Request $request, $key)
    {
        $parameter = trim($request->get_param($key));
        return $parameter !== '' ? $parameter : null;
    }

    protected static function createWPError($code, $message, $status)
    {
        return new WP_Error($code, $message, ['status' => $status]);
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

    public static function getAttachmentImagePaths($postId)
    {
        $galleryAttachmentMeta = get_post_meta($postId, 'gm_gallery_attachment', false);
        $uploadDir = wp_upload_dir();
        $uploadBaseDir = $uploadDir['basedir'];

        $imageAttachmentPaths = [];

        foreach ($galleryAttachmentMeta as $meta) {
            $attachId = $meta['attach_id'];
            $attachmentMetaData = wp_get_attachment_metadata($attachId);

            $origFilePath = $uploadBaseDir . '/' . $attachmentMetaData['file'];
            $fileBasename = basename($origFilePath);

            $imageRootPath = str_replace($fileBasename, '', $origFilePath);

            $imageAttachmentPaths[] = $origFilePath;

            foreach ($attachmentMetaData['sizes'] as $size) {
                $imageAttachmentPaths[] = $imageRootPath . $size['file'];
            }
        }

        return $imageAttachmentPaths;
    }

    protected static function getCompleteMetaData($postId, $metaKey, $attachmentId = null) {

        if ($attachmentId !== null)
            $attachmentId = (int) $attachmentId;

        global $wpdb;
        $meta = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s", $postId, $metaKey) );

        if (empty($meta)) {
            return false;
        }

        foreach ($meta as $m) {
            $m->meta_value = isset($m->meta_value) ? maybe_unserialize($m->meta_value) : null;
            $checkAttachId = isset($m->meta_value['attach_id']) ? $m->meta_value['attach_id'] : false;

            if ($checkAttachId === $attachmentId) {
                return $m;
            }
        }

        if ($attachmentId !== null) {
            // Couldn't find the meta data with the attachment id.
            return false;
        }

        return $meta;
    }

    protected static function getAttachmentPathsById($attachId) {
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