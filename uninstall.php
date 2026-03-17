<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete options
delete_option( 'c7vl_settings' );

// Delete transients
delete_transient( 'c7vl_vendor_data' );

// Clear Cron
wp_clear_scheduled_hook( 'c7vl_cron_refresh_vendors' );