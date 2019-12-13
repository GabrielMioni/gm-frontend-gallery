<?php

namespace GmFrontendGallery\Controller;

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

        var_dump($postIds);
    }
}