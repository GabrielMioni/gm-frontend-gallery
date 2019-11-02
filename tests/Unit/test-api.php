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
            'post_content' => 'So much default content',
            'post_nonce' => wp_create_nonce('gm_gallery_submit')
        ];

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
    public function plugin_can_register_submit_route()
    {
        $request = new WP_REST_Request('POST', $this->namespaced_route . '/submit');
        $this->requestDataProviderParams($request);
        $this->requestDataProviderImage($request);
        $response = $this->server->dispatch($request);
        $this->assertEquals(200, $response->get_status());
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

        $postThumbnailID = get_post_thumbnail_id($postID);

        $this->assertNotEquals('', $postThumbnailID);
    }

    /** @test */
    public function gallery_posts_can_be_retrieved()
    {
        $postIDs = [];
        $responseCount = 5;

        // Create some gallery Posts
        while (count($postIDs) < $responseCount) {
            $request = $this->createGalleryPostRequest();
            $this->requestDataProviderParams($request);
            $this->requestDataProviderImage($request);

            $response = $this->dispatchRequest($request);
            $postResponse = $response->get_data();
            $postID = $postResponse['postID'];
            $postIDs[] = $postID;
        }

        // Retrieve Post data from route
        $getRequest = $this->createGalleryGetRequest();
        $getResponse = $this->dispatchRequest($getRequest);
        $responseData = $getResponse->get_data();

        // Show that post data retrieved matches that which was created
        $newIds = wp_list_pluck($responseData, 'ID');
        sort($newIds);
        sort($postIDs);
        $this->assertEqualSets($newIds, $postIDs);
    }

    /** @test */
    public function gallery_posts_can_be_paginated()
    {
        $postArray = [
            'post_content' => 'I am some words for the Content',
            'post_title'   => 'I am a default title',
            'post_type'    => 'gallery',
            'post_status'  => 'published'
        ];

        $postIds = $this->factory->post->create_many(31, $postArray);

        $pages = range(1, 4);
        $resultsPerPage = 10;

        $chunkedIDs = array_chunk($postIds, $resultsPerPage);

        $paginatedIDs = [];

        foreach ($pages as $page) {
            $getRequest  = $this->createGalleryGetRequest($page, $resultsPerPage, 'id', 'asc');
            $getResponse = $this->dispatchRequest($getRequest);
            $paginatedIDs[] = wp_list_pluck($getResponse->get_data(), 'ID');
        }

        $this->assertEqualSets($chunkedIDs, $paginatedIDs);
    }

    /** @test */
    public function gallery_pagination_can_be_ordered_both_asc_and_desc()
    {
        $postArray = [
            'post_content' => 'I am some words for the Content',
            'post_title'   => 'I am a default title',
            'post_type'    => 'gallery',
            'post_status'  => 'published'
        ];

        $start = time();
        $responses = [];

        for ($t = 0 ; $t < 31 ; ++$t) {
            $args = $postArray;
            $postDate = date('Y-m-d H:i:s', $start + ($t * 1000));
            $args['post_date'] = $postDate;

            $postId = $this->factory->post->create($args);

            $responses[] = [
                'ID' => $postId,
                'post_date' => $postDate,
            ];
        }

        $pages = range(1, 4);
        $resultsPerPage = 10;

        $paginatedResponsesAscending  = [];
        $paginatedResponsesDescending = [];

        function getIdAndPostDate(array $inputArray) {
            return [
                'ID' => $inputArray['ID'],
                'post_date' => $inputArray['post_date'],
            ];
        }

        foreach ($pages as $page) {
            $ascendingResponseData  = $this->sendGetRequest($page, $resultsPerPage, 'date', 'asc');
            $descendingResponseData = $this->sendGetRequest($page, $resultsPerPage, 'date', 'desc');

            $outAscending = [];
            $outDescending = [];

            foreach ($ascendingResponseData as $key => $ascendingResponseDatum) {
                $outAscending[]  = getIdAndPostDate($ascendingResponseDatum);
                $outDescending[] = getIdAndPostDate($descendingResponseData[$key]);
            }

            $paginatedResponsesAscending[] = $outAscending;
            $paginatedResponsesDescending[] = $outDescending;
        }

        $chunkedResponseAscending  = array_chunk($responses, $resultsPerPage);
        $chunkedResponseDescending = array_chunk(array_reverse($responses), $resultsPerPage);

        $this->assertEqualSets($chunkedResponseAscending, $paginatedResponsesAscending);
        $this->assertEqualSets($chunkedResponseDescending, $paginatedResponsesDescending);
    }

    protected function sendGetRequest($page = null, $resultsPerPage = null, $orderBy = null, $order = null)
    {
        $getRequest  = $this->createGalleryGetRequest($page, $resultsPerPage, $orderBy, $order);
        $getResponse = $this->dispatchRequest($getRequest);
        return $getResponse->get_data();
    }

    protected function requestDataProviderParams(WP_REST_Request $request, array $nonDefaultValues = [])
    {
        $setValues = $this->default_request_values;

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

    protected function createGalleryPostRequest()
    {
        $request =  new WP_REST_Request('POST', $this->namespaced_route . '/submit');
        $request->set_header('Content-Type', 'multipart/form-data');

        return $request;
    }

    protected function createGalleryGetRequest($page = null, $results = null, $orderBy = null, $order = null)
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

    protected function registerPluginSubmitRoute()
    {
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery::registerApiSubmitRoute();
    }

    protected function registerPluginGetRoute()
    {
        $gmFrontendGallery = new gmFrontendGallery();
        $gmFrontendGallery::registerApiGetRoute();
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
