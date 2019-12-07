<?php

require_once dirname(dirname(__FILE__)) . '/GalleryUnitTestCase.php';

class OptionsTest extends GalleryUnitTestCase
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
    public function plugin_options_exists()
    {
        $options = get_option($this->pluginOptionName);

        $this->assertNotFalse($options);
        $this->assertTrue(isset($options['user_required']));
        $this->assertTrue(isset($options['admin_must_approve']));
        $this->assertTrue(isset($options['allowed_mimes']));
    }

    /** @test */
    public function plugin_options_can_be_updated()
    {
        $user_required_key = 'user_required';
        $this->createGalleryUser(['administrator']);

        $originalOptions = get_option($this->pluginOptionName);

        $setNewOptions = $originalOptions;
        $setNewOptions[$user_required_key] = true;

        $request =  new WP_REST_Request('POST', $this->namespaced_route . '/options');
        $request->set_header('Content-Type', 'multipart/form-data');
        $request->set_param('updatedOptions', $setNewOptions);
        $response = $this->dispatchRequest($request);

        $newOptions = get_option($this->pluginOptionName);

        $this->assertEquals(200, $response->get_status());

        // Settings have been updated
        $this->assertFalse($originalOptions[$user_required_key]);
        $this->assertTrue($newOptions[$user_required_key]);
    }

    /** @test */
    public function user_is_required_if_user_required_setting_is_true()
    {
        $optionValues = get_option($this->pluginOptionName);
        $optionValues['user_required'] = true;

        update_option($this->pluginOptionName, $optionValues);

        /* Submit a gallery post WITHOUT a user */
        $responseWithoutUser = $this->submitAGalleryPost();
        $this->assertEquals(401, $responseWithoutUser->get_status());

        /* Submit a gallery post WITH a user */
        $this->createGalleryUser();
        $responseWithUser = $this->submitAGalleryPost();
        $this->assertEquals(200, $responseWithUser->get_status());
    }

    protected function submitAGalleryPost()
    {
        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request, [
            // Creating a the post nonce here insures the nonce is valid for a given user
            'post_nonce' => wp_create_nonce('gm_gallery_submit')
        ]);
        $this->requestDataProviderImage($request);

        return $this->dispatchRequest($request);
    }
}
