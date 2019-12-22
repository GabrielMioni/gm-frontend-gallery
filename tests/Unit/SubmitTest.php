<?php

use GmFrontendGallery\definitionsTrait;

require_once dirname(dirname(__FILE__)) . '/GalleryUnitTestCase.php';

class SubmitTest extends GalleryUnitTestCase
{
    use definitionsTrait;

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
        $request = $this->createRequestSubmitGallery();
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
        $postID  = $postResponse['response']->get_data();

        $attachmentIds = get_post_meta($postID, $this->galleryAttachmentMetaKey, false);

        $postImages = [];

        foreach ($attachmentIds as $attachmentId) {
            $postImages[] = basename(wp_get_attachment_image_url($attachmentId, 'full'));
        }

        sort($postImages);
        sort($responseFiles);

        $this->assertEqualSets($postImages, $responseFiles);
    }

    /** @test */
    public function plug_can_create_gallery_post_item()
    {
        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);

        $postId = $response->get_data();

        $newPost = get_post($postId);
        $this->assertNotNull($newPost);
    }

    /** @test */
    public function gallery_submissions_require_title()
    {
        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request, [
            'mainTitle' => '',
        ]);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(404, $response->get_status());

        $responseData = $response->get_data();
        $this->assertTrue(isset($responseData['code']));
    }

    /** @test */
    public function gallery_submissions_require_image()
    {
        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(404, $response->get_status());
    }

    /** @test */
    public function gallery_submissions_require_nonce()
    {
        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request, [
            'postNonce' => 'I am an invalid nonce, nice to meet you!'
        ]);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(401, $response->get_status());
    }

    /** @test */
    public function gallery_submissions_include_image_attachments()
    {
        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);

        $postID = $response->get_data();

        $attachmentIds = get_post_meta($postID, $this->galleryAttachmentMetaKey, false);

        $attachmentId = (int) $attachmentIds[0];

        $attachmentPost = get_post($attachmentId);

        $this->assertFalse(is_null($attachmentPost));
    }
}
