<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create Settings Page
function ai_answer_generator_create_menu() {
    add_options_page(
        'AI Answer Generator Settings',
        'AI Answer Generator',
        'manage_options',
        'ai-answer-generator',
        'ai_answer_generator_settings_page'
    );
}
add_action('admin_menu', 'ai_answer_generator_create_menu');

function ai_answer_generator_settings_page() {
    ?>
    <div class="wrap">
        <h1>AI Answer Generator Settings</h1>
        
        <div class="nav-tab-wrapper">
            <a href="?page=ai-answer-generator&tab=api" class="nav-tab <?php echo !isset($_GET['tab']) || $_GET['tab'] === 'api' ? 'nav-tab-active' : ''; ?>">API Settings</a>
            <a href="?page=ai-answer-generator&tab=appearance" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'appearance' ? 'nav-tab-active' : ''; ?>">Appearance</a>
            <a href="?page=ai-answer-generator&tab=advanced" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'advanced' ? 'nav-tab-active' : ''; ?>">Advanced</a>
        </div>
        
        <div class="tab-content">
            <form method="post" action="options.php">
                <?php
                $tab = isset($_GET['tab']) ? $_GET['tab'] : 'api';
                
                switch ($tab) {
                    case 'appearance':
                        settings_fields('ai_answer_generator_appearance_options');
                        do_settings_sections('ai-answer-generator-appearance');
                        break;
                    case 'advanced':
                        settings_fields('ai_answer_generator_advanced_options');
                        do_settings_sections('ai-answer-generator-advanced');
                        break;
                    default:
                        settings_fields('ai_answer_generator_options');
                        do_settings_sections('ai-answer-generator');
                        break;
                }
                
                submit_button();
                ?>
            </form>
            
            <?php if ($tab === 'api'): ?>
                <hr>
                <h2>Test API Connection</h2>
                <form method="post">
                    <input type="submit" name="ai_test_api" value="Test API Connection" class="button button-primary">
                </form>
                
                <?php
                if (isset($_POST['ai_test_api'])) {
                    $response = ai_answer_generator_test_api();
                    $decoded = json_decode($response, true);
                    
                    if (isset($decoded['candidates'])) {
                        echo '<div class="notice notice-success"><p><strong>API Test Successful!</strong></p>';
                        echo '<p>Response from API:</p>';
                        echo '<pre>' . esc_html(print_r($decoded, true)) . '</pre></div>';
                    } else {
                        echo '<div class="notice notice-error"><p><strong>API Test Failed!</strong></p>';
                        echo '<p>Response from API:</p>';
                        echo '<pre>' . esc_html(print_r($decoded, true)) . '</pre></div>';
                    }
                }
                ?>
                
                <hr>
                <h3>Shortcode Usage</h3>
                <p>Use the following shortcode to add the AI chat to any page or post:</p>
                <code>[ai_answer_generator]</code>
                
                <p>You can also customize the width:</p>
                <code>[ai_answer_generator width="100%" max_width="800px"]</code>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

function ai_answer_generator_register_settings() {
    // API Settings
    register_setting('ai_answer_generator_options', 'ai_answer_generator_api_key');
    register_setting('ai_answer_generator_options', 'ai_answer_generator_model');
    
    add_settings_section(
        'ai_answer_generator_main_section',
        'API Configuration',
        null,
        'ai-answer-generator'
    );
    
    add_settings_field(
        'ai_answer_generator_api_key_field',
        'Gemini API Key',
        'ai_answer_generator_api_key_field_callback',
        'ai-answer-generator',
        'ai_answer_generator_main_section'
    );
    
    add_settings_field(
        'ai_answer_generator_model_field',
        'AI Model',
        'ai_answer_generator_model_field_callback',
        'ai-answer-generator',
        'ai_answer_generator_main_section'
    );
    
    // Appearance Settings
    register_setting('ai_answer_generator_appearance_options', 'ai_answer_generator_bot_name');
    register_setting('ai_answer_generator_appearance_options', 'ai_answer_generator_welcome_message');
    register_setting('ai_answer_generator_appearance_options', 'ai_answer_generator_theme_color');
    register_setting('ai_answer_generator_appearance_options', 'ai_answer_generator_show_branding');
    
    add_settings_section(
        'ai_answer_generator_appearance_section',
        'Appearance Settings',
        null,
        'ai-answer-generator-appearance'
    );
    
    add_settings_field(
        'ai_answer_generator_bot_name_field',
        'Bot Name',
        'ai_answer_generator_bot_name_field_callback',
        'ai-answer-generator-appearance',
        'ai_answer_generator_appearance_section'
    );
    
    add_settings_field(
        'ai_answer_generator_welcome_message_field',
        'Welcome Message',
        'ai_answer_generator_welcome_message_field_callback',
        'ai-answer-generator-appearance',
        'ai_answer_generator_appearance_section'
    );
    
    add_settings_field(
        'ai_answer_generator_theme_color_field',
        'Theme Color',
        'ai_answer_generator_theme_color_field_callback',
        'ai-answer-generator-appearance',
        'ai_answer_generator_appearance_section'
    );
    
    add_settings_field(
        'ai_answer_generator_show_branding_field',
        'Show Branding',
        'ai_answer_generator_show_branding_field_callback',
        'ai-answer-generator-appearance',
        'ai_answer_generator_appearance_section'
    );
    
    // Advanced Settings
    register_setting('ai_answer_generator_advanced_options', 'ai_answer_generator_markdown');
    register_setting('ai_answer_generator_advanced_options', 'ai_answer_generator_streaming');
    
    add_settings_section(
        'ai_answer_generator_advanced_section',
        'Advanced Settings',
        null,
        'ai-answer-generator-advanced'
    );
    
    add_settings_field(
        'ai_answer_generator_markdown_field',
        'Enable Markdown',
        'ai_answer_generator_markdown_field_callback',
        'ai-answer-generator-advanced',
        'ai_answer_generator_advanced_section'
    );
    
    add_settings_field(
        'ai_answer_generator_streaming_field',
        'Enable Streaming Responses',
        'ai_answer_generator_streaming_field_callback',
        'ai-answer-generator-advanced',
        'ai_answer_generator_advanced_section'
    );
}
add_action('admin_init', 'ai_answer_generator_register_settings');

// API Settings Callbacks
function ai_answer_generator_api_key_field_callback() {
    $api_key = get_option('ai_answer_generator_api_key');
    echo '<input type="text" name="ai_answer_generator_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
    echo '<p class="description">Enter your Gemini API key. You can get one from <a href="https://makersuite.google.com/" target="_blank">Google AI Studio</a>.</p>';
}

function ai_answer_generator_model_field_callback() {
    $model = get_option('ai_answer_generator_model', 'gemini-1.5-flash');
    ?>
    <select name="ai_answer_generator_model">
        <option value="gemini-1.5-flash" <?php selected($model, 'gemini-1.5-flash'); ?>>Gemini 1.5 Flash (Fast)</option>
        <option value="gemini-1.5-pro" <?php selected($model, 'gemini-1.5-pro'); ?>>Gemini 1.5 Pro (More Capable)</option>
    </select>
    <p class="description">Choose which AI model to use for generating responses.</p>
    <?php
}

// Appearance Settings Callbacks
function ai_answer_generator_bot_name_field_callback() {
    $bot_name = get_option('ai_answer_generator_bot_name', 'AI Assistant');
    echo '<input type="text" name="ai_answer_generator_bot_name" value="' . esc_attr($bot_name) . '" class="regular-text">';
}

function ai_answer_generator_welcome_message_field_callback() {
    $welcome_message = get_option('ai_answer_generator_welcome_message', 'Hello! How can I help you today?');
    echo '<textarea name="ai_answer_generator_welcome_message" class="large-text" rows="3">' . esc_textarea($welcome_message) . '</textarea>';
}

function ai_answer_generator_theme_color_field_callback() {
    $theme_color = get_option('ai_answer_generator_theme_color', '#4F46E5');
    echo '<input type="color" name="ai_answer_generator_theme_color" value="' . esc_attr($theme_color) . '" class="color-picker">';
}

function ai_answer_generator_show_branding_field_callback() {
    $show_branding = get_option('ai_answer_generator_show_branding', 'yes');
    ?>
    <select name="ai_answer_generator_show_branding">
        <option value="yes" <?php selected($show_branding, 'yes'); ?>>Yes</option>
        <option value="no" <?php selected($show_branding, 'no'); ?>>No</option>
    </select>
    <?php
}

// Advanced Settings Callbacks
function ai_answer_generator_markdown_field_callback() {
    $markdown = get_option('ai_answer_generator_markdown', 'yes');
    ?>
    <select name="ai_answer_generator_markdown">
        <option value="yes" <?php selected($markdown, 'yes'); ?>>Yes</option>
        <option value="no" <?php selected($markdown, 'no'); ?>>No</option>
    </select>
    <p class="description">Enable to render markdown in AI responses (requires a markdown library).</p>
    <?php
}

function ai_answer_generator_streaming_field_callback() {
    $streaming = get_option('ai_answer_generator_streaming', 'no');
    ?>
    <select name="ai_answer_generator_streaming">
        <option value="yes" <?php selected($streaming, 'yes'); ?>>Yes</option>
        <option value="no" <?php selected($streaming, 'no'); ?>>No</option>
    </select>
    <p class="description">Enable to show AI responses as they're being generated. (Note: This is an experimental feature)</p>
    <?php
}

function ai_answer_generator_test_api() {
    $api_key = get_option('ai_answer_generator_api_key');
    $model = get_option('ai_answer_generator_model', 'gemini-1.5-flash');

    if (!$api_key) {
        return json_encode(['success' => false, 'message' => 'API Key is missing']);
    }

    $api_url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $api_key;

    $request_body = json_encode([
        "contents" => [
            [
                "parts" => [
                    ["text" => "Hello, how are you?"]
                ]
            ]
        ]
    ]);

    $response = wp_remote_post($api_url, [
        'method'    => 'POST',
        'body'      => $request_body,
        'headers'   => [
            'Content-Type' => 'application/json',
        ],
        'timeout'   => 20,
    ]);

    if (is_wp_error($response)) {
        return json_encode(['success' => false, 'message' => 'Request Failed: ' . $response->get_error_message()]);
    }

    $body = wp_remote_retrieve_body($response);
    return $body;
}
