<?php

require_once dirname( dirname( __FILE__ ) ) . '/galleryTestTrait.php';

class submitTest extends WP_UnitTestCase
{
    use galleryTestTrait;

    public function setUp()
    {
        $this->setGalleryTestTraitDefaults();
        parent::setUp();
        /** @var WP_REST_Server $wp_rest_server */
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;
        do_action('rest_api_init');

        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery::createPostType();
        $gmFrontendGallery::registerApiGetRoute();
        $gmFrontendGallery::registerApiSubmitRoute();
    }

    public function tearDown()
    {
        parent::tearDown();

        $uploadDirectory = wp_get_upload_dir();
        $uploadDirectory = $uploadDirectory['basedir'];

        $dirs = glob($uploadDirectory . '/*');

        foreach ($dirs as $dir) {
            system('rm -rf ' . escapeshellarg($dir), $retval);
        }
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
