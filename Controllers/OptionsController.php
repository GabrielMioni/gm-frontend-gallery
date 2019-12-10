<?php

namespace GmFrontendGallery\Controller;

use WP_REST_Request;
use WP_REST_Response;

class OptionsController extends BaseController
{
    public function updateGalleryOptions(WP_REST_Request $request)
    {
        $isAdmin = $this->currentUserIsAdmin();

        if (!$isAdmin) {
            return $this->createWPError('invalid_request', 'Invalid user capabilities', 403);
        }

        $resetToDefault = $this->setRequestParams($request, 'resetOptions');

        if ($resetToDefault === true) {
            $updated = update_option($this->pluginOptionName, $this->defaultOptions);

            if ($updated === true) {
                $response = new WP_REST_Response();
                $response->set_status(200);

                return $response;
            }
        }

        $updatedOptions = $this->setRequestParams($request, 'updatedOptions');
        
        $updated = $this->updateOption($updatedOptions);

        if (is_a($updated, 'WP_Error')) {
            return $updated;
        }

        $response = new WP_REST_Response();
        $response->set_status(200);

        return $response;
    }

    protected function updateOption(array $newOptions, $optionName = null)
    {
        $optionName = $optionName === null ? $this->pluginOptionName : $optionName;
        
        $options = get_option($optionName);

        $error_code = null;
        
        foreach ($newOptions as $optionKey => $value) {
            if (!isset($options[$optionKey])) {
                $error_code = 'invalid_options';
                break;
            }
            if ($optionKey === 'allowed_mimes' && $this->newMimeTypeValueIsValid($value) === false) {
                $error_code = 'invalid_mime_type';
                break;
            }
            if (is_bool($this->defaultOptions[$optionKey]) && (!is_bool($value))) {
                $error_code = 'invalid_value';
                break;
            }
            if ($optionKey === 'max_attachments' && (int) $value > $this->defaultOptions['max_attachments']) {
                $value = $this->defaultOptions['max_attachments'];
            }
            $options[$optionKey] = $value;
        }

        if (!is_null($error_code)) {
            return $this->createWPError($error_code, 'No options updated', 403);
        }

        $updated = update_option($optionName, $options);

        if ($updated === false) {
            return $this->createWPError('update_unsuccessful', 'Update failed', 403);
        }

        return true;
    }

    protected function newMimeTypeValueIsValid($newMimeTypes) {
        if (!is_array($newMimeTypes)) {
            return false;
        }

        foreach ($newMimeTypes as $allowedMimeType) {
            if (!in_array($allowedMimeType, $this->defaultOptions['allowed_mimes'])) {
                return false;
            }
        }

        return true;
    }
}