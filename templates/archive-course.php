<?php
/**
 * Course Archive Template
 */
get_header(); ?>

<div class="wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            
            <header class="page-header">
                <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
                <?php the_archive_description('<div class="archive-description">', '</div>'); ?>
            </header>
            
            <div class="archive-course-list">
                <?php if (have_posts()) : ?>
                    
                    <div class="course-grid">
                        <?php while (have_posts()) : the_post(); 
                            $course_id = get_the_ID();
                            $price = get_post_meta($course_id, 'price', true);
                            $short_description = get_post_meta($course_id, 'short_description', true);
                        ?>
                        
                        <article class="course-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="course-thumbnail">
                                    <?php the_post_thumbnail('medium'); ?>
                                </div>
                            <?php endif; ?>
                            
                            <h2 class="course-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <?php if ($short_description) : ?>
                                <div class="course-excerpt">
                                    <?php echo esc_html($short_description); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($price) : ?>
                                <div class="course-price">
                                    <?php printf(__('£%s', 'oxford-cds'), esc_html(number_format($price, 0))); ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="<?php the_permalink(); ?>" class="course-link">
                                <?php _e('View Course', 'oxford-cds'); ?>
                            </a>
                        </article>
                        
                        <?php endwhile; ?>
                    </div>
                    
                    <?php the_posts_pagination(); ?>
                    
                <?php else : ?>
                    <p><?php _e('No courses found.', 'oxford-cds'); ?></p>
                <?php endif; ?>
            </div>
            
        </main>
    </div>
</div>

<?php get_footer(); ?>