<?php

require_once __DIR__ . '/getCourse.php';

// Function to clean description text and remove HTML
function cleanText($text) {
    // Remove ads and unwanted scripts (adsbygoogle)
    $text = preg_replace('/\(adsbygoogle = window.adsbygoogle \|\| \[\]\)\.push\(\{\}\);/s', '', $text);
    
    // Remove all HTML tags
    $text = strip_tags($text);
    
    // Convert HTML entities to normal text
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    
    // Remove excessive newlines, carriage returns, and extra spaces
    $text = preg_replace('/\s+/', ' ', trim($text));

    return $text;
}

// Extract data using regex
function extractData($html) {
    $data = [];

    // 1. Course Name
    preg_match('/<h1[^>]*id="course_title"[^>]*>(.*?)<\/h1>/', $html, $match);
    $data["course_name"] = trim($match[1] ?? "Not found");

    // 2. Category
    preg_match('/<li class="breadcrumb-item color">.*?<a[^>]*href="[^"]*certified\/cat\/\d+\/([^"]+)"[^>]*>/', $html, $match);
    $data["category"] = trim($match[1] ?? "Not found");

    // 3. Lesson Count (Fixed)
    preg_match('/<div class="home_title">[^|]+\|\s*(\d+)\s*<\/div>/', $html, $match);
    $data["lesson_count"] = trim($match[1] ?? "Not found");

    // 4. Course Description (Now Clean)
    preg_match_all('/<div class="m3aarf_card">(.*?)<\/div>/s', $html, $matches);
    $raw_description = isset($matches[1]) ? implode("\n", $matches[1]) : "Not found";
    $data["description"] = cleanText($raw_description);

    // 5. Lessons (Links, Titles, and Durations)
    preg_match_all('/<a[^>]*href="(https:\/\/www\.mindluster\.com\/lesson\/\d+-video)"[^>]*title="([^"]+)"/', $html, $matches);
    preg_match_all('/<span class="lesson_duration">([^<]+)<\/span>/', $html, $durationMatches);
    
    $lessons = [];
    foreach ($matches[1] as $index => $link) {
        $lessons[] = [
            "title" => trim($matches[2][$index]),
            "link" => $link,
            "duration" => $durationMatches[1][$index] ?? "Unknown"
        ];
    }
    $data["lessons"] = $lessons;

    // 6. Intro Video (Using First Lesson's Video)
    $data["intro_video"] = isset($lessons[0]) ? $lessons[0]["link"] : "Not found";

    return $data;
}

// **MAIN EXECUTION**
$config = json_decode(file_get_contents('config.json'), true);

$courseUrl = $config['course_url'] ?? "https://www.mindluster.com/certificate/41/Social-Network-Theme-UI-With-Sass-video";
$cookiesFile = $config['cookies_file'] ?? "cookies.json";

// Include function to extract cookies
include 'load_cookies.php';
$cookies = getCookiesFromJson($cookiesFile);

$html = getCourseData($courseUrl, $cookies);

if ($html) {
    $courseData = extractData($html);

    // Save result as JSON file (overwrite existing file)
    file_put_contents("result.json", json_encode($courseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    echo "✅ Course data saved to result.json!\n";
    print_r($courseData);
} else {
    echo "❌ Failed to fetch course data.\n";
}

?>
