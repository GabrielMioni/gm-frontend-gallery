<?php

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

        $imageAttachmentPaths = gmFrontendGallery::getAttachmentImagePaths($postId);

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

        $attachmentMetaDataAfterDelete = get_post_meta($postId, 'gm_gallery_attachment', false);

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
        $request = new WP_REST_Request('DELETE', $this->namespaced_route . '/' . $postId . '/' . $randomAttachmentId);
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $attachmentIdsAfterDelete = $this->getAttachmentIds($postId);

        $this->assertEquals(200, $response->get_status());
        $this->assertEqualSets($attachmentIdsAfterDelete, $expectedIdsAfterDelete);
    }

    protected function getAttachmentIds($postId) {
        $response = $this->createRequestGetSingleGalleryItem($postId);
        $newGalleryItem = $response->get_data();
        $attachedImages = $newGalleryItem['images'];

        return array_map(function ($item) {
            return $item['attach_id'];
        }, $attachedImages);
    }
}
