<?php
defined( 'ABSPATH' ) || exit;

class SW_Site_Reader {

    // Get pages content based on admin settings
    public function get_site_content() {
        $include_all    = get_option( 'sw_include_all_pages', '0' );
        $selected_pages = get_option( 'sw_selected_pages', [] );

        // If include all is ON — get all published pages
        if ( $include_all === '1' ) {
            $pages = get_pages( [ 'post_status' => 'publish' ] );
        }
         else {
            // Get only admin selected pages
            $pages = get_pages( [
                'post_status' => 'publish',
                'include'     => $selected_pages,
            ]);
        }

        // No pages found
        if ( empty( $pages ) ) {
            return '';
        }

        // Build content string from all pages
        $content = '';
        foreach ( $pages as $page ) {
            $content .= 'Page: ' . $page->post_title . "\n";
            $content .= wp_strip_all_tags( $page->post_content ) . "\n\n";
        }

        return $content;
    }

    // Build system prompt with site content public function get_system_prompt() {
    $site_name    = get_bloginfo( 'name' );
    $site_content = $this->get_site_content();

    $prompt  = "You are a smart, friendly assistant for '{$site_name}' website.\n\n";
    $prompt .= "YOUR RULES:\n";
    $prompt .= "- Only answer based on the site content provided below.\n";
    $prompt .= "- Keep answers short, clear and helpful.\n";
    $prompt .= "- If the answer is not in the content, say: 'I don't have that information. Please contact us directly.'\n";
    $prompt .= "- Never make up information that is not in the content.\n";
    $prompt .= "- Be friendly and professional in tone.\n";
    $prompt .= "- If user greets you, greet back briefly then ask how you can help.\n";
    $prompt .= "- Do not mention that you are an AI unless directly asked.\n\n";
    $prompt .= "SITE CONTENT:\n";
    $prompt .= $site_content;

    return $prompt;
}

}