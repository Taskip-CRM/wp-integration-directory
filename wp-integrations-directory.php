<?php
/**
 * Plugin Name: WP Integrations Directory
 * Plugin URI: https://taskip.net/wp-integrations-directory
 * Description: A comprehensive WordPress plugin that creates an integrations directory similar to Ghost's integrations page.
 * Version: 1.0.0
 * Author: taskip
 * Author URI: https://taskip.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-integrations-directory
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_INTEGRATIONS_DIRECTORY_VERSION', '1.0.0');
define('WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * Main plugin class
 */
class WP_Integrations_Directory {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
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
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'wp-integrations-directory',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Load dependencies
     */
    private function load_dependencies() {
        require_once WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'includes/class-post-type.php';
        require_once WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'includes/class-taxonomy.php';
        require_once WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'includes/class-meta-boxes.php';
        require_once WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'includes/class-admin.php';
        require_once WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'includes/class-frontend.php';
        require_once WP_INTEGRATIONS_DIRECTORY_PLUGIN_PATH . 'blocks/step-by-step-guide/block.php';
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Register post type and taxonomy directly first
        $this->register_integration_post_type();
        $this->register_integration_taxonomy();
        
        // Then initialize classes
        WP_Integrations_Directory_Post_Type::get_instance();
        WP_Integrations_Directory_Taxonomy::get_instance();
        WP_Integrations_Directory_Meta_Boxes::get_instance();
        WP_Integrations_Directory_Admin::get_instance();
        WP_Integrations_Directory_Frontend::get_instance();
        WP_Integrations_Step_By_Step_Block::get_instance();
        
        // Debug: Ensure post type is registered (can be removed in production)
        if (is_admin() && current_user_can('manage_options')) {
            add_action('admin_notices', array($this, 'debug_post_type_registration'));
        }
        
        // Fallback: Force admin menu if post type exists but menu doesn't show
        add_action('admin_menu', array($this, 'add_integration_admin_menu'), 20);
    }
    
    /**
     * Register integration post type directly
     */
    public function register_integration_post_type() {
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
            'not_found'            => __('No integrations found.', 'wp-integrations-directory'),
            'not_found_in_trash'   => __('No integrations found in Trash.', 'wp-integrations-directory'),
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
            )
        );
        
        register_post_type('integration', $args);
    }
    
    /**
     * Register integration taxonomy directly
     */
    public function register_integration_taxonomy() {
        $labels = array(
            'name'              => _x('Integration Categories', 'taxonomy general name', 'wp-integrations-directory'),
            'singular_name'     => _x('Integration Category', 'taxonomy singular name', 'wp-integrations-directory'),
            'search_items'      => __('Search Categories', 'wp-integrations-directory'),
            'all_items'         => __('All Categories', 'wp-integrations-directory'),
            'parent_item'       => __('Parent Category', 'wp-integrations-directory'),
            'parent_item_colon' => __('Parent Category:', 'wp-integrations-directory'),
            'edit_item'         => __('Edit Category', 'wp-integrations-directory'),
            'update_item'       => __('Update Category', 'wp-integrations-directory'),
            'add_new_item'      => __('Add New Category', 'wp-integrations-directory'),
            'new_item_name'     => __('New Category Name', 'wp-integrations-directory'),
            'menu_name'         => __('Categories', 'wp-integrations-directory'),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud'     => true,
            'show_in_rest'      => true,
            'rewrite'           => array('slug' => 'integration-category'),
        );

        register_taxonomy('integration_category', array('integration'), $args);
    }
    
    /**
     * Debug function to check if post type is registered (remove in production)
     */
    public function debug_post_type_registration() {
        // Don't show more than once per hour
        if (get_transient('wp_integrations_debug_shown')) {
            return;
        }
        
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>WP Integrations Directory Debug Info:</strong></p>';
        echo '<ul>';
        
        // Check if plugin is loaded
        echo '<li>Plugin loaded: ✓ YES</li>';
        
        // Check if post type exists
        if (post_type_exists('integration')) {
            echo '<li>Integration post type registered: ✓ YES</li>';
        } else {
            echo '<li>Integration post type registered: ✗ NO</li>';
        }
        
        // Check if taxonomy exists
        if (taxonomy_exists('integration_category')) {
            echo '<li>Integration taxonomy registered: ✓ YES</li>';
        } else {
            echo '<li>Integration taxonomy registered: ✗ NO</li>';
        }
        
        // Check current user capabilities
        if (current_user_can('edit_posts')) {
            echo '<li>User can edit posts: ✓ YES</li>';
        } else {
            echo '<li>User can edit posts: ✗ NO</li>';
        }
        
        // List all registered post types for comparison
        $post_types = get_post_types(array('show_ui' => true), 'names');
        echo '<li>Available post types: ' . implode(', ', $post_types) . '</li>';
        
        echo '</ul>';
        
        if (!post_type_exists('integration')) {
            echo '<p><strong>Action Required:</strong> Please deactivate and reactivate the plugin to register the post type.</p>';
        } else {
            echo '<p><strong>Success:</strong> The "Integrations" menu should be visible in your admin sidebar.</p>';
        }
        
        echo '</div>';
        
        set_transient('wp_integrations_debug_shown', true, 3600); // Show for 1 hour only
    }
    
    /**
     * Fallback function to add admin menu if post type menu doesn't appear
     */
    public function add_integration_admin_menu() {
        // Only add if post type exists but menu might not be showing
        if (post_type_exists('integration')) {
            // Check if the menu already exists
            global $menu;
            $integration_menu_exists = false;
            
            foreach ($menu as $menu_item) {
                if (isset($menu_item[2]) && strpos($menu_item[2], 'edit.php?post_type=integration') !== false) {
                    $integration_menu_exists = true;
                    break;
                }
            }
            
            // If menu doesn't exist, add it manually
            if (!$integration_menu_exists) {
                add_menu_page(
                    __('Integrations', 'wp-integrations-directory'),
                    __('Integrations', 'wp-integrations-directory'),
                    'edit_posts',
                    'edit.php?post_type=integration',
                    '',
                    'dashicons-admin-plugins',
                    59
                );
                
                add_submenu_page(
                    'edit.php?post_type=integration',
                    __('All Integrations', 'wp-integrations-directory'),
                    __('All Integrations', 'wp-integrations-directory'),
                    'edit_posts',
                    'edit.php?post_type=integration'
                );
                
                add_submenu_page(
                    'edit.php?post_type=integration',
                    __('Add New Integration', 'wp-integrations-directory'),
                    __('Add New', 'wp-integrations-directory'),
                    'edit_posts',
                    'post-new.php?post_type=integration'
                );
                
                add_submenu_page(
                    'edit.php?post_type=integration',
                    __('Categories', 'wp-integrations-directory'),
                    __('Categories', 'wp-integrations-directory'),
                    'manage_categories',
                    'edit-tags.php?taxonomy=integration_category&post_type=integration'
                );
            }
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Load dependencies first
        $this->load_dependencies();
        
        // Initialize classes to register post types and taxonomies
        WP_Integrations_Directory_Post_Type::get_instance();
        WP_Integrations_Directory_Taxonomy::get_instance();
        
        // Create default terms
        $this->create_default_categories();
        
        // Flush rewrite rules after registering post types
        flush_rewrite_rules();
        
        // Set activation flag for admin notices
        set_transient('wp_integrations_directory_activated', true, 30);
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create default integration categories
     */
    private function create_default_categories() {
        $categories = array(
            'Analytics' => 'Track and analyze your website performance',
            'Email Marketing' => 'Email marketing and newsletter services',
            'Social Media' => 'Social media management and sharing tools',
            'E-commerce' => 'Online store and payment processing solutions',
            'Forms' => 'Contact forms and lead generation tools',
            'SEO' => 'Search engine optimization tools',
            'Security' => 'Website security and protection services',
            'Performance' => 'Website speed and performance optimization',
            'Content Management' => 'Content creation and management tools',
            'Communication' => 'Chat, support, and communication platforms'
        );
        
        foreach ($categories as $name => $description) {
            if (!term_exists($name, 'integration_category')) {
                wp_insert_term($name, 'integration_category', array(
                    'description' => $description,
                    'slug' => sanitize_title($name)
                ));
            }
        }
    }
}

// Initialize the plugin
WP_Integrations_Directory::get_instance();