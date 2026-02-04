<?php
// Admin settings and ACF configuration
add_action('acf/init', 'oxford_acf_fields_setup');
function oxford_acf_fields_setup() {
    if (function_exists('acf_add_local_field_group')) {
        // Course Fields
        acf_add_local_field_group([
            'key' => 'group_course_fields',
            'title' => 'Course Details',
            'fields' => [
                [
                    'key' => 'field_price',
                    'label' => 'Price',
                    'name' => 'price',
                    'type' => 'number',
                    'instructions' => 'Enter the course price in GBP',
                    'required' => 1,
                    'default_value' => 0,
                    'min' => 0
                ],
                [
                    'key' => 'field_short_description',
                    'label' => 'Short Description',
                    'name' => 'short_description',
                    'type' => 'textarea',
                    'rows' => 3,
                    'required' => 1
                ],
                [
                    'key' => 'field_long_description',
                    'label' => 'Long Description',
                    'name' => 'long_description',
                    'type' => 'wysiwyg',
                    'required' => 1
                ],
                [
                    'key' => 'field_instructors',
                    'label' => 'Instructors',
                    'name' => 'instructors',
                    'type' => 'relationship',
                    'post_type' => ['instructor'],
                    'filters' => ['search'],
                    'return_format' => 'id',
                    'multiple' => 1
                ],
                [
                    'key' => 'field_providers',
                    'label' => 'Providers',
                    'name' => 'providers',
                    'type' => 'relationship',
                    'post_type' => ['provider'],
                    'filters' => ['search'],
                    'return_format' => 'id',
                    'multiple' => 1
                ],
                [
                    'key' => 'field_start_dates',
                    'label' => 'Start Dates',
                    'name' => 'start_dates',
                    'type' => 'repeater',
                    'layout' => 'table',
                    'button_label' => 'Add Date',
                    'sub_fields' => [
                        [
                            'key' => 'field_month_year',
                            'label' => 'Month-Year',
                            'name' => 'month_year',
                            'type' => 'text',
                            'instructions' => 'Format: Month-Year (e.g., January-2024)',
                            'required' => 1
                        ],
                        [
                            'key' => 'field_actual_date',
                            'label' => 'Actual Start Date',
                            'name' => 'actual_date',
                            'type' => 'date_picker',
                            'required' => 1
                        ]
                    ]
                ]
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'course',
                    ],
                ],
            ],
        ]);
        
        // Provider Fields
        acf_add_local_field_group([
            'key' => 'group_provider_fields',
            'title' => 'Provider Details',
            'fields' => [
                [
                    'key' => 'field_location',
                    'label' => 'Location',
                    'name' => 'location',
                    'type' => 'text',
                    'required' => 1
                ]
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'provider',
                    ],
                ],
            ],
        ]);
    }
}

// Save hook to update custom dates table
add_action('save_post_course', 'oxford_save_course_dates', 20, 2);
function oxford_save_course_dates($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if ($post->post_type !== 'course') return;
    
    $dates = get_field('start_dates', $post_id);
    global $wpdb;
    $table_name = $wpdb->prefix . 'oxford_course_dates';
    
    // Clear existing dates
    $wpdb->delete($table_name, ['course_id' => $post_id]);
    
    if ($dates) {
        foreach ($dates as $date) {
            $wpdb->insert($table_name, [
                'course_id' => $post_id,
                'start_date' => $date['actual_date'],
                'month_year' => $date['month_year']
            ]);
        }
    }
}

// Add admin menu
add_action('admin_menu', 'oxford_cds_admin_menu');
function oxford_cds_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=course',
        'Course Discovery Settings',
        'Discovery Settings',
        'manage_options',
        'course-discovery-settings',
        'oxford_cds_settings_page'
    );
}

function oxford_cds_settings_page() {
    ?>
    <div class="wrap">
        <h1>Course Discovery System Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('oxford_cds_settings');
            do_settings_sections('oxford_cds_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}