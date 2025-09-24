<?php
/**
 * Plugin Name: Product Filter for Avada
 * Plugin URI: https://github.com/ahautanen/product-filter-for-avada
 * Description: Advanced WooCommerce product filter designed for Avada theme and Fusion Builder compatibility. Filter products by category and pre-set attributes with shortcode and Fusion Builder element support.
 * Version: 1.0.0
 * Author: ahautanen
 * Text Domain: product-filter-for-avada
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AVADA_PRODUCT_FILTER_VERSION', '1.0.0');
define('AVADA_PRODUCT_FILTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AVADA_PRODUCT_FILTER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AVADA_PRODUCT_FILTER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class Avada_Product_Filter {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Include required files
        $this->includes();
        
        // Initialize components
        $this->init_hooks();
    }
    
    /**
     * Include required files
     */
    private function includes() {
        require_once AVADA_PRODUCT_FILTER_PLUGIN_PATH . 'includes/class-shortcode.php';
        require_once AVADA_PRODUCT_FILTER_PLUGIN_PATH . 'includes/class-fusion-element.php';
        require_once AVADA_PRODUCT_FILTER_PLUGIN_PATH . 'includes/class-ajax-handler.php';
        require_once AVADA_PRODUCT_FILTER_PLUGIN_PATH . 'includes/class-admin.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Initialize components
        new Avada_Product_Filter_Shortcode();
        new Avada_Product_Filter_Fusion_Element();
        new Avada_Product_Filter_Ajax_Handler();
        new Avada_Product_Filter_Admin();
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'avada-product-filter-style',
            AVADA_PRODUCT_FILTER_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            AVADA_PRODUCT_FILTER_VERSION
        );
        
        wp_enqueue_script(
            'avada-product-filter-script',
            AVADA_PRODUCT_FILTER_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            AVADA_PRODUCT_FILTER_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('avada-product-filter-script', 'avada_product_filter_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('avada_product_filter_nonce')
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        wp_enqueue_style(
            'avada-product-filter-admin-style',
            AVADA_PRODUCT_FILTER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            AVADA_PRODUCT_FILTER_VERSION
        );
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('product-filter-for-avada', false, dirname(AVADA_PRODUCT_FILTER_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create default options
        add_option('avada_product_filter_settings', array(
            'enable_categories' => 'yes',
            'enable_attributes' => 'yes',
            'ajax_filtering' => 'yes',
            'show_product_count' => 'yes'
        ));
        
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('Product Filter for Avada requires WooCommerce to be installed and active.', 'product-filter-for-avada');
        echo '</p></div>';
    }
}

// Initialize the plugin
function avada_product_filter() {
    return Avada_Product_Filter::instance();
}

// Start the plugin
avada_product_filter();