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

     // Start session early
    if ( ! session_id() ) {
        session_start();
    }

        // Add chat bubble to frontend only
        add_action( 'wp_footer', [ $this, 'render_chat' ] );
        // Register AJAX handlers for both logged in and guest users
        add_action( 'wp_ajax_sw_send_message',        [ $this, 'handle_message' ] );
        add_action( 'wp_ajax_nopriv_sw_send_message', [ $this, 'handle_message' ] );
        // Enqueue styles and scripts
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Register history loader
        add_action( 'wp_ajax_sw_get_history',        [ $this, 'get_history' ] );
        add_action( 'wp_ajax_nopriv_sw_get_history', [ $this, 'get_history' ] );
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

    // Handle AJAX message requestpublic 
    function handle_message() {

    check_ajax_referer( 'sw_nonce', 'nonce' );

    $session_id = session_id(); // already started in init()

    error_log( 'Session ID: ' . $session_id ); // debug

    $message = sanitize_text_field( $_POST['message'] ?? '' );

    if ( empty( $message ) ) {
        wp_send_json_error( 'Empty message.' );
    }

    $ai       = new SW_AI();
    $response = $ai->send_message( $message );

    $this->save_to_log( $session_id, $message, $response );

    wp_send_json_success( $response );
}




// ============================== HISTORY AND SESSION ========================================================


// Save message and response to DB
private function save_to_log( $session_id, $message, $response ) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'sw_chat_logs', // table name
        [
            'session_id' => $session_id, // unique session
            'message'    => $message,    // user message
            'response'   => $response,   // AI response
        ]
    );
}

// Get chat history for current session
private function get_session_history( $session_id ) {
    global $wpdb;

    // Fetch all logs for this session
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT message, response FROM {$wpdb->prefix}sw_chat_logs WHERE session_id = %s ORDER BY id ASC",
            $session_id
        )
    );
}



// Return session history as JSON
public function get_history() {

    check_ajax_referer( 'sw_nonce', 'nonce' );

    if ( ! session_id() ) {
        session_start();
    }

    $session_id = session_id();
    $history    = $this->get_session_history( $session_id );

    wp_send_json_success( $history );
}
}