<?php


class ApiTest extends WP_UnitTestCase
{
    protected $server;
    protected $namespaced_route = 'gm-frontend-gallery/v1';

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
        $request = new WP_REST_Request('POST', '/gm-frontend-gallery/v1/submit');
        $response = $this->server->dispatch($request);
        $this->assertEquals(200, $response->get_status());
    }

    protected function registerPluginSubmitRoute()
    {
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery::registerApiSubmitRoute();
    }
}
