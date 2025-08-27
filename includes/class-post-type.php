<?php
/**
 * Custom Post Type: Integrations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Integrations_Directory_Post_Type {
    
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
        add_action('init', array($this, 'register_post_type'));
        add_filter('template_include', array($this, 'template_include'));
        add_action('pre_get_posts', array($this, 'modify_main_query'));
    }
    
    /**
     * Register the integrations post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Integrations', 'Post type general name', 'wp-integrations-directory'),
            'singular_name'         => _x('Integration', 'Post type singular name', 'wp-integrations-directory'),
            'menu_name'            => _x('Integrations', 'Admin Menu text', 'wp-integrations-directory'),
            'name_admin_bar'       => _x('Integration', 'Add New on Toolbar', 'wp-integrations-directory'),
            'add_new'              => __('Add New', 'wp-integrations-directory'),
            'add_new_item'         => __('Add New Integration', 'wp-integrations-directory'),
            'new_item'             => __('New Integration', 'wp-integrations-directory'),
            'edit_item'            => __('Edit Integration', 'wp-integrations-directory'),
            'view_item'            => __('View Integration', 'wp-integrations-directory'),
            'all_items'            => __('All Integrations', 'wp-integrations-directory'),
            'search_items'         => __('Search Integrations', 'wp-integrations-directory'),
            'parent_item_colon'    => __('Parent Integrations:', 'wp-integrations-directory'),
            'not_found'            => __('No integrations found.', 'wp-integrations-directory'),
            'not_found_in_trash'   => __('No integrations found in Trash.', 'wp-integrations-directory'),
            'featured_image'       => _x('Integration Logo', 'Overrides the "Featured Image" phrase', 'wp-integrations-directory'),
            'set_featured_image'   => _x('Set integration logo', 'Overrides the "Set featured image" phrase', 'wp-integrations-directory'),
            'remove_featured_image' => _x('Remove integration logo', 'Overrides the "Remove featured image" phrase', 'wp-integrations-directory'),
            'use_featured_image'   => _x('Use as integration logo', 'Overrides the "Use as featured image" phrase', 'wp-integrations-directory'),
            'archives'             => _x('Integration archives', 'The post type archive label', 'wp-integrations-directory'),
            'insert_into_item'     => _x('Insert into integration', 'Overrides the "Insert into post" phrase', 'wp-integrations-directory'),
            'uploaded_to_this_item' => _x('Uploaded to this integration', 'Overrides the "Uploaded to this post" phrase', 'wp-integrations-directory'),
            'filter_items_list'    => _x('Filter integrations list', 'Screen reader text for the filter links', 'wp-integrations-directory'),
            'items_list_navigation' => _x('Integrations list navigation', 'Screen reader text for the pagination', 'wp-integrations-directory'),
            'items_list'           => _x('Integrations list', 'Screen reader text for the items list', 'wp-integrations-directory'),
        );
        
        $args = array(
            'labels'             => $labels,
            'description'        => __('Integrations for the directory', 'wp-integrations-directory'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'can_export'         => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug' => 'integration',
                'with_front' => false
            ),
            'capability_type'    => 'post',
            'has_archive'        => 'integrations',
            'hierarchical'       => false,
            'menu_position'      => 59,
            'menu_icon'          => 'dashicons-admin-plugins',
            'show_in_rest'       => true,
            'rest_base'          => 'integrations',
            'supports'           => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'custom-fields',
                'revisions',
                'author'
            ),
            'taxonomies'         => array('integration_category')
        );
        
        register_post_type('integration', $args);
    }
    
    /**
     * Load custom templates
     */
    public function template_include($template) {
        global $post;
        
        // Single integration template
        if (is_singular('integration')) {
            $custom_template = WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'templates/single-integration.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        // Archive integrations template
        if (is_post_type_archive('integration') || is_tax('integration_category')) {
            $custom_template = WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'templates/archive-integration.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Modify main query for integrations archive
     */
    public function modify_main_query($query) {
        if (!is_admin() && $query->is_main_query()) {
            // Set posts per page for integrations archive
            if (is_post_type_archive('integration') || is_tax('integration_category')) {
                $query->set('posts_per_page', 12);
                $query->set('meta_query', array(
                    'relation' => 'OR',
                    array(
                        'key' => '_integration_logo',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key' => '_integration_logo',
                        'compare' => 'NOT EXISTS'
                    )
                ));
            }
        }
    }
    
    /**
     * Get integration meta
     */
    public static function get_integration_meta($post_id) {
        return array(
            'logo' => get_post_meta($post_id, '_integration_logo', true),
            'description' => get_post_meta($post_id, '_integration_description', true),
            'category' => wp_get_post_terms($post_id, 'integration_category', array('fields' => 'names')),
            'external_url' => get_post_meta($post_id, '_integration_external_url', true),
            'difficulty' => get_post_meta($post_id, '_integration_difficulty', true),
            'setup_time' => get_post_meta($post_id, '_integration_setup_time', true),
            'features' => get_post_meta($post_id, '_integration_features', true),
            'requirements' => get_post_meta($post_id, '_integration_requirements', true),
            'screenshots' => get_post_meta($post_id, '_integration_screenshots', true)
        );
    }
    
    /**
     * Get related integrations
     */
    public static function get_related_integrations($post_id, $limit = 3) {
        $terms = wp_get_post_terms($post_id, 'integration_category', array('fields' => 'ids'));
        
        $args = array(
            'post_type' => 'integration',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'post__not_in' => array($post_id),
            'orderby' => 'rand'
        );
        
        // If we have terms, filter by category
        if (!empty($terms) && !is_wp_error($terms)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'integration_category',
                    'field' => 'term_id',
                    'terms' => $terms,
                    'operator' => 'IN'
                )
            );
        }
        
        return new WP_Query($args);
    }
}