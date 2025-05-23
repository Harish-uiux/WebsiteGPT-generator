<?php
/**
 * Plugin Name: AI Answer Generator
 * Plugin URI:  https://boostmyshop.com/
 * Description: A WordPress plugin that provides AI-generated answers to user queries using OpenAI's GPT model.
 * Version: 1.0.0
 * Author: Hariharan G
 * Author URI: https://boostmyshop.com/
 * License: GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define Constants
define('AI_ANSWER_GENERATOR_PATH', plugin_dir_path(__FILE__));
define('AI_ANSWER_GENERATOR_URL', plugin_dir_url(__FILE__));

// Include Settings Page
include_once AI_ANSWER_GENERATOR_PATH . 'includes/settings.php';

// Enqueue Scripts & Styles
function ai_answer_generator_enqueue_scripts() {
    // Enqueue Tailwind CSS from CDN
    wp_enqueue_style('tailwind-css', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', array(), '2.2.19');
    
    // Enqueue custom CSS
    wp_enqueue_style('ai-answer-generator-css', AI_ANSWER_GENERATOR_URL . 'assets/style.css', array('tailwind-css'), '1.0.1');
    
    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Enqueue custom JavaScript
    wp_enqueue_script('ai-answer-generator-js', AI_ANSWER_GENERATOR_URL . 'js/main.js', array('jquery'), '1.0.1', true);
    
    wp_localize_script('ai-answer-generator-js', 'aiAnswerGen', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ai_answer_nonce'),
        'bot_name' => get_option('ai_answer_generator_bot_name', 'AI Assistant'),
        'theme_color' => get_option('ai_answer_generator_theme_color', '#4F46E5'),
        'welcome_message' => get_option('ai_answer_generator_welcome_message', 'Hello! How can I help you today?')
    ));
}
add_action('wp_enqueue_scripts', 'ai_answer_generator_enqueue_scripts');

// Shortcode for the AI Answer Generator Form
// Shortcode for the AI Answer Generator Form
function ai_answer_generator_shortcode($atts) {
    $atts = shortcode_atts(array(
        'width' => '100%',
        'max_width' => '480px',
        'theme' => 'light',
    ), $atts, 'ai_answer_generator');
    
    $bot_name = get_option('ai_answer_generator_bot_name', 'AI Assistant');
    $theme_color = get_option('ai_answer_generator_theme_color', '#4F46E5');
    $welcome_message = get_option('ai_answer_generator_welcome_message', 'Hello! How can I help you today?');
    $show_branding = get_option('ai_answer_generator_show_branding', 'yes');
    
    // Enqueue highlight.js for code syntax highlighting
    wp_enqueue_style('highlight-css', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-light.min.css');
    wp_enqueue_script('highlight-js', 'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js', array(), '11.7.0', true);
    
    // Font for code blocks
    wp_enqueue_style('jetbrains-mono', 'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap');
    
    // Current timestamp
    $current_time = date('g:i A');
    
    ob_start();
    ?>
    <div class="ai-chat-container" style="width: <?php echo esc_attr($atts['width']); ?>; max-width: <?php echo esc_attr($atts['max_width']); ?>; --primary-color: <?php echo esc_attr($theme_color); ?>;">
        <!-- Chat Box Header -->
        <div class="ai-chat-header" style="background-color: <?php echo esc_attr($theme_color); ?>;">
            <div class="ai-chat-header-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="ai-chat-header-info">
                <h3><?php echo esc_html($bot_name); ?></h3>
                <span class="ai-chat-status">
                    <span class="ai-status-dot"></span> 
                    <span class="ai-status-text">Online</span>
                </span>
            </div>
            <div class="ai-chat-header-actions">
                <button class="ai-chat-minimize" title="Minimize chat">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        
        <!-- Chat Messages Container -->
        <div class="ai-chat-messages" id="ai-chat-messages">
            <!-- Welcome Message -->
            <div class="ai-message ai-message-bot">
                <div class="ai-message-inner">
                    <div class="ai-message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="ai-message-content">
                        <div class="ai-content">
                            <p><?php echo esc_html($welcome_message); ?></p>
                        </div>
                    </div>
                </div>
                <div class="ai-message-footer">
                    <span class="ai-message-time"><?php echo esc_html($current_time); ?></span>
                    <div class="ai-message-actions">
                        <button class="ai-btn" onclick="copyEntireMessage(this)">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
<!-- Chat Input Area - Improved version -->
<div class="ai-chat-input-area">
    <div class="ai-chat-input-container">
        <textarea id="ai-user-input" class="ai-chat-input" placeholder="Type your message..." rows="1"></textarea>
        <button id="ai-send-message" class="ai-chat-send" aria-label="Send message">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>


        
        <?php if ($show_branding === 'yes') : ?>
        <!-- Branding -->
        <div class="ai-chat-branding">
            <small>Powered by <a href="https://boostmyshop.com/" target="_blank">BMS</a></small>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Minimized Chat Button -->
    <div class="ai-chat-minimized" style="background-color: <?php echo esc_attr($theme_color); ?>;">
        <i class="fas fa-comment-dots"></i>
    </div>
    
    <script>
        // Initialize highlight.js for code syntax highlighting
        document.addEventListener('DOMContentLoaded', function() {
            if (window.hljs) {
                hljs.configure({
                    languages: ['javascript', 'html', 'css', 'php', 'python', 'java', 'c', 'cpp', 'csharp', 'ruby', 'go', 'json', 'xml', 'bash', 'sql'],
                    ignoreUnescapedHTML: true
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}


add_shortcode('ai_answer_generator', 'ai_answer_generator_shortcode');

// AJAX Request Handler
function ai_answer_generator_ajax_handler() {
    check_ajax_referer('ai_answer_nonce', 'security');

    $api_key = get_option('ai_answer_generator_api_key');
    $question = sanitize_text_field($_POST['question']);
    $model = get_option('ai_answer_generator_model', 'gemini-1.5-flash');
    $stream_mode = get_option('ai_answer_generator_streaming', 'no');

    if (!$api_key) {
        wp_send_json_error(['message' => 'API Key is missing.']);
    }

    // Gemini API Endpoint
    $api_url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $api_key;
    
    // Add streaming parameter if enabled
    if ($stream_mode === 'yes') {
        $api_url .= "&alt=sse";
    }

    // Correct request format for Gemini API
    $request_body = json_encode([
        "contents" => [
            [
                "parts" => [
                    ["text" => $question]
                ]
            ]
        ]
    ]);

    // Send Request to Gemini API
    $response = wp_remote_post($api_url, [
        'method'    => 'POST',
        'body'      => $request_body,
        'headers'   => [
            'Content-Type' => 'application/json',
        ],
        'timeout'   => 30,
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Request failed: ' . $response->get_error_message()]);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Check for a valid answer in the response
    if (!empty($body['candidates'][0]['content']['parts'][0]['text'])) {
        $answer = $body['candidates'][0]['content']['parts'][0]['text'];
        
        // Process Markdown if option is enabled
        if (get_option('ai_answer_generator_markdown', 'yes') === 'yes' && function_exists('Markdown')) {
            $answer = Markdown($answer);
        }
        
        wp_send_json_success([
            'answer' => $answer,
            'timestamp' => current_time('g:i A')
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to retrieve an answer from Gemini API.']);
    }
}
add_action('wp_ajax_ai_answer_generator', 'ai_answer_generator_ajax_handler');
add_action('wp_ajax_nopriv_ai_answer_generator', 'ai_answer_generator_ajax_handler');
