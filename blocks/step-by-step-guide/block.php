<?php
/**
 * Step-by-Step Integration Guide Block
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Integrations_Step_By_Step_Block {
    
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
        add_action('init', array($this, 'register_block'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }
    
    /**
     * Register the block
     */
    public function register_block() {
        // Register block type if function exists
        if (!function_exists('register_block_type')) {
            return;
        }
        
        // Register block type with all attributes
        register_block_type('wp-integrations-directory/step-by-step-guide', array(
            'render_callback' => array($this, 'render_block'),
            'attributes' => array(
                'title' => array(
                    'type' => 'string',
                    'default' => 'Step-by-Step Guide'
                ),
                'description' => array(
                    'type' => 'string', 
                    'default' => ''
                ),
                'steps' => array(
                    'type' => 'array',
                    'default' => array(
                        array(
                            'id' => 1,
                            'title' => 'Step 1',
                            'content' => 'Enter your step description here.',
                            'image' => '',
                            'imageAlt' => '',
                            'code' => '',
                            'codeLanguage' => 'html',
                            'showCode' => false
                        )
                    )
                ),
                'layout' => array(
                    'type' => 'string',
                    'default' => 'numbered'
                ),
                'showNumbers' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'accentColor' => array(
                    'type' => 'string',
                    'default' => '#0073aa'
                )
            )
        ));
    }
    
    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'step-by-step-guide-editor',
            WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'blocks/step-by-step-guide/src/index.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-data'),
            WP_INTEGRATIONS_DIRECTORY_VERSION,
            true
        );
        
        wp_enqueue_style(
            'step-by-step-guide-editor-style',
            WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'blocks/step-by-step-guide/assets/editor.css',
            array('wp-edit-blocks'),
            WP_INTEGRATIONS_DIRECTORY_VERSION
        );
        
        wp_localize_script('step-by-step-guide-editor', 'stepByStepGuideBlock', array(
            'pluginUrl' => WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL,
            'nonce' => wp_create_nonce('step_by_step_nonce')
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (has_block('wp-integrations-directory/step-by-step-guide')) {
            wp_enqueue_style(
                'step-by-step-guide-style',
                WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'blocks/step-by-step-guide/assets/style.css',
                array(),
                WP_INTEGRATIONS_DIRECTORY_VERSION
            );
            
            wp_enqueue_script(
                'step-by-step-guide-frontend',
                WP_INTEGRATIONS_DIRECTORY_PLUGIN_URL . 'blocks/step-by-step-guide/assets/frontend.js',
                array('jquery'),
                WP_INTEGRATIONS_DIRECTORY_VERSION,
                true
            );
        }
    }
    
    /**
     * Render block on frontend
     */
    public function render_block($attributes) {
        $title = esc_html($attributes['title'] ?? 'Step-by-Step Guide');
        $description = esc_html($attributes['description'] ?? '');
        $steps = $attributes['steps'] ?? array();
        $layout = esc_attr($attributes['layout'] ?? 'numbered');
        $show_numbers = $attributes['showNumbers'] ?? true;
        $accent_color = esc_attr($attributes['accentColor'] ?? '#0073aa');
        
        if (empty($steps)) {
            return '<p>' . __('No steps added yet.', 'wp-integrations-directory') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="wp-block-step-by-step-guide layout-<?php echo $layout; ?>" style="--accent-color: <?php echo $accent_color; ?>">
            <div class="step-guide-header">
                <h2 class="step-guide-title"><?php echo $title; ?></h2>
                <?php if ($description): ?>
                    <p class="step-guide-description"><?php echo $description; ?></p>
                <?php endif; ?>
            </div>
            
            <div class="step-guide-content">
                <?php foreach ($steps as $index => $step): ?>
                    <?php
                    $step_number = $index + 1;
                    $step_title = esc_html($step['title'] ?? "Step {$step_number}");
                    $step_content = wp_kses_post($step['content'] ?? '');
                    $step_image = esc_url($step['image'] ?? '');
                    $step_image_alt = esc_attr($step['imageAlt'] ?? '');
                    $step_code = esc_html($step['code'] ?? '');
                    $code_language = esc_attr($step['codeLanguage'] ?? 'html');
                    $show_code = $step['showCode'] ?? false;
                    ?>
                    
                    <div class="step-item" data-step="<?php echo $step_number; ?>">
                        <div class="step-header">
                            <?php if ($show_numbers): ?>
                                <span class="step-number"><?php echo $step_number; ?></span>
                            <?php endif; ?>
                            <h3 class="step-title"><?php echo $step_title; ?></h3>
                        </div>
                        
                        <div class="step-content">
                            <?php if ($step_content): ?>
                                <div class="step-description">
                                    <?php echo wpautop($step_content); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($step_image): ?>
                                <div class="step-image">
                                    <img src="<?php echo $step_image; ?>" alt="<?php echo $step_image_alt; ?>" loading="lazy" />
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_code && $step_code): ?>
                                <div class="step-code">
                                    <div class="code-header">
                                        <span class="code-language"><?php echo strtoupper($code_language); ?></span>
                                        <button class="copy-code-btn" data-code="<?php echo esc_attr($step_code); ?>">
                                            <span class="dashicons dashicons-clipboard"></span>
                                            <?php _e('Copy', 'wp-integrations-directory'); ?>
                                        </button>
                                    </div>
                                    <pre class="language-<?php echo $code_language; ?>"><code><?php echo $step_code; ?></code></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="step-guide-footer">
                <div class="step-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="progress-text">
                        <span class="current-step">1</span> / <?php echo count($steps); ?>
                    </span>
                </div>
                
                <div class="step-navigation">
                    <button class="step-nav-btn prev-btn" disabled>
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                        <?php _e('Previous', 'wp-integrations-directory'); ?>
                    </button>
                    <button class="step-nav-btn next-btn">
                        <?php _e('Next', 'wp-integrations-directory'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the block
WP_Integrations_Step_By_Step_Block::get_instance();