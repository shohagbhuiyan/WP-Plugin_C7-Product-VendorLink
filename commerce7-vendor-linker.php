<?php
/**
 * Plugin Name: Commerce7 Vendor Linker
 * Description: Integrates WordPress with the Commerce7 Vendor API to dynamically link vendor names on the frontend.
 * Version: 1.0.0
 * Author: Zaman Bhuiyan
 * Text Domain: c7vl
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'C7VL_VERSION', '1.0.0' );
define( 'C7VL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'C7VL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Require core files
require_once C7VL_PLUGIN_DIR . 'includes/class-c7vl-core.php';
require_once C7VL_PLUGIN_DIR . 'includes/class-c7vl-admin.php';
require_once C7VL_PLUGIN_DIR . 'includes/class-c7vl-api.php';
require_once C7VL_PLUGIN_DIR . 'includes/class-c7vl-cache.php';
require_once C7VL_PLUGIN_DIR . 'includes/class-c7vl-frontend.php';

// Activation Hook
register_activation_hook( __FILE__, 'c7vl_activate_plugin' );
function c7vl_activate_plugin() {
    if ( ! wp_next_scheduled( 'c7vl_cron_refresh_vendors' ) ) {
        wp_schedule_event( time(), 'hourly', 'c7vl_cron_refresh_vendors' );
    }
}

// Deactivation Hook
register_deactivation_hook( __FILE__, 'c7vl_deactivate_plugin' );
function c7vl_deactivate_plugin() {
    wp_clear_scheduled_hook( 'c7vl_cron_refresh_vendors' );
}

// Initialize Plugin
function c7vl_init() {
    $plugin = new C7VL_Core();
    $plugin->init();
}
add_action( 'plugins_loaded', 'c7vl_init' );