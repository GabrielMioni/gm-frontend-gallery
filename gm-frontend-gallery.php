<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/

require_once('autoload.php');

use GmFrontendGallery\gmFrontendGallery;

$gmFrontendGallery = new gmFrontendGallery();

register_activation_hook(__FILE__, function() use ($gmFrontendGallery) {
    $gmFrontendGallery->activate();
});
add_action('rest_api_init', function() use ($gmFrontendGallery) {
    $gmFrontendGallery->registerApiRoutes();
});
add_action('wp_enqueue_scripts', function() use ($gmFrontendGallery) {
    $gmFrontendGallery->registerVue();
});
add_shortcode('gm-gallery', function() use ($gmFrontendGallery) {
    return $gmFrontendGallery->mountVueApp();
});