<?php

require_once dirname(dirname(__FILE__)) . '/GalleryUnitTestCase.php';

class OptionsTest extends GalleryUnitTestCase
{
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
        $this->assertFalse($originalOptions[$user_required_key]);

        $setNewOptions = $originalOptions;
        $setNewOptions[$user_required_key] = true;

        $request = new WP_REST_Request('POST', $this->routeNameSpace . '/options');
        $request->set_param('updatedOptions', $setNewOptions);
        $response = $this->dispatchRequest($request);

        $newOptions = get_option($this->pluginOptionName);

        $this->assertEquals(200, $response->get_status());
        $this->assertEquals($originalOptions[$user_required_key], $newOptions[$user_required_key]);
    }
}