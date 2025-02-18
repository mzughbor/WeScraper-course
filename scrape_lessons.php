<?php

require_once __DIR__ . '/getCourse.php';

// Read the JSON file that was previously saved by the course scraper (scrape_course.php)
$jsonData = file_get_contents('result.json');
$data = json_decode($jsonData, true);

// Check if the data is properly decoded
if ($data === null) {
    die("Error decoding JSON data.");
}

// Load cookies configuration
$config = json_decode(file_get_contents('config.json'), true);
$cookiesFile = $config['cookies_file'] ?? 'cookies.json';
require_once 'load_cookies.php';
$cookies = getCookiesFromJson($cookiesFile);

// Initialize lessons array
$allLessonData = [];

// Loop through each lesson
foreach ($data['lessons'] as $lesson) {
    $lessonTitle = $lesson['title'];
    $lessonLink = $lesson['link'];
    $videoLength = $lesson['duration'] ?? "Unknown";

    echo "-----------------------------------------------------" . "\n";
    echo "Processing: " . $lessonTitle . "\n";

    // Fetch the lesson page HTML using the saved lesson link
    $lessonHtml = getCourseData($lessonLink, $cookies);

    if (!$lessonHtml) {
        echo "❌ Failed to fetch lesson: " . $lessonTitle . "\n";
        continue;
    }

    // Extract YouTube video ID - using simpler regex
    if (preg_match('/img\.youtube\.com\/vi\/([^\/]+)\//', $lessonHtml, $videoIdMatches)) {
        $videoId = $videoIdMatches[1];
        $videoUrl = "https://www.youtube.com/embed/" . $videoId;
        echo "✓ Found video ID: " . $videoId . "\n";
    } else {
        $videoUrl = "N/A";
        echo "⚠ No video ID found\n";
    }

    // Extract lesson extension content - using simpler approach
    $extensionText = "";
    if (preg_match('/<div class="row m3aarf_card">(.*?)<\/div>/s', $lessonHtml, $matches)) {
        $extensionHtml = $matches[1];
        
        // Clean the text
        $extensionText = strip_tags($extensionHtml);
        $extensionText = preg_replace('/\s+/', ' ', trim($extensionText));
        
        echo "✓ Found lesson extension\n";
    } else {
        $extensionText = "No extension content";
        echo "⚠ No extension content found\n";
    }

    // Store lesson data
    $lessonData = [
        'title' => $lessonTitle,
        'video_url' => $videoUrl,
        'video_length' => $videoLength,
        'extensions' => $extensionText,
    ];

    $allLessonData[] = $lessonData;
    echo "✓ Lesson data stored\n";
}

// Save all lessons data
if (!empty($allLessonData)) {
    $lessonDataJson = [
        'lessons' => $allLessonData,
        'total_lessons' => count($allLessonData),
        'scraped_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents(
        "lesson_data.json", 
        json_encode($lessonDataJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
    
    echo "\n✅ Successfully saved " . count($allLessonData) . " lessons to lesson_data.json!\n";
} else {
    echo "\n❌ No lessons data to save.\n";
}

?>
