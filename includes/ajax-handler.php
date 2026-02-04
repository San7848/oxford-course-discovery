<?php
// AJAX handlers for filter requests
add_action('wp_ajax_filter_courses', 'oxford_ajax_filter_courses');
add_action('wp_ajax_nopriv_filter_courses', 'oxford_ajax_filter_courses');
function oxford_ajax_filter_courses() {
    check_ajax_referer('oxford_cds_nonce', 'nonce');
    
    $filters = [];
    
    // Sanitize all filter inputs
    if (!empty($_POST['text_search'])) {
        $filters['text_search'] = sanitize_text_field($_POST['text_search']);
    }
    
    if (!empty($_POST['provider'])) {
        $filters['provider'] = array_map('intval', $_POST['provider']);
    }
    
    if (!empty($_POST['location'])) {
        $filters['location'] = array_map('sanitize_text_field', $_POST['location']);
    }
    
    if (!empty($_POST['start_date'])) {
        $filters['start_date'] = array_map('sanitize_text_field', $_POST['start_date']);
    }
    
    if (!empty($_POST['category'])) {
        $filters['category'] = array_map('intval', $_POST['category']);
    }
    
    // Render courses with filters
    ob_start();
    oxford_render_courses($filters);
    $html = ob_get_clean();
    
    wp_send_json_success(['html' => $html]);
}