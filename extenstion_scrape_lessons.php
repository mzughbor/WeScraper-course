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

// Initialize an empty array for all lessons data
$allLessonData = [];

// Loop through each lesson from the result.json
foreach ($data['lessons'] as $index => $lesson) {
    $lessonTitle = $lesson['title'];
    $lessonLink = $lesson['link'];
    $videoLength = $lesson['duration'] ?? "Unknown";

    echo "-----------------------------------------------------" . "\n";
    echo "Processing: " . $lessonTitle . "\n";

    // Fetch the lesson page HTML using the saved lesson link
    $lessonHtml = getCourseData($lessonLink, $cookies);

    if ($lessonHtml) {
        // Extract YouTube video ID from the lesson thumbnail image
        preg_match('/<img[^>]+src="https:\/\/img\.youtube\.com\/vi\/([^\/]+)\/hqdefault\.jpg"/', $lessonHtml, $videoIdMatches);
        $videoId = isset($videoIdMatches[1]) ? $videoIdMatches[1] : "N/A";

        // Set course thumbnail ONLY for the first lesson
        if ($index === 0 && $videoId !== "N/A") {
            $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
            
            // Update result.json with the thumbnail
            $jsonData = json_decode(file_get_contents('result.json'), true);
            $jsonData['thumbnail'] = $thumbnail;
            file_put_contents('result.json', json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            echo "Course thumbnail updated in result.json from first lesson\n";
        }

        // Construct YouTube embed URL
        $videoUrl = ($videoId !== "N/A") ? "https://www.youtube.com/embed/" . $videoId : "N/A";

        echo "Extracted Video ID: " . $videoId . "\n";
        echo "YouTube Video URL: " . $videoUrl . "\n";
            
        // Extract lesson extension iframe URL if "row m3aarf_card" exists
        preg_match('/<div class="row m3aarf_card">.*?<iframe[^>]+src="([^"]+)"[^>]*>/s', $lessonHtml, $extensionIframeMatches);

        //var_dump($extensionIframeMatches);

        $extensionUrl = isset($extensionIframeMatches[1]) ? $extensionIframeMatches[1] : null;

        if ($extensionUrl) {
            // Fetch the content from the extension iframe
            $extensionHtml = getCourseData($extensionUrl, $cookies);
           
            // Clean the fetched HTML (strip all tags and extra spaces)
            $lessonExtensionText = strip_tags($extensionHtml);
            
            // Remove any embedded CSS code like "* { font-size: 18px; }"
            $lessonExtensionText = preg_replace('/\* \{[^}]+\}|\s*<style[^>]*>.*?<\/style>/is', '', $lessonExtensionText);

            // Replace multiple spaces with single space and trim the text
            $lessonExtensionText = trim(preg_replace('/\s+/', ' ', $lessonExtensionText));
            
            // Fix text formatting by adding a line break where needed
            $lessonExtensionText = nl2br($lessonExtensionText);
        } else {
            $lessonExtensionText = "No Extensions Found";
        }

        // Output for the current lesson
        echo "Title: " . $lessonTitle . "\n";
        echo "Video Length: " . $videoLength . "\n";
        echo "Extensions: " . $lessonExtensionText . "\n\n";
        
        // Store the result for this lesson
        $lessonData = [
            'title' => $lessonTitle,
            'video_url' => $videoUrl,
            'video_length' => $videoLength,
            'extensions' => $lessonExtensionText,
        ];

        // Add to the all lessons array
        $allLessonData[] = $lessonData;
    } else {
        echo "❌ Failed to fetch lesson page for: " . $lessonTitle . "\n";
    }
}

// Optionally save the accumulated data to a JSON file
if (!empty($allLessonData)) {
    // Save just the array without wrapper object
    file_put_contents("lesson_data.json", 
        json_encode($allLessonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
    echo "✅ Lessons data saved to lesson_data.json!\n";
    
    // Upload thumbnail after successful scraping
    echo "\nUploading thumbnail...\n";
    require_once __DIR__ . '/upload_thumbnail.php';
} else {
    echo "❌ No lessons data to save.\n";
}

?>
