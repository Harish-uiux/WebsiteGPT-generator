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
        <form method="post" action="options.php">
            <?php
            settings_fields('ai_answer_generator_options');
            do_settings_sections('ai-answer-generator');
            submit_button();
            ?>
        </form>

        <h2>Test API</h2>
        <form method="post">
            <input type="submit" name="ai_test_api" value="Test API" class="button button-primary">
        </form>

        <?php
        if (isset($_POST['ai_test_api'])) {
            $response = ai_answer_generator_test_api();
            echo "<pre>" . esc_html(print_r(json_decode($response, true), true)) . "</pre>";
        }
        ?>
    </div>
    <?php
}



function ai_answer_generator_register_settings() {
    register_setting('ai_answer_generator_options', 'ai_answer_generator_api_key');
    add_settings_section(
        'ai_answer_generator_main_section',
        'API Settings',
        null,
        'ai-answer-generator'
    );
    add_settings_field(
        'ai_answer_generator_api_key_field',
        'OpenAI API Key',
        'ai_answer_generator_api_key_field_callback',
        'ai-answer-generator',
        'ai_answer_generator_main_section'
    );
}
add_action('admin_init', 'ai_answer_generator_register_settings');

function ai_answer_generator_api_key_field_callback() {
    $api_key = get_option('ai_answer_generator_api_key');
    echo '<input type="text" name="ai_answer_generator_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
}


function ai_answer_generator_test_api() {
    $api_key = get_option('ai_answer_generator_api_key');

    if (!$api_key) {
        return json_encode(['success' => false, 'message' => 'API Key is missing']);
    }

    $api_url = "https://api.openai.com/v1/chat/completions";

    $request_body = json_encode([
        "model" => "gpt-3.5-turbo",
        "messages" => [["role" => "user", "content" => "Hello, how are you?"]],
        "temperature" => 0.7
    ]);

    $response = wp_remote_post($api_url, [
        'method'    => 'POST',
        'body'      => $request_body,
        'headers'   => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'timeout'   => 20,
    ]);

    if (is_wp_error($response)) {
        return json_encode(['success' => false, 'message' => 'Request Failed: ' . $response->get_error_message()]);
    }

    $body = wp_remote_retrieve_body($response);
    return $body;
}


// AIzaSyAibungBlCTKxqrlNdP6ZfTQYrcSYbLTtw