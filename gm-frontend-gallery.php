<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

require_once('autoload.php');
require_once('*/gmFrontendGallery.php');

$gmFrontendGallery = new gmFrontendGallery();

add_action('rest_api_init', function () use ($gmFrontendGallery) {
    $gmFrontendGallery->registerApiRoutes();
});
register_activation_hook(__FILE__, function() use ($gmFrontendGallery) {
    $gmFrontendGallery->activate();
});