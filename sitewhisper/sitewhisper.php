<?php
/**
 * Plugin Name: SiteWhisper
 * Plugin URI:  #
 * Description: AI Chatbot powered by Open Router
 * Version:     1.0.0
 * Author:      Kamran
 * Text Domain: sitewhisper
 */

defined( 'ABSPATH' ) || exit;

// Define constants
define( 'SW_VERSION',  '1.0.0' );
define( 'SW_PATH',     plugin_dir_path( __FILE__ ) ); //server file path.
define( 'SW_URL',      plugin_dir_url( __FILE__ ) ); //Returns browser URL.
define( 'SW_BASENAME', plugin_basename( __FILE__ ) );

class SiteWhisper {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->register_hooks();
        $this->init_classes();
    }

    private function load_dependencies() {
        require_once SW_PATH . 'includes/class_activator.php';
        require_once SW_PATH . 'includes/class_deactivator.php';
        require_once SW_PATH . 'includes/class_admin.php';
        require_once SW_PATH . 'includes/class_ai.php';
        require_once SW_PATH . 'includes/class_chat_ui.php';
        require_once SW_PATH . 'includes/class_site_reader.php';

    }

    private function register_hooks() {
        register_activation_hook( __FILE__, [ 'SW_Activator', 'activate' ] );
        register_deactivation_hook( __FILE__, [ 'SW_Deactivator', 'deactivate' ] );
    }

    // Initialize classes that need to run
    private function init_classes() {
        new SW_Admin();
        new SW_AI();
        new SW_Chat_UI();

    }

}

SiteWhisper::get_instance();