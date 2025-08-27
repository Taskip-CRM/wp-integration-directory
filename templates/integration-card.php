<?php
/**
 * Integration card template
 * This file can be copied to your theme to customize the integration card display
 */

$integration_id = $args['integration_id'] ?? get_the_ID();
$meta = WP_Integrations_Directory_Post_Type::get_integration_meta($integration_id);
$categories = get_the_terms($integration_id, 'integration_category');
$logo_url = $meta['logo'] ? wp_get_attachment_image_url($meta['logo'], 'medium') : '';
?>

<div class="integration-card" data-integration-id="<?php echo esc_attr($integration_id); ?>">
    <div class="integration-card-header">
        <?php if ($logo_url): ?>
            <div class="integration-logo">
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_the_title($integration_id)); ?>" loading="lazy" />
            </div>
        <?php else: ?>
            <div class="integration-logo integration-logo-placeholder">
                <i class="dashicons dashicons-admin-plugins"></i>
            </div>
        <?php endif; ?>
        
        <div class="integration-badges">
            <?php if ($meta['type']): ?>
                <span class="integration-badge integration-badge-<?php echo esc_attr($meta['type']); ?>">
                    <?php echo esc_html(ucfirst($meta['type'])); ?>
                </span>
            <?php endif; ?>
            
            <?php if ($categories && !is_wp_error($categories)): ?>
                <?php $category = reset($categories); ?>
                <?php $category_meta = WP_Integrations_Directory_Taxonomy::get_category_with_meta($category->term_id); ?>
                <span class="integration-category-badge" 
                      style="background-color: <?php echo esc_attr($category_meta['color']); ?>">
                    <?php if ($category_meta['icon']): ?>
                        <?php if (strpos($category_meta['icon'], 'dashicons-') !== false): ?>
                            <span class="dashicons <?php echo esc_attr($category_meta['icon']); ?>"></span>
                        <?php else: ?>
                            <i class="<?php echo esc_attr($category_meta['icon']); ?>"></i>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php echo esc_html($category->name); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="integration-card-content">
        <h3 class="integration-title">
            <a href="<?php echo esc_url(get_permalink($integration_id)); ?>" class="integration-title-link">
                <?php echo esc_html(get_the_title($integration_id)); ?>
            </a>
        </h3>
        
        <div class="integration-excerpt">
            <?php 
            $excerpt = get_the_excerpt($integration_id);
            if (empty($excerpt)) {
                $content = get_post_field('post_content', $integration_id);
                $excerpt = wp_trim_words(strip_shortcodes($content), 15);
            }
            echo esc_html($excerpt);
            ?>
        </div>
        
        <div class="integration-meta">
            <?php if ($meta['difficulty']): ?>
                <span class="integration-difficulty integration-difficulty-<?php echo esc_attr($meta['difficulty']); ?>">
                    <i class="dashicons dashicons-performance"></i>
                    <?php echo esc_html(ucfirst($meta['difficulty'])); ?>
                </span>
            <?php endif; ?>
            
            <?php if ($meta['setup_time']): ?>
                <span class="integration-setup-time">
                    <i class="dashicons dashicons-clock"></i>
                    <?php echo esc_html($meta['setup_time']); ?>
                </span>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($meta['features']) && is_array($meta['features'])): ?>
            <div class="integration-features-preview">
                <ul class="features-preview-list">
                    <?php foreach (array_slice($meta['features'], 0, 3) as $feature): ?>
                        <?php if (!empty($feature)): ?>
                            <li>
                                <i class="dashicons dashicons-yes-alt"></i>
                                <?php echo esc_html($feature); ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (count($meta['features']) > 3): ?>
                        <li class="features-more">
                            <?php printf(_n('+%d more feature', '+%d more features', count($meta['features']) - 3, 'wp-integrations-directory'), count($meta['features']) - 3); ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="integration-card-footer">
        <div class="integration-actions">
            <a href="<?php echo esc_url(get_permalink($integration_id)); ?>" class="integration-learn-more">
                <?php _e('Learn More', 'wp-integrations-directory'); ?>
                <i class="dashicons dashicons-arrow-right-alt2"></i>
            </a>
            
            <?php if ($meta['external_url']): ?>
                <a href="<?php echo esc_url($meta['external_url']); ?>" 
                   class="integration-external-link" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   title="<?php _e('Visit official website', 'wp-integrations-directory'); ?>">
                    <?php _e('Visit Site', 'wp-integrations-directory'); ?>
                    <i class="dashicons dashicons-external"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="integration-card-meta">
            <span class="integration-updated" title="<?php _e('Last updated', 'wp-integrations-directory'); ?>">
                <i class="dashicons dashicons-update"></i>
                <time datetime="<?php echo get_the_modified_date('c', $integration_id); ?>">
                    <?php echo human_time_diff(get_the_modified_time('U', $integration_id), current_time('U')) . ' ' . __('ago', 'wp-integrations-directory'); ?>
                </time>
            </span>
        </div>
    </div>
    
    <!-- Hover overlay for additional info -->
    <div class="integration-card-overlay">
        <div class="overlay-content">
            <?php if ($categories && !is_wp_error($categories) && count($categories) > 1): ?>
                <div class="additional-categories">
                    <span class="overlay-label"><?php _e('Also in:', 'wp-integrations-directory'); ?></span>
                    <?php foreach (array_slice($categories, 1, 2) as $category): ?>
                        <span class="category-tag"><?php echo esc_html($category->name); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="overlay-actions">
                <button class="bookmark-btn" data-integration-id="<?php echo esc_attr($integration_id); ?>" title="<?php _e('Bookmark this integration', 'wp-integrations-directory'); ?>">
                    <i class="dashicons dashicons-star-empty"></i>
                </button>
                <button class="share-btn" data-integration-id="<?php echo esc_attr($integration_id); ?>" title="<?php _e('Share this integration', 'wp-integrations-directory'); ?>">
                    <i class="dashicons dashicons-share"></i>
                </button>
            </div>
        </div>
    </div>
</div>