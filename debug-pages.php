<?php
/**
 * Debug script to check existing pages
 */
require_once('../../../wp-load.php');

echo '<h1>Debug: Checking Pages</h1>';
echo '<style>body{font-family:monospace;}</style>';

// Check all pages
$pages = get_posts([
    'post_type' => 'page',
    'posts_per_page' => -1,
    'post_status' => ['publish', 'draft', 'pending', 'private']
]);

echo '<h2>All Pages:</h2>';
if (empty($pages)) {
    echo '<p>No pages found.</p>';
} else {
    echo '<table border="1" cellpadding="10">';
    echo '<tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Shortcode?</th></tr>';
    foreach ($pages as $page) {
        $has_shortcode = has_shortcode($page->post_content, 'course_discovery') ? 'Yes' : 'No';
        echo '<tr>';
        echo '<td>' . $page->ID . '</td>';
        echo '<td>' . esc_html($page->post_title) . '</td>';
        echo '<td>' . esc_html($page->post_name) . '</td>';
        echo '<td>' . $page->post_status . '</td>';
        echo '<td>' . $has_shortcode . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Check if specific page exists
echo '<h2>Check Specific URL:</h2>';
$page_exists = get_page_by_path('course-discovery-test');
if ($page_exists) {
    echo '<p style="color:green;">✅ Page "course-discovery-test" exists!</p>';
    echo '<p>Page ID: ' . $page_exists->ID . '</p>';
    echo '<p>Status: ' . $page_exists->post_status . '</p>';
    echo '<p>Link: <a href="' . get_permalink($page_exists->ID) . '">' . get_permalink($page_exists->ID) . '</a></p>';
} else {
    echo '<p style="color:red;">❌ Page "course-discovery-test" does NOT exist.</p>';
}

// Check rewrite rules
echo '<h2>Permalink Structure:</h2>';
global $wp_rewrite;
echo '<p><strong>Structure:</strong> ' . ($wp_rewrite->permalink_structure ?: 'Default') . '</p>';

// Quick create button
echo '<h2>Quick Actions:</h2>';
echo '<form method="post">';
wp_nonce_field('create_page', 'debug_nonce');
echo '<p><button type="submit" name="create_course_page">Create Course Discovery Page</button></p>';
echo '</form>';

if (isset($_POST['create_course_page']) && check_admin_referer('create_page', 'debug_nonce')) {
    $page_id = wp_insert_post([
        'post_title' => 'Course Discovery',
        'post_name' => 'course-discovery',
        'post_content' => '[course_discovery]',
        'post_status' => 'publish',
        'post_type' => 'page'
    ]);
    
    if ($page_id) {
        echo '<p style="color:green;">✅ Page created! ID: ' . $page_id . '</p>';
        echo '<p><a href="' . get_permalink($page_id) . '">View Page</a></p>';
    } else {
        echo '<p style="color:red;">❌ Failed to create page.</p>';
    }
}