<?php

require_once __DIR__ . '/getCourse.php';

// Main execution
try {
    $config = json_decode(file_get_contents('config.json'), true);
    $cookiesFile = $config['cookies_file'] ?? 'cookies.json';
    require_once 'load_cookies.php';
    $cookies = getCookiesFromJson($cookiesFile);

    // Read lesson URLs from result.json
    $resultJson = file_get_contents('result.json');
    $courseData = json_decode($resultJson, true);

    if (!isset($courseData['lessons'])) {
        throw new Exception("No lessons found in result.json");
    }

    $lessons = [];
    foreach ($courseData['lessons'] as $index => $lesson) {
        echo "Processing lesson " . ($index + 1) . ": " . $lesson['title'] . "\n";
        
        $lessonHtml = getCourseData($lesson['link'], $cookies);
        if ($lessonHtml) {
            // Create lesson data with title from result.json
            $lessonData = [
                'title' => $lesson['title'],  // Use title from result.json
                'video_url' => null,
                'duration' => $lesson['duration'],
                'extensions' => "No Extensions Found"
            ];

            // Extract video ID and other data
            preg_match('/<img[^>]+src="https:\/\/img\.youtube\.com\/vi\/([^\/]+)\/hqdefault\.jpg"/', $lessonHtml, $videoIdMatch);
            if (isset($videoIdMatch[1])) {
                $videoId = $videoIdMatch[1];
                $lessonData['video_url'] = "https://www.youtube.com/embed/" . $videoId;

                // Set course thumbnail ONLY for the first lesson
                if ($index === 0) {
                    $thumbnail = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                    $courseData['thumbnail'] = $thumbnail;
                    file_put_contents('result.json', json_encode($courseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    echo "Course thumbnail updated in result.json from first lesson\n";
                }
            }

            // Extract lesson extension content
            preg_match('/<div class="row m3aarf_card">.*?<iframe[^>]+src="([^"]+)"[^>]*>/s', $lessonHtml, $extensionIframeMatches);
            if (isset($extensionIframeMatches[1])) {
                $extensionHtml = getCourseData($extensionIframeMatches[1], $cookies);
                if ($extensionHtml) {
                    $extensionText = strip_tags($extensionHtml);
                    $extensionText = preg_replace('/\* \{[^}]+\}|\s*<style[^>]*>.*?<\/style>/is', '', $extensionText);
                    $lessonData['extensions'] = trim(preg_replace('/\s+/', ' ', $extensionText));
                }
            }

            $lessons[] = $lessonData;
            echo "✅ Lesson data extracted successfully\n";
        } else {
            echo "❌ Failed to fetch lesson page\n";
        }
    }

    if (!empty($lessons)) {
        // Remove the wrapper object and just save the lessons array
        file_put_contents('lesson_data.json', 
            json_encode($lessons, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        echo "\n✅ All lesson data saved to lesson_data.json\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
