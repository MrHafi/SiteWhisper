<?php
defined( 'ABSPATH' ) || exit;

class SW_AI {

    // OpenRouter API endpoint
    private $api_url = 'https://openrouter.ai/api/v1/chat/completions';

    // API key from admin settings
    private $api_key;

    public function __construct() {
        $this->api_key = get_option( 'sw_api_key', '' );
    }

    // Main method — send message and get response
    public function send_message( $user_message ) {

        // No API key — stop here
        if ( empty( $this->api_key ) ) {
            return 'API key is missing. Please add it in SiteWhisper settings.';
        }

        // Get site content as system prompt
        $site_reader   = new SW_Site_Reader();
        $system_prompt = $site_reader->get_system_prompt();


        // Build request body with system prompt
        $body = json_encode([
'model' => 'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning-20260428:free',
            'messages' => [
                [ 'role' => 'system', 'content' => $system_prompt ], // brief AI with site content
                [ 'role' => 'user',   'content' => $user_message  ], // visitor question
            ]
        ]);

        // Send request
        $response = wp_remote_post( $this->api_url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
                'HTTP-Referer'  => get_site_url(),
            ],
            'body'    => $body,
            'timeout' => 30,
        ]);

        // Request failed
        if ( is_wp_error( $response ) ) {
            return 'Connection error: ' . $response->get_error_message();
        }

        // Parse response
        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        return $data['choices'][0]['message']['content'] ?? 'No response received.';
    }

}