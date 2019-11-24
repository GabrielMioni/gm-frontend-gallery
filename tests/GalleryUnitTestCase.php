<?php

require_once(__DIR__ . '/../gm-frontend-gallery.php');

class GalleryUnitTestCase extends WP_UnitTestCase
{
    protected $server;
    protected $namespaced_route = '/gm-frontend-gallery/v1';
    protected $image_sizes = ['thumbnail', 'medium', 'full'];
    protected $default_post_values = [];
    protected $default_user_values = [];

    public function setUp()
    {
        $this->setGalleryTestDefaults();
        parent::setUp();
        /** @var WP_REST_Server $wp_rest_server */
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server;
        do_action('rest_api_init');

        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery->createPostType();
        $gmFrontendGallery->registerApiRoutes();
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

    public function setGalleryTestDefaults()
    {
        $this->default_post_values = [
            'post_title' => 'So much default title',
            'post_content' => 'So much default content',
            'post_nonce' => wp_create_nonce('gm_gallery_submit')
        ];

        $this->default_user_values = [
            'user_login' => 'Jangles',
            'user_pass'  => 'password',
            'user_email' => 'jangles@jangler.net',
        ];
    }

    protected function createGalleryPostWithMultipleImages()
    {
        $path = $this->getPluginFilePath();
        $path .= '/tests/images';
        $files = preg_grep('/^([^.])/', scandir($path));

        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request, [
            'post_nonce' => wp_create_nonce('gm_gallery_submit'),
        ]);

        $fileUploads = [];

        foreach ($files as $file) {
            $setFile = $path . '/' . $file;

            $fileUploads[] = [
                'file' => file_get_contents($setFile),
                'name' => basename($setFile),
                'size' => filesize($setFile),
                'tmp_name' => 'abc',
                'path' => $setFile,
            ];
        }

        $request->set_file_params($fileUploads);
        $response = $this->dispatchRequest($request);

        return [
            'files' => $files,
            'response' => $response,
        ];
    }

    protected function sendGetRequest($page = null, $resultsPerPage = null, $orderBy = null, $order = null)
    {
        $getRequest  = $this->createRequestGetPaginatedGalleryItems($page, $resultsPerPage, $orderBy, $order);
        $getResponse = $this->dispatchRequest($getRequest);
        return $getResponse->get_data();
    }

    protected function requestDataProviderParams(WP_REST_Request $request, array $nonDefaultValues = [])
    {
        $setValues = $this->default_post_values;

        if (!empty($nonDefaultValues)) {
            $setValues = array_replace($setValues, $nonDefaultValues);
        }

        $request->set_param('post_title', $setValues['post_title']);
        $request->set_param('post_content', $setValues['post_content']);
        $request->set_param('post_nonce', $setValues['post_nonce']);
    }

    protected function requestDataProviderImage(WP_REST_Request $request)
    {
        $path = $this->getPluginFilePath();

        $testImagePath = $path . '/tests/images/conceited-ape.jpg';
        //example: '/app/wp-content/plugins/gm-frontend-gallery/tests/images/conceited-ape.jpg';
        $fileParams['image'] = [
            'file' => file_get_contents($testImagePath),
            'name' => 'conceited-ape.jpg',
            'size' => filesize($testImagePath),
            'tmp_name' => 'abc',
            'path' => $testImagePath,
        ];

        $request->set_file_params($fileParams);
    }

    protected function createRequestSubmitGallery()
    {
        $request =  new WP_REST_Request('POST', $this->namespaced_route . '/submit');
        $request->set_header('Content-Type', 'multipart/form-data');

        return $request;
    }

    protected function createRequestGetPaginatedGalleryItems($page = null, $results = null, $orderBy = null, $order = null)
    {
        $route = $this->namespaced_route . '/get';

        if (!is_null($page) && !is_null($results)) {
            $route .= "/$page/$results";
        }
        if (!is_null($orderBy)) {
            $route .= "/$orderBy";
        }
        if (!is_null($orderBy) && !is_null($order)) {
            $route .= "/$order";
        }

//        $request =  new WP_REST_Request('GET', $this->namespaced_route . '/get');
        $request = new WP_REST_Request('GET', $route);
        $request->set_header('Content-Type', 'application/json');

        return $request;
    }

    protected function createRequestGetSingleGalleryItem($postId)
    {
        $request = new WP_REST_Request('GET', $this->namespaced_route . '/' . $postId);
        $request->set_header('Content-Type', 'application/json');
        return $this->dispatchRequest($request);
    }

    protected function registerPluginSubmitRoute()
    {
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery->registerApiRoutes();
    }

    protected function registerPluginGetRoute()
    {
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery->registerApiGetRoute();
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