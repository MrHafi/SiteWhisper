<?php 

// Settings page under WordPress admin menu
// Fields: API key, chatbot name, welcome message, which pages to read


defined( 'ABSPATH' ) || exit;

class SW_Admin {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    // Register top level menu
    public function register_menu() {
        add_menu_page(
            'SiteWhisper',          // Page title
            'SiteWhisper',          // Menu title
            'manage_options',       // Capability
            'sitewhisper',          // Menu slug
            [ $this, 'render_page' ], // Callback
            'dashicons-format-chat', // Icon
            80                      // Position
        );
    }

    // Register all settings
    public function register_settings() {
        register_setting( 'sw_settings_group', 'sw_api_key' );
        register_setting( 'sw_settings_group', 'sw_chatbot_name' );
        register_setting( 'sw_settings_group', 'sw_welcome_message' );
        register_setting( 'sw_settings_group', 'sw_include_all_pages' );
        register_setting( 'sw_settings_group', 'sw_selected_pages' );
    }

    // Builds and displays the entire settings page UI.
    public function render_page() {
        // Get all published pages for checkbox list
        $all_pages = get_pages( [ 'post_status' => 'publish' ] );

        // Get saved values
        $api_key          = get_option( 'sw_api_key', '' );
        $chatbot_name     = get_option( 'sw_chatbot_name', 'SiteWhisper' );
        $welcome_message  = get_option( 'sw_welcome_message', 'Hi! How can I help you?' );
        $include_all      = get_option( 'sw_include_all_pages', '0' );
        $selected_pages   = get_option( 'sw_selected_pages', [] );
        ?>

        <div class="wrap">
            <h1>SiteWhisper Settings</h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'sw_settings_group' ); ?>

                <table class="form-table">

                    <!-- API Key -->
                   <!-- API Key -->
                    <tr>
                        <th><label for="sw_api_key">OpenRouter API Key</label></th>
                        <td>
                            <input
                                type="password"
                                id="sw_api_key"
                                name="sw_api_key"
                                value="<?php echo esc_attr( $api_key ); ?>"
                                class="regular-text"
                            />
                            <p class="description">
                                Get your free API key from 
                                <a href="https://openrouter.ai/settings/keys" target="_blank">OpenRouter</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Chatbot Name -->
                    <tr>
                        <th><label for="sw_chatbot_name">Chatbot Name</label></th>
                        <td>
                            <input
                                type="text"
                                id="sw_chatbot_name"
                                name="sw_chatbot_name"
                                value="<?php echo esc_attr( $chatbot_name ); ?>"
                                class="regular-text"
                            />
                        </td>
                    </tr>

                    <!-- Welcome Message -->
                    <tr>
                        <th><label for="sw_welcome_message">Welcome Message</label></th>
                        <td>
                            <input
                                type="text"
                                id="sw_welcome_message"
                                name="sw_welcome_message"
                                value="<?php echo esc_attr( $welcome_message ); ?>"
                                class="regular-text"
                            />
                        </td>
                    </tr>

                    <!-- Include All Pages Toggle -->
                    <tr>
                        <th><label for="sw_include_all_pages">Include All Published Pages</label></th>
                        <td>
                            <input
                                type="checkbox"
                                id="sw_include_all_pages"
                                name="sw_include_all_pages"
                                value="1"
                                <?php checked( $include_all, '1' ); ?>
                            />
                            <span>Automatically include all published pages</span>
                        </td>
                    </tr>

                    <!-- Manual Page Selection -->
                    <tr id="sw_pages_row">
                        <th>Select Pages Manually</th>
                        <td>
                            <?php foreach ( $all_pages as $page ) : ?>
                                <label style="display:block; margin-bottom:5px;">
                                    <input
                                        type="checkbox"
                                        name="sw_selected_pages[]"
                                        value="<?php echo esc_attr( $page->ID ); ?>"
                                        <?php checked( in_array( $page->ID, (array) $selected_pages ) ); ?>
                                    />
                                    <?php echo esc_html( $page->post_title ); ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>

                </table>

                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>

        <!-- Toggle manual pages list based on checkbox -->
        <script>
            const toggle = document.getElementById('sw_include_all_pages');
            const row    = document.getElementById('sw_pages_row');

            // Set initial state
            row.style.display = toggle.checked ? 'none' : '';

            // On change
            toggle.addEventListener('change', function() {
                row.style.display = this.checked ? 'none' : '';
            });
        </script>

        <?php
    }

}



