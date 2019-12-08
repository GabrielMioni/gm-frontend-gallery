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
        $userRequiredKey = 'user_required';

        $originalOptions = get_option($this->pluginOptionName);

        $response = $this->updateSettingsViaAPI($userRequiredKey, true);

        $newOptions = get_option($this->pluginOptionName);

        $this->assertEquals(200, $response->get_status());

        // Settings have been updated
        $this->assertFalse($originalOptions[$userRequiredKey]);
        $this->assertTrue($newOptions[$userRequiredKey]);
    }

    /** @test */
    public function user_is_required_if_user_required_setting_is_true()
    {
        $optionValues = get_option($this->pluginOptionName);
        $optionValues['user_required'] = true;

        update_option($this->pluginOptionName, $optionValues);

        $newOptionValues = get_option($this->pluginOptionName);
        $this->assertTrue($newOptionValues['user_required']);

        /* Submit a gallery post WITHOUT a user */
        $responseWithoutUser = $this->submitAGalleryPost();
        $this->assertEquals(401, $responseWithoutUser->get_status());

        /* Submit a gallery post WITH a user */
        $this->createGalleryUser();
        $responseWithUser = $this->submitAGalleryPost();
        $this->assertEquals(200, $responseWithUser->get_status());
    }

    /** @test */
    public function gallery_attachments_limited_by_max_attachment_setting()
    {
        $setMaxAttachment = 3;
        $optionValues = get_option($this->pluginOptionName);
        $optionValues['max_attachments'] = $setMaxAttachment;

        update_option($this->pluginOptionName, $optionValues);

        $newOptionValues = get_option($this->pluginOptionName);
        $this->assertEquals($setMaxAttachment, $newOptionValues['max_attachments']);

        /* Try to submit 5 attachments */
        $multiplePostData = $this->createGalleryPostWithMultipleImages();
        $response = $multiplePostData['response'];

        $postId = $response->data['postID'];
        
        $attachmentIds = get_post_meta($postId, $this->galleryAttachmentMetaKey, false);

        /* Show that only 3 attachments have been created */
        $this->assertEquals($setMaxAttachment, count($attachmentIds));
    }

    protected function updateSettingsViaAPI($settingKey, $newSettingValue, $createAdminUser = true)
    {
        if ($createAdminUser === true) {
            $this->createGalleryUser(['administrator']);
        }

        $options = get_option($this->pluginOptionName);
        $options[$settingKey] = $newSettingValue;

        $request =  new WP_REST_Request('POST', $this->namespaced_route . '/options');
        $request->set_header('Content-Type', 'multipart/form-data');
        $request->set_param('updatedOptions', $options);
        $response = $this->dispatchRequest($request);

        return $response;
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
