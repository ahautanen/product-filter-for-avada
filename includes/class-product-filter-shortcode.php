<?php
/**
 * Product Filter Shortcode Class
 *
 * @package Product_Filter_Avada
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product_Filter_Shortcode Class.
 */
class Product_Filter_Shortcode {

    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode('product_filter_avada', array($this, 'render_shortcode'));
    }

    /**
     * Render the product filter shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_shortcode($atts) {
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'categories' => '',
            'attributes' => '',
            'layout' => 'sidebar',
            'show_count' => 'yes',
            'ajax' => 'yes',
            'products_per_page' => 12,
        ), $atts, 'product_filter_avada');

        // Start output buffering
        ob_start();

        // Include the filter template
        $this->load_template('product-filter', $atts);

        return ob_get_clean();
    }

    /**
     * Load template file.
     *
     * @param string $template_name Template name.
     * @param array $args Template arguments.
     */
    private function load_template($template_name, $args = array()) {
        $template_path = PRODUCT_FILTER_AVADA_PLUGIN_DIR . 'templates/' . $template_name . '.php';
        
        if (file_exists($template_path)) {
            // Extract arguments to variables
            extract($args);
            include $template_path;
        } else {
            echo '<p>' . __('Template not found:', 'product-filter-avada') . ' ' . $template_name . '</p>';
        }
    }

    /**
     * Get product categories for filter.
     *
     * @param string $selected_categories Comma-separated category slugs.
     * @return array
     */
    public static function get_product_categories($selected_categories = '') {
        $categories = array();
        
        // Get all product categories
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
        ));

        if (!is_wp_error($terms) && !empty($terms)) {
            // Filter categories if specific ones are selected
            if (!empty($selected_categories)) {
                $selected_slugs = array_map('trim', explode(',', $selected_categories));
                $terms = array_filter($terms, function($term) use ($selected_slugs) {
                    return in_array($term->slug, $selected_slugs);
                });
            }

            foreach ($terms as $term) {
                $categories[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'count' => $term->count,
                );
            }
        }

        return $categories;
    }

    /**
     * Get product attributes for filter.
     *
     * @param string $selected_attributes Comma-separated attribute names.
     * @return array
     */
    public static function get_product_attributes($selected_attributes = '') {
        $attributes = array();
        
        // Get all product attributes
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        if (!empty($attribute_taxonomies)) {
            // Filter attributes if specific ones are selected
            if (!empty($selected_attributes)) {
                $selected_names = array_map('trim', explode(',', $selected_attributes));
                $attribute_taxonomies = array_filter($attribute_taxonomies, function($attr) use ($selected_names) {
                    return in_array($attr->attribute_name, $selected_names);
                });
            }

            foreach ($attribute_taxonomies as $attribute) {
                $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
                
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => true,
                ));

                if (!is_wp_error($terms) && !empty($terms)) {
                    $attribute_terms = array();
                    foreach ($terms as $term) {
                        $attribute_terms[] = array(
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug,
                            'count' => $term->count,
                        );
                    }

                    $attributes[] = array(
                        'name' => $attribute->attribute_name,
                        'label' => $attribute->attribute_label,
                        'taxonomy' => $taxonomy,
                        'terms' => $attribute_terms,
                    );
                }
            }
        }

        return $attributes;
    }

    /**
     * Get filtered products based on current filters.
     *
     * @param array $filter_args Filter arguments.
     * @return WP_Query
     */
    public static function get_filtered_products($filter_args = array()) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => isset($filter_args['posts_per_page']) ? intval($filter_args['posts_per_page']) : 12,
            'paged' => isset($filter_args['paged']) ? intval($filter_args['paged']) : 1,
            'meta_query' => array(),
            'tax_query' => array(),
        );

        // Add WooCommerce specific queries
        $args['meta_query'][] = array(
            'key' => '_visibility',
            'value' => array('catalog', 'visible'),
            'compare' => 'IN',
        );

        // Filter by categories
        if (!empty($filter_args['categories'])) {
            $categories = is_array($filter_args['categories']) ? $filter_args['categories'] : explode(',', $filter_args['categories']);
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $categories,
                'operator' => 'IN',
            );
        }

        // Filter by attributes
        if (!empty($filter_args['attributes'])) {
            foreach ($filter_args['attributes'] as $taxonomy => $terms) {
                if (!empty($terms)) {
                    $args['tax_query'][] = array(
                        'taxonomy' => $taxonomy,
                        'field' => 'slug',
                        'terms' => $terms,
                        'operator' => 'IN',
                    );
                }
            }
        }

        // Filter by price range
        if (!empty($filter_args['min_price']) || !empty($filter_args['max_price'])) {
            $price_meta_query = array(
                'key' => '_price',
                'type' => 'NUMERIC',
            );

            if (!empty($filter_args['min_price'])) {
                $price_meta_query['value'] = array(floatval($filter_args['min_price']));
                $price_meta_query['compare'] = '>=';
            }

            if (!empty($filter_args['max_price'])) {
                if (!empty($filter_args['min_price'])) {
                    $price_meta_query['value'][] = floatval($filter_args['max_price']);
                    $price_meta_query['compare'] = 'BETWEEN';
                } else {
                    $price_meta_query['value'] = floatval($filter_args['max_price']);
                    $price_meta_query['compare'] = '<=';
                }
            }

            $args['meta_query'][] = $price_meta_query;
        }

        // Set tax_query relation if multiple taxonomies
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }

        // Set meta_query relation if multiple meta queries
        if (count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }

        return new WP_Query($args);
    }
}