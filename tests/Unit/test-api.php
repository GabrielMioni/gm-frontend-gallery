<?php


class ApiTest extends WP_UnitTestCase
{
    protected $server;
    protected $namespaced_route = '/gm-frontend-gallery/v1';
    protected $default_request_values = [];

    public function setUp()
    {
        $this->default_request_values = [
            'post_title' => 'So much default title',
            'post_content' => 'So much default content'
        ];

        parent::setUp();
        /** @var WP_REST_Server $wp_rest_server */
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;
        do_action('rest_api_init');

        $this->registerPluginSubmitRoute();
    }

    /** @test */
    public function plugin_can_register_submit_route()
    {
        $request = new WP_REST_Request('POST', $this->namespaced_route . '/submit');
        $this->requestDataProvider($request);
        $response = $this->server->dispatch($request);
        $this->assertEquals(200, $response->get_status());
    }

    /** @test */
    public function file_data_can_be_submitted_to_api()
    {
        $path = $this->getPluginFilePath();

        $testImagePath = $path . '/tests/images/conceited-ape.jpg';
        //example: '/app/wp-content/plugins/gm-frontend-gallery/tests/images/conceited-ape.jpg';
        $fileParams['file'] = [
            'file' => file_get_contents($testImagePath),
            'name' => 'conceited-ape.jpg',
            'size' => filesize($testImagePath),
            'tmp_name' => 'abc',
        ];

        $request = $this->createGalleryPostRequest();
        $this->requestDataProvider($request);
        $request->set_file_params($fileParams);
        $response = $this->dispatchRequest($request);
        $this->assertEquals(200, $response->get_status());
    }

    /** @test */
    public function plug_can_create_gallery_post_item()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProvider($request);

        $response = $this->dispatchRequest($request);

        $postResponse = $response->get_data();

        $newPost = get_post($postResponse['postID']);
        $this->assertNotNull($newPost);
    }

    /** @test */
    public function gallery_submissions_must_include_title_and_content()
    {
        $request = $this->createGalleryPostRequest();
        $this->requestDataProvider($request, [
            'post_title' => '',
            'post_content' => ''
        ]);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(404, $response->get_status());

        $responseData = $response->get_data();
        $this->assertTrue(isset($responseData['code']));
    }

    protected function requestDataProvider(WP_REST_Request $request, array $nonDefaultValues = [])
    {
        $setValues = $this->default_request_values;

        if (!empty($nonDefaultValues)) {
            $setValues = array_replace($setValues, $nonDefaultValues);
        }

        $request->set_param('post_title', $setValues['post_title']);
        $request->set_param('post_content', $setValues['post_content']);
    }

    protected function createGalleryPostRequest()
    {
        $request =  new WP_REST_Request('POST', $this->namespaced_route . '/submit');
        $request->set_header('Content-Type', 'multipart/form-data');

        return $request;
    }

    protected function registerPluginSubmitRoute()
    {
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery::registerApiSubmitRoute();
    }

    protected function dispatchRequest(WP_REST_Request $request)
    {
        return $this->server->dispatch($request);
    }

    protected function getPluginFilePath()
    {
        $path = plugin_dir_path(__FILE__);
        preg_match('/^(.*?)gm-frontend-gallery/', $path, $match);

        if (empty($match)) {
            return false;
        }
        return $match[0];
    }
}
