<?php
// Register shortcode for course discovery
add_shortcode('course_discovery', 'oxford_course_discovery_shortcode');
function oxford_course_discovery_shortcode($atts) {
    ob_start();
    ?>
    <div class="oxford-course-discovery" role="region" aria-label="Course Discovery">
        <?php oxford_render_filters(); ?>
        <div class="course-results" role="status" aria-live="polite">
            <?php oxford_render_courses(); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function oxford_render_filters() {
    ?>
    <form class="course-filters" id="courseFilters" role="search">
        <div class="filter-section">
            <label for="textSearch" class="screen-reader-text">Search courses</label>
            <input type="text" 
                   id="textSearch" 
                   name="text_search"
                   placeholder="Search courses by name or description"
                   aria-label="Search courses by name or description">
        </div>
        
        <div class="filter-section">
            <label for="providerFilter">Provider</label>
            <select id="providerFilter" name="provider[]" multiple aria-multiselectable="true">
                <?php oxford_render_provider_options(); ?>
            </select>
        </div>
        
        <div class="filter-section">
            <label for="locationFilter">Location</label>
            <select id="locationFilter" name="location[]" multiple aria-multiselectable="true">
                <?php oxford_render_location_options(); ?>
            </select>
        </div>
        
        <div class="filter-section">
            <label for="dateFilter">Start Date</label>
            <select id="dateFilter" name="start_date[]" multiple aria-multiselectable="true">
                <?php oxford_render_date_options(); ?>
            </select>
        </div>
        
        <div class="filter-section">
            <label for="categoryFilter">Category</label>
            <select id="categoryFilter" name="category[]" multiple aria-multiselectable="true">
                <?php oxford_render_category_options(); ?>
            </select>
        </div>
        
        <button type="button" id="applyFilters" class="filter-button">Apply Filters</button>
        <button type="button" id="resetFilters" class="filter-button secondary">Reset</button>
    </form>
    <?php
}

function oxford_render_provider_options() {
    $providers = get_posts([
        'post_type' => 'provider',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    foreach ($providers as $provider) {
        printf('<option value="%d">%s</option>', $provider->ID, esc_html($provider->post_title));
    }
}

function oxford_render_location_options() {
    global $wpdb;
    $locations = $wpdb->get_col("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'location' ORDER BY meta_value ASC");
    
    foreach ($locations as $location) {
        printf('<option value="%s">%s</option>', esc_attr($location), esc_html($location));
    }
}

function oxford_render_date_options() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'oxford_course_dates';
    $dates = $wpdb->get_col("SELECT DISTINCT month_year FROM $table_name ORDER BY start_date ASC");
    
    foreach ($dates as $date) {
        printf('<option value="%s">%s</option>', esc_attr($date), esc_html($date));
    }
}

function oxford_render_category_options() {
    $categories = get_terms([
        'taxonomy' => 'course_category',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    
    foreach ($categories as $category) {
        printf('<option value="%d">%s</option>', $category->term_id, esc_html($category->name));
    }
}

function oxford_render_courses($filters = []) {
    global $oxford_filter_manager;
    
    $args = $oxford_filter_manager->apply_filters($filters);
    $courses = get_posts($args);
    
    if (empty($courses)) {
        echo '<p class="no-results">No courses found matching your criteria.</p>';
        return;
    }
    
    echo '<div class="course-grid">';
    foreach ($courses as $course) {
        $price = get_field('price', $course->ID);
        $instructors = get_field('instructors', $course->ID);
        $providers = get_field('providers', $course->ID);
        ?>
        <article class="course-card" role="article">
            <h3><?php echo esc_html($course->post_title); ?></h3>
            <div class="course-excerpt">
                <?php echo wp_kses_post($course->post_excerpt); ?>
            </div>
            <?php if ($price) : ?>
                <div class="course-price" aria-label="Course price">
                    £<?php echo esc_html($price); ?>
                </div>
            <?php endif; ?>
            <a href="<?php echo get_permalink($course->ID); ?>" 
               class="course-link" 
               aria-label="Learn more about <?php echo esc_attr($course->post_title); ?>">
                Learn More
            </a>
        </article>
        <?php
    }
    echo '</div>';
}