<?php
/**
 * Frontend functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Integrations_Directory_Frontend {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_schema_markup'));
        add_action('wp_head', array($this, 'add_og_tags'));
        add_action('wp_ajax_filter_integrations', array($this, 'ajax_filter_integrations'));
        add_action('wp_ajax_nopriv_filter_integrations', array($this, 'ajax_filter_integrations'));
        add_action('wp_ajax_load_more_integrations', array($this, 'ajax_load_more_integrations'));
        add_action('wp_ajax_nopriv_load_more_integrations', array($this, 'ajax_load_more_integrations'));
        add_shortcode('integrations_directory', array($this, 'integrations_directory_shortcode'));
        add_shortcode('integration_categories', array($this, 'integration_categories_shortcode'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        if (is_singular('integration') || is_post_type_archive('integration') || is_tax('integration_category')) {
            wp_enqueue_style(
                'integration-frontend-css',
                WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                WP_INTEGRATIONS_DIRECTORY_VERSION
            );
            
            wp_enqueue_script(
                'integration-frontend-js',
                WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                WP_INTEGRATIONS_DIRECTORY_VERSION,
                true
            );
            
            wp_localize_script('integration-frontend-js', 'integrationAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('integration_ajax_nonce'),
                'loading_text' => __('Loading...', 'wp-integrations-directory'),
                'no_more_text' => __('No more integrations to load.', 'wp-integrations-directory'),
                'error_text' => __('Error loading integrations. Please try again.', 'wp-integrations-directory')
            ));
        }
    }
    
    /**
     * Add schema markup for SEO
     */
    public function add_schema_markup() {
        if (is_singular('integration')) {
            global $post;
            $meta = WP_Integrations_Directory_Post_Type::get_integration_meta($post->ID);
            $logo_url = $meta['logo'] ? wp_get_attachment_url($meta['logo']) : '';
            
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => get_the_title(),
                'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 20),
                'url' => get_permalink(),
                'applicationCategory' => 'WebApplication'
            );
            
            if ($logo_url) {
                $schema['image'] = $logo_url;
            }
            
            if ($meta['external_url']) {
                $schema['downloadUrl'] = $meta['external_url'];
            }
            
            
            echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>' . "\n";
        }
    }
    
    /**
     * Add Open Graph tags
     */
    public function add_og_tags() {
        if (is_singular('integration')) {
            global $post;
            $meta = WP_Integrations_Directory_Post_Type::get_integration_meta($post->ID);
            $logo_url = $meta['logo'] ? wp_get_attachment_url($meta['logo']) : '';
            
            echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr(get_the_excerpt() ?: wp_trim_words(get_the_content(), 20)) . '">' . "\n";
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
            
            if ($logo_url) {
                echo '<meta property="og:image" content="' . esc_url($logo_url) . '">' . "\n";
            }
        }
    }
    
    /**
     * AJAX filter integrations
     */
    public function ajax_filter_integrations() {
        check_ajax_referer('integration_ajax_nonce', 'nonce');
        
        $category = sanitize_text_field($_POST['category'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        $page = intval($_POST['page'] ?? 1);
        
        $args = array(
            'post_type' => 'integration',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => $page
        );
        
        $meta_query = array('relation' => 'AND');
        $tax_query = array('relation' => 'AND');
        
        if (!empty($category)) {
            $tax_query[] = array(
                'taxonomy' => 'integration_category',
                'field' => 'slug',
                'terms' => $category
            );
        }
        
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_integration_card(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<div class="integration-no-results">';
            echo '<p>' . __('No integrations found matching your criteria.', 'wp-integrations-directory') . '</p>';
            echo '</div>';
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $html,
            'found_posts' => $query->found_posts,
            'max_pages' => $query->max_num_pages,
            'current_page' => $page
        ));
    }
    
    /**
     * AJAX load more integrations
     */
    public function ajax_load_more_integrations() {
        check_ajax_referer('integration_ajax_nonce', 'nonce');
        
        $page = intval($_POST['page'] ?? 1);
        $category = sanitize_text_field($_POST['category'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        
        $args = array(
            'post_type' => 'integration',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => $page
        );
        
        // Apply same filters as filter function
        $meta_query = array('relation' => 'AND');
        $tax_query = array('relation' => 'AND');
        
        if (!empty($category)) {
            $tax_query[] = array(
                'taxonomy' => 'integration_category',
                'field' => 'slug',
                'terms' => $category
            );
        }
        
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_integration_card(get_the_ID());
            }
            wp_reset_postdata();
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $html,
            'max_pages' => $query->max_num_pages,
            'current_page' => $page
        ));
    }
    
    /**
     * Render integration card
     */
    public function render_integration_card($post_id) {
        $meta = WP_Integrations_Directory_Post_Type::get_integration_meta($post_id);
        $categories = get_the_terms($post_id, 'integration_category');
        $logo_url = $meta['logo'] ? wp_get_attachment_image_url($meta['logo'], 'medium') : '';
        
        ?>
        <div class="integration-card">
            <div class="integration-card-header">
                <?php if ($logo_url): ?>
                    <div class="integration-logo">
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" />
                    </div>
                <?php endif; ?>
                
                <div class="integration-badges">
                    
                    <?php if ($categories && !is_wp_error($categories)): ?>
                        <?php foreach (array_slice($categories, 0, 1) as $category): ?>
                            <span class="integration-category-badge" style="background-color: <?php echo esc_attr(get_term_meta($category->term_id, 'category_color', true) ?: '#0073aa'); ?>">
                                <?php echo esc_html($category->name); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="integration-card-content">
                <h3 class="integration-title">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                        <?php echo esc_html(get_the_title($post_id)); ?>
                    </a>
                </h3>
                
                <div class="integration-excerpt">
                    <?php echo wp_trim_words(get_the_excerpt($post_id) ?: get_the_content(), 15); ?>
                </div>
                
                <div class="integration-meta">
                    <?php if ($meta['difficulty']): ?>
                        <span class="integration-difficulty integration-difficulty-<?php echo esc_attr($meta['difficulty']); ?>">
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
            </div>
            
            <div class="integration-card-footer">
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="integration-learn-more">
                    <?php _e('Learn More', 'wp-integrations-directory'); ?>
                </a>
                
                <?php if ($meta['external_url']): ?>
                    <a href="<?php echo esc_url($meta['external_url']); ?>" class="integration-external-link" target="_blank" rel="noopener noreferrer">
                        <?php _e('Visit Site', 'wp-integrations-directory'); ?>
                        <i class="dashicons dashicons-external"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Integrations directory shortcode
     */
    public function integrations_directory_shortcode($atts) {
        $atts = shortcode_atts(array(
            'category' => '',
            'limit' => 12,
            'show_filters' => 'yes'
        ), $atts, 'integrations_directory');
        
        $args = array(
            'post_type' => 'integration',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['limit'])
        );
        
        if ($atts['category']) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'integration_category',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['category'])
                )
            );
        }
        
        
        $query = new WP_Query($args);
        
        ob_start();
        
        if ($atts['show_filters'] === 'yes') {
            $this->render_filters();
        }
        
        echo '<div class="integrations-grid" id="integrations-grid">';
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_integration_card(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<div class="integration-no-results">';
            echo '<p>' . __('No integrations found.', 'wp-integrations-directory') . '</p>';
            echo '</div>';
        }
        
        echo '</div>';
        
        if ($query->max_num_pages > 1) {
            echo '<div class="integration-load-more-container">';
            echo '<button id="load-more-integrations" class="integration-load-more" data-page="2" data-max-pages="' . $query->max_num_pages . '">';
            echo __('Load More', 'wp-integrations-directory');
            echo '</button>';
            echo '</div>';
        }
        
        return ob_get_clean();
    }
    
    /**
     * Integration categories shortcode
     */
    public function integration_categories_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_count' => 'yes',
            'show_icons' => 'yes'
        ), $atts, 'integration_categories');
        
        $categories = WP_Integrations_Directory_Taxonomy::get_categories_with_count();
        
        ob_start();
        echo '<div class="integration-categories-grid">';
        
        foreach ($categories as $category_data) {
            $term = $category_data['term'];
            $icon = $category_data['icon'];
            $color = $category_data['color'];
            $count = $category_data['count'];
            
            echo '<div class="integration-category-item">';
            echo '<a href="' . esc_url(get_term_link($term)) . '" style="border-color: ' . esc_attr($color) . ';">';
            
            if ($atts['show_icons'] === 'yes' && $icon) {
                if (strpos($icon, 'dashicons-') !== false) {
                    echo '<span class="dashicons ' . esc_attr($icon) . '" style="color: ' . esc_attr($color) . ';"></span>';
                } else {
                    echo '<i class="' . esc_attr($icon) . '" style="color: ' . esc_attr($color) . ';"></i>';
                }
            }
            
            echo '<h4>' . esc_html($term->name) . '</h4>';
            
            if ($term->description) {
                echo '<p>' . esc_html($term->description) . '</p>';
            }
            
            if ($atts['show_count'] === 'yes') {
                echo '<span class="category-count">' . sprintf(_n('%d integration', '%d integrations', $count, 'wp-integrations-directory'), $count) . '</span>';
            }
            
            echo '</a>';
            echo '</div>';
        }
        
        echo '</div>';
        return ob_get_clean();
    }
    
    /**
     * Render filters
     */
    private function render_filters() {
        $categories = get_terms(array(
            'taxonomy' => 'integration_category',
            'hide_empty' => true
        ));
        
        ?>
        <div class="integration-filters">
            <div class="integration-search">
                <input type="text" id="integration-search" placeholder="<?php _e('Search integrations...', 'wp-integrations-directory'); ?>" />
                <button type="button" id="search-integrations">
                    <i class="dashicons dashicons-search"></i>
                </button>
            </div>
            
            <div class="integration-filter-categories">
                <button class="filter-category active" data-category=""><?php _e('All', 'wp-integrations-directory'); ?></button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-category" data-category="<?php echo esc_attr($category->slug); ?>">
                        <?php echo esc_html($category->name); ?>
                        <span class="category-count">(<?php echo $category->count; ?>)</span>
                    </button>
                <?php endforeach; ?>
            </div>
            
        </div>
        <?php
    }
}