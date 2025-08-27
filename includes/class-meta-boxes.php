<?php
/**
 * Meta Boxes for Integration Post Type
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Integrations_Directory_Meta_Boxes {
    
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
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'integration_details',
            __('Integration Details', 'wp-integrations-directory'),
            array($this, 'integration_details_callback'),
            'integration',
            'normal',
            'high'
        );
        
        add_meta_box(
            'integration_features',
            __('Features & Requirements', 'wp-integrations-directory'),
            array($this, 'integration_features_callback'),
            'integration',
            'normal',
            'high'
        );
        
        add_meta_box(
            'integration_media',
            __('Media & Code', 'wp-integrations-directory'),
            array($this, 'integration_media_callback'),
            'integration',
            'normal',
            'default'
        );
    }
    
    /**
     * Integration details meta box callback
     */
    public function integration_details_callback($post) {
        wp_nonce_field('integration_meta_box', 'integration_meta_box_nonce');
        
        $logo = get_post_meta($post->ID, '_integration_logo', true);
        $external_url = get_post_meta($post->ID, '_integration_external_url', true);
        $difficulty = get_post_meta($post->ID, '_integration_difficulty', true);
        $setup_time = get_post_meta($post->ID, '_integration_setup_time', true);
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="integration_logo"><?php _e('Integration Logo', 'wp-integrations-directory'); ?> *</label>
                    </th>
                    <td>
                        <div id="integration_logo_container">
                            <input type="hidden" id="integration_logo" name="integration_logo" value="<?php echo esc_attr($logo); ?>" />
                            <button type="button" class="button" id="integration_logo_button">
                                <?php echo $logo ? __('Change Logo', 'wp-integrations-directory') : __('Select Logo', 'wp-integrations-directory'); ?>
                            </button>
                            <button type="button" class="button" id="integration_logo_remove" style="<?php echo $logo ? '' : 'display:none;'; ?>">
                                <?php _e('Remove', 'wp-integrations-directory'); ?>
                            </button>
                            <div id="integration_logo_preview" style="margin-top: 10px;">
                                <?php if ($logo): ?>
                                    <?php echo wp_get_attachment_image($logo, 'thumbnail'); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="description"><?php _e('Upload a logo for this integration. Recommended size: 200x200px.', 'wp-integrations-directory'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="integration_external_url"><?php _e('External URL', 'wp-integrations-directory'); ?></label>
                    </th>
                    <td>
                        <input type="url" id="integration_external_url" name="integration_external_url" value="<?php echo esc_attr($external_url); ?>" class="regular-text" />
                        <p class="description"><?php _e('Official website URL for this integration.', 'wp-integrations-directory'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="integration_difficulty"><?php _e('Difficulty Level', 'wp-integrations-directory'); ?></label>
                    </th>
                    <td>
                        <select id="integration_difficulty" name="integration_difficulty">
                            <option value=""><?php _e('Select Difficulty', 'wp-integrations-directory'); ?></option>
                            <option value="beginner" <?php selected($difficulty, 'beginner'); ?>><?php _e('Beginner', 'wp-integrations-directory'); ?></option>
                            <option value="intermediate" <?php selected($difficulty, 'intermediate'); ?>><?php _e('Intermediate', 'wp-integrations-directory'); ?></option>
                            <option value="advanced" <?php selected($difficulty, 'advanced'); ?>><?php _e('Advanced', 'wp-integrations-directory'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="integration_setup_time"><?php _e('Setup Time', 'wp-integrations-directory'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="integration_setup_time" name="integration_setup_time" value="<?php echo esc_attr($setup_time); ?>" class="regular-text" placeholder="e.g., 5 minutes, 1 hour" />
                        <p class="description"><?php _e('Estimated time to set up this integration.', 'wp-integrations-directory'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Integration features meta box callback
     */
    public function integration_features_callback($post) {
        $features = get_post_meta($post->ID, '_integration_features', true);
        $requirements = get_post_meta($post->ID, '_integration_requirements', true);
        
        if (!is_array($features)) {
            $features = array('');
        }
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label><?php _e('Key Features', 'wp-integrations-directory'); ?></label>
                    </th>
                    <td>
                        <div id="integration_features_container">
                            <?php foreach ($features as $index => $feature): ?>
                                <div class="feature-item" style="margin-bottom: 10px;">
                                    <input type="text" name="integration_features[]" value="<?php echo esc_attr($feature); ?>" class="regular-text" placeholder="<?php _e('Enter a feature', 'wp-integrations-directory'); ?>" />
                                    <button type="button" class="button remove-feature" <?php echo count($features) === 1 ? 'style="display:none;"' : ''; ?>><?php _e('Remove', 'wp-integrations-directory'); ?></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" id="add_feature"><?php _e('Add Feature', 'wp-integrations-directory'); ?></button>
                        <p class="description"><?php _e('List the key features of this integration.', 'wp-integrations-directory'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="integration_requirements"><?php _e('Requirements', 'wp-integrations-directory'); ?></label>
                    </th>
                    <td>
                        <textarea id="integration_requirements" name="integration_requirements" rows="4" class="large-text"><?php echo esc_textarea($requirements); ?></textarea>
                        <p class="description"><?php _e('Any special requirements or prerequisites for this integration.', 'wp-integrations-directory'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Integration media meta box callback
     */
    public function integration_media_callback($post) {
        $screenshots = get_post_meta($post->ID, '_integration_screenshots', true);
        
        if (!is_array($screenshots)) {
            $screenshots = array();
        }
        ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label><?php _e('Screenshots Gallery', 'wp-integrations-directory'); ?></label>
                    </th>
                    <td>
                        <div id="integration_screenshots_container">
                            <input type="hidden" id="integration_screenshots" name="integration_screenshots" value="<?php echo esc_attr(implode(',', $screenshots)); ?>" />
                            <div id="screenshots_preview">
                                <?php foreach ($screenshots as $screenshot_id): ?>
                                    <?php if ($screenshot_id): ?>
                                        <div class="screenshot-item" data-id="<?php echo esc_attr($screenshot_id); ?>">
                                            <?php echo wp_get_attachment_image($screenshot_id, 'thumbnail'); ?>
                                            <button type="button" class="remove-screenshot">×</button>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="button" id="integration_screenshots_button">
                                <?php _e('Add Screenshots', 'wp-integrations-directory'); ?>
                            </button>
                        </div>
                        <p class="description"><?php _e('Upload screenshots showing the integration in action (optional).', 'wp-integrations-directory'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['integration_meta_box_nonce']) || !wp_verify_nonce($_POST['integration_meta_box_nonce'], 'integration_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (isset($_POST['post_type']) && 'integration' === $_POST['post_type']) {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        }
        
        // Save all meta fields
        $meta_fields = array(
            'integration_logo',
            'integration_external_url',
            'integration_difficulty',
            'integration_setup_time',
            'integration_requirements'
        );
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                $value = $_POST[$field];
                if ($field === 'integration_external_url') {
                    $value = esc_url_raw($value);
                } else {
                    $value = sanitize_text_field($value);
                }
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
        
        // Save features array
        if (isset($_POST['integration_features'])) {
            $features = array_filter(array_map('sanitize_text_field', $_POST['integration_features']));
            update_post_meta($post_id, '_integration_features', $features);
        }
        
        // Save screenshots
        if (isset($_POST['integration_screenshots'])) {
            $screenshots = array_filter(array_map('intval', explode(',', $_POST['integration_screenshots'])));
            update_post_meta($post_id, '_integration_screenshots', $screenshots);
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type, $pagenow;
        
        if (($pagenow === 'post.php' || $pagenow === 'post-new.php') && $post_type === 'integration') {
            wp_enqueue_media();
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_style('wp-color-picker');
            
            wp_enqueue_script(
                'integration-admin',
                WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'wp-color-picker'),
                WP_INTEGRATIONS_DIRECTORY_VERSION,
                true
            );
            
            wp_localize_script('integration-admin', 'integrationAdmin', array(
                'select_logo' => __('Select Logo', 'wp-integrations-directory'),
                'change_logo' => __('Change Logo', 'wp-integrations-directory'),
                'select_screenshots' => __('Select Screenshots', 'wp-integrations-directory'),
                'remove' => __('Remove', 'wp-integrations-directory')
            ));
        }
        
        // Enqueue on taxonomy pages for color picker
        if ($hook === 'edit-tags.php' || $hook === 'term.php') {
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_style('wp-color-picker');
        }
    }
}