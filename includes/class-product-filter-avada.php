<?php
/**
 * Main Product Filter for Avada Class
 *
 * @package Product_Filter_Avada
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Product_Filter_Avada Class.
 */
final class Product_Filter_Avada {

    /**
     * The single instance of the class.
     *
     * @var Product_Filter_Avada
     */
    protected static $_instance = null;

    /**
     * Plugin version.
     *
     * @var string
     */
    public $version = PRODUCT_FILTER_AVADA_VERSION;

    /**
     * Main Product_Filter_Avada Instance.
     *
     * Ensures only one instance of Product_Filter_Avada is loaded or can be loaded.
     *
     * @static
     * @return Product_Filter_Avada - Main instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Product_Filter_Avada Constructor.
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Hook into actions and filters.
     */
    private function init_hooks() {
        // Plugin activation and deactivation
        register_activation_hook(PRODUCT_FILTER_AVADA_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(PRODUCT_FILTER_AVADA_PLUGIN_FILE, array($this, 'deactivate'));

        // Initialize plugin when WordPress is ready
        add_action('init', array($this, 'init'), 0);
        
        // Check for WooCommerce dependency
        add_action('admin_notices', array($this, 'check_dependencies'));
        
        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }

    /**
     * Include required files.
     */
    private function includes() {
        // Core includes
        include_once PRODUCT_FILTER_AVADA_PLUGIN_DIR . 'includes/class-product-filter-shortcode.php';
        include_once PRODUCT_FILTER_AVADA_PLUGIN_DIR . 'includes/class-product-filter-ajax.php';
        include_once PRODUCT_FILTER_AVADA_PLUGIN_DIR . 'includes/class-product-filter-admin.php';
        
        // Fusion Builder integration
        if (class_exists('FusionBuilder')) {
            include_once PRODUCT_FILTER_AVADA_PLUGIN_DIR . 'includes/class-fusion-builder-element.php';
        }
    }

    /**
     * Initialize the plugin.
     */
    public function init() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            return;
        }

        // Initialize components
        new Product_Filter_Shortcode();
        new Product_Filter_Ajax();
        
        // Initialize admin components
        if (is_admin()) {
            new Product_Filter_Admin();
        }
        
        // Initialize Fusion Builder element if available
        if (class_exists('FusionBuilder')) {
            new Product_Filter_Fusion_Element();
        }
    }

    /**
     * Plugin activation.
     */
    public function activate() {
        // Check for minimum requirements
        if (!$this->check_requirements()) {
            deactivate_plugins(PRODUCT_FILTER_AVADA_PLUGIN_BASENAME);
            wp_die(__('Product Filter for Avada requires WooCommerce to be installed and activated.', 'product-filter-avada'));
        }
        
        // Set default options
        $this->set_default_options();
        
        // Clear rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation.
     */
    public function deactivate() {
        // Clear rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Check plugin requirements.
     *
     * @return bool
     */
    private function check_requirements() {
        return $this->is_woocommerce_active();
    }

    /**
     * Check if WooCommerce is active.
     *
     * @return bool
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Check dependencies and show admin notices.
     */
    public function check_dependencies() {
        if (!$this->is_woocommerce_active()) {
            echo '<div class="notice notice-error"><p>';
            echo '<strong>' . __('Product Filter for Avada', 'product-filter-avada') . '</strong> ';
            echo __('requires WooCommerce to be installed and activated.', 'product-filter-avada');
            echo '</p></div>';
        }
    }

    /**
     * Set default plugin options.
     */
    private function set_default_options() {
        $default_options = array(
            'enable_category_filter' => 'yes',
            'enable_attribute_filter' => 'yes',
            'enable_price_filter' => 'yes',
            'filter_layout' => 'sidebar',
            'ajax_filtering' => 'yes',
        );
        
        add_option('product_filter_avada_options', $default_options);
    }

    /**
     * Load plugin text domain.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'product-filter-avada',
            false,
            dirname(PRODUCT_FILTER_AVADA_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Enqueue frontend scripts and styles.
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'product-filter-avada',
            PRODUCT_FILTER_AVADA_PLUGIN_URL . 'assets/css/product-filter.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            'product-filter-avada',
            PRODUCT_FILTER_AVADA_PLUGIN_URL . 'assets/js/product-filter.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize script for AJAX
        wp_localize_script('product-filter-avada', 'product_filter_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('product_filter_nonce'),
        ));
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on plugin settings page
        if (strpos($hook, 'product-filter-avada') === false) {
            return;
        }

        wp_enqueue_style(
            'product-filter-avada-admin',
            PRODUCT_FILTER_AVADA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version
        );

        wp_enqueue_script(
            'product-filter-avada-admin',
            PRODUCT_FILTER_AVADA_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $this->version,
            true
        );
    }

    /**
     * Get plugin option.
     *
     * @param string $key Option key.
     * @param mixed $default Default value.
     * @return mixed
     */
    public function get_option($key, $default = null) {
        $options = get_option('product_filter_avada_options', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Update plugin option.
     *
     * @param string $key Option key.
     * @param mixed $value Option value.
     */
    public function update_option($key, $value) {
        $options = get_option('product_filter_avada_options', array());
        $options[$key] = $value;
        update_option('product_filter_avada_options', $options);
    }
}