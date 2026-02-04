<?php
/**
 * Plugin Name: Oxford Course Discovery System
 * Plugin URI: https://oxfordinternational.com/
 * Description: Custom course discovery system for Oxford International
 * Version: 1.0.0
 * Author: Santosh Singh
 * License: GPL v2 or later
 * Text Domain: oxford-cds
 */

// Prevent direct access
defined('ABSPATH') || exit;

// Define plugin constants
define('OXFORD_CDS_PATH', plugin_dir_path(__FILE__));
define('OXFORD_CDS_URL', plugin_dir_url(__FILE__));
define('OXFORD_CDS_VERSION', '1.0.0');

// Core includes
require_once OXFORD_CDS_PATH . 'includes/post-types.php';
require_once OXFORD_CDS_PATH . 'includes/database.php';
require_once OXFORD_CDS_PATH . 'includes/filters.php';
require_once OXFORD_CDS_PATH . 'includes/shortcodes.php';
require_once OXFORD_CDS_PATH . 'includes/ajax-handler.php';
require_once OXFORD_CDS_PATH . 'includes/admin-settings.php';

// Initialize plugin
add_action('init', 'oxford_cds_init');
function oxford_cds_init() {
    // Load text domain for translations
    load_plugin_textdomain('oxford-cds', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Enqueue scripts and styles
    add_action('wp_enqueue_scripts', 'oxford_cds_enqueue_assets');
    add_action('admin_enqueue_scripts', 'oxford_cds_admin_assets');
}

function oxford_cds_enqueue_assets() {
    // Only load on pages with our shortcode
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'course_discovery')) {
        wp_enqueue_style(
            'oxford-cds-frontend',
            OXFORD_CDS_URL . 'assets/css/frontend.css',
            [],
            OXFORD_CDS_VERSION
        );
        
        wp_enqueue_script(
            'oxford-cds-frontend',
            OXFORD_CDS_URL . 'assets/js/frontend.js',
            ['jquery'],
            OXFORD_CDS_VERSION,
            true
        );
        
        wp_localize_script('oxford-cds-frontend', 'oxford_cds_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oxford_cds_nonce')
        ]);
    }
}

function oxford_cds_admin_assets($hook) {
    // Only load on course-related admin pages
    $screen = get_current_screen();
    if ($screen && in_array($screen->post_type, ['course', 'instructor', 'provider'])) {
        wp_enqueue_style('oxford-cds-admin', OXFORD_CDS_URL . 'assets/css/admin.css');
    }
}

// Activation hook
register_activation_hook(__FILE__, 'oxford_cds_activate');
function oxford_cds_activate() {
    // Trigger database setup
    require_once OXFORD_CDS_PATH . 'includes/database.php';
    oxford_cds_install();
    
    // Flush rewrite rules for custom post types
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'oxford_cds_deactivate');
function oxford_cds_deactivate() {
    flush_rewrite_rules();
}