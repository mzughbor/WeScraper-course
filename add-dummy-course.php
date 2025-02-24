<?php
require_once __DIR__ . '/includes/tutor-api-client.php';
require_once __DIR__ . '/category-name.php';

try {
    $api_client = new TutorAPIClient();
    
    // Read the result.json file
    $json_file = __DIR__ . '/result.json';
    if (!file_exists($json_file)) {
        throw new Exception("result.json file not found!");
    }

    $json_data = json_decode(file_get_contents($json_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error parsing JSON: " . json_last_error_msg());
    }

    // Calculate total duration from lessons
    $total_minutes = 0;
    foreach ($json_data['lessons'] as $lesson) {
        $duration_parts = explode(':', $lesson['duration']);
        $total_minutes += ($duration_parts[0] * 60) + $duration_parts[1] + ($duration_parts[2] / 60);
    }
    $duration_hours = floor($total_minutes / 60);
    $duration_minutes = round($total_minutes % 60);

    // Format course benefits from lessons
    $benefits = array_map(function($lesson) {
        return "Learn " . $lesson['title'];
    }, $json_data['lessons']);

    // Read lesson data first to get intro video
    $lesson_file = __DIR__ . '/lesson_data.json';
    if (!file_exists($lesson_file)) {
        throw new Exception("lesson_data.json file not found!");
    }

    $lesson_json_data = json_decode(file_get_contents($lesson_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error parsing lesson_data.json: " . json_last_error_msg());
    }

    // Get first lesson's video for course intro
    $intro_video = isset($lesson_json_data['lessons'][0]) ? $lesson_json_data['lessons'][0]['link'] : null;

    // Get category ID for the course
    $category_manager = new CategoryManager();
    
    // Get category from result.json
    $category_name = $json_data['category'];
    $category_id = $category_manager->get_category_id($category_name);
    
    if (!$category_id) {
        throw new Exception("Failed to get/create category ID for: " . $category_name);
    }
    
    echo "Using category: " . $category_name . " (ID: " . $category_id . ")\n\n";

    // Prepare course data according to API requirements
    $course_data = [
        "post_author" => 1,
        "post_content" => $json_data['description'],
        "post_title" => $json_data['course_name'],
        "post_excerpt" => substr($json_data['description'], 0, 155) . '...',
        "post_status" => "publish",
        "comment_status" => "open",
        "additional_content" => [
            "course_benefits" => implode("\n", $benefits),
            "course_target_audience" => "Students interested in " . $category_name,
            "course_duration" => [
                "hours" => $duration_hours,
                "minutes" => $duration_minutes
            ],
            "course_material_includes" => "Video lessons\nPractical examples\nLifetime access",
            "course_requirements" => "Basic computer knowledge"
        ],
        "course_level" => "beginner",
        "course_categories" => [$category_id],  // Using dynamic category ID
        "thumbnail_id" => 1,
        "video" => $intro_video ? [
            "source_type" => "youtube",
            "source" => $intro_video
        ] : null
    ];
    
    echo "Creating new course from result.json...\n";
    echo "Course data to be sent:\n";
    echo json_encode($course_data, JSON_PRETTY_PRINT) . "\n\n";
    
    // Create the course
    $response = $api_client->makeRequest(
        TutorAPIConfig::ENDPOINT_COURSES,
        'POST',
        $course_data
    );
    
    if (!isset($response['data'])) {
        throw new Exception("Course creation failed. Response: " . print_r($response, true));
    }

    $course_id = $response['data'];
    echo "Course created successfully with ID: " . $course_id . "\n\n";
    
    // Save current course ID to a separate file
    $courseData = [
        'course_id' => $course_id,
        'course_name' => $json_data['course_name'],
        'created_at' => date('Y-m-d H:i:s'),
        'lesson_count' => count($json_data['lessons'])
    ];
    
    if (!is_writable(__DIR__)) {
        echo "************************************************************\n";
        throw new Exception("Error: Cannot write to directory " . __DIR__);
    }
    
    //file_put_contents("current_course_id.json", 
    file_put_contents(__DIR__ . "/current_course_id.json", 
        json_encode($courseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
    echo "Current course ID saved to current_course_id.json\n";
    
    // Before creating topics and lessons, read the current course ID
    $current_course_file = __DIR__ . '/current_course_id.json';
    if (!file_exists($current_course_file)) {
        throw new Exception("current_course_id.json not found! Please create course first.");
    }

    $current_course = json_decode(file_get_contents($current_course_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error parsing current_course_id.json: " . json_last_error_msg());
    }

    $course_id = $current_course['course_id'];
    echo "Using course ID from current_course_id.json: " . $course_id . "\n\n";
    
    // Now create a topic and add lessons
    $topic_data = [
        "topic_course_id" => $course_id,  // Using course ID from json file
        "topic_title" => $json_data['course_name'] . " - Lessons",
        "topic_summary" => "All lessons for " . $json_data['course_name'],
        "topic_author" => 1
    ];
    
    echo "Creating topic for lessons...\n";
    $topic_response = $api_client->makeRequest(
        TutorAPIConfig::ENDPOINT_TOPICS,
        'POST',
        $topic_data
    );
    
    if (!isset($topic_response['data'])) {
        throw new Exception("Failed to create topic. Response: " . print_r($topic_response, true));
    }
    
    $topic_id = $topic_response['data'];
    echo "Topic created with ID: " . $topic_id . "\n\n";
    
    // Read lesson data from lesson_data.json
    $lesson_file = __DIR__ . '/lesson_data.json';
    if (!file_exists($lesson_file)) {
        throw new Exception("lesson_data.json file not found!");
    }

    $lessons = json_decode(file_get_contents($lesson_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error parsing lesson_data.json: " . json_last_error_msg());
    }

    // Add each lesson from lesson_data.json
    echo "Adding lessons from lesson_data.json...\n";
    
    foreach ($lessons as $index => $lesson) {
        // Extract video ID from video_url
        $video_id = '';
        if (preg_match('/embed\/([^\/\?]+)/', $lesson['video_url'], $matches)) {
            $video_id = $matches[1];
        }
        
        if (empty($video_id)) {
            echo "Warning: Skipping lesson " . ($index + 1) . " due to invalid video URL\n";
            continue;
        }

        // Parse duration
        $duration_parts = explode(':', $lesson['video_length']);
        if (count($duration_parts) !== 3) {
            $duration_parts = ['00', '00', '00']; // Default if invalid
        }

        $lesson_data = [
            "topic_id" => $topic_id,
            "lesson_title" => $lesson['title'],
            "lesson_content" => $lesson['extensions'],  // Using the extensions text from lesson data
            "lesson_author" => 1,
            "video" => [
                "source_type" => "youtube",
                "source" => "https://www.youtube.com/watch?v=" . $video_id,
                "runtime" => [
                    "hours" => $duration_parts[0],
                    "minutes" => $duration_parts[1],
                    "seconds" => $duration_parts[2]
                ]
            ]
        ];
        
        echo "Creating lesson " . ($index + 1) . ": " . $lesson['title'] . "\n";
        
        try {
            $lesson_response = $api_client->makeRequest(
                TutorAPIConfig::ENDPOINT_LESSONS,
                'POST',
                $lesson_data
            );
            
            if (!isset($lesson_response['data'])) {
                throw new Exception("Failed to create lesson. Response: " . print_r($lesson_response, true));
            }
            
            echo "Lesson created with ID: " . $lesson_response['data'] . "\n";
        } catch (Exception $e) {
            echo "Warning: Failed to create lesson " . ($index + 1) . ": " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    echo "\nCourse creation completed successfully!\n";
    echo "Course ID: " . $course_id . "\n";
    echo "Total lessons added: " . count($json_data['lessons']) . "\n";
    
    // Save complete course data to file
    $filename = 'created_courses/' . date('Y-m-d_H-i-s') . '_course_' . $course_id . '.json';
    
    if (!file_exists('created_courses')) {
        mkdir('created_courses', 0777, true);
    }
    
    $completeData = [
        'source_data' => $json_data,
        'course_data' => $course_data,
        'course_id' => $course_id,
        'topic_id' => $topic_id,
        'lessons' => $json_data['lessons']
    ];
    
    file_put_contents($filename, 
        json_encode($completeData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
    
    echo "\nComplete course data saved to: $filename\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 