<?php
/**
 * Admin functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Integrations_Directory_Admin {
    
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
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_filter('manage_integration_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_integration_posts_custom_column', array($this, 'admin_column_content'), 10, 2);
        add_filter('manage_edit-integration_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_posts', array($this, 'admin_query_sorting'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('quick_edit_custom_box', array($this, 'quick_edit_fields'), 10, 2);
        add_action('wp_ajax_save_bulk_edit', array($this, 'save_bulk_edit'));
        add_action('bulk_edit_custom_box', array($this, 'bulk_edit_fields'), 10, 2);
    }
    
    /**
     * Admin initialization
     */
    public function admin_init() {
        // Register settings
        register_setting('wp_integrations_directory_settings', 'integrations_archive_title');
        register_setting('wp_integrations_directory_settings', 'integrations_archive_description');
        register_setting('wp_integrations_directory_settings', 'integrations_per_page');
        
        // Single integration page settings
        register_setting('wp_integrations_directory_settings', 'show_key_features');
        register_setting('wp_integrations_directory_settings', 'show_requirements');
        register_setting('wp_integrations_directory_settings', 'show_screenshots');
        register_setting('wp_integrations_directory_settings', 'key_features_title');
        register_setting('wp_integrations_directory_settings', 'requirements_title');
        register_setting('wp_integrations_directory_settings', 'screenshots_title');
        register_setting('wp_integrations_directory_settings', 'get_started_button_text');
        register_setting('wp_integrations_directory_settings', 'get_started_use_external_url');
        register_setting('wp_integrations_directory_settings', 'integration_hero_site_name');
        register_setting('wp_integrations_directory_settings', 'integration_hero_site_logo');
        
        // Add settings section
        add_settings_section(
            'wp_integrations_directory_settings',
            __('Integration Directory Settings', 'wp-integrations-directory'),
            array($this, 'settings_section_callback'),
            'wp-integrations-directory'
        );
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=integration',
            __('Directory Settings', 'wp-integrations-directory'),
            __('Settings', 'wp-integrations-directory'),
            'manage_options',
            'integration-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'edit.php?post_type=integration',
            __('Import/Export', 'wp-integrations-directory'),
            __('Import/Export', 'wp-integrations-directory'),
            'manage_options',
            'integration-import-export',
            array($this, 'import_export_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        global $post_type;
        
        if ($post_type === 'integration' || strpos($hook, 'integration') !== false) {
            // Enqueue media uploader
            wp_enqueue_media();
            
            wp_enqueue_style(
                'integration-admin-css',
                WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                WP_INTEGRATIONS_DIRECTORY_VERSION
            );
            
            wp_enqueue_script(
                'integration-admin-js',
                WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'media-upload', 'media-views'),
                WP_INTEGRATIONS_DIRECTORY_VERSION,
                true
            );
            
            wp_localize_script('integration-admin-js', 'integrationAdmin', array(
                'select_logo' => __('Select Logo', 'wp-integrations-directory'),
                'change_logo' => __('Change Logo', 'wp-integrations-directory'),
                'select_screenshots' => __('Select Screenshots', 'wp-integrations-directory')
            ));
        }
    }
    
    /**
     * Add custom admin columns
     */
    public function add_admin_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['integration_logo'] = __('Logo', 'wp-integrations-directory');
        $new_columns['integration_category'] = __('Category', 'wp-integrations-directory');
        $new_columns['integration_difficulty'] = __('Difficulty', 'wp-integrations-directory');
        $new_columns['integration_url'] = __('External URL', 'wp-integrations-directory');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Admin column content
     */
    public function admin_column_content($column, $post_id) {
        switch ($column) {
            case 'integration_logo':
                $logo_id = get_post_meta($post_id, '_integration_logo', true);
                if ($logo_id) {
                    echo wp_get_attachment_image($logo_id, array(50, 50));
                } else {
                    echo '<span class="dashicons dashicons-format-image" style="color: #ccc; font-size: 30px;"></span>';
                }
                break;
                
            case 'integration_category':
                $terms = get_the_terms($post_id, 'integration_category');
                if ($terms && !is_wp_error($terms)) {
                    $term_names = array();
                    foreach ($terms as $term) {
                        $term_names[] = '<a href="' . admin_url('edit.php?post_type=integration&integration_category=' . $term->slug) . '">' . $term->name . '</a>';
                    }
                    echo implode(', ', $term_names);
                } else {
                    echo '—';
                }
                break;
                
            case 'integration_difficulty':
                $difficulty = get_post_meta($post_id, '_integration_difficulty', true);
                if ($difficulty) {
                    $difficulty_colors = array(
                        'beginner' => '#28a745',
                        'intermediate' => '#ffc107',
                        'advanced' => '#dc3545'
                    );
                    $color = isset($difficulty_colors[$difficulty]) ? $difficulty_colors[$difficulty] : '#6c757d';
                    echo '<span style="color: ' . $color . '; font-weight: bold;">' . ucfirst($difficulty) . '</span>';
                } else {
                    echo '—';
                }
                break;
                
            case 'integration_url':
                $url = get_post_meta($post_id, '_integration_external_url', true);
                if ($url) {
                    echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . 
                         '<span class="dashicons dashicons-external"></span></a>';
                } else {
                    echo '—';
                }
                break;
        }
    }
    
    /**
     * Make columns sortable
     */
    public function sortable_columns($columns) {
        $columns['integration_difficulty'] = 'integration_difficulty';
        return $columns;
    }
    
    /**
     * Handle admin query sorting
     */
    public function admin_query_sorting($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        $orderby = $query->get('orderby');
        
        
        if ($orderby === 'integration_difficulty') {
            $query->set('meta_key', '_integration_difficulty');
            $query->set('orderby', 'meta_value');
        }
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        if (get_transient('wp_integrations_directory_activated')) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . __('WP Integrations Directory has been activated! You can now create your first integration.', 'wp-integrations-directory') . '</p>';
            echo '</div>';
            delete_transient('wp_integrations_directory_activated');
        }
        
        // Check for missing required fields
        global $pagenow, $post;
        if ($pagenow === 'post.php' && isset($post) && $post->post_type === 'integration') {
            $logo = get_post_meta($post->ID, '_integration_logo', true);
            if (empty($logo) && $post->post_status === 'publish') {
                echo '<div class="notice notice-warning">';
                echo '<p>' . __('This integration is missing a logo. Please upload a logo for better display.', 'wp-integrations-directory') . '</p>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Quick edit fields
     */
    public function quick_edit_fields($column_name, $post_type) {
        if ($post_type !== 'integration') {
            return;
        }
        
        // No quick edit fields currently needed
        switch ($column_name) {
            // Reserved for future quick edit fields
        }
    }
    
    /**
     * Bulk edit fields
     */
    public function bulk_edit_fields($column_name, $post_type) {
        if ($post_type !== 'integration') {
            return;
        }
        
        // No bulk edit fields currently needed
        switch ($column_name) {
            // Reserved for future bulk edit fields
        }
    }
    
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Integration Directory Settings', 'wp-integrations-directory'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('wp_integrations_directory_settings'); ?>
                <?php do_settings_sections('wp-integrations-directory'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Archive Page Title', 'wp-integrations-directory'); ?></th>
                        <td>
                            <input type="text" name="integrations_archive_title" value="<?php echo esc_attr(get_option('integrations_archive_title', 'Integrations')); ?>" class="regular-text" />
                            <p class="description"><?php _e('Title to display on the integrations archive page.', 'wp-integrations-directory'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Archive Page Description', 'wp-integrations-directory'); ?></th>
                        <td>
                            <textarea name="integrations_archive_description" rows="3" class="large-text"><?php echo esc_textarea(get_option('integrations_archive_description', 'Discover powerful integrations to enhance your website functionality.')); ?></textarea>
                            <p class="description"><?php _e('Description to display on the integrations archive page.', 'wp-integrations-directory'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Items Per Page', 'wp-integrations-directory'); ?></th>
                        <td>
                            <input type="number" name="integrations_per_page" value="<?php echo esc_attr(get_option('integrations_per_page', '12')); ?>" min="1" max="50" />
                            <p class="description"><?php _e('Number of integrations to display per page.', 'wp-integrations-directory'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2><?php _e('Single Integration Page Settings', 'wp-integrations-directory'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Key Features Section', 'wp-integrations-directory'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="show_key_features" value="1" <?php checked(get_option('show_key_features', '1'), '1'); ?> />
                                    <?php _e('Show Key Features section', 'wp-integrations-directory'); ?>
                                </label>
                                <br><br>
                                <label for="key_features_title"><?php _e('Section Title:', 'wp-integrations-directory'); ?></label><br>
                                <input type="text" name="key_features_title" id="key_features_title" value="<?php echo esc_attr(get_option('key_features_title', __('Key Features', 'wp-integrations-directory'))); ?>" class="regular-text" />
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Requirements Section', 'wp-integrations-directory'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="show_requirements" value="1" <?php checked(get_option('show_requirements', '1'), '1'); ?> />
                                    <?php _e('Show Requirements section', 'wp-integrations-directory'); ?>
                                </label>
                                <br><br>
                                <label for="requirements_title"><?php _e('Section Title:', 'wp-integrations-directory'); ?></label><br>
                                <input type="text" name="requirements_title" id="requirements_title" value="<?php echo esc_attr(get_option('requirements_title', __('Requirements', 'wp-integrations-directory'))); ?>" class="regular-text" />
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Screenshots Section', 'wp-integrations-directory'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="show_screenshots" value="1" <?php checked(get_option('show_screenshots', '1'), '1'); ?> />
                                    <?php _e('Show Screenshots section', 'wp-integrations-directory'); ?>
                                </label>
                                <br><br>
                                <label for="screenshots_title"><?php _e('Section Title:', 'wp-integrations-directory'); ?></label><br>
                                <input type="text" name="screenshots_title" id="screenshots_title" value="<?php echo esc_attr(get_option('screenshots_title', __('Screenshots', 'wp-integrations-directory'))); ?>" class="regular-text" />
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Get Started Button', 'wp-integrations-directory'); ?></th>
                        <td>
                            <fieldset>
                                <label for="get_started_button_text"><?php _e('Button Text:', 'wp-integrations-directory'); ?></label><br>
                                <input type="text" name="get_started_button_text" id="get_started_button_text" value="<?php echo esc_attr(get_option('get_started_button_text', __('Get Started', 'wp-integrations-directory'))); ?>" class="regular-text" />
                                <p class="description"><?php _e('Text to display on the main action button.', 'wp-integrations-directory'); ?></p>
                                <br>
                                <label>
                                    <input type="checkbox" name="get_started_use_external_url" value="1" <?php checked(get_option('get_started_use_external_url', '1'), '1'); ?> />
                                    <?php _e('Use external URL for button (if available)', 'wp-integrations-directory'); ?>
                                </label>
                                <p class="description"><?php _e('When checked, the button will link to the external URL if set. When unchecked, it will link to the full integration page.', 'wp-integrations-directory'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Hero Section', 'wp-integrations-directory'); ?></th>
                        <td>
                            <fieldset>
                                <label for="integration_hero_site_name"><?php _e('Site Name in Hero:', 'wp-integrations-directory'); ?></label><br>
                                <input type="text" name="integration_hero_site_name" id="integration_hero_site_name" value="<?php echo esc_attr(get_option('integration_hero_site_name', get_bloginfo('name'))); ?>" class="regular-text" />
                                <p class="description"><?php _e('Name to display in the hero section (e.g., "Taskip + Integration Name"). Defaults to your site name.', 'wp-integrations-directory'); ?></p>
                                
                                <br><br>
                                
                                <label for="integration_hero_site_logo"><?php _e('Custom Site Logo for Hero:', 'wp-integrations-directory'); ?></label><br>
                                <?php
                                $logo_id = get_option('integration_hero_site_logo', '');
                                $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';
                                ?>
                                <div class="hero-logo-upload">
                                    <div class="logo-preview" style="<?php echo $logo_url ? '' : 'display: none;'; ?>">
                                        <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 60px; height: auto; border-radius: 8px; margin-bottom: 10px;" />
                                        <br>
                                        <button type="button" class="button remove-logo-btn"><?php _e('Remove Logo', 'wp-integrations-directory'); ?></button>
                                    </div>
                                    <button type="button" class="button upload-logo-btn" style="<?php echo $logo_url ? 'display: none;' : ''; ?>"><?php _e('Upload Logo', 'wp-integrations-directory'); ?></button>
                                    <input type="hidden" name="integration_hero_site_logo" id="integration_hero_site_logo" value="<?php echo esc_attr($logo_id); ?>" />
                                </div>
                                <p class="description"><?php _e('Upload a custom logo to display in the hero section. If not set, will use your theme\'s custom logo or a default icon.', 'wp-integrations-directory'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Import/Export page
     */
    public function import_export_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Import/Export Integrations', 'wp-integrations-directory'); ?></h1>
            
            <div class="card">
                <h2><?php _e('Export Integrations', 'wp-integrations-directory'); ?></h2>
                <p><?php _e('Export all integrations to a JSON file.', 'wp-integrations-directory'); ?></p>
                <a href="<?php echo admin_url('admin-ajax.php?action=export_integrations&nonce=' . wp_create_nonce('export_integrations')); ?>" class="button button-primary">
                    <?php _e('Export Now', 'wp-integrations-directory'); ?>
                </a>
            </div>
            
            <div class="card">
                <h2><?php _e('Import Integrations', 'wp-integrations-directory'); ?></h2>
                <p><?php _e('Import integrations from a JSON file.', 'wp-integrations-directory'); ?></p>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('import_integrations', 'import_nonce'); ?>
                    <input type="file" name="import_file" accept=".json" required />
                    <input type="submit" name="import_integrations" class="button button-primary" value="<?php _e('Import File', 'wp-integrations-directory'); ?>" />
                </form>
            </div>
        </div>
        <?php
        
        // Handle import
        if (isset($_POST['import_integrations']) && wp_verify_nonce($_POST['import_nonce'], 'import_integrations')) {
            $this->handle_import();
        }
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure settings for the integrations directory.', 'wp-integrations-directory') . '</p>';
    }
    
    /**
     * Handle import
     */
    private function handle_import() {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="notice notice-error"><p>' . __('Error uploading file.', 'wp-integrations-directory') . '</p></div>';
            return;
        }
        
        $json_data = file_get_contents($_FILES['import_file']['tmp_name']);
        $data = json_decode($json_data, true);
        
        if (!$data) {
            echo '<div class="notice notice-error"><p>' . __('Invalid JSON file.', 'wp-integrations-directory') . '</p></div>';
            return;
        }
        
        $imported = 0;
        foreach ($data as $integration) {
            $post_id = wp_insert_post(array(
                'post_title' => sanitize_text_field($integration['title']),
                'post_content' => wp_kses_post($integration['content']),
                'post_status' => 'publish',
                'post_type' => 'integration'
            ));
            
            if ($post_id) {
                // Import meta data
                foreach ($integration['meta'] as $key => $value) {
                    update_post_meta($post_id, $key, $value);
                }
                $imported++;
            }
        }
        
        echo '<div class="notice notice-success"><p>' . sprintf(__('Successfully imported %d integrations.', 'wp-integrations-directory'), $imported) . '</p></div>';
    }
}