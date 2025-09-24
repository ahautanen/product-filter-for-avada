<?php
/**
 * Fusion Builder Element for Product Filter
 *
 * @package Product_Filter_Avada
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product_Filter_Fusion_Element Class.
 */
class Product_Filter_Fusion_Element {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('fusion_builder_before_init', array($this, 'init_fusion_element'));
    }

    /**
     * Initialize Fusion Builder element.
     */
    public function init_fusion_element() {
        if (!class_exists('FusionBuilder')) {
            return;
        }

        // Map the element
        fusion_builder_map(array(
            'name' => esc_attr__('Product Filter for Avada', 'product-filter-avada'),
            'shortcode' => 'product_filter_avada',
            'icon' => 'fusiona-filter',
            'preview' => PRODUCT_FILTER_AVADA_PLUGIN_URL . 'assets/images/preview.png',
            'preview_id' => 'product-filter-avada-preview',
            'allow_generator' => true,
            'inline_editor' => false,
            'help_url' => '',
            'params' => array(
                array(
                    'type' => 'select',
                    'heading' => esc_attr__('Filter Layout', 'product-filter-avada'),
                    'description' => esc_attr__('Choose the layout for the product filter.', 'product-filter-avada'),
                    'param_name' => 'layout',
                    'value' => array(
                        'sidebar' => esc_attr__('Sidebar', 'product-filter-avada'),
                        'horizontal' => esc_attr__('Horizontal', 'product-filter-avada'),
                        'modal' => esc_attr__('Modal', 'product-filter-avada'),
                    ),
                    'default' => 'sidebar',
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_attr__('Specific Categories', 'product-filter-avada'),
                    'description' => esc_attr__('Enter comma-separated category slugs to limit filtering to specific categories. Leave empty for all categories.', 'product-filter-avada'),
                    'param_name' => 'categories',
                    'value' => '',
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_attr__('Specific Attributes', 'product-filter-avada'),
                    'description' => esc_attr__('Enter comma-separated attribute names to limit filtering to specific attributes. Leave empty for all attributes.', 'product-filter-avada'),
                    'param_name' => 'attributes',
                    'value' => '',
                ),
                array(
                    'type' => 'radio_button_set',
                    'heading' => esc_attr__('Show Product Count', 'product-filter-avada'),
                    'description' => esc_attr__('Display the number of products for each filter option.', 'product-filter-avada'),
                    'param_name' => 'show_count',
                    'value' => array(
                        'yes' => esc_attr__('Yes', 'product-filter-avada'),
                        'no' => esc_attr__('No', 'product-filter-avada'),
                    ),
                    'default' => 'yes',
                ),
                array(
                    'type' => 'radio_button_set',
                    'heading' => esc_attr__('Enable AJAX', 'product-filter-avada'),
                    'description' => esc_attr__('Enable AJAX filtering for better user experience.', 'product-filter-avada'),
                    'param_name' => 'ajax',
                    'value' => array(
                        'yes' => esc_attr__('Yes', 'product-filter-avada'),
                        'no' => esc_attr__('No', 'product-filter-avada'),
                    ),
                    'default' => 'yes',
                ),
                array(
                    'type' => 'range',
                    'heading' => esc_attr__('Products Per Page', 'product-filter-avada'),
                    'description' => esc_attr__('Number of products to display per page.', 'product-filter-avada'),
                    'param_name' => 'products_per_page',
                    'value' => '12',
                    'min' => '1',
                    'max' => '100',
                    'step' => '1',
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_attr__('Filter Background Color', 'product-filter-avada'),
                    'description' => esc_attr__('Choose a background color for the filter area.', 'product-filter-avada'),
                    'param_name' => 'filter_bg_color',
                    'value' => '#ffffff',
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_attr__('Filter Text Color', 'product-filter-avada'),
                    'description' => esc_attr__('Choose a text color for the filter options.', 'product-filter-avada'),
                    'param_name' => 'filter_text_color',
                    'value' => '#333333',
                ),
                array(
                    'type' => 'colorpicker',
                    'heading' => esc_attr__('Filter Accent Color', 'product-filter-avada'),
                    'description' => esc_attr__('Choose an accent color for active filter items and buttons.', 'product-filter-avada'),
                    'param_name' => 'filter_accent_color',
                    'value' => '#65bc7b',
                ),
                array(
                    'type' => 'textfield',
                    'heading' => esc_attr__('Custom CSS Class', 'product-filter-avada'),
                    'description' => esc_attr__('Add custom CSS class for additional styling.', 'product-filter-avada'),
                    'param_name' => 'class',
                    'value' => '',
                ),
            ),
        ));
    }
}

/**
 * Shortcode function for Fusion Builder compatibility.
 */
function product_filter_avada_shortcode($atts, $content = '') {
    // Parse attributes with Fusion Builder compatibility
    $atts = shortcode_atts(array(
        'layout' => 'sidebar',
        'categories' => '',
        'attributes' => '',
        'show_count' => 'yes',
        'ajax' => 'yes',
        'products_per_page' => 12,
        'filter_bg_color' => '#ffffff',
        'filter_text_color' => '#333333',
        'filter_accent_color' => '#65bc7b',
        'class' => '',
    ), $atts, 'product_filter_avada');

    // Generate custom CSS if colors are specified
    $custom_css = '';
    if ($atts['filter_bg_color'] !== '#ffffff' || $atts['filter_text_color'] !== '#333333' || $atts['filter_accent_color'] !== '#65bc7b') {
        $filter_id = 'product-filter-' . uniqid();
        $custom_css = '<style>';
        $custom_css .= '#' . $filter_id . ' { background-color: ' . esc_attr($atts['filter_bg_color']) . '; color: ' . esc_attr($atts['filter_text_color']) . '; }';
        $custom_css .= '#' . $filter_id . ' .filter-option.active, #' . $filter_id . ' .filter-button { background-color: ' . esc_attr($atts['filter_accent_color']) . '; }';
        $custom_css .= '</style>';
        
        // Add the ID to the wrapper
        $atts['wrapper_id'] = $filter_id;
    }

    // Create shortcode instance
    $shortcode = new Product_Filter_Shortcode();
    
    // Render the shortcode
    $output = $custom_css . $shortcode->render_shortcode($atts);
    
    return $output;
}

// Register the shortcode
add_shortcode('product_filter_avada', 'product_filter_avada_shortcode');