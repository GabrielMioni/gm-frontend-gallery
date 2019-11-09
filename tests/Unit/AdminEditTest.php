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

    /** @test */
    public function gallery_posts_and_images_can_be_trashed()
    {
        $postResponse  = $this->createGalleryPostWithMultipleImages();
        $responseFiles = $postResponse['files'];
        $responseData  = $postResponse['response']->get_data();

        $postId = $responseData['postID'];
        $statusBeforeDelete = get_post_status($postId);

        $this->assertNotFalse($statusBeforeDelete);

        $userId = $this->factory()->user->create($this->default_user_values);
        $user = get_user_by( 'id', $userId);
        $user->set_role('administrator');
        wp_set_current_user($user->ID, $user->user_login);

        $request = new WP_REST_Request('DELETE', $this->namespaced_route . '/' . $postId);
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals('trash', get_post_status($postId));
    }
}
