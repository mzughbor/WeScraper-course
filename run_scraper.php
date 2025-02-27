<?php

function runCommand($command, $description) {
    echo "\n🚀 Starting: $description...\n";
    echo "==========================================\n";
    
    $output = [];
    $return_var = 0;
    exec("php $command", $output, $return_var);
    
    // Print the output
    echo implode("\n", $output) . "\n";
    
    if ($return_var !== 0) {
        echo "\n❌ Error: $description failed!\n";
        exit(1);
    }
    
    echo "\n✅ Completed: $description\n";
    echo "==========================================\n";
}

try {
    // Step 1: Run course scraper
    runCommand('scrape_course.php', 'Course Scraping');
    
    // Step 2: Run lesson scraper with extensions
    runCommand('extenstion_scrape_lessons.php', 'Lesson Scraping');
    
    // Step 3: Upload thumbnail (this is now called from extenstion_scrape_lessons.php)
    // The thumbnail upload is already integrated into extenstion_scrape_lessons.php
    
    // Step 4: Create course with lessons
    runCommand('add-course.php', 'Course Creation');
    
    echo "\n✨ All processes completed successfully! ✨\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
} 