<?php
/**
 * Admin functionality for Product Filter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Avada_Product_Filter_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_filter('plugin_action_links_' . AVADA_PRODUCT_FILTER_PLUGIN_BASENAME, array($this, 'add_action_links'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Product Filter for Avada', 'product-filter-for-avada'),
            __('Product Filter for Avada', 'product-filter-for-avada'),
            'manage_options',
            'avada-product-filter',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function settings_init() {
        register_setting('avada_product_filter_settings', 'avada_product_filter_settings');
        
        add_settings_section(
            'avada_product_filter_general_section',
            __('General Settings', 'product-filter-for-avada'),
            array($this, 'settings_section_callback'),
            'avada_product_filter_settings'
        );
        
        add_settings_field(
            'enable_categories',
            __('Enable Category Filter', 'product-filter-for-avada'),
            array($this, 'checkbox_field_callback'),
            'avada_product_filter_settings',
            'avada_product_filter_general_section',
            array(
                'field' => 'enable_categories',
                'description' => __('Enable category filtering functionality.', 'product-filter-for-avada')
            )
        );
        
        add_settings_field(
            'enable_attributes',
            __('Enable Attribute Filters', 'product-filter-for-avada'),
            array($this, 'checkbox_field_callback'),
            'avada_product_filter_settings',
            'avada_product_filter_general_section',
            array(
                'field' => 'enable_attributes',
                'description' => __('Enable product attribute filtering functionality.', 'product-filter-for-avada')
            )
        );
        
        add_settings_field(
            'ajax_filtering',
            __('Enable AJAX Filtering', 'product-filter-for-avada'),
            array($this, 'checkbox_field_callback'),
            'avada_product_filter_settings',
            'avada_product_filter_general_section',
            array(
                'field' => 'ajax_filtering',
                'description' => __('Enable AJAX filtering for seamless user experience without page reloads.', 'product-filter-for-avada')
            )
        );
        
        add_settings_field(
            'show_product_count',
            __('Show Product Count', 'product-filter-for-avada'),
            array($this, 'checkbox_field_callback'),
            'avada_product_filter_settings',
            'avada_product_filter_general_section',
            array(
                'field' => 'show_product_count',
                'description' => __('Show product count next to filter options.', 'product-filter-for-avada')
            )
        );
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure the product filter settings below.', 'product-filter-for-avada') . '</p>';
    }
    
    /**
     * Checkbox field callback
     */
    public function checkbox_field_callback($args) {
        $options = get_option('avada_product_filter_settings');
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : 'yes';
        
        echo '<label>';
        echo '<input type="checkbox" name="avada_product_filter_settings[' . esc_attr($field) . ']" value="yes" ' . checked($value, 'yes', false) . '>';
        echo ' ' . esc_html($args['description']);
        echo '</label>';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="avada-product-filter-admin">
                <div class="main-content">
                    <form action="options.php" method="post">
                        <?php
                        settings_fields('avada_product_filter_settings');
                        do_settings_sections('avada_product_filter_settings');
                        submit_button();
                        ?>
                    </form>
                    
                    <div class="usage-section">
                        <h2><?php _e('Usage Instructions', 'product-filter-for-avada'); ?></h2>
                        
                        <h3><?php _e('Shortcode Usage', 'product-filter-for-avada'); ?></h3>
                        <p><?php _e('Use the following shortcode to display the product filter:', 'product-filter-for-avada'); ?></p>
                        <code>[avada_product_filter]</code>
                        
                        <h4><?php _e('Shortcode Parameters', 'product-filter-for-avada'); ?></h4>
                        <ul>
                            <li><strong>categories:</strong> <?php _e('Comma-separated category IDs to filter by specific categories', 'product-filter-for-avada'); ?></li>
                            <li><strong>attributes:</strong> <?php _e('Comma-separated attribute names to show specific attribute filters', 'product-filter-for-avada'); ?></li>
                            <li><strong>show_categories:</strong> <?php _e('Show category filter (yes/no)', 'product-filter-for-avada'); ?></li>
                            <li><strong>show_attributes:</strong> <?php _e('Show attribute filters (yes/no)', 'product-filter-for-avada'); ?></li>
                            <li><strong>show_price_filter:</strong> <?php _e('Show price range filter (yes/no)', 'product-filter-for-avada'); ?></li>
                            <li><strong>columns:</strong> <?php _e('Number of product columns (1-6)', 'product-filter-for-avada'); ?></li>
                            <li><strong>products_per_page:</strong> <?php _e('Number of products per page', 'product-filter-for-avada'); ?></li>
                            <li><strong>orderby:</strong> <?php _e('Product ordering (menu_order, title, date, price, popularity, rating)', 'product-filter-for-avada'); ?></li>
                            <li><strong>order:</strong> <?php _e('Sort direction (ASC/DESC)', 'product-filter-for-avada'); ?></li>
                        </ul>
                        
                        <h4><?php _e('Example Usage', 'product-filter-for-avada'); ?></h4>
                        <code>[avada_product_filter categories="12,15" columns="4" products_per_page="16"]</code>
                        
                        <h3><?php _e('Fusion Builder Integration', 'product-filter-for-avada'); ?></h3>
                        <p><?php _e('When using Avada theme with Fusion Builder, you can find the "Product Filter" element in the Fusion Builder elements list. Simply drag and drop it into your page and configure the settings in the element options.', 'product-filter-for-avada'); ?></p>
                    </div>
                </div>
                
                <div class="sidebar">
                    <div class="postbox">
                        <h3><?php _e('Plugin Information', 'product-filter-for-avada'); ?></h3>
                        <div class="inside">
                            <p><strong><?php _e('Version:', 'product-filter-for-avada'); ?></strong> <?php echo AVADA_PRODUCT_FILTER_VERSION; ?></p>
                            <p><strong><?php _e('Requirements:', 'product-filter-for-avada'); ?></strong></p>
                            <ul>
                                <li>WordPress 5.0+</li>
                                <li>WooCommerce 5.0+</li>
                                <li>PHP 7.4+</li>
                                <li>Avada Theme (recommended)</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h3><?php _e('Support', 'product-filter-for-avada'); ?></h3>
                        <div class="inside">
                            <p><?php _e('For support and documentation, please visit:', 'product-filter-for-avada'); ?></p>
                            <p><a href="https://github.com/ahautanen/product-filter-for-avada" target="_blank"><?php _e('GitHub Repository', 'product-filter-for-avada'); ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .avada-product-filter-admin {
                display: flex;
                gap: 20px;
            }
            .main-content {
                flex: 1;
            }
            .sidebar {
                width: 300px;
            }
            .usage-section {
                margin-top: 30px;
                padding: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .usage-section h2 {
                margin-top: 0;
            }
            .usage-section code {
                display: block;
                padding: 10px;
                background: #f4f4f4;
                border: 1px solid #ddd;
                border-radius: 4px;
                margin: 10px 0;
            }
            .usage-section ul {
                margin: 10px 0 10px 20px;
            }
            .postbox {
                margin-bottom: 20px;
            }
            .postbox h3 {
                margin: 0;
                padding: 10px 15px;
                background: #f7f7f7;
                border-bottom: 1px solid #ddd;
            }
            .postbox .inside {
                padding: 15px;
            }
            .postbox .inside ul {
                margin: 10px 0 10px 20px;
            }
        </style>
        <?php
    }
    
    /**
     * Add action links to plugin list
     */
    public function add_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=avada-product-filter') . '">' . __('Settings', 'product-filter-for-avada') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}