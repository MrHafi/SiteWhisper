<?php
// Block direct access — required in uninstall.php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop chat logs table
$table = $wpdb->prefix . 'sw_chat_logs';
$wpdb->query( "DROP TABLE IF EXISTS $table" );

// Delete all plugin options
delete_option( 'sw_version' );
delete_option( 'sw_api_key' );
delete_option( 'sw_chatbot_name' );
delete_option( 'sw_welcome_message' );
delete_option( 'sw_include_all_pages' );
delete_option( 'sw_selected_pages' );