<?php
/**
 * Custom Taxonomy: Integration Categories
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Integrations_Directory_Taxonomy {
    
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
        add_action('init', array($this, 'register_taxonomy'));
        add_action('integration_category_add_form_fields', array($this, 'add_category_fields'));
        add_action('integration_category_edit_form_fields', array($this, 'edit_category_fields'));
        add_action('edited_integration_category', array($this, 'save_category_fields'));
        add_action('create_integration_category', array($this, 'save_category_fields'));
        add_filter('manage_edit-integration_category_columns', array($this, 'add_category_columns'));
        add_filter('manage_integration_category_custom_column', array($this, 'add_category_column_content'), 10, 3);
    }
    
    /**
     * Register the integration category taxonomy
     */
    public function register_taxonomy() {
        $labels = array(
            'name'                       => _x('Integration Categories', 'Taxonomy General Name', 'wp-integrations-directory'),
            'singular_name'              => _x('Integration Category', 'Taxonomy Singular Name', 'wp-integrations-directory'),
            'menu_name'                  => __('Categories', 'wp-integrations-directory'),
            'all_items'                  => __('All Categories', 'wp-integrations-directory'),
            'parent_item'                => __('Parent Category', 'wp-integrations-directory'),
            'parent_item_colon'          => __('Parent Category:', 'wp-integrations-directory'),
            'new_item_name'              => __('New Category Name', 'wp-integrations-directory'),
            'add_new_item'               => __('Add New Category', 'wp-integrations-directory'),
            'edit_item'                  => __('Edit Category', 'wp-integrations-directory'),
            'update_item'                => __('Update Category', 'wp-integrations-directory'),
            'view_item'                  => __('View Category', 'wp-integrations-directory'),
            'separate_items_with_commas' => __('Separate categories with commas', 'wp-integrations-directory'),
            'add_or_remove_items'        => __('Add or remove categories', 'wp-integrations-directory'),
            'choose_from_most_used'      => __('Choose from the most used', 'wp-integrations-directory'),
            'popular_items'              => __('Popular Categories', 'wp-integrations-directory'),
            'search_items'               => __('Search Categories', 'wp-integrations-directory'),
            'not_found'                  => __('Not Found', 'wp-integrations-directory'),
            'no_terms'                   => __('No categories', 'wp-integrations-directory'),
            'items_list'                 => __('Categories list', 'wp-integrations-directory'),
            'items_list_navigation'      => __('Categories list navigation', 'wp-integrations-directory'),
        );
        
        $rewrite = array(
            'slug'                       => 'integration-category',
            'with_front'                 => false,
            'hierarchical'               => true,
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rest_base'                  => 'integration-categories',
            'rewrite'                    => $rewrite,
            'query_var'                  => true,
            'meta_box_cb'                => 'post_categories_meta_box',
        );
        
        register_taxonomy('integration_category', array('integration'), $args);
    }
    
    /**
     * Add custom fields to category add form
     */
    public function add_category_fields() {
        ?>
        <div class="form-field term-icon-wrap">
            <label for="category-icon"><?php _e('Category Icon', 'wp-integrations-directory'); ?></label>
            <input type="text" name="category_icon" id="category-icon" value="" placeholder="dashicons-admin-plugins" />
            <p class="description"><?php _e('Enter a dashicons class name or Font Awesome icon class.', 'wp-integrations-directory'); ?></p>
        </div>
        
        <div class="form-field term-color-wrap">
            <label for="category-color"><?php _e('Category Color', 'wp-integrations-directory'); ?></label>
            <input type="text" name="category_color" id="category-color" value="#0073aa" class="color-picker" />
            <p class="description"><?php _e('Choose a color for this category.', 'wp-integrations-directory'); ?></p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.color-picker').wpColorPicker();
        });
        </script>
        <?php
    }
    
    /**
     * Add custom fields to category edit form
     */
    public function edit_category_fields($term) {
        $icon = get_term_meta($term->term_id, 'category_icon', true);
        $color = get_term_meta($term->term_id, 'category_color', true);
        if (empty($color)) {
            $color = '#0073aa';
        }
        ?>
        <tr class="form-field term-icon-wrap">
            <th scope="row">
                <label for="category-icon"><?php _e('Category Icon', 'wp-integrations-directory'); ?></label>
            </th>
            <td>
                <input type="text" name="category_icon" id="category-icon" value="<?php echo esc_attr($icon); ?>" placeholder="dashicons-admin-plugins" />
                <p class="description"><?php _e('Enter a dashicons class name or Font Awesome icon class.', 'wp-integrations-directory'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field term-color-wrap">
            <th scope="row">
                <label for="category-color"><?php _e('Category Color', 'wp-integrations-directory'); ?></label>
            </th>
            <td>
                <input type="text" name="category_color" id="category-color" value="<?php echo esc_attr($color); ?>" class="color-picker" />
                <p class="description"><?php _e('Choose a color for this category.', 'wp-integrations-directory'); ?></p>
            </td>
        </tr>
        
        <script>
        jQuery(document).ready(function($) {
            $('.color-picker').wpColorPicker();
        });
        </script>
        <?php
    }
    
    /**
     * Save custom category fields
     */
    public function save_category_fields($term_id) {
        if (isset($_POST['category_icon'])) {
            update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
        }
        
        if (isset($_POST['category_color'])) {
            update_term_meta($term_id, 'category_color', sanitize_hex_color($_POST['category_color']));
        }
    }
    
    /**
     * Add custom columns to category list
     */
    public function add_category_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['name'] = $columns['name'];
        $new_columns['icon'] = __('Icon', 'wp-integrations-directory');
        $new_columns['color'] = __('Color', 'wp-integrations-directory');
        $new_columns['description'] = $columns['description'];
        $new_columns['slug'] = $columns['slug'];
        $new_columns['posts'] = $columns['posts'];
        
        return $new_columns;
    }
    
    /**
     * Add content to custom columns
     */
    public function add_category_column_content($content, $column_name, $term_id) {
        switch ($column_name) {
            case 'icon':
                $icon = get_term_meta($term_id, 'category_icon', true);
                if ($icon) {
                    if (strpos($icon, 'dashicons-') !== false) {
                        $content = '<span class="dashicons ' . esc_attr($icon) . '"></span>';
                    } else {
                        $content = '<i class="' . esc_attr($icon) . '"></i>';
                    }
                } else {
                    $content = '—';
                }
                break;
                
            case 'color':
                $color = get_term_meta($term_id, 'category_color', true);
                if ($color) {
                    $content = '<span style="display: inline-block; width: 20px; height: 20px; background-color: ' . esc_attr($color) . '; border-radius: 3px; border: 1px solid #ddd;"></span> ' . esc_html($color);
                } else {
                    $content = '—';
                }
                break;
        }
        
        return $content;
    }
    
    /**
     * Get category with meta
     */
    public static function get_category_with_meta($term_id) {
        $term = get_term($term_id, 'integration_category');
        if (!$term || is_wp_error($term)) {
            return false;
        }
        
        return array(
            'term' => $term,
            'icon' => get_term_meta($term_id, 'category_icon', true),
            'color' => get_term_meta($term_id, 'category_color', true) ?: '#0073aa'
        );
    }
    
    /**
     * Get all categories with count
     */
    public static function get_categories_with_count() {
        $terms = get_terms(array(
            'taxonomy' => 'integration_category',
            'hide_empty' => false,
        ));
        
        $categories = array();
        foreach ($terms as $term) {
            $categories[] = array(
                'term' => $term,
                'icon' => get_term_meta($term->term_id, 'category_icon', true),
                'color' => get_term_meta($term->term_id, 'category_color', true) ?: '#0073aa',
                'count' => $term->count
            );
        }
        
        return $categories;
    }
}