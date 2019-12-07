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

        $updatedOptions = (array) $this->setRequestParams($request, 'updatedOptions');
        file_put_contents(dirname(__FILE__) . '/log', print_r($updatedOptions, true), FILE_APPEND);
        
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

        $optionsAreValid = false;
        
        foreach ($newOptions as $optionKey => $value) {
            if (!isset($options[$optionKey])) {
                continue;
            }
            $optionsAreValid = true;
            
            $options[$optionKey] = $value;
        }

        if ($optionsAreValid === false) {
            return $this->createWPError('invalid_options', 'No options updated', 403);
        }

        $updated = update_option($optionName, $options);

        if ($updated === false) {
            return $this->createWPError('update_unsuccessful', 'Update failed', 403);
        }

        return true;
    }
}