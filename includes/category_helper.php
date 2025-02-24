<?php

require_once 'config.php';

// Function to send an authenticated API request
function api_request($url, $method = 'GET', $body = null) {
    global $username, $password;

    $options = array(
        'http' => array(
            'method'  => $method,
            'header'  => "Authorization: Basic " . base64_encode("$username:$password") . "\r\n" .
                         "Content-Type: application/json\r\n",
            'content' => $body ? json_encode($body) : ''
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result ? json_decode($result, true) : false;
}

// Function to check if a category exists and get its ID, or create it
function get_or_create_category($category_name) {
    global $api_url;

    // Step 1: Check if category exists
    $categories = api_request($api_url . '?search=' . urlencode($category_name));

    if ($categories && is_array($categories)) {
        foreach ($categories as $category) {
            if (strcasecmp($category['name'], $category_name) == 0) {
                return $category['id']; // Existing category ID
            }
        }
    }

    // Step 2: Create category if not found
    $new_category = api_request($api_url, 'POST', array('name' => $category_name));

    if (isset($new_category['id'])) {
        return $new_category['id']; // Return new category ID
    }

    return false; // Failed to get or create category
}

?>
