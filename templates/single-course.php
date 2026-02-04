<?php
/**
 * Single Course Template
 * This template is loaded when viewing a single course
 */

get_header(); ?>

<div class="wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

            <?php while (have_posts()) : the_post(); 
                $course_id = get_the_ID();
                $price = get_post_meta($course_id, 'price', true);
                $short_description = get_post_meta($course_id, 'short_description', true);
                $long_description = get_post_meta($course_id, 'long_description', true);
                $instructors = get_post_meta($course_id, 'instructors', true);
                $providers = get_post_meta($course_id, 'providers', true);
                $start_dates = get_post_meta($course_id, 'start_dates', true);
                $categories = get_the_terms($course_id, 'course_category');
            ?>
            
            <article id="course-<?php the_ID(); ?>" <?php post_class('single-course'); ?>>
                
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>
                
                <div class="entry-content">
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="course-thumbnail">
                            <?php the_post_thumbnail('large'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($short_description) : ?>
                        <div class="course-short-description">
                            <h2><?php _e('Course Overview', 'oxford-cds'); ?></h2>
                            <p><?php echo esc_html($short_description); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($long_description) : ?>
                        <div class="course-long-description">
                            <h2><?php _e('Course Details', 'oxford-cds'); ?></h2>
                            <div class="course-content">
                                <?php echo wp_kses_post($long_description); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-meta-grid">
                        
                        <?php if ($price) : ?>
                        <div class="course-meta-item">
                            <h3><?php _e('Price', 'oxford-cds'); ?></h3>
                            <div class="course-price">£<?php echo esc_html(number_format($price, 0)); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($instructors && is_array($instructors)) : ?>
                        <div class="course-meta-item">
                            <h3><?php _e('Instructors', 'oxford-cds'); ?></h3>
                            <ul class="instructor-list">
                                <?php foreach ($instructors as $instructor_id) : 
                                    $instructor = get_post($instructor_id);
                                    if ($instructor) : ?>
                                    <li><?php echo esc_html($instructor->post_title); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($providers && is_array($providers)) : ?>
                        <div class="course-meta-item">
                            <h3><?php _e('Providers', 'oxford-cds'); ?></h3>
                            <ul class="provider-list">
                                <?php foreach ($providers as $provider_id) : 
                                    $provider = get_post($provider_id);
                                    $location = get_post_meta($provider_id, 'location', true);
                                    if ($provider) : ?>
                                    <li>
                                        <strong><?php echo esc_html($provider->post_title); ?></strong>
                                        <?php if ($location) : ?>
                                        <br><em><?php echo esc_html($location); ?></em>
                                        <?php endif; ?>
                                    </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($categories && !is_wp_error($categories)) : ?>
                        <div class="course-meta-item">
                            <h3><?php _e('Categories', 'oxford-cds'); ?></h3>
                            <ul class="category-list">
                                <?php foreach ($categories as $category) : ?>
                                    <li><?php echo esc_html($category->name); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($start_dates && is_array($start_dates)) : ?>
                        <div class="course-meta-item">
                            <h3><?php _e('Upcoming Start Dates', 'oxford-cds'); ?></h3>
                            <ul class="date-list">
                                <?php foreach ($start_dates as $date) : 
                                    if (!empty($date['month_year'])) : ?>
                                    <li><?php echo esc_html($date['month_year']); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <div class="course-actions">
                        <a href="<?php echo esc_url(home_url('/course-discovery/')); ?>" 
                           class="button back-to-courses">
                            ← <?php _e('Back to Courses', 'oxford-cds'); ?>
                        </a>
                        <button class="button enroll-button">
                            <?php _e('Enroll Now', 'oxford-cds'); ?>
                        </button>
                    </div>
                    
                </div>
                
            </article>
            
            <?php endwhile; ?>

        </main>
    </div>
</div>

<?php get_footer(); ?>