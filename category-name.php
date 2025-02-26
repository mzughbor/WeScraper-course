<?php

require_once __DIR__ . '/includes/category_helper.php';

class CategoryManager {
    private $category_ids = [];
    private $json_file;

    public function __construct() {
        $this->json_file = __DIR__ . '/data/categories.json';
        $this->load_categories();
    }

    private function load_categories() {
        // Create data directory if it doesn't exist
        if (!file_exists(dirname($this->json_file))) {
            mkdir(dirname($this->json_file), 0777, true);
        }

        // Create categories.json with default data if it doesn't exist
        if (!file_exists($this->json_file)) {
            $default_categories = [
                'Business' => 33,
                'Cooking' => 35,
                'Digital Marketing' => 37,
                'Fitness' => 39,
                'Graphic Design' => 96,
                'Kitchen and Cooking' => 95,
                'Languages' => 128,
                'Marketing' => 97,
                'Motivation' => 48,
                'Online Art' => 49,
                'Photography' => 52,
                'Programming' => 53,
                'Mobile Development' => 127,
                'Yoga' => 57
            ];
            $this->save_categories($default_categories);
        }

        // Load categories from JSON file
        $json_content = file_get_contents($this->json_file);
        $this->category_ids = json_decode($json_content, true);

        if ($this->category_ids === null) {
            throw new Exception("Error loading categories from JSON file");
        }
    }

    private function save_categories($categories = null) {
        if ($categories === null) {
            $categories = $this->category_ids;
        }
        
        if (!is_writable(dirname($this->json_file))) {
            throw new Exception("Directory is not writable: " . dirname($this->json_file));
        }

        $json_content = json_encode($categories, JSON_PRETTY_PRINT);
        if ($json_content === false) {
            throw new Exception("Error encoding categories to JSON");
        }

        if (file_put_contents($this->json_file, $json_content) === false) {
            throw new Exception("Error saving categories to JSON file");
        }
    }

    public function get_category_id($category_name) {
        // If category already exists in array, return its ID immediately
        if (isset($this->category_ids[$category_name])) {
            echo "Found category '{$category_name}' in local cache with ID: {$this->category_ids[$category_name]}\n";
            return $this->category_ids[$category_name];
        }

        echo "Category '{$category_name}' not found in cache, checking WordPress...\n";
        
        // If not found in array, check/create it in WordPress
        $category_id = get_or_create_category($category_name);

        // If successful, store the new ID and save to JSON
        if ($category_id) {
            $this->category_ids[$category_name] = $category_id;
            $this->save_categories();
            echo "Added new category '{$category_name}' with ID: {$category_id}\n";
        }

        return $category_id;
    }

    public function get_all_categories() {
        return $this->category_ids;
    }
}
