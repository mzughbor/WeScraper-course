<?php

function getCookiesFromJson($filePath) {
    if (!file_exists($filePath)) {
        die("❌ Error: cookies.json file not found!\n");
    }

    // Read and decode JSON file
    $json = file_get_contents($filePath);
    $cookiesArray = json_decode($json, true);

    if (!$cookiesArray) {
        die("❌ Error: Failed to parse cookies.json\n");
    }

    // Extract relevant cookies
    $cookieNames = ["__eoi", "laravel_session", "remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d", "XSRF-TOKEN"];
    $cookies = [];

    foreach ($cookiesArray as $cookie) {
        if (in_array($cookie["name"], $cookieNames)) {
            $cookies[] = $cookie["name"] . "=" . $cookie["value"];
        }
    }

    if (empty($cookies)) {
        die("❌ Error: No relevant cookies found!\n");
    }

    return implode("; ", $cookies);
}

function checkLoginStatus($url, $cookies) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Cookie: " . $cookies,
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "❌ Error: " . curl_error($ch) . "\n";
        return false;
    }

    curl_close($ch);

    // Check if profile page contains user-specific content
    if (strpos($response, 'My Account') !== false || strpos($response, 'p_name') !== false) {
        return true;  // Logged in
    } else {
        return false; // Not logged in
    }
}

// **MAIN EXECUTION**
$cookiesFile = "cookies.json";
$profileUrl = "https://www.mindluster.com/profile/info";

$cookies = getCookiesFromJson($cookiesFile);

if (checkLoginStatus($profileUrl, $cookies)) {
    echo "✅ Successfully logged in!\n";
} else {
    echo "❌ Not logged in. Please check your cookies.json file.\n";
}

?>
