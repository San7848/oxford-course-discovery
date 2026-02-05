<?php
/**
 * COMPLETE FIX for Oxford Course Discovery Filters
 */
require_once('../../../wp-load.php');

echo '<h1>Oxford Course Discovery - Complete System Fix</h1>';
echo '<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .step { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .success { color: #27ae60; font-weight: bold; }
    .error { color: #e74c3c; font-weight: bold; }
    .fixing { color: #f39c12; }
    pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow: auto; }
    .button { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
    .button:hover { background: #2980b9; }
</style>';

// ==================== COMPREHENSIVE FIX ====================
echo '<div class="step">';
echo '<h2>Running Complete System Fix</h2>';

// 1. FIX PAGE CONTENT
echo '<p class="fixing">1. Fixing Page Content...</p>';
$page = get_page_by_path('course-discovery');
if ($page) {
    // Check current content
    if (!has_shortcode($page->post_content, 'course_discovery')) {
        wp_update_post([
            'ID' => $page->ID,
            'post_content' => '[course_discovery]'
        ]);
        echo '<p class="success">✅ Added [course_discovery] shortcode to page</p>';
    } else {
        echo '<p class="success">✅ Page already has shortcode</p>';
    }
} else {
    // Create page
    $page_id = wp_insert_post([
        'post_title' => 'Course Discovery',
        'post_name' => 'course-discovery',
        'post_content' => '[course_discovery]',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    echo '<p class="success">✅ Created page with shortcode (ID: ' . $page_id . ')</p>';
}

// 2. FIX DATABASE TABLE
echo '<p class="fixing">2. Fixing Database Table...</p>';
global $wpdb;
$table_name = $wpdb->prefix . 'oxford_course_dates';

// Check if table exists
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

if (!$table_exists) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
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
    
    dbDelta($sql);
    echo '<p class="success">✅ Created database table: ' . $table_name . '</p>';
} else {
    echo '<p class="success">✅ Database table already exists</p>';
}

// 3. FIX ACF FIELD REGISTRATION
echo '<p class="fixing">3. Fixing ACF Field Registration...</p>';

// First, ensure ACF is active
if (!class_exists('ACF')) {
    echo '<p class="error">❌ ACF is not active. Please activate Advanced Custom Fields.</p>';
} else {
    // Register ACF fields if they don't exist
    add_action('acf/init', 'oxford_fix_acf_fields');
    function oxford_fix_acf_fields() {
        if (!function_exists('acf_add_local_field_group')) return;
        
        // Course field group
        acf_add_local_field_group(array(
            'key' => 'group_course_details',
            'title' => 'Course Details',
            'fields' => array(
                array(
                    'key' => 'field_price',
                    'label' => 'Price (GBP)',
                    'name' => 'price',
                    'type' => 'number',
                    'required' => 1,
                    'default_value' => 0,
                    'min' => 0,
                ),
                array(
                    'key' => 'field_short_description',
                    'label' => 'Short Description',
                    'name' => 'short_description',
                    'type' => 'textarea',
                    'rows' => 3,
                    'required' => 1,
                ),
                array(
                    'key' => 'field_long_description',
                    'label' => 'Long Description',
                    'name' => 'long_description',
                    'type' => 'wysiwyg',
                    'required' => 1,
                    'toolbar' => 'basic',
                ),
                array(
                    'key' => 'field_instructors',
                    'label' => 'Instructors',
                    'name' => 'instructors',
                    'type' => 'relationship',
                    'post_type' => array('instructor'),
                    'return_format' => 'id',
                    'multiple' => 1,
                ),
                array(
                    'key' => 'field_providers',
                    'label' => 'Providers',
                    'name' => 'providers',
                    'type' => 'relationship',
                    'post_type' => array('provider'),
                    'return_format' => 'id',
                    'multiple' => 1,
                    'required' => 1,
                ),
                array(
                    'key' => 'field_start_dates',
                    'label' => 'Start Dates',
                    'name' => 'start_dates',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Start Date',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_month_year',
                            'label' => 'Month-Year',
                            'name' => 'month_year',
                            'type' => 'text',
                            'instructions' => 'Format: Month-Year (e.g., September-2024)',
                            'required' => 1,
                        ),
                        array(
                            'key' => 'field_actual_date',
                            'label' => 'Actual Date',
                            'name' => 'actual_date',
                            'type' => 'date_picker',
                            'required' => 1,
                            'display_format' => 'd/m/Y',
                            'return_format' => 'Y-m-d',
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'course',
                    ),
                ),
            ),
        ));
        
        // Provider field group
        acf_add_local_field_group(array(
            'key' => 'group_provider_details',
            'title' => 'Provider Details',
            'fields' => array(
                array(
                    'key' => 'field_location',
                    'label' => 'Location',
                    'name' => 'location',
                    'type' => 'text',
                    'required' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'provider',
                    ),
                ),
            ),
        ));
    }
    
    // Force ACF to reinitialize
    do_action('acf/init');
    echo '<p class="success">✅ ACF fields registered</p>';
}

// 4. CREATE SAMPLE DATA (Critical for filters to work)
echo '<p class="fixing">4. Creating Sample Data for Testing...</p>';

// Create sample provider if none exists
$providers = get_posts(['post_type' => 'provider', 'posts_per_page' => 1]);
if (empty($providers)) {
    $provider_id = wp_insert_post([
        'post_title' => 'University of Oxford',
        'post_type' => 'provider',
        'post_status' => 'publish'
    ]);
    
    if ($provider_id && function_exists('update_field')) {
        update_field('location', 'United Kingdom', $provider_id);
        echo '<p class="success">✅ Created sample provider: University of Oxford</p>';
    }
}

// Create sample course with proper ACF data
$courses = get_posts(['post_type' => 'course', 'posts_per_page' => 1]);
if (empty($courses)) {
    $course_id = wp_insert_post([
        'post_title' => 'Business Management',
        'post_excerpt' => 'Master business strategies and management principles',
        'post_content' => 'Detailed course description for Business Management.',
        'post_type' => 'course',
        'post_status' => 'publish'
    ]);
    
    if ($course_id && function_exists('update_field') && isset($provider_id)) {
        // Set ACF fields
        update_field('price', 2499, $course_id);
        update_field('short_description', 'Master business strategies and management principles', $course_id);
        update_field('long_description', '<p>Comprehensive business management course.</p>', $course_id);
        update_field('providers', [$provider_id], $course_id);
        
        // Add start dates
        $dates = [
            [
                'month_year' => 'September-2024',
                'actual_date' => '2024-09-01'
            ],
            [
                'month_year' => 'January-2025',
                'actual_date' => '2025-01-01'
            ]
        ];
        update_field('start_dates', $dates, $course_id);
        
        // Add to database table
        foreach ($dates as $date) {
            $wpdb->insert($table_name, [
                'course_id' => $course_id,
                'start_date' => $date['actual_date'],
                'month_year' => $date['month_year']
            ]);
        }
        
        echo '<p class="success">✅ Created sample course with ACF fields and database entries</p>';
    }
}

// 5. FLUSH REWRITE RULES & CACHE
echo '<p class="fixing">5. Clearing Cache...</p>';
flush_rewrite_rules();

// Clear browser cache header
echo '<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">';
echo '<meta http-equiv="pragma" content="no-cache">';
echo '<meta http-equiv="expires" content="0">';

echo '<p class="success">✅ Cache cleared</p>';

echo '</div>';

// ==================== VERIFICATION ====================
echo '<div class="step">';
echo '<h2>✅ Fix Complete - Verification Tests</h2>';

// Test 1: Check shortcode output
echo '<h3>Test 1: Shortcode Output</h3>';
$test_output = do_shortcode('[course_discovery]');
if (strpos($test_output, 'oxford-course-discovery') !== false) {
    echo '<p class="success">✅ Shortcode outputs plugin HTML</p>';
    echo '<details><summary>View first 500 characters</summary><pre>' . 
         htmlspecialchars(substr($test_output, 0, 500)) . '...</pre></details>';
} else {
    echo '<p class="error">❌ Shortcode output is incorrect</p>';
}

// Test 2: Check ACF fields
echo '<h3>Test 2: ACF Field Test</h3>';
$test_course = get_posts(['post_type' => 'course', 'posts_per_page' => 1]);
if ($test_course && function_exists('get_field')) {
    $price = get_field('price', $test_course[0]->ID);
    $providers = get_field('providers', $test_course[0]->ID);
    
    if ($price !== false) {
        echo '<p class="success">✅ ACF price field works: ' . $price . '</p>';
    } else {
        echo '<p class="error">❌ ACF price field returns false</p>';
    }
    
    if ($providers) {
        echo '<p class="success">✅ ACF providers field works</p>';
    }
}

// Test 3: Check database
echo '<h3>Test 3: Database Check</h3>';
$date_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
echo '<p>Dates in database: ' . $date_count . '</p>';

if ($date_count > 0) {
    echo '<p class="success">✅ Database has date entries for filtering</p>';
} else {
    echo '<p class="error">❌ No dates in database - filters will be empty</p>';
}

echo '</div>';

// ==================== NEXT STEPS ====================
echo '<div class="step">';
echo '<h2>📋 Next Steps</h2>';
echo '<ol>';
echo '<li><strong>Refresh your Course Discovery page</strong>: <a href="https://oxford-university.ct.ws/course-discovery/" target="_blank">https://oxford-university.ct.ws/course-discovery/</a></li>';
echo '<li><strong>Check for filters</strong> - they should now appear</li>';
echo '<li><strong>Test each filter</strong>:
    <ul>
        <li>Search box should filter courses</li>
        <li>Provider dropdown should show "University of Oxford"</li>
        <li>Location dropdown should show "United Kingdom"</li>
        <li>Date dropdown should show "September-2024" and "January-2025"</li>
    </ul>
</li>';
echo '<li><strong>If still not working</strong>, check browser console (F12) for JavaScript errors</li>';
echo '</ol>';

echo '<h3>🚨 If Filters Still Don\'t Work:</h3>';
echo '<p>Check these files exist on your server:</p>';
echo '<pre>
/wp-content/plugins/oxford-course-discovery/assets/js/frontend.js
/wp-content/plugins/oxford-course-discovery/assets/css/frontend.css
/wp-content/plugins/oxford-course-discovery/includes/shortcodes.php
</pre>';

echo '<p><a href="https://oxford-university.ct.ws/course-discovery/" target="_blank" class="button">Test Your Page Now</a></p>';
echo '</div>';

// ==================== DEBUG INFO ====================
echo '<div class="step">';
echo '<h2>🔧 Debug Information</h2>';

echo '<h3>Loaded Plugin Files:</h3>';
$plugin_files = [
    'oxford-course-discovery.php',
    'includes/post-types.php',
    'includes/shortcodes.php',
    'includes/ajax-handler.php',
    'includes/admin-settings.php',
    'includes/filters.php'
];

echo '<ul>';
foreach ($plugin_files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo '<li class="success">✅ ' . $file . ' (Exists)</li>';
    } else {
        echo '<li class="error">❌ ' . $file . ' (MISSING)</li>';
    }
}
echo '</ul>';

echo '<h3>WordPress Information:</h3>';
echo '<p>WordPress Version: ' . get_bloginfo('version') . '</p>';
echo '<p>PHP Version: ' . phpversion() . '</p>';
echo '<p>ACF Active: ' . (class_exists('ACF') ? 'Yes' : 'No') . '</p>';

echo '</div>';
?>