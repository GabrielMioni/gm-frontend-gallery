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

        $postId = $response->data;
        
        $attachmentIds = get_post_meta($postId, $this->galleryAttachmentMetaKey, false);

        /* Show that only 3 attachments have been created */
        $this->assertEquals($setMaxAttachment, count($attachmentIds));
    }

    /** @test */
    public function max_attachments_cannot_be_set_higher_than_maxAttachmentsAbsolute_value()
    {
        $maxAttachmentKey = 'max_attachments';
        $optionValuesBeforeUpdate = get_option($this->pluginOptionName);
        $maxAttachmentValueBeforeUpdate = $optionValuesBeforeUpdate[$maxAttachmentKey];
        $this->assertEquals(5, $maxAttachmentValueBeforeUpdate);

        $this->updateSettingsViaAPI($maxAttachmentKey, 3);
        $optionValuesAfterUpdateOne = get_option($this->pluginOptionName);
        $maxAttachmentValueAfterUpdateOne = $optionValuesAfterUpdateOne[$maxAttachmentKey];
        $this->assertEquals(3, $maxAttachmentValueAfterUpdateOne);

        $this->updateSettingsViaAPI($maxAttachmentKey, 100, false);
        $optionValuesAfterUpdateTwo = get_option($this->pluginOptionName);
        $maxAttachmentValueAfterUpdateTwo = $optionValuesAfterUpdateTwo[$maxAttachmentKey];
        $this->assertEquals(5, $maxAttachmentValueAfterUpdateTwo);
    }

    /** @test */
    public function gallery_posts_must_be_approved_if_admin_must_approve_is_true()
    {
        $responseWithNoApprovalRequired = $this->submitAGalleryPost();
        $postIdOne = $responseWithNoApprovalRequired->data;
        $postOne = get_post($postIdOne);
        $this->assertEquals('publish', $postOne->post_status);

        $optionValues = get_option($this->pluginOptionName);
        $optionValues['admin_must_approve'] = true;
        update_option($this->pluginOptionName, $optionValues);

        $responseWithApprovalRequired = $this->submitAGalleryPost();
        $postIdTwo = $responseWithApprovalRequired->data;
        $postTwo = get_post($postIdTwo);
        $this->assertEquals('draft', $postTwo->post_status);
    }
    
    /** @test */
    public function gallery_post_attachments_restricted_by_allowed_mimes()
    {
        $allowedMimesSettingKey = 'allowed_mimes';
        $optionValues = get_option($this->pluginOptionName);
        $allowedMimesSettings = $optionValues[$allowedMimesSettingKey];

        $this->assertTrue(array_search('image/jpeg', $allowedMimesSettings) !== false);

        if (($key = array_search('image/jpeg', $allowedMimesSettings)) !== false) {
            unset($allowedMimesSettings[$key]);
        }

        $optionValues[$allowedMimesSettingKey] = $allowedMimesSettings;
        update_option($this->pluginOptionName, $optionValues);

        /* Show that the allowed mime setting DOES NOT include 'image/jpeg' */
        $newOptionValues = get_option($this->pluginOptionName);
        $this->assertFalse(array_search('image/jpeg', $newOptionValues[$allowedMimesSettingKey]));
        $response = $this->submitAGalleryPost();

        $this->assertEquals(400, $response->get_status());
    }

    /** @test */
    public function updating_allowed_mime_types_works_when_new_mimes_are_valid()
    {
        $goodMimeTypeValue = [
            'image/jpeg',
        ];

        $responseOne = $this->updateSettingsViaAPI('allowed_mimes', $goodMimeTypeValue);
        $this->assertEquals(200, $responseOne->get_status());

        $newOptionsOne = get_option($this->pluginOptionName);
        $this->assertEqualSets($newOptionsOne['allowed_mimes'], $goodMimeTypeValue);

        $goodMimeTypeValue[] = 'image/gif';
        $responseTwo = $this->updateSettingsViaAPI('allowed_mimes', $goodMimeTypeValue, false);
        $this->assertEquals(200, $responseTwo->get_status());

        $newOptionsTwo = get_option($this->pluginOptionName);
        $this->assertEqualSets($newOptionsTwo['allowed_mimes'], $goodMimeTypeValue);

        $goodMimeTypeValue[] = 'image/png';
        $responseThree = $this->updateSettingsViaAPI('allowed_mimes', $goodMimeTypeValue, false);
        $this->assertEquals(200, $responseThree->get_status());

        $newOptionsThree = get_option($this->pluginOptionName);
        $this->assertEqualSets($newOptionsThree['allowed_mimes'], $goodMimeTypeValue);
    }

    /** @test */
    public function updating_allowed_mime_type_must_be_array()
    {
        $badMimeTypeValue = "This string isn't even array, wut?";

        $response = $this->updateSettingsViaAPI('allowed_mimes', $badMimeTypeValue);

        $this->assertEquals(403, $response->get_status());
    }

    /** @test */
    public function updating_allowed_mime_type_must_be_a_valid_mime_type()
    {
        $badMimeTypeValue = [
            'text/plain',
            'image/svg+xml',
            'audio/mpeg'
        ];

        $response = $this->updateSettingsViaAPI('allowed_mimes', $badMimeTypeValue);

        $this->assertEquals(403, $response->get_status());
    }

    /** @test */
    public function admin_must_approve_setting_must_be_boolean()
    {
        $this->runOptionBooleanAsserts('admin_must_approve');
    }

    /** @test */
    public function user_required_setting_must_be_boolean()
    {
        $this->runOptionBooleanAsserts('user_required');
    }

    protected function runOptionBooleanAsserts($optionKey)
    {
        $preAssertOptionValue = $this->getOptionByKey($optionKey);
        $this->assertFalse($preAssertOptionValue);

        $responseOne = $this->updateSettingsViaAPI($optionKey, 'I am a string, not a boolean value', true);
        $this->assertEquals(403, $responseOne->get_status());

        $responseTwo = $this->updateSettingsViaAPI($optionKey, true, false);
        $this->assertEquals(200, $responseTwo->get_status());

        $optionValue = $this->getOptionByKey($optionKey);
        $this->assertTrue($optionValue);
    }

    /** @test */
    public function options_can_be_reset_to_default()
    {
        $updateOptionsValues = [
            'user_required' => true,
            'admin_must_approve' => true,
            'max_attachments' => 3,
            'allowed_mimes' => [
                'image/jpeg',
            ]
        ];

        $this->createGalleryUser(['administrator']);

        $request =  new WP_REST_Request('POST', $this->namespaced_route . '/options');
        $request->set_header('Content-Type', 'multipart/form-data');
        $request->set_param('updatedOptions', $updateOptionsValues);
        $response = $this->dispatchRequest($request);
        $this->assertEquals(200, $response->get_status());

        /* Show that options have been updated */
        $updatedOptions = get_option($this->pluginOptionName);
        $this->assertEqualSets($updatedOptions, $updateOptionsValues);

        $request =  new WP_REST_Request('POST', $this->namespaced_route . '/options');
        $request->set_header('Content-Type', 'multipart/form-data');
        $request->set_param('resetOptions', true);
        $response = $this->dispatchRequest($request);
        $this->assertEquals(200, $response->get_status());

        $resetOptions = get_option($this->pluginOptionName);
        $this->assertEqualSets($this->defaultOptions, $resetOptions);
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

    protected function getOptionByKey($optionKey) {
        $options = get_option($this->pluginOptionName);
        if (isset($options[$optionKey])) {
            return $options[$optionKey];
        }

        return false;
    }

    protected function submitAGalleryPost()
    {
        $request = $this->createRequestSubmitGallery();
        $this->requestDataProviderParams($request, [
            // Creating a the post nonce here insures the nonce is valid for a given user
            'postNonce' => wp_create_nonce($this->gallerySubmitNonce)
        ]);
        $this->requestDataProviderImage($request);

        return $this->dispatchRequest($request);
    }
}
