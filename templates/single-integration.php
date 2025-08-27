<?php
/**
 * Single integration template
 */

get_header();

$meta = WP_Integrations_Directory_Post_Type::get_integration_meta(get_the_ID());
$categories = get_the_terms(get_the_ID(), 'integration_category');
$logo_url = $meta['logo'] ? wp_get_attachment_image_url($meta['logo'], 'large') : '';
$screenshots = $meta['screenshots'] ? $meta['screenshots'] : array();
?>

<div class="wpid-single-integration-container">
    <!-- Integration Hero Section -->
    <div class="wpid-integration-hero" style="background-image: url('https://wcr2.taskip.net/BBG1.png')">
        <div class="wpid-container">
            <div class="wpid-integration-hero-content">
                <div class="wpid-hero-logos">
                    <div class="wpid-hero-logo wpid-site-logo">
                        <img src="<?php echo WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL .'/assets/images/taskip-logo.webp'?>" alt="taskip logo">
                    </div>
                    <div class="wpid-hero-plus">+</div>
                    <div class="wpid-hero-logo wpid-integration-logo">
                        <?php if ($logo_url): ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                        <?php else: ?>
                            <div class="wpid-integration-logo-placeholder">
                                <span class="dashicons dashicons-admin-plugins"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h1 class="wpid-hero-title">
                    <?php echo esc_html(get_option('integration_hero_site_name', get_bloginfo('name'))); ?> + <?php the_title(); ?>
                </h1>
            </div>
        </div>
    </div>
    
    <!-- Integration Header -->
    <div class="integration-header">
        <div class="container">
            <!-- Breadcrumbs -->
            <nav class="integration-breadcrumbs" aria-label="<?php _e('Breadcrumb', 'wp-integrations-directory'); ?>">
                <a href="<?php echo home_url(); ?>"><?php _e('Home', 'wp-integrations-directory'); ?></a>
                <span class="separator">/</span>
                <a href="<?php echo get_post_type_archive_link('integration'); ?>"><?php _e('Integrations', 'wp-integrations-directory'); ?></a>
                <?php if ($categories && !is_wp_error($categories)): ?>
                    <span class="separator">/</span>
                    <a href="<?php echo get_term_link($categories[0]); ?>"><?php echo esc_html($categories[0]->name); ?></a>
                <?php endif; ?>
                <span class="separator">/</span>
                <span class="current"><?php the_title(); ?></span>
            </nav>
            
            <div class="integration-header-content">
                <div class="integration-header-main">
                    <?php if ($logo_url): ?>
                        <div class="integration-logo-large">
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                        </div>
                    <?php endif; ?>
                    
                    <div class="integration-header-info">
                        <h1 class="integration-title"><?php the_title(); ?></h1>
                        
                        <div class="integration-badges-large">
                            
                            <?php if ($categories && !is_wp_error($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <?php $category_meta = WP_Integrations_Directory_Taxonomy::get_category_with_meta($category->term_id); ?>
                                    <a href="<?php echo get_term_link($category); ?>" 
                                       class="integration-category-badge large" 
                                       style="background-color: <?php echo esc_attr($category_meta['color']); ?>">
                                        <?php if ($category_meta['icon']): ?>
                                            <?php if (strpos($category_meta['icon'], 'dashicons-') !== false): ?>
                                                <span class="dashicons <?php echo esc_attr($category_meta['icon']); ?>"></span>
                                            <?php else: ?>
                                                <i class="<?php echo esc_attr($category_meta['icon']); ?>"></i>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="integration-excerpt">
                            <?php if (!empty($meta['description'])): ?>
                                <?php echo wp_kses_post(wpautop($meta['description'])); ?>
                            <?php elseif (has_excerpt()): ?>
                                <?php the_excerpt(); ?>
                            <?php else: ?>
                                <?php echo wp_trim_words(get_the_content(), 30); ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="integration-meta-info">
                            <?php if ($meta['difficulty']): ?>
                                <div class="meta-item">
                                    <span class="meta-label"><?php _e('Difficulty:', 'wp-integrations-directory'); ?></span>
                                    <span class="integration-difficulty integration-difficulty-<?php echo esc_attr($meta['difficulty']); ?>">
                                        <?php echo esc_html(ucfirst($meta['difficulty'])); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($meta['setup_time']): ?>
                                <div class="meta-item">
                                    <span class="meta-label"><?php _e('Setup Time:', 'wp-integrations-directory'); ?></span>
                                    <span class="setup-time">
                                        <i class="dashicons dashicons-clock"></i>
                                        <?php echo esc_html($meta['setup_time']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="integration-header-actions">
                    <?php if ($meta['external_url']): ?>
                        <a href="<?php echo esc_url($meta['external_url']); ?>" 
                           class="button button-primary button-large" 
                           target="_blank" 
                           rel="noopener noreferrer">
                            <?php _e('Visit Official Site', 'wp-integrations-directory'); ?>
                            <i class="dashicons dashicons-external"></i>
                        </a>
                    <?php endif; ?>
                    
                    <div class="integration-share">
                        <span class="share-label"><?php _e('Share:', 'wp-integrations-directory'); ?></span>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="share-button twitter">
                            <i class="dashicons dashicons-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="share-button facebook">
                            <i class="dashicons dashicons-facebook"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="share-button linkedin">
                            <i class="dashicons dashicons-linkedin"></i>
                        </a>
                        <button class="share-button copy-link" data-url="<?php echo esc_url(get_permalink()); ?>">
                            <i class="dashicons dashicons-admin-links"></i>
                            <span class="tooltip"><?php _e('Copy link', 'wp-integrations-directory'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Integration Content -->
    <div class="integration-content" id="integration-content">
        <div class="container">
            <div class="integration-main">
                <div class="integration-description">
                    <h2><?php _e('About this Integration', 'wp-integrations-directory'); ?></h2>
                    <div class="integration-content-text">
                        <?php the_content(); ?>
                    </div>
                </div>
                
                <!-- Features -->
                <?php if (get_option('show_key_features', '1') == '1' && !empty($meta['features']) && is_array($meta['features'])): ?>
                    <div class="integration-features">
                        <h2><?php echo esc_html(get_option('key_features_title', __('Key Features', 'wp-integrations-directory'))); ?></h2>
                        <ul class="features-list">
                            <?php foreach ($meta['features'] as $feature): ?>
                                <?php if (!empty($feature)): ?>
                                    <li>
                                        <i class="dashicons dashicons-yes-alt"></i>
                                        <?php echo esc_html($feature); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Requirements -->
                <?php if (get_option('show_requirements', '1') == '1' && !empty($meta['requirements'])): ?>
                    <div class="integration-requirements">
                        <h2><?php echo esc_html(get_option('requirements_title', __('Requirements', 'wp-integrations-directory'))); ?></h2>
                        <div class="requirements-content">
                            <?php echo wpautop(esc_html($meta['requirements'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Screenshots Gallery -->
                <?php if (get_option('show_screenshots', '1') == '1' && !empty($screenshots) && is_array($screenshots)): ?>
                    <div class="integration-screenshots">
                        <h2><?php echo esc_html(get_option('screenshots_title', __('Screenshots', 'wp-integrations-directory'))); ?></h2>
                        <div class="screenshots-gallery">
                            <?php foreach ($screenshots as $screenshot_id): ?>
                                <?php if ($screenshot_id): ?>
                                    <div class="screenshot-item">
                                        <a href="<?php echo wp_get_attachment_image_url($screenshot_id, 'full'); ?>" 
                                           class="screenshot-link" 
                                           data-lightbox="integration-gallery">
                                            <?php echo wp_get_attachment_image($screenshot_id, 'medium_large', false, array('loading' => 'lazy')); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="integration-sidebar">
                <!-- Quick Info -->
                <div class="sidebar-widget integration-quick-info">
                    <h3><?php _e('Quick Info', 'wp-integrations-directory'); ?></h3>
                    <div class="quick-info-items">
                        
                        <?php if ($meta['difficulty']): ?>
                            <div class="info-item">
                                <span class="info-label"><?php _e('Difficulty:', 'wp-integrations-directory'); ?></span>
                                <span class="info-value integration-difficulty integration-difficulty-<?php echo esc_attr($meta['difficulty']); ?>">
                                    <?php echo esc_html(ucfirst($meta['difficulty'])); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($meta['setup_time']): ?>
                            <div class="info-item">
                                <span class="info-label"><?php _e('Setup Time:', 'wp-integrations-directory'); ?></span>
                                <span class="info-value">
                                    <i class="dashicons dashicons-clock"></i>
                                    <?php echo esc_html($meta['setup_time']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($categories && !is_wp_error($categories)): ?>
                            <div class="info-item">
                                <span class="info-label"><?php _e('Category:', 'wp-integrations-directory'); ?></span>
                                <div class="info-value">
                                    <?php foreach ($categories as $category): ?>
                                        <a href="<?php echo get_term_link($category); ?>" class="category-link">
                                            <?php echo esc_html($category->name); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <span class="info-label"><?php _e('Last Updated:', 'wp-integrations-directory'); ?></span>
                            <span class="info-value">
                                <time datetime="<?php echo get_the_modified_date('c'); ?>">
                                    <?php echo get_the_modified_date(); ?>
                                </time>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="sidebar-widget integration-actions">
                    <?php 
                    $button_text = get_option('get_started_button_text', __('Get Started', 'wp-integrations-directory'));
                    $use_external_url = get_option('get_started_use_external_url', '1') == '1';
                    ?>

                        <a href="https://taskip.app?ref=website"
                           class="button button-primary button-block" 
                        >
                            <?php echo esc_html($button_text); ?>
                            <i class="dashicons dashicons-external"></i>
                        </a>
                    
                    <button class="button button-secondary button-block bookmark-integration" 
                            data-integration-id="<?php echo get_the_ID(); ?>">
                        <i class="dashicons dashicons-star-empty"></i>
                        <?php _e('Bookmark', 'wp-integrations-directory'); ?>
                    </button>
                </div>
                
                <!-- Related Categories -->
                <?php if ($categories && !is_wp_error($categories) && count($categories) > 1): ?>
                    <div class="sidebar-widget related-categories">
                        <h3><?php _e('Related Categories', 'wp-integrations-directory'); ?></h3>
                        <div class="category-tags">
                            <?php foreach (array_slice($categories, 1) as $category): ?>
                                <?php $category_meta = WP_Integrations_Directory_Taxonomy::get_category_with_meta($category->term_id); ?>
                                <a href="<?php echo get_term_link($category); ?>" 
                                   class="category-tag" 
                                   style="background-color: <?php echo esc_attr($category_meta['color']); ?>20; color: <?php echo esc_attr($category_meta['color']); ?>; border-color: <?php echo esc_attr($category_meta['color']); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
    
    <!-- Related Integrations -->
    <?php
    $related_query = WP_Integrations_Directory_Post_Type::get_related_integrations(get_the_ID(), 3);
    if ($related_query->have_posts()):
    ?>
        <div class="related-integrations">
            <div class="container">
                <h2><?php _e('Related Integrations', 'wp-integrations-directory'); ?></h2>
                <div class="related-integrations-grid">
                    <?php while ($related_query->have_posts()): $related_query->the_post(); ?>
                        <?php
                        $frontend = WP_Integrations_Directory_Frontend::get_instance();
                        $frontend->render_integration_card(get_the_ID());
                        ?>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
                
                <div class="view-more-integrations">
                    <a href="<?php echo get_post_type_archive_link('integration'); ?>" class="button button-outline">
                        <?php _e('View All Integrations', 'wp-integrations-directory'); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Lightbox Modal -->
<div id="lightbox-modal" class="lightbox-modal" style="display: none;">
    <div class="lightbox-backdrop"></div>
    <div class="lightbox-container">
        <button class="lightbox-close" aria-label="<?php _e('Close', 'wp-integrations-directory'); ?>">
            <i class="dashicons dashicons-no-alt"></i>
        </button>
        <div class="lightbox-content">
            <img src="" alt="" class="lightbox-image" />
        </div>
        <div class="lightbox-nav">
            <button class="lightbox-prev" aria-label="<?php _e('Previous', 'wp-integrations-directory'); ?>">
                <i class="dashicons dashicons-arrow-left-alt2"></i>
            </button>
            <button class="lightbox-next" aria-label="<?php _e('Next', 'wp-integrations-directory'); ?>">
                <i class="dashicons dashicons-arrow-right-alt2"></i>
            </button>
        </div>
    </div>
</div>

<?php get_footer(); ?>