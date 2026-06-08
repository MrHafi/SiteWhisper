<?php 

// Add floating chat bubble on frontend
// Open/close chat window on click
// Input box + send button
// JS sends message to WordPress endpoint
// Response displays in chat box


defined( 'ABSPATH' ) || exit;

class SW_Chat_UI {

    public function __construct() {
        $this->init();
    }

    private function init() {
        // Add chat bubble to frontend only
        add_action( 'wp_footer', [ $this, 'render_chat' ] );
        // Register AJAX handlers for both logged in and guest users
        add_action( 'wp_ajax_sw_send_message',        [ $this, 'handle_message' ] );
        add_action( 'wp_ajax_nopriv_sw_send_message', [ $this, 'handle_message' ] );
        // Enqueue styles and scripts
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    // Enqueue CSS and JS files
    public function enqueue_assets() {
        wp_enqueue_style(  'sw-chat', SW_URL . 'assets/chat.css', [], SW_VERSION );
        wp_enqueue_script( 'sw-chat', SW_URL . 'assets/chat.js',  [], SW_VERSION, true );

        // Pass data to JS
        wp_localize_script( 'sw-chat', 'SW', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'sw_nonce' ),
        ]);
    }

    // Render chat bubble HTML in footer
    public function render_chat() {
        $site_name       = get_bloginfo( 'name' );
        $chatbot_name    = get_option( 'sw_chatbot_name', 'SiteWhisper' );
        $welcome_message = get_option( 'sw_welcome_message', 'Hi! How can I help you?' );
        ?>

        <!-- Chat Bubble -->
        <div id="sw-bubble">
            <span id="sw-bubble-icon">💬</span>
            <span id="sw-bubble-text">Ask me about <?php echo esc_html( $site_name ); ?></span>
        </div>

        <!-- Chat Window -->
        <div id="sw-window" style="display:none;">

            <!-- Header -->
            <div id="sw-header">
                <span><?php echo esc_html( $chatbot_name ); ?></span>
                <button id="sw-close">✕</button>
            </div>

            <!-- Messages Area -->
            <div id="sw-messages">
                <!-- Welcome message -->
                <div class="sw-message sw-bot">
                    <?php echo esc_html( $welcome_message ); ?>
                </div>
            </div>

            <!-- Input Area -->
            <div id="sw-input-area">
                <input type="text" id="sw-input" placeholder="Type your message..." />
                <button id="sw-send">Send</button>
            </div>

        </div>

        <?php
    }

    // Handle AJAX message request
    public function handle_message() {

        // Security check
        check_ajax_referer( 'sw_nonce', 'nonce' );

        // Get and sanitize user message
        $message = sanitize_text_field( $_POST['message'] ?? '' );

        if ( empty( $message ) ) {
            wp_send_json_error( 'Empty message.' );
        }

        // Send to AI and get response
      $ai       = new SW_AI();
$response = $ai->send_message( $message );

        wp_send_json_success( $response );
    }

}