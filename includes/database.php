<?php
// Database setup and custom tables
add_action('activate_oxford-course-discovery/oxford-course-discovery.php', 'oxford_cds_install');
function oxford_cds_install() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'oxford_course_dates';
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        course_id bigint(20) NOT NULL,
        start_date date NOT NULL,
        month_year varchar(50) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY course_id (course_id),
        KEY start_date (start_date),
        KEY month_year (month_year)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Add version option for future migrations
    update_option('oxford_cds_db_version', '1.0.0');
    
    // Check if we need to insert sample data
    oxford_cds_insert_sample_data();
}

function oxford_cds_insert_sample_data() {
    // Only insert if no courses exist yet
    $course_count = wp_count_posts('course');
    if ($course_count->publish == 0) {
        // Create sample categories
        $categories = ['Computer Science', 'Business', 'Arts', 'Engineering', 'Medicine'];
        $cat_ids = [];
        
        foreach ($categories as $category) {
            $term = wp_insert_term($category, 'course_category');
            if (!is_wp_error($term)) {
                $cat_ids[] = $term['term_id'];
            }
        }
        
        // Create sample providers
        $providers = [
            ['University of Oxford', 'Oxford, UK'],
            ['Cambridge University', 'Cambridge, UK'],
            ['Harvard University', 'Cambridge, USA'],
            ['Stanford University', 'Stanford, USA']
        ];
        
        $provider_ids = [];
        foreach ($providers as $provider) {
            $provider_post = wp_insert_post([
                'post_title' => $provider[0],
                'post_type' => 'provider',
                'post_status' => 'publish'
            ]);
            
            if ($provider_post) {
                $provider_ids[] = $provider_post;
                update_post_meta($provider_post, 'location', $provider[1]);
            }
        }
        
        // Create sample courses
        $sample_courses = [
            [
                'title' => 'Web Development Bootcamp',
                'excerpt' => 'Learn full-stack web development with modern technologies',
                'price' => 2999,
                'category' => $cat_ids[0] ?? 0
            ],
            [
                'title' => 'Business Management',
                'excerpt' => 'Master business strategies and management principles',
                'price' => 2499,
                'category' => $cat_ids[1] ?? 0
            ],
            [
                'title' => 'Digital Art & Design',
                'excerpt' => 'Create stunning digital art and designs',
                'price' => 1999,
                'category' => $cat_ids[2] ?? 0
            ]
        ];
        
        $month_years = ['January-2024', 'February-2024', 'March-2024', 'April-2024'];
        
        foreach ($sample_courses as $index => $course_data) {
            $course_id = wp_insert_post([
                'post_title' => $course_data['title'],
                'post_excerpt' => $course_data['excerpt'],
                'post_content' => '<p>Detailed course description for ' . $course_data['title'] . '. This course covers all essential topics.</p>',
                'post_type' => 'course',
                'post_status' => 'publish'
            ]);
            
            if ($course_id) {
                // Assign category
                if ($course_data['category']) {
                    wp_set_post_terms($course_id, [$course_data['category']], 'course_category');
                }
                
                // Add meta fields
                update_post_meta($course_id, 'price', $course_data['price']);
                update_post_meta($course_id, 'short_description', $course_data['excerpt']);
                update_post_meta($course_id, 'long_description', '<p>Long detailed description for ' . $course_data['title'] . '.</p>');
                
                // Assign providers (randomly)
                if (!empty($provider_ids)) {
                    $assigned_providers = array_slice($provider_ids, 0, rand(1, 2));
                    update_post_meta($course_id, 'providers', $assigned_providers);
                }
                
                // Add course dates
                oxford_cds_add_course_dates($course_id, $month_years);
            }
        }
    }
}

function oxford_cds_add_course_dates($course_id, $month_years) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'oxford_course_dates';
    
    foreach ($month_years as $month_year) {
        // Convert Month-Year to actual date (first day of month)
        $date_parts = explode('-', $month_year);
        if (count($date_parts) == 2) {
            $month = $date_parts[0];
            $year = $date_parts[1];
            $start_date = date('Y-m-d', strtotime("first day of $month $year"));
            
            $wpdb->insert($table_name, [
                'course_id' => $course_id,
                'start_date' => $start_date,
                'month_year' => $month_year
            ]);
        }
    }
}

// Migration for future updates
add_action('plugins_loaded', 'oxford_cds_check_db_updates');
function oxford_cds_check_db_updates() {
    $current_version = get_option('oxford_cds_db_version', '1.0.0');
    
    if (version_compare($current_version, '1.1.0', '<')) {
        oxford_cds_update_to_110();
    }
}

function oxford_cds_update_to_110() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'oxford_course_dates';
    
    // Example: Add new column for future extension
    $column_exists = $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'is_active'");
    if (!$column_exists) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER month_year");
    }
    
    update_option('oxford_cds_db_version', '1.1.0');
}

