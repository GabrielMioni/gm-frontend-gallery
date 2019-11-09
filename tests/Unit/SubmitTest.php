<?php

require_once dirname(dirname(__FILE__)) . '/GalleryUnitTestCase.php';

class SubmitTest extends GalleryUnitTestCase
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
    public function file_data_can_be_submitted_to_api()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProviderParams($request);
        $this->requestDataProviderImage($request);
        $response = $this->dispatchRequest($request);
        $this->assertEquals(200, $response->get_status());
    }

    /** @test */
    public function gallery_posts_can_be_submitted_with_multiple_images()
    {
        $postResponse  = $this->createGalleryPostWithMultipleImages();
        $responseFiles = $postResponse['files'];
        $responseData  = $postResponse['response']->get_data();

        $postID = $responseData['postID'];
        $postAttachmentData = get_post_meta($postID, 'gm_gallery_attachment', false);

        $postImages = [];

        foreach ($postAttachmentData as $postAttachmentDatum) {
            $attachId = $postAttachmentDatum['attach_id'];
            $postImages[] = basename(wp_get_attachment_image_url($attachId, 'full'));
        }

        sort($postImages);
        sort($responseFiles);

        $this->assertEqualSets($postImages, $responseFiles);
    }

    /** @test */
    public function plug_can_create_gallery_post_item()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProviderParams($request);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);

        $postResponse = $response->get_data();

        $newPost = get_post($postResponse['postID']);
        $this->assertNotNull($newPost);
    }

    /** @test */
    public function gallery_submissions_require_title_and_content()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProviderParams($request, [
            'post_title' => '',
            'post_content' => ''
        ]);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(404, $response->get_status());

        $responseData = $response->get_data();
        $this->assertTrue(isset($responseData['code']));
    }

    /** @test */
    public function gallery_submissions_require_image()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProviderParams($request);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(404, $response->get_status());
    }

    /** @test */
    public function gallery_submissions_require_nonce()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProviderParams($request, [
            'post_nonce' => 'I am an invalid nonce, nice to meet you!'
        ]);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(401, $response->get_status());
    }

    /** @test */
    public function gallery_submissions_include_image_attachments()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProviderParams($request);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);

        $postResponse = $response->get_data();
        $postID = $postResponse['postID'];

        $postAttachmentData = get_post_meta($postID, 'gm_gallery_attachment', false);
        $postAttachmentId = $postAttachmentData[0]['attach_id'];

        $this->assertNotEquals('', $postAttachmentId);
    }
}
