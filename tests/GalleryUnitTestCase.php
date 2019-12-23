<?php

use GmFrontendGallery\definitionsTrait;
use GmFrontendGallery\gmFrontendGallery;

class GalleryUnitTestCase extends WP_UnitTestCase
{
    use definitionsTrait;

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
        $gmFrontendGallery->registerPostType();
        $gmFrontendGallery->createOptions();
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
            'mainTitle' => 'So much default title',
            'attachmentContents' => json_encode(['I am some content']),
            'postNonce' => wp_create_nonce($this->gallerySubmitNonce)
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
        $nonce = wp_create_nonce($this->gallerySubmitNonce);

        $request = $this->createRequestSubmitGallery();
        $request->set_header('X-WP-Nonce', $nonce);

        $fileUploads = $this->createFileUploadData(5);

        $attachmentContents = [];

        for ($c = 0 ; $c < count($fileUploads) ; ++$c) {
            $attachmentContents[] = 'This is some content ' . $c;
        }

        $request->set_file_params(['image_files' => $fileUploads]);
        $request->set_param('mainTitle', 'Default title');
        $request->set_param('attachmentContents', json_encode($attachmentContents));
        $response = $this->dispatchRequest($request);

        return [
            'files' => $files,
            'response' => $response,
            'postId' => $response->data,
        ];
    }

    protected function sendGetRequest($page = null, $resultsPerPage = null, $orderBy = null, $order = null)
    {
        $getRequest  = $this->createRequestGetPaginatedGalleryItems($page, $resultsPerPage, $orderBy, $order);
        $getResponse = $this->dispatchRequest($getRequest);
        return $getResponse->get_data();
    }

    /**
     * @param $postId
     * @param $setOrder
     * @return WP_REST_Response
     */
    protected function sendGalleryUpdateOrderRequest($postId, $setOrder)
    {
        $request = new WP_REST_Request('POST', $this->namespaced_route . '/order/post/' . $postId . '/' . $setOrder);
        $request->set_header('Content-Type', 'application/json');
        $response = $this->dispatchRequest($request);

        return $response;
    }

    protected function requestDataProviderParams(WP_REST_Request $request, array $nonDefaultValues = [])
    {
        $setValues = $this->default_post_values;

        if (!empty($nonDefaultValues)) {
            $setValues = array_replace($setValues, $nonDefaultValues);
        }

        $request->set_param('mainTitle', $setValues['mainTitle']);
        $request->set_param('attachmentContents', $setValues['attachmentContents']);
        $request->set_param('postNonce', $setValues['postNonce']);
    }

    protected function requestDataProviderImage(WP_REST_Request $request, $count = 1)
    {
        $fileData = $this->createFileUploadData($count);
        $request->set_file_params(['image_files' => $fileData]);
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

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
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

    protected function createGalleryPostWithFactory($count = false, $milliseconds = false)
    {
        $order = 0;
        $postIds = [];
        $milliseconds = $milliseconds !== false ? (int) $milliseconds : 1000;
        $start = time() - (24 * 60 * 60); // Go back a day so posts can't be set in the future.
        $count = $count === false || $count <= 0 ? 1 : (int) $count;

        while(count($postIds) < $count) {
            $postIds[] = $this->factory()->post->create([
                'post_content' => 'I am some words for the Content',
                'post_title'   => 'I am a default title',
                'post_type'    => $this->galleryPostType,
                'post_status'  => $this->galleryPostStatus,
                'post_date'    => date('Y-m-d H:i:s', $start + (count($postIds) * $milliseconds)),
                'meta_input' => [
                    $this->galleryPostOrderKey => $order,
                ]
            ]);
            ++$order;
        }

        if (count($postIds) > 1) {
            return $postIds;
        }

        return $postIds[0];
    }

    protected function createFileUploadData($attachmentCount = 1, $random = false)
    {
        $path = $this->getPluginFilePath();
        $path .= '/tests/images';
        $files = preg_grep('/^([^.])/', scandir($path));

        if (count($files) > $attachmentCount) {
            $files = array_splice($files, 0, -$attachmentCount);
        }

        $files = array_values($files);

        /*if ($random === true) {
            shuffle($files);
        }*/

        $fileUploads = [
            'name' => [],
            'type' => [],
            'tmp_name' => [],
            'error' => [],
            'size' => [],
        ];

        foreach ($files as $key => $file) {
            $setFile = $path . '/' . $file;

            $fileUploads['name'][$key] = basename($setFile);
            $fileUploads['type'][$key] = mime_content_type($setFile);
            $fileUploads['tmp_name'][$key] = $setFile;
            $fileUploads['error'][$key] = 0;
            $fileUploads['size'][$key] = filesize($setFile);
        }

        return $fileUploads;
    }

    protected function createGalleryUser(array $roles = [])
    {
        $userId = $this->factory()->user->create($this->default_user_values);
        $user = get_user_by( 'id', $userId);
        foreach ($roles as $role) {
            $user->set_role($role);
        }
        $currentUser = wp_set_current_user($user->ID, $user->user_login);

        if (is_a($currentUser, 'WP_User')) {
            return $userId;
        }
        return false;
    }
}