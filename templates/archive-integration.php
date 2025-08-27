<?php
/**
 * Archive template for integrations
 */

get_header(); ?>

<div class="wpid-integrations-archive-container">
    <div class="wpid-integrations-archive-header">
        <div class="wpid-container">
            <h1 class="wpid-archive-title">
                <?php
                if (is_tax('integration_category')) {
                    single_term_title();
                    echo '<span class="archive-subtitle">' . __('Integrations', 'wp-integrations-directory') . '</span>';
                } else {
                    echo get_option('integrations_archive_title', __('Integrations', 'wp-integrations-directory'));
                }
                ?>
            </h1>
            
            <?php if (is_tax('integration_category') && term_description()): ?>
                <div class="archive-description">
                    <?php echo term_description(); ?>
                </div>
            <?php else: ?>
                <div class="archive-description">
                    <p><?php echo esc_html(get_option('integrations_archive_description', __('Discover powerful integrations to enhance your website functionality.', 'wp-integrations-directory'))); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="integrations-content">
        <div class="container">
            <!-- Filters -->
            <div class="integration-filters">
                <div class="filters-row">
                    <div class="integration-search">
                        <input type="text" id="integration-search" placeholder="<?php _e('Search integrations...', 'wp-integrations-directory'); ?>" />
                        <button type="button" id="search-integrations">
                            <i class="dashicons dashicons-search"></i>
                            <span class="sr-only"><?php _e('Search', 'wp-integrations-directory'); ?></span>
                        </button>
                    </div>
                    
                </div>
                
                <div class="integration-filter-categories">
                    <button class="filter-category <?php echo !is_tax() ? 'active' : ''; ?>" data-category="">
                        <?php _e('All Categories', 'wp-integrations-directory'); ?>
                    </button>
                    
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'integration_category',
                        'hide_empty' => true
                    ));
                    
                    $current_term = get_queried_object();
                    $current_category = is_tax('integration_category') ? $current_term->slug : '';
                    
                    foreach ($categories as $category):
                        $category_meta = WP_Integrations_Directory_Taxonomy::get_category_with_meta($category->term_id);
                    ?>
                        <button class="filter-category <?php echo $current_category === $category->slug ? 'active' : ''; ?>" 
                                data-category="<?php echo esc_attr($category->slug); ?>"
                                style="--category-color: <?php echo esc_attr($category_meta['color']); ?>">
                            <?php if ($category_meta['icon']): ?>
                                <?php if (strpos($category_meta['icon'], 'dashicons-') !== false): ?>
                                    <span class="dashicons <?php echo esc_attr($category_meta['icon']); ?>"></span>
                                <?php else: ?>
                                    <i class="<?php echo esc_attr($category_meta['icon']); ?>"></i>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php echo esc_html($category->name); ?>
                            <span class="category-count">(<?php echo $category->count; ?>)</span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Results Info -->
            <div class="integration-results-info">
                <div class="results-count">
                    <span id="results-showing"><?php _e('Showing all integrations', 'wp-integrations-directory'); ?></span>
                </div>
                
                <div class="results-sort">
                    <select id="integration-sort">
                        <option value="date"><?php _e('Sort by Date', 'wp-integrations-directory'); ?></option>
                        <option value="title"><?php _e('Sort by Title', 'wp-integrations-directory'); ?></option>
                        <option value="popularity"><?php _e('Sort by Popularity', 'wp-integrations-directory'); ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Loading Indicator -->
            <div class="integration-loading" id="integration-loading" style="display: none;">
                <div class="loading-spinner"></div>
                <p><?php _e('Loading integrations...', 'wp-integrations-directory'); ?></p>
            </div>
            
            <!-- Integrations Grid -->
            <div class="integrations-grid" id="integrations-grid">
                <?php if (have_posts()): ?>
                    <?php while (have_posts()): the_post(); ?>
                        <?php
                        $frontend = WP_Integrations_Directory_Frontend::get_instance();
                        $frontend->render_integration_card(get_the_ID());
                        ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="integration-no-results">
                        <div class="no-results-icon">
                            <i class="dashicons dashicons-search"></i>
                        </div>
                        <h3><?php _e('No integrations found', 'wp-integrations-directory'); ?></h3>
                        <p><?php _e('Try adjusting your search criteria or browse all categories.', 'wp-integrations-directory'); ?></p>
                        <a href="<?php echo get_post_type_archive_link('integration'); ?>" class="button">
                            <?php _e('View All Integrations', 'wp-integrations-directory'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Load More Button -->
            <?php
            global $wp_query;
            if ($wp_query->max_num_pages > 1):
            ?>
                <div class="integration-load-more-container">
                    <button id="load-more-integrations" class="integration-load-more" 
                            data-page="2" 
                            data-max-pages="<?php echo $wp_query->max_num_pages; ?>"
                            data-category="<?php echo is_tax() ? get_queried_object()->slug : ''; ?>">
                        <span class="load-more-text"><?php _e('Load More Integrations', 'wp-integrations-directory'); ?></span>
                        <span class="load-more-spinner" style="display: none;">
                            <i class="dashicons dashicons-update"></i>
                        </span>
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Navigation -->
            <nav class="integration-pagination">
                <?php
                echo paginate_links(array(
                    'total' => $wp_query->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'format' => '?paged=%#%',
                    'show_all' => false,
                    'end_size' => 1,
                    'mid_size' => 2,
                    'prev_next' => true,
                    'prev_text' => '&larr; ' . __('Previous', 'wp-integrations-directory'),
                    'next_text' => __('Next', 'wp-integrations-directory') . ' &rarr;',
                    'type' => 'list'
                ));
                ?>
            </nav>
        </div>
    </div>

</div>

<?php get_footer(); ?>