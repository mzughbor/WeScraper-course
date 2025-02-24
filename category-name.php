<?php

require_once __DIR__ . '/includes/category_helper.php';

class CategoryManager {
    private $category_ids = [
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

    public function get_category_id($category_name) {
        // If category already exists in array, return its ID immediately
        if (isset($this->category_ids[$category_name])) {
            return $this->category_ids[$category_name];
        }

        // If not found in array, check/create it in WordPress
        $category_id = get_or_create_category($category_name);

        // If successful, store the new ID in the array
        if ($category_id) {
            $this->category_ids[$category_name] = $category_id;
        }

        return $category_id;
    }

    public function get_all_categories() {
        return $this->category_ids;
    }
}

?>
