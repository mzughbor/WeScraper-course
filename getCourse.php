<?php

function getCourseData($url, $cookies) {
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
    return $response;
}

?>