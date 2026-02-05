<?php
/**
 * Oxford Course Discovery - Filter System Debug
 */
require_once('../../../wp-load.php');

echo '<h1>Oxford Course Discovery - System Debug</h1>';
echo '<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    pre { background: #333; color: #fff; padding: 10px; overflow: auto; }
</style>';

// ==================== 1. CHECK PLUGIN STATUS ====================
echo '<div class="box"><h2>1. Plugin Status Check</h2>';
$plugin_path = 'oxford-course-discovery/oxford-course-discovery.php';
$plugin_active = is_plugin_active($plugin_path);
if ($plugin_active) {
    echo '<p class="success">✅ Plugin is ACTIVE</p>';
} else {
    echo '<p class="error">❌ Plugin is NOT ACTIVE</p>';
    echo '<p>Trying to activate...</p>';
    activate_plugin($plugin_path);
}
echo '</div>';

// ==================== 2. CHECK SHORTCODE ====================
echo '<div class="box"><h2>2. Shortcode Registration</h2>';
if (shortcode_exists('course_discovery')) {
    echo '<p class="success">✅ Shortcode [course_discovery] is REGISTERED</p>';
    
    // Test the shortcode output
    echo '<h3>Shortcode Test Output:</h3>';
    $test_output = do_shortcode('[course_discovery]');
    if (strpos($test_output, 'oxford-course-discovery') !== false) {
        echo '<p class="success">✅ Shortcode outputs plugin HTML</p>';
    } else {
        echo '<p class="error">❌ Shortcode output is empty or incorrect</p>';
        echo '<pre>' . htmlspecialchars(substr($test_output, 0, 500)) . '...</pre>';
    }
} else {
    echo '<p class="error">❌ Shortcode [course_discovery] is NOT REGISTERED</p>';
}
echo '</div>';

// ==================== 3. CHECK POST TYPES ====================
echo '<div class="box"><h2>3. Custom Post Types</h2>';
$post_types = ['course', 'provider', 'instructor'];
foreach ($post_types as $pt) {
    if (post_type_exists($pt)) {
        echo '<p class="success">✅ Post type "' . $pt . '" exists</p>';
        
        // Count posts
        $count = wp_count_posts($pt)->publish;
        echo '<p>Published ' . $pt . 's: ' . $count . '</p>';
    } else {
        echo '<p class="error">❌ Post type "' . $pt . '" MISSING</p>';
    }
}
echo '</div>';

// ==================== 4. CHECK ACF FIELDS ====================
echo '<div class="box"><h2>4. ACF Fields Check</h2>';
if (class_exists('ACF')) {
    echo '<p class="success">✅ ACF is active</p>';
    
    // Check course fields
    $course_fields = ['price', 'short_description', 'providers', 'start_dates'];
    $course_id = get_posts(['post_type' => 'course', 'posts_per_page' => 1, 'fields' => 'ids']);
    
    if ($course_id) {
        echo '<p>Testing ACF fields on course ID: ' . $course_id[0] . '</p>';
        foreach ($course_fields as $field) {
            $value = get_field($field, $course_id[0]);
            if ($value !== false) {
                echo '<p class="success">✅ Field "' . $field . '" works</p>';
            } else {
                echo '<p class="warning">⚠ Field "' . $field . '" returns false (might be empty)</p>';
            }
        }
    }
} else {
    echo '<p class="error">❌ ACF is NOT active</p>';
}
echo '</div>';

// ==================== 5. CHECK AJAX HANDLER ====================
echo '<div class="box"><h2>5. AJAX Handler Check</h2>';
$ajax_actions = ['filter_courses'];
foreach ($ajax_actions as $action) {
    if (has_action('wp_ajax_' . $action) || has_action('wp_ajax_nopriv_' . $action)) {
        echo '<p class="success">✅ AJAX action "' . $action . '" is registered</p>';
    } else {
        echo '<p class="error">❌ AJAX action "' . $field . '" is NOT registered</p>';
    }
}
echo '</div>';

// ==================== 6. CHECK ASSETS ====================
echo '<div class="box"><h2>6. CSS & JavaScript Files</h2>';
$plugin_url = plugin_dir_url(__FILE__);
$assets = [
    'CSS' => $plugin_url . 'assets/css/frontend.css',
    'JS' => $plugin_url . 'assets/js/frontend.js'
];

foreach ($assets as $type => $url) {
    $response = @get_headers($url);
    if ($response && strpos($response[0], '200')) {
        echo '<p class="success">✅ ' . $type . ' file is accessible: <a href="' . $url . '" target="_blank">' . basename($url) . '</a></p>';
    } else {
        echo '<p class="error">❌ ' . $type . ' file is MISSING: ' . $url . '</p>';
    }
}
echo '</div>';

// ==================== 7. CHECK DATABASE TABLE ====================
echo '<div class="box"><h2>7. Database Tables</h2>';
global $wpdb;
$table_name = $wpdb->prefix . 'oxford_course_dates';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

if ($table_exists) {
    echo '<p class="success">✅ Custom table "' . $table_name . '" exists</p>';
    
    // Count records
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    echo '<p>Records in table: ' . $count . '</p>';
    
    // Show sample data
    $dates = $wpdb->get_results("SELECT month_year FROM $table_name GROUP BY month_year ORDER BY start_date LIMIT 5");
    if ($dates) {
        echo '<p>Sample dates in filter:</p><ul>';
        foreach ($dates as $date) {
            echo '<li>' . $date->month_year . '</li>';
        }
        echo '</ul>';
    }
} else {
    echo '<p class="error">❌ Custom table "' . $table_name . '" is MISSING</p>';
    echo '<p><button onclick="createTable()">Create Table Now</button></p>';
}
echo '</div>';

// ==================== 8. CHECK PAGE CONTENT ====================
echo '<div class="box"><h2>8. Course Discovery Page</h2>';
$page = get_page_by_path('course-discovery');
if ($page) {
    echo '<p class="success">✅ Page exists (ID: ' . $page->ID . ')</p>';
    
    // Check page content
    if (has_shortcode($page->post_content, 'course_discovery')) {
        echo '<p class="success">✅ Page contains [course_discovery] shortcode</p>';
    } else {
        echo '<p class="error">❌ Page does NOT contain [course_discovery] shortcode</p>';
        echo '<p><a href="/wp-admin/post.php?post=' . $page->ID . '&action=edit">Edit Page</a> and add shortcode: <code>[course_discovery]</code></p>';
    }
    
    // View page
    echo '<p><a href="' . get_permalink($page->ID) . '" target="_blank">View Live Page</a></p>';
} else {
    echo '<p class="error">❌ "course-discovery" page does not exist</p>';
    echo '<p><button onclick="createPage()">Create Page Now</button></p>';
}
echo '</div>';

// ==================== 9. PHP ERROR CHECK ====================
echo '<div class="box"><h2>9. PHP Error Check</h2>';
// Enable error reporting temporarily
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load the shortcode with error capture
ob_start();
$output = do_shortcode('[course_discovery]');
$errors = ob_get_clean();

if ($errors) {
    echo '<p class="error">❌ PHP Errors Found:</p>';
    echo '<pre>' . htmlspecialchars($errors) . '</pre>';
} else {
    echo '<p class="success">✅ No PHP errors when executing shortcode</p>';
}
echo '</div>';

// ==================== 10. QUICK FIX BUTTONS ====================
echo '<div class="box"><h2>10. Quick Fixes</h2>';
echo '<form method="post">';
wp_nonce_field('oxford_fixes', 'oxford_nonce');

echo '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 20px 0;">';
echo '<div><button type="submit" name="fix_page" class="button button-primary">Fix Page Shortcode</button><br><small>Adds [course_discovery] to page</small></div>';
echo '<div><button type="submit" name="fix_table" class="button button-primary">Recreate Database Table</button><br><small>Creates missing dates table</small></div>';
echo '<div><button type="submit" name="fix_assets" class="button button-primary">Check Asset Files</button><br><small>Verifies CSS/JS files</small></div>';
echo '<div><button type="submit" name="fix_sample" class="button button-primary">Create Sample Data</button><br><small>Adds test courses & providers</small></div>';
echo '</div>';
echo '</form>';
echo '</div>';

// ==================== HANDLE FORM SUBMISSIONS ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('oxford_fixes', 'oxford_nonce')) {
    if (isset($_POST['fix_page'])) {
        $page = get_page_by_path('course-discovery');
        if ($page) {
            wp_update_post([
                'ID' => $page->ID,
                'post_content' => '[course_discovery]'
            ]);
            echo '<p class="success">✅ Page updated with shortcode</p>';
        } else {
            $page_id = wp_insert_post([
                'post_title' => 'Course Discovery',
                'post_name' => 'course-discovery',
                'post_content' => '[course_discovery]',
                'post_status' => 'publish',
                'post_type' => 'page'
            ]);
            echo '<p class="success">✅ Page created with shortcode (ID: ' . $page_id . ')</p>';
        }
    }
    
    if (isset($_POST['fix_table'])) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        global $wpdb;
        $table_name = $wpdb->prefix . 'oxford_course_dates';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            course_id bigint(20) NOT NULL,
            start_date date NOT NULL,
            month_year varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY course_id (course_id),
            KEY start_date (start_date)
        ) $charset_collate;";
        
        dbDelta($sql);
        echo '<p class="success">✅ Database table created/updated</p>';
    }
    
    if (isset($_POST['fix_sample'])) {
        // Create sample provider
        $provider_id = wp_insert_post([
            'post_title' => 'University of Oxford',
            'post_type' => 'provider',
            'post_status' => 'publish'
        ]);
        update_field('location', 'United Kingdom', $provider_id);
        
        // Create sample course
        $course_id = wp_insert_post([
            'post_title' => 'Business Management',
            'post_excerpt' => 'Master business strategies and management principles',
            'post_content' => 'Detailed course description.',
            'post_type' => 'course',
            'post_status' => 'publish'
        ]);
        update_field('price', 2499, $course_id);
        update_field('short_description', 'Master business strategies', $course_id);
        update_field('providers', [$provider_id], $course_id);
        
        // Add dates
        $dates = [
            ['month_year' => 'September-2024', 'actual_date' => '2024-09-01'],
            ['month_year' => 'January-2025', 'actual_date' => '2025-01-01']
        ];
        update_field('start_dates', $dates, $course_id);
        
        echo '<p class="success">✅ Sample data created (Provider ID: ' . $provider_id . ', Course ID: ' . $course_id . ')</p>';
    }
    
    echo '<meta http-equiv="refresh" content="2">';
}
?>

<script>
function createTable() {
    if (confirm('Create the missing database table?')) {
        window.location.href = '<?php echo admin_url('admin.php?page=course-discovery-settings'); ?>';
    }
}

function createPage() {
    if (confirm('Create the Course Discovery page?')) {
        window.location.href = '<?php echo admin_url('post-new.php?post_type=page'); ?>';
    }
}
</script>