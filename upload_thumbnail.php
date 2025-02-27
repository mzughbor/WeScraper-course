<?php
require_once __DIR__ . '/includes/config.php';

try {
    // Read result.json to get thumbnail URL
    $json_file = __DIR__ . '/result.json';
    if (!file_exists($json_file)) {
        throw new Exception("result.json file not found!");
    }

    $json_data = json_decode(file_get_contents($json_file), true);
    if (!isset($json_data['thumbnail'])) {
        throw new Exception("No thumbnail URL found in result.json");
    }

    // Extract video ID from thumbnail URL
    if (preg_match('/vi\/([^\/]+)\/hqdefault\.jpg/', $json_data['thumbnail'], $matches)) {
        $video_id = $matches[1];
    } else {
        throw new Exception("Could not extract video ID from thumbnail URL");
    }

    // Create temp directory if it doesn't exist
    $temp_dir = __DIR__ . '/temp';
    if (!file_exists($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }

    // Download image to temp directory
    $temp_file = $temp_dir . '/' . $video_id . '.jpg';
    if (!file_put_contents($temp_file, file_get_contents($json_data['thumbnail']))) {
        throw new Exception("Failed to download thumbnail image");
    }

    echo "✅ Thumbnail downloaded successfully\n";

    // WordPress REST API upload endpoint
    $wp_api_url = $site_url . '/wp-json/wp/v2/media';
    
    // Setup cURL for WordPress upload
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wp_api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Set WordPress authentication using variables from config.php
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($username . ':' . $password),
        'Content-Disposition: attachment; filename=' . $video_id . '.jpg'
    ]);

    // Prepare file upload
    $file_data = new CURLFile($temp_file, 'image/jpeg', $video_id . '.jpg');
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $file_data]);

    // Make the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Debug output
    echo "Response code: " . $http_code . "\n";
    echo "Response: " . $response . "\n";
    
    // Clean up temp file
    unlink($temp_file);

    if ($http_code !== 201) {
        throw new Exception("Failed to upload thumbnail. Response: " . $response);
    }

    $response_data = json_decode($response, true);
    if (!isset($response_data['id'])) {
        throw new Exception("No media ID in response: " . $response);
    }

    $thumbnail_id = $response_data['id'];
    echo "✅ Thumbnail uploaded successfully with ID: " . $thumbnail_id . "\n";

    // Save thumbnail ID to result.json
    $json_data['thumbnail_id'] = $thumbnail_id;
    file_put_contents($json_file, 
        json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    echo "✅ Thumbnail ID saved to result.json\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} 