<?php
/*
Plugin Name: GM Frontend Gallery
Description: This thing
Author: Gabriel Mioni
Version: 0.1
Author URI: gabrielmioni.com
*/
function gmFrontendGalleryActivate() {
    file_put_contents(dirname(__FILE__) . '/log', print_r('This is the water' . "\n", true), FILE_APPEND);
}
register_activation_hook( __FILE__, 'gmFrontendGalleryActivate');

function gmFrontendGalleryDeactivate() {
    file_put_contents(dirname(__FILE__) . '/log', print_r("And this is the well\n", true), FILE_APPEND);
}
register_deactivation_hook( __FILE__, 'gmFrontendGalleryDeactivate' );