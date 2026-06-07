<?php
defined( 'ABSPATH' ) || exit;

class SW_Activator {

    public static function activate() {
        self::create_tables();
        self::save_version();
    }

    private static function create_tables() {
        global $wpdb;

        $table   = $wpdb->prefix . 'sw_chat_logs';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            response TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    private static function save_version() {
        add_option( 'sw_version', SW_VERSION );
    }

}