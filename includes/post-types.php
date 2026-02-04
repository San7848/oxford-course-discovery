<?php
// Register custom post types and taxonomies
add_action('init', 'oxford_register_post_types');
function oxford_register_post_types() {
    // Course Post Type
    register_post_type('course', [
        'labels' => [
            'name' => __('Courses', 'oxford-cds'),
            'singular_name' => __('Course', 'oxford-cds'),
            'add_new' => __('Add New Course', 'oxford-cds'),
            'add_new_item' => __('Add New Course', 'oxford-cds'),
            'edit_item' => __('Edit Course', 'oxford-cds'),
            'new_item' => __('New Course', 'oxford-cds'),
            'view_item' => __('View Course', 'oxford-cds'),
            'search_items' => __('Search Courses', 'oxford-cds'),
            'not_found' => __('No courses found', 'oxford-cds'),
            'not_found_in_trash' => __('No courses found in Trash', 'oxford-cds'),
            'menu_name' => __('Courses', 'oxford-cds')
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'courses'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'taxonomies' => ['course_category'],
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'show_in_menu' => true,
        'show_ui' => true,
        'publicly_queryable' => true
    ]);
    
    // Instructor Post Type
    register_post_type('instructor', [
        'labels' => [
            'name' => __('Instructors', 'oxford-cds'),
            'singular_name' => __('Instructor', 'oxford-cds'),
            'add_new' => __('Add New Instructor', 'oxford-cds'),
            'add_new_item' => __('Add New Instructor', 'oxford-cds'),
            'edit_item' => __('Edit Instructor', 'oxford-cds'),
            'new_item' => __('New Instructor', 'oxford-cds'),
            'view_item' => __('View Instructor', 'oxford-cds'),
            'search_items' => __('Search Instructors', 'oxford-cds'),
            'not_found' => __('No instructors found', 'oxford-cds'),
            'not_found_in_trash' => __('No instructors found in Trash', 'oxford-cds'),
            'menu_name' => __('Instructors', 'oxford-cds')
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'instructors'],
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-businessperson',
        'show_in_menu' => true,
        'show_ui' => true
    ]);
    
    // Provider Post Type
    register_post_type('provider', [
        'labels' => [
            'name' => __('Providers', 'oxford-cds'),
            'singular_name' => __('Provider', 'oxford-cds'),
            'add_new' => __('Add New Provider', 'oxford-cds'),
            'add_new_item' => __('Add New Provider', 'oxford-cds'),
            'edit_item' => __('Edit Provider', 'oxford-cds'),
            'new_item' => __('New Provider', 'oxford-cds'),
            'view_item' => __('View Provider', 'oxford-cds'),
            'search_items' => __('Search Providers', 'oxford-cds'),
            'not_found' => __('No providers found', 'oxford-cds'),
            'not_found_in_trash' => __('No providers found in Trash', 'oxford-cds'),
            'menu_name' => __('Providers', 'oxford-cds')
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'providers'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-building',
        'show_in_menu' => true,
        'show_ui' => true
    ]);
    
    // Course Category Taxonomy
    register_taxonomy('course_category', 'course', [
        'labels' => [
            'name' => __('Course Categories', 'oxford-cds'),
            'singular_name' => __('Course Category', 'oxford-cds'),
            'search_items' => __('Search Categories', 'oxford-cds'),
            'all_items' => __('All Categories', 'oxford-cds'),
            'parent_item' => __('Parent Category', 'oxford-cds'),
            'parent_item_colon' => __('Parent Category:', 'oxford-cds'),
            'edit_item' => __('Edit Category', 'oxford-cds'),
            'update_item' => __('Update Category', 'oxford-cds'),
            'add_new_item' => __('Add New Category', 'oxford-cds'),
            'new_item_name' => __('New Category Name', 'oxford-cds'),
            'menu_name' => __('Categories', 'oxford-cds')
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'course-category'],
        'show_in_rest' => true,
        'public' => true,
        'show_in_nav_menus' => true
    ]);

    // Single course template loader
add_filter('template_include', 'oxford_load_course_template');
function oxford_load_course_template($template) {
    if (is_singular('course')) {
        $plugin_template = OXFORD_CDS_PATH . 'templates/single-course.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}

// Archive course template loader
add_filter('template_include', 'oxford_load_course_archive_template');
function oxford_load_course_archive_template($template) {
    if (is_post_type_archive('course')) {
        $plugin_template = OXFORD_CDS_PATH . 'templates/archive-course.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
}