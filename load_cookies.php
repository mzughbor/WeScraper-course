<?php

function getCookiesFromJson($filePath) {
    if (!file_exists($filePath)) {
        die("❌ Error: cookies.json file not found!\n");
    }

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
        die("❌ Error: No relevant cookies found in cookies.json!\n");
    }

    echo "✅ Cookies loaded successfully!\n";
    return implode("; ", $cookies);
}

?>
