<?php

namespace GmFrontendGallery\Controller;

include_once( ABSPATH . 'wp-admin/includes/image.php' );

class TestController extends BaseController
{
    public function index()
    {
        $postIds = [];

        $start = time();
        $milliseconds = 1000;

        while (count($postIds) < 31) {
            $id = wp_insert_post([
                'post_content' => 'I am some words for the Content',
                'post_title'   => 'I am a default title',
                'post_type'    => $this->galleryPostType,
                'post_status'  => $this->galleryPostStatus,
                'post_date'    => date('Y-m-d H:i:s', $start + (count($postIds) * $milliseconds)),
                'meta_input' => [
                    $this->galleryPostOrderKey => count($postIds),
                ]
            ]);
            $postIds[] = $id;
        }

        foreach ($postIds as $postId) {
            $this->processImageAttachments($postId);
        }

        var_dump($postIds);
    }

    protected function processImageAttachments($postId)
    {
        $maxAttachments = 5;
        $attachmentIds = [];
        $order = 0;

        $path = plugin_dir_path(__DIR__) . 'tests/images';
        $files = preg_grep('/^([^.])/', scandir($path));

        foreach ($files as $file) {

            if (count($attachmentIds) > $maxAttachments) {
                break;
            }

            $setFile = $path . '/' . $file;

            $imageDatum = [
                'file' => file_get_contents($setFile),
                'name' => basename($setFile),
                'size' => filesize($setFile),
                'tmp_name' => 'abc',
                'path' => $setFile,
            ];

            $attachmentIds[] = $this->createImageAttachment($imageDatum, $postId, $order);
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
}