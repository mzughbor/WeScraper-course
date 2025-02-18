<?php
require_once __DIR__ . '/includes/tutor-api-client.php';

try {
    $api_client = new TutorAPIClient();
    
    // Test course data
    $course_data = [
        "title" => "My New Course title",
        "description" => "A detailed description of my new course. description",
        "instructor" => 1,
        "thumbnail_id" => 1,
        "categories" => [10, 11],
        "price_type" => "paid",
        "price" => 99.99,
        "post_status" => "publish",
        "post_author" => "5",
        "post_content" => "This is a detailed description of my new course.",
        "post_title" => "My Test Course",
        "course_level" => "beginner"
    ];
    
    echo "Attempting to create a new course...\n";
    echo "Course data to be sent:\n";
    echo json_encode($course_data, JSON_PRETTY_PRINT) . "\n\n";
    
    $response = $api_client->makeRequest(
        TutorAPIConfig::ENDPOINT_COURSES,
        'POST',
        $course_data
    );
    
    echo "Course creation successful!\n";
    echo "API Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Course creation failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
} 