#!/bin/bash

echo "🚀 Starting WeScraper process..."

# Function to run a PHP script and check its exit status
run_script() {
    echo -e "\n==========================================";
    echo "🔄 Running $1...";
    echo "==========================================";
    
    php "$1"
    
    if [ $? -ne 0 ]; then
        echo -e "\n❌ Error: $1 failed!";
        exit 1;
    fi
    
    echo -e "\n✅ $1 completed successfully!";
}

# Run each script in sequence
run_script scrape_course.php
run_script extenstion_scrape_lessons.php
run_script add-course.php

echo -e "\n✨ All processes completed successfully! ✨" 