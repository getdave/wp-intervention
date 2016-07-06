<?php
/*
Plugin Name: Intervention Image Manipulation
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: Test
Author: Mr Foo
Version: 0.1
Author URI: http://www.wordpress.org
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';


// Plugin core
require_once __DIR__ . '/src/plugin.php';

// Wrapper for Intervention Library
require_once __DIR__ . '/src/intervention-wrapper.php';

// Global helper functions
require_once __DIR__ . '/src/globals.php';

// Boot
$wp_intervention_plugin = new WP_Intervention(__FILE__);
add_action( 'wp_loaded', array( $wp_intervention_plugin, 'load' ) );