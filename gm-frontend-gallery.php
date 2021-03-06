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
    $gmFrontendGallery->createOptions();
});
add_action('init', function () use ($gmFrontendGallery) {
    $gmFrontendGallery->registerPostType();
});
add_filter('the_content', function ($content) use ($gmFrontendGallery) {
    return $gmFrontendGallery->setGalleryPostVueMountElm($content);
}, 11);
add_action('rest_api_init', function() use ($gmFrontendGallery) {
    $gmFrontendGallery->registerApiRoutes();
});
add_action('wp_enqueue_scripts', function() use ($gmFrontendGallery) {
    $gmFrontendGallery->registerGalleryVue();
    $gmFrontendGallery->registerSubmitVue();
    $gmFrontendGallery->registerSingleVue();
    $gmFrontendGallery->enqueueSingleVue();
});
add_shortcode('gm-gallery', function() use ($gmFrontendGallery) {
    return $gmFrontendGallery->mountVueGallery();
});
add_shortcode('gm-gallery-submit', function() use ($gmFrontendGallery) {
    return $gmFrontendGallery->mountVueSubmit();
});