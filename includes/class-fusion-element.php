<?php
/**
 * Fusion Builder Element for Product Filter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Avada_Product_Filter_Fusion_Element {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('fusion_builder_before_init', array($this, 'register_fusion_element'));
    }
    
    /**
     * Register Fusion Builder element
     */
    public function register_fusion_element() {
        if (!class_exists('FusionBuilder')) {
            return;
        }
        
        fusion_builder_map(
            fusion_builder_frontend_data(
                'AvadaProductFilter',
                array(
                    'name' => esc_attr__('Product Filter', 'product-filter-for-avada'),
                    'shortcode' => 'avada_product_filter',
                    'icon' => 'fusiona-search',
                    'preview' => AVADA_PRODUCT_FILTER_PLUGIN_PATH . 'includes/fusion-preview.php',
                    'preview_id' => 'fusion-builder-block-module-avada-product-filter-preview-template',
                    'help_url' => '',
                    'inline_editor' => false,
                    'params' => array(
                        array(
                            'type' => 'select',
                            'heading' => esc_attr__('Show Categories Filter', 'product-filter-for-avada'),
                            'description' => esc_attr__('Choose whether to display the category filter.', 'product-filter-for-avada'),
                            'param_name' => 'show_categories',
                            'value' => array(
                                'yes' => esc_attr__('Yes', 'product-filter-for-avada'),
                                'no' => esc_attr__('No', 'product-filter-for-avada'),
                            ),
                            'default' => 'yes',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_attr__('Specific Categories', 'product-filter-for-avada'),
                            'description' => esc_attr__('Enter specific category IDs separated by commas. Leave empty to show all categories.', 'product-filter-for-avada'),
                            'param_name' => 'categories',
                            'value' => '',
                            'dependency' => array(
                                array(
                                    'element' => 'show_categories',
                                    'value' => 'yes',
                                    'operator' => '==',
                                ),
                            ),
                        ),
                        array(
                            'type' => 'select',
                            'heading' => esc_attr__('Show Attributes Filter', 'product-filter-for-avada'),
                            'description' => esc_attr__('Choose whether to display attribute filters.', 'product-filter-for-avada'),
                            'param_name' => 'show_attributes',
                            'value' => array(
                                'yes' => esc_attr__('Yes', 'product-filter-for-avada'),
                                'no' => esc_attr__('No', 'product-filter-for-avada'),
                            ),
                            'default' => 'yes',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_attr__('Specific Attributes', 'product-filter-for-avada'),
                            'description' => esc_attr__('Enter specific attribute names separated by commas. Leave empty to show all attributes.', 'product-filter-for-avada'),
                            'param_name' => 'attributes',
                            'value' => '',
                            'dependency' => array(
                                array(
                                    'element' => 'show_attributes',
                                    'value' => 'yes',
                                    'operator' => '==',
                                ),
                            ),
                        ),
                        array(
                            'type' => 'select',
                            'heading' => esc_attr__('Show Price Filter', 'product-filter-for-avada'),
                            'description' => esc_attr__('Choose whether to display the price range filter.', 'product-filter-for-avada'),
                            'param_name' => 'show_price_filter',
                            'value' => array(
                                'yes' => esc_attr__('Yes', 'product-filter-for-avada'),
                                'no' => esc_attr__('No', 'product-filter-for-avada'),
                            ),
                            'default' => 'yes',
                        ),
                        array(
                            'type' => 'range',
                            'heading' => esc_attr__('Columns', 'product-filter-for-avada'),
                            'description' => esc_attr__('Number of columns for product display.', 'product-filter-for-avada'),
                            'param_name' => 'columns',
                            'value' => '3',
                            'min' => '1',
                            'max' => '6',
                            'step' => '1',
                        ),
                        array(
                            'type' => 'range',
                            'heading' => esc_attr__('Products Per Page', 'product-filter-for-avada'),
                            'description' => esc_attr__('Number of products to show per page.', 'product-filter-for-avada'),
                            'param_name' => 'products_per_page',
                            'value' => '12',
                            'min' => '1',
                            'max' => '50',
                            'step' => '1',
                        ),
                        array(
                            'type' => 'select',
                            'heading' => esc_attr__('Order By', 'product-filter-for-avada'),
                            'description' => esc_attr__('Choose how to order the products.', 'product-filter-for-avada'),
                            'param_name' => 'orderby',
                            'value' => array(
                                'menu_order' => esc_attr__('Menu Order', 'product-filter-for-avada'),
                                'title' => esc_attr__('Title', 'product-filter-for-avada'),
                                'date' => esc_attr__('Date', 'product-filter-for-avada'),
                                'price' => esc_attr__('Price', 'product-filter-for-avada'),
                                'popularity' => esc_attr__('Popularity', 'product-filter-for-avada'),
                                'rating' => esc_attr__('Rating', 'product-filter-for-avada'),
                            ),
                            'default' => 'menu_order',
                        ),
                        array(
                            'type' => 'select',
                            'heading' => esc_attr__('Order Direction', 'product-filter-for-avada'),
                            'description' => esc_attr__('Choose the sort direction.', 'product-filter-for-avada'),
                            'param_name' => 'order',
                            'value' => array(
                                'ASC' => esc_attr__('Ascending', 'product-filter-for-avada'),
                                'DESC' => esc_attr__('Descending', 'product-filter-for-avada'),
                            ),
                            'default' => 'ASC',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_attr__('CSS Class', 'product-filter-for-avada'),
                            'description' => esc_attr__('Add a custom CSS class for styling.', 'product-filter-for-avada'),
                            'param_name' => 'class',
                            'value' => '',
                        ),
                        array(
                            'type' => 'textfield',
                            'heading' => esc_attr__('CSS ID', 'product-filter-for-avada'),
                            'description' => esc_attr__('Add a custom CSS ID for styling.', 'product-filter-for-avada'),
                            'param_name' => 'id',
                            'value' => '',
                        ),
                    ),
                ),
                'parent'
            )
        );
    }
}

/**
 * Map shortcode for Fusion Builder
 */
function avada_product_filter_fusion_element_map() {
    // This function is called by the Fusion Builder
    return array(
        'name' => esc_attr__('Product Filter', 'product-filter-for-avada'),
        'shortcode' => 'avada_product_filter',
        'icon' => 'fusiona-search',
        'preview' => AVADA_PRODUCT_FILTER_PLUGIN_PATH . 'includes/fusion-preview.php',
        'preview_id' => 'fusion-builder-block-module-avada-product-filter-preview-template',
        'params' => array(
            array(
                'type' => 'select',
                'heading' => esc_attr__('Show Categories Filter', 'product-filter-for-avada'),
                'description' => esc_attr__('Choose whether to display the category filter.', 'product-filter-for-avada'),
                'param_name' => 'show_categories',
                'value' => array(
                    'yes' => esc_attr__('Yes', 'product-filter-for-avada'),
                    'no' => esc_attr__('No', 'product-filter-for-avada'),
                ),
                'default' => 'yes',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_attr__('Specific Categories', 'product-filter-for-avada'),
                'description' => esc_attr__('Enter specific category IDs separated by commas. Leave empty to show all categories.', 'product-filter-for-avada'),
                'param_name' => 'categories',
                'value' => '',
            ),
            array(
                'type' => 'select',
                'heading' => esc_attr__('Show Attributes Filter', 'product-filter-for-avada'),
                'description' => esc_attr__('Choose whether to display attribute filters.', 'product-filter-for-avada'),
                'param_name' => 'show_attributes',
                'value' => array(
                    'yes' => esc_attr__('Yes', 'product-filter-for-avada'),
                    'no' => esc_attr__('No', 'product-filter-for-avada'),
                ),
                'default' => 'yes',
            ),
            array(
                'type' => 'textfield',
                'heading' => esc_attr__('Specific Attributes', 'product-filter-for-avada'),
                'description' => esc_attr__('Enter specific attribute names separated by commas. Leave empty to show all attributes.', 'product-filter-for-avada'),
                'param_name' => 'attributes',
                'value' => '',
            ),
            array(
                'type' => 'select',
                'heading' => esc_attr__('Show Price Filter', 'product-filter-for-avada'),
                'description' => esc_attr__('Choose whether to display the price range filter.', 'product-filter-for-avada'),
                'param_name' => 'show_price_filter',
                'value' => array(
                    'yes' => esc_attr__('Yes', 'product-filter-for-avada'),
                    'no' => esc_attr__('No', 'product-filter-for-avada'),
                ),
                'default' => 'yes',
            ),
        ),
    );
}