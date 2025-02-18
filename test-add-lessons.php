<?php
require_once __DIR__ . '/includes/tutor-api-client.php';

try {
    $api_client = new TutorAPIClient();
    
    // Course ID from the previously created course
    $course_id = 23716;
    
    // First create a topic (section) with correct field names
    $topic_data = [
        "topic_course_id" => $course_id,
        "topic_title" => "Section 1: Introduction",
        "topic_summary" => "Getting started with the course",
        "topic_author" => 1
    ];
    
    echo "Creating new topic...\n";
    $topic_response = $api_client->makeRequest(
        TutorAPIConfig::ENDPOINT_TOPICS,
        'POST',
        $topic_data
    );
    
    if (!isset($topic_response['data']) || !is_numeric($topic_response['data'])) {
        throw new Exception("Failed to create topic. Response: " . print_r($topic_response, true));
    }
    
    $topic_id = $topic_response['data'];
    echo "Topic created successfully with ID: " . $topic_id . "\n\n";
    
    // Now add some lessons to this topic
    $lessons = [
        [
            "topic_id" => $topic_id,
            "lesson_title" => "Lesson 1: Welcome",
            "lesson_content" => "Welcome to the course! In this lesson we will...",
            "lesson_author" => 1,
            "video" => [
                "source_type" => "youtube",
                "source" => "https://www.youtube.com/watch?v=RWKYJU0MGY0",
                "runtime" => [
                    "hours" => "00",
                    "minutes" => "10",
                    "seconds" => "00"
                ]
            ],
            "preview" => true
        ],
        [
            "topic_id" => $topic_id,
            "lesson_title" => "Lesson 2: Basic Concepts",
            "lesson_content" => "Let's learn about the fundamental concepts...",
            "lesson_author" => 1,
            "video" => [
                "source_type" => "youtube",
                "source" => "https://www.youtube.com/watch?v=RWKYJU0MGY0",
                "runtime" => [
                    "hours" => "00",
                    "minutes" => "10",
                    "seconds" => "00"
                ]
            ],
            "preview" => true
        ]
    ];
    
    foreach ($lessons as $index => $lesson) {
        echo "Creating lesson " . ($index + 1) . "...\n";
        echo "Lesson data: " . json_encode($lesson, JSON_PRETTY_PRINT) . "\n";
        
        $lesson_response = $api_client->makeRequest(
            TutorAPIConfig::ENDPOINT_LESSONS,
            'POST',
            $lesson
        );
        
        if (!isset($lesson_response['id'])) {
            throw new Exception("Failed to create lesson. Response: " . print_r($lesson_response, true));
        }
        
        echo "Lesson " . ($index + 1) . " created successfully with ID: " . $lesson_response['id'] . "\n\n";
    }
    
    echo "All lessons created successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 