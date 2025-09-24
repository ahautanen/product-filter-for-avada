<?php
/**
 * Product Filter Admin Class
 *
 * @package Product_Filter_Avada
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product_Filter_Admin Class.
 */
class Product_Filter_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_options_page(
            __('Product Filter for Avada Settings', 'product-filter-avada'),
            __('Product Filter Avada', 'product-filter-avada'),
            'manage_options',
            'product-filter-avada',
            array($this, 'admin_page')
        );
    }

    /**
     * Initialize settings.
     */
    public function init_settings() {
        register_setting(
            'product_filter_avada_settings',
            'product_filter_avada_options',
            array($this, 'sanitize_options')
        );

        // General Settings Section
        add_settings_section(
            'general_settings',
            __('General Settings', 'product-filter-avada'),
            array($this, 'general_settings_callback'),
            'product-filter-avada'
        );

        // Filter Options Section
        add_settings_section(
            'filter_options',
            __('Filter Options', 'product-filter-avada'),
            array($this, 'filter_options_callback'),
            'product-filter-avada'
        );

        // Display Settings Section
        add_settings_section(
            'display_settings',
            __('Display Settings', 'product-filter-avada'),
            array($this, 'display_settings_callback'),
            'product-filter-avada'
        );

        // Add settings fields
        $this->add_settings_fields();
    }

    /**
     * Add settings fields.
     */
    private function add_settings_fields() {
        // General settings fields
        add_settings_field(
            'ajax_filtering',
            __('Enable AJAX Filtering', 'product-filter-avada'),
            array($this, 'checkbox_field_callback'),
            'product-filter-avada',
            'general_settings',
            array(
                'field' => 'ajax_filtering',
                'description' => __('Enable AJAX-based filtering for better user experience.', 'product-filter-avada')
            )
        );

        add_settings_field(
            'filter_layout',
            __('Default Filter Layout', 'product-filter-avada'),
            array($this, 'select_field_callback'),
            'product-filter-avada',
            'general_settings',
            array(
                'field' => 'filter_layout',
                'options' => array(
                    'sidebar' => __('Sidebar', 'product-filter-avada'),
                    'horizontal' => __('Horizontal', 'product-filter-avada'),
                    'modal' => __('Modal', 'product-filter-avada'),
                ),
                'description' => __('Choose the default layout for the product filter.', 'product-filter-avada')
            )
        );

        // Filter options fields
        add_settings_field(
            'enable_category_filter',
            __('Enable Category Filter', 'product-filter-avada'),
            array($this, 'checkbox_field_callback'),
            'product-filter-avada',
            'filter_options',
            array(
                'field' => 'enable_category_filter',
                'description' => __('Allow filtering by product categories.', 'product-filter-avada')
            )
        );

        add_settings_field(
            'enable_attribute_filter',
            __('Enable Attribute Filter', 'product-filter-avada'),
            array($this, 'checkbox_field_callback'),
            'product-filter-avada',
            'filter_options',
            array(
                'field' => 'enable_attribute_filter',
                'description' => __('Allow filtering by product attributes.', 'product-filter-avada')
            )
        );

        add_settings_field(
            'enable_price_filter',
            __('Enable Price Filter', 'product-filter-avada'),
            array($this, 'checkbox_field_callback'),
            'product-filter-avada',
            'filter_options',
            array(
                'field' => 'enable_price_filter',
                'description' => __('Allow filtering by price range.', 'product-filter-avada')
            )
        );

        // Display settings fields
        add_settings_field(
            'show_product_count',
            __('Show Product Count', 'product-filter-avada'),
            array($this, 'checkbox_field_callback'),
            'product-filter-avada',
            'display_settings',
            array(
                'field' => 'show_product_count',
                'description' => __('Display the number of products for each filter option.', 'product-filter-avada')
            )
        );

        add_settings_field(
            'products_per_page',
            __('Products Per Page', 'product-filter-avada'),
            array($this, 'number_field_callback'),
            'product-filter-avada',
            'display_settings',
            array(
                'field' => 'products_per_page',
                'min' => 1,
                'max' => 100,
                'description' => __('Number of products to display per page.', 'product-filter-avada')
            )
        );
    }

    /**
     * Admin page callback.
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="product-filter-admin-header">
                <p><?php _e('Configure your Product Filter for Avada settings below.', 'product-filter-avada'); ?></p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('product_filter_avada_settings');
                do_settings_sections('product-filter-avada');
                submit_button();
                ?>
            </form>

            <div class="product-filter-admin-sidebar">
                <div class="postbox">
                    <h3><span><?php _e('Usage Instructions', 'product-filter-avada'); ?></span></h3>
                    <div class="inside">
                        <h4><?php _e('Shortcode Usage', 'product-filter-avada'); ?></h4>
                        <p><?php _e('Use the following shortcode to display the product filter:', 'product-filter-avada'); ?></p>
                        <code>[product_filter_avada]</code>
                        
                        <h4><?php _e('Shortcode Parameters', 'product-filter-avada'); ?></h4>
                        <ul>
                            <li><strong>categories</strong>: <?php _e('Comma-separated category slugs', 'product-filter-avada'); ?></li>
                            <li><strong>attributes</strong>: <?php _e('Comma-separated attribute names', 'product-filter-avada'); ?></li>
                            <li><strong>layout</strong>: <?php _e('sidebar, horizontal, or modal', 'product-filter-avada'); ?></li>
                            <li><strong>show_count</strong>: <?php _e('yes or no', 'product-filter-avada'); ?></li>
                            <li><strong>ajax</strong>: <?php _e('yes or no', 'product-filter-avada'); ?></li>
                            <li><strong>products_per_page</strong>: <?php _e('Number of products per page', 'product-filter-avada'); ?></li>
                        </ul>

                        <h4><?php _e('Fusion Builder', 'product-filter-avada'); ?></h4>
                        <p><?php _e('If you\'re using Avada theme, you can also find the Product Filter element in Fusion Builder.', 'product-filter-avada'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .product-filter-admin-header {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .product-filter-admin-sidebar {
            margin-top: 20px;
        }
        .product-filter-admin-sidebar .postbox {
            max-width: none;
        }
        .product-filter-admin-sidebar code {
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 3px;
        }
        </style>
        <?php
    }

    /**
     * General settings section callback.
     */
    public function general_settings_callback() {
        echo '<p>' . __('Configure general plugin settings.', 'product-filter-avada') . '</p>';
    }

    /**
     * Filter options section callback.
     */
    public function filter_options_callback() {
        echo '<p>' . __('Enable or disable specific filter types.', 'product-filter-avada') . '</p>';
    }

    /**
     * Display settings section callback.
     */
    public function display_settings_callback() {
        echo '<p>' . __('Configure how the filter and products are displayed.', 'product-filter-avada') . '</p>';
    }

    /**
     * Checkbox field callback.
     */
    public function checkbox_field_callback($args) {
        $options = get_option('product_filter_avada_options', array());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : 'yes';
        
        echo '<label>';
        echo '<input type="checkbox" name="product_filter_avada_options[' . $args['field'] . ']" value="yes" ' . checked($value, 'yes', false) . ' />';
        echo ' ' . $args['description'];
        echo '</label>';
    }

    /**
     * Select field callback.
     */
    public function select_field_callback($args) {
        $options = get_option('product_filter_avada_options', array());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        
        echo '<select name="product_filter_avada_options[' . $args['field'] . ']">';
        foreach ($args['options'] as $option_value => $option_label) {
            echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>';
            echo esc_html($option_label);
            echo '</option>';
        }
        echo '</select>';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

    /**
     * Number field callback.
     */
    public function number_field_callback($args) {
        $options = get_option('product_filter_avada_options', array());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : 12;
        
        echo '<input type="number" name="product_filter_avada_options[' . $args['field'] . ']" value="' . esc_attr($value) . '"';
        if (isset($args['min'])) echo ' min="' . esc_attr($args['min']) . '"';
        if (isset($args['max'])) echo ' max="' . esc_attr($args['max']) . '"';
        echo ' />';
        
        if (!empty($args['description'])) {
            echo '<p class="description">' . $args['description'] . '</p>';
        }
    }

    /**
     * Sanitize options.
     */
    public function sanitize_options($options) {
        $sanitized = array();
        
        if (isset($options['ajax_filtering'])) {
            $sanitized['ajax_filtering'] = $options['ajax_filtering'] === 'yes' ? 'yes' : 'no';
        }
        
        if (isset($options['filter_layout'])) {
            $allowed_layouts = array('sidebar', 'horizontal', 'modal');
            $sanitized['filter_layout'] = in_array($options['filter_layout'], $allowed_layouts) ? $options['filter_layout'] : 'sidebar';
        }
        
        if (isset($options['enable_category_filter'])) {
            $sanitized['enable_category_filter'] = $options['enable_category_filter'] === 'yes' ? 'yes' : 'no';
        }
        
        if (isset($options['enable_attribute_filter'])) {
            $sanitized['enable_attribute_filter'] = $options['enable_attribute_filter'] === 'yes' ? 'yes' : 'no';
        }
        
        if (isset($options['enable_price_filter'])) {
            $sanitized['enable_price_filter'] = $options['enable_price_filter'] === 'yes' ? 'yes' : 'no';
        }
        
        if (isset($options['show_product_count'])) {
            $sanitized['show_product_count'] = $options['show_product_count'] === 'yes' ? 'yes' : 'no';
        }
        
        if (isset($options['products_per_page'])) {
            $sanitized['products_per_page'] = max(1, min(100, intval($options['products_per_page'])));
        }
        
        return $sanitized;
    }
}