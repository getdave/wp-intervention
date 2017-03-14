<?php
/**
 * Plugin Name: WP Intervention Image Manipulation
 * Version: 0.1
 * Description: A fully featured, on demand image manipulation tool for WordPress powered by the  <a href="http://image.intervention.io/">Intervention Library</a> (by Oliver Vogel).
 * Author: David Smith
 * Author URI: https://www.aheadcreative.com
 * Plugin URI: https://github.com/getdave/wp-intervention/
 * Text Domain: wp-intervention
 * Domain Path: /languages
 * @package WP Intervention
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Composer Autoloader
// requires all necessary Plugin files via "files" map
require_once __DIR__ . '/vendor/autoload.php';

// Setup hooks
register_activation_hook(__FILE__, array( 'WP_Intervention', 'activated' ) );
register_deactivation_hook( __FILE__, array( 'WP_Intervention', 'deactivated' ) );

// Boot Plugin
$wp_intervention_plugin = new WP_Intervention(__FILE__);
add_action( 'wp_loaded', array( $wp_intervention_plugin, 'load' ) );

