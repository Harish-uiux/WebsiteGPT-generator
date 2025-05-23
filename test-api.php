<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to test OpenAI API
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

// Run the test and display the result
echo "<pre>";
print_r(json_decode(ai_answer_generator_test_api(), true));
echo "</pre>";
