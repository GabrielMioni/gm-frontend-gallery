<?php


class ApiTest extends WP_UnitTestCase
{
    protected $server;
    protected $namespaced_route = '/gm-frontend-gallery/v1';

    public function setUp()
    {
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

        $request = new WP_REST_Request('POST', $this->namespaced_route . '/submit');
        $request->set_header('Content-Type', 'multipart/form-data');
        $request->set_param('data', 'so much fun');
        $request->set_file_params($fileParams);
        $response = $this->server->dispatch($request);
        $this->assertEquals(200, $response->get_status());
    }

    protected function registerPluginSubmitRoute()
    {
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery::registerApiSubmitRoute();
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
