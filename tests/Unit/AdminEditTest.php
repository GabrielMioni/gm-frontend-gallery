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
        $files  = $setupData['files'];

        $attachmentMetaDataBeforeDelete = get_post_meta($postId, 'gm_gallery_attachment', false);

        $imageAttachmentPaths = [];

        foreach ($attachmentMetaDataBeforeDelete as $attachmentBeforeDeleteDatum) {
            $attachId = $attachmentBeforeDeleteDatum['attach_id'];
            $imagePath = get_attached_file($attachId);
            $imageAttachmentPaths[] = $imagePath;

            $this->assertTrue(file_exists($imagePath));
        }

        $statusBeforeDelete = get_post_status($postId);

        $this->assertNotFalse($statusBeforeDelete);

        $request = new WP_REST_Request('DELETE', $this->namespaced_route . '/' . $postId . '/1');
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
        $this->assertFalse(get_post_status($postId));

        foreach ($imageAttachmentPaths as $imagePath) {
            $this->assertFalse(file_exists($imagePath));
        }

        $attachmentMetaDataAfterDelete = get_post_meta($postId, 'gm_gallery_attachment', false);

        $this->assertTrue(empty($attachmentMetaDataAfterDelete));
    }
}
