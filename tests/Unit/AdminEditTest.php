<?php

use GmFrontendGallery\Controller\AdminController;

require_once dirname(dirname(__FILE__)) . '/GalleryUnitTestCase.php';

class AdminEditTest extends GalleryUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    protected function setup_for_trash_and_delete_tests()
    {
        $postResponse  = $this->createGalleryPostWithMultipleImages();
        $responseFiles = $postResponse['files'];
        $responseData  = $postResponse['response']->get_data();
        $postId        = $responseData['postID'];

        $userId = $this->factory()->user->create($this->default_user_values);
        $user = get_user_by( 'id', $userId);
        $user->set_role('administrator');
        wp_set_current_user($user->ID, $user->user_login);

        return [
            'postId' => $postId,
            'postStatus' => get_post_status($postId),
            'files' => $responseFiles,
            'responseData' => $responseData
        ];
    }

    /** @test */
    public function gallery_posts_and_images_can_be_trashed()
    {
        $setupData = $this->setup_for_trash_and_delete_tests();
        $postId = $setupData['postId'];
        $statusBeforeDelete = $setupData['postStatus'];

        $this->assertNotFalse($statusBeforeDelete);

        $request = new WP_REST_Request('DELETE', $this->namespaced_route . '/' . $postId);
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals('trash', get_post_status($postId));
    }

    /** @test */
    public function gallery_post_and_attachments_can_be_deleted_permanently()
    {
        $setupData = $this->setup_for_trash_and_delete_tests();
        $postId = $setupData['postId'];

        $adminController = new AdminController();

        $imageAttachmentPaths = $adminController->getAttachmentImagePaths($postId);

        $statusBeforeDelete = get_post_status($postId);

        $this->assertNotFalse($statusBeforeDelete);

        // Permanently delete the post and all associated attachments.
        $request = new WP_REST_Request('DELETE', $this->namespaced_route . '/' . $postId . '/1');
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
        $this->assertFalse(get_post_status($postId));

        // Check attachments have been removed
        foreach ($imageAttachmentPaths as $imagePath) {
            $this->assertFalse(file_exists($imagePath));
        }

        $attachmentMetaDataAfterDelete = get_post_meta($postId, $this->galleryAttachmentMetaKey, false);

        $this->assertTrue(empty($attachmentMetaDataAfterDelete));
    }

    /** @test */
    public function individual_attachments_can_be_deleted()
    {
        $setupData = $this->setup_for_trash_and_delete_tests();
        $postId = $setupData['postId'];

        $attachmentIdsBeforeDelete = $this->getAttachmentIds($postId);
        $randomAttachmentId = $attachmentIdsBeforeDelete[rand(0, count($attachmentIdsBeforeDelete)-1)];

        $expectedIdsAfterDelete = $attachmentIdsBeforeDelete;
        if (($key = array_search($randomAttachmentId, $expectedIdsAfterDelete)) !== false) {
            unset($expectedIdsAfterDelete[$key]);
            $expectedIdsAfterDelete = array_values($expectedIdsAfterDelete);
        }

        // Delete the random attachment
        $request = new WP_REST_Request('DELETE', $this->namespaced_route . '/image/' . $postId . '/' . $randomAttachmentId);
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $attachmentIdsAfterDelete = $this->getAttachmentIds($postId);

        $this->assertEquals(200, $response->get_status());
        $this->assertEqualSets($attachmentIdsAfterDelete, $expectedIdsAfterDelete);
    }

    /** @test */
    public function gallery_posts_order_meta_data_can_be_changed()
    {
        $order = 0;
        $postIds = [];
        $originalOrder = [];

        while($order < 10) {
            $postIds[] = $this->factory()->post->create([
                'post_content' => 'I am some words for the Content',
                'post_title'   => 'I am a default title',
                'post_type'    => 'gallery',
                'post_status'  => $this->galleryPostStatus,
                'meta_input' => [
                    $this->galleryPostOrderKey => $order,
                ]
            ]);
            $originalOrder[] = $order;
            ++$order;
        }

        $lastPostId = $postIds[count($postIds)-1];
        $lastPostOrder = get_post_meta($lastPostId, $this->galleryPostOrderKey, true);

        $this->assertEquals($order-1, $lastPostOrder);

        $request = new WP_REST_Request('POST', $this->namespaced_route . '/order/post/' . $lastPostId . '/' . '5');
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());

        $lastPostOrderNew = get_post_meta($lastPostId, $this->galleryPostOrderKey, true);

        $newOrder = [];

        foreach ($postIds as $postId) {
            $orderMeta = get_post_meta($postId, $this->galleryPostOrderKey, true);
            $newOrder[] = (int) $orderMeta[0];
        }

        $expectedOrder = $originalOrder;
        unset($expectedOrder[5]);
        $expectedOrder[] = 5;
        $expectedOrder = array_values($expectedOrder);

        $this->assertEqualSets($expectedOrder, $newOrder);
        $this->assertEquals(5, $lastPostOrderNew);
    }

    /** @test */
    public function gallery_attachments_order_meta_data_can_be_changed()
    {
        $data = $this->createGalleryPostWithMultipleImages();
        $createGalleryPostResponse = $data['response'];
        $newGalleryPostData = $createGalleryPostResponse->get_data();

        $postId = $newGalleryPostData['postID'];
        $attachmentIds = get_post_meta($postId, $this->galleryAttachmentMetaKey, false);

        $originalOrder = [];

        foreach ($attachmentIds as $attachmentId) {
            $order = get_post_meta($attachmentId, 'gm_gallery_attachment_order', true);
            $originalOrder[] = $order;
        }

        $lastAttachmentId = $attachmentIds[count($attachmentIds)-1];
        $setAttachmentIdOrder = 2;

        $request = new WP_REST_Request('POST', $this->namespaced_route . '/order/attachment/' . $postId . '/' . $lastAttachmentId . '/' . $setAttachmentIdOrder);
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());

        $newOrder = [];

        foreach ($attachmentIds as $attachmentId) {
            $order = get_post_meta($attachmentId, 'gm_gallery_attachment_order', true);
            $newOrder[] = $order;
        }

        $expectedOrder = $originalOrder;
        unset($expectedOrder[$setAttachmentIdOrder]);
        $expectedOrder[] = $setAttachmentIdOrder;
        $expectedOrder = array_values($expectedOrder);

        $lastAttachmentOrderNew = get_post_meta($lastAttachmentId, 'gm_gallery_attachment_order', true);

        $this->assertEqualSets($expectedOrder, $newOrder);
        $this->assertEquals($setAttachmentIdOrder, $lastAttachmentOrderNew);
    }

    protected function getAttachmentIds($postId)
    {
        $response = $this->createRequestGetSingleGalleryItem($postId);
        $newGalleryItem = $response->get_data();
        $attachedImages = $newGalleryItem['images'];

        return array_map(function ($item) {
            return $item['attach_id'];
        }, $attachedImages);
    }
}
