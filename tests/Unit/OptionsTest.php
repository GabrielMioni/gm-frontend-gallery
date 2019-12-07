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
        $this->createAdminUser();

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

        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request);
        $this->requestDataProviderImage($request);

        $response = $this->dispatchRequest($request);
        $this->assertEquals(401, $response->get_status());
    }
}
