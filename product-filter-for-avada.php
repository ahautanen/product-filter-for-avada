<?php
/**
 * Plugin Name: Product Filter for Avada
 * Plugin URI: https://github.com/ahautanen/product-filter-for-avada
 * Description: Advanced WooCommerce product filter designed for Avada theme and Fusion Builder compatibility. Filter products by categories and attributes with shortcode or Fusion Builder element.
 * Version: 1.0.0
 * Author: ahautanen
 * Text Domain: product-filter-avada
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
define('PRODUCT_FILTER_AVADA_VERSION', '1.0.0');
define('PRODUCT_FILTER_AVADA_PLUGIN_FILE', __FILE__);
define('PRODUCT_FILTER_AVADA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PRODUCT_FILTER_AVADA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PRODUCT_FILTER_AVADA_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include the main plugin class
require_once PRODUCT_FILTER_AVADA_PLUGIN_DIR . 'includes/class-product-filter-avada.php';

/**
 * Main instance of Product_Filter_Avada.
 *
 * Returns the main instance of Product_Filter_Avada to prevent the need to use globals.
 *
 * @return Product_Filter_Avada
 */
function product_filter_avada() {
    return Product_Filter_Avada::instance();
}

// Initialize the plugin
product_filter_avada();