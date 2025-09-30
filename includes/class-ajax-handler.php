<?php
/**
 * AJAX Handler for Product Filter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Avada_Product_Filter_Ajax_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_avada_filter_products', array($this, 'filter_products'));
        add_action('wp_ajax_nopriv_avada_filter_products', array($this, 'filter_products'));
    }
    
    /**
     * Handle AJAX product filtering
     */
    public function filter_products() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'avada_product_filter_nonce')) {
            wp_die(__('Security check failed', 'product-filter-for-avada'));
        }
        
        // Get filter parameters
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $attributes = isset($_POST['attributes']) ? array_map('sanitize_text_field', $_POST['attributes']) : array();
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : '';
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : '';
    // Dimension filters
    $min_width = isset($_POST['min_width']) ? floatval($_POST['min_width']) : '';
    $max_width = isset($_POST['max_width']) ? floatval($_POST['max_width']) : '';
    $min_depth = isset($_POST['min_depth']) ? floatval($_POST['min_depth']) : '';
    $max_depth = isset($_POST['max_depth']) ? floatval($_POST['max_depth']) : '';
    $min_area = isset($_POST['min_area']) ? floatval($_POST['min_area']) : '';
    $max_area = isset($_POST['max_area']) ? floatval($_POST['max_area']) : '';
        $columns = isset($_POST['columns']) ? intval($_POST['columns']) : 3;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC';
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;

        // Use hardcoded WooCommerce attribute taxonomies for dimensions
        $width_taxonomy = 'pa_leveys-cm';
        $depth_taxonomy = 'pa_syvyys-cm';
        $area_taxonomy = 'pa_pinta-ala-m2';

        // Sanity: ensure min <= max for dimensions (swap if needed)
        if ($min_width !== '' && $max_width !== '' && $min_width > $max_width) {
            $tmp = $min_width; $min_width = $max_width; $max_width = $tmp;
        }
        if ($min_depth !== '' && $max_depth !== '' && $min_depth > $max_depth) {
            $tmp = $min_depth; $min_depth = $max_depth; $max_depth = $tmp;
        }
        if ($min_area !== '' && $max_area !== '' && $min_area > $max_area) {
            $tmp = $min_area; $min_area = $max_area; $max_area = $tmp;
        }
        
        // Build query arguments
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'orderby' => $orderby,
            'order' => $order,
            'paged' => $paged,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        // Category filter
        if (!empty($category)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category
            );
        }
        
        // Attribute filters
        if (!empty($attributes)) {
            foreach ($attributes as $attribute_value) {
                // Find which attribute this value belongs to
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                foreach ($attribute_taxonomies as $attribute) {
                    $taxonomy = 'pa_' . $attribute->attribute_name;
                    if (term_exists($attribute_value, $taxonomy)) {
                        $args['tax_query'][] = array(
                            'taxonomy' => $taxonomy,
                            'field' => 'slug',
                            'terms' => $attribute_value
                        );
                        break;
                    }
                }
            }
        }
        
        // Price filter
        if (!empty($min_price) || !empty($max_price)) {
            $price_query = array('key' => '_price');
            
            if (!empty($min_price) && !empty($max_price)) {
                $price_query['value'] = array($min_price, $max_price);
                $price_query['type'] = 'DECIMAL';
                $price_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_price)) {
                $price_query['value'] = $min_price;
                $price_query['type'] = 'DECIMAL';
                $price_query['compare'] = '>=';
            } elseif (!empty($max_price)) {
                $price_query['value'] = $max_price;
                $price_query['type'] = 'DECIMAL';
                $price_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $price_query;
        }

        // Set tax_query relation
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        // Execute query
        $products = new WP_Query($args);
        
        $response = array(
            'success' => true,
            'products' => '',
            'pagination' => '',
            'found_posts' => $products->found_posts
        );
        
        if ($products->have_posts()) {
            ob_start();
            echo '<ul class="products products-' . esc_attr($columns) . ' woocommerce columns-' . esc_attr($columns) . ' fusion-woo-product-grid fusion-columns-' . esc_attr($columns) . ' fusion-columns-total-' . esc_attr($columns) . '">';
            
            while ($products->have_posts()) {
                $products->the_post();
                wc_get_template_part('content', 'product');
            }
            
            echo '</ul>';
            $response['products'] = ob_get_clean();
            
            // Generate pagination
            if ($products->max_num_pages > 1) {
                ob_start();
                echo '<div class="avada-filter-pagination">';
                
                $pagination_args = array(
                    'total' => $products->max_num_pages,
                    'current' => $paged,
                    'prev_text' => __('&laquo; Previous', 'product-filter-for-avada'),
                    'next_text' => __('Next &raquo;', 'product-filter-for-avada'),
                    'type' => 'array'
                );
                
                $pagination_links = paginate_links($pagination_args);
                
                if ($pagination_links) {
                    echo '<nav class="woocommerce-pagination">';
                    echo '<ul class="page-numbers">';
                    foreach ($pagination_links as $link) {
                        echo '<li>' . $link . '</li>';
                    }
                    echo '</ul>';
                    echo '</nav>';
                }
                
                echo '</div>';
                $response['pagination'] = ob_get_clean();
            }
        } else {
            $response['products'] = '<div class="no-products-found"><p>' . __('No products found matching your criteria.', 'product-filter-for-avada') . '</p></div>';
        }
        
        wp_reset_postdata();
        
        wp_send_json($response);
    }
    
    /**
     * Get filtered products count for AJAX updates
     */
    public function get_products_count() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'avada_product_filter_nonce')) {
            wp_die(__('Security check failed', 'product-filter-for-avada'));
        }
        
        // Get filter parameters
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
        $attributes = isset($_POST['attributes']) ? array_map('sanitize_text_field', $_POST['attributes']) : array();
        $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : '';
        $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : '';
        // Dimension filters
        $min_width = isset($_POST['min_width']) ? floatval($_POST['min_width']) : '';
        $max_width = isset($_POST['max_width']) ? floatval($_POST['max_width']) : '';
        $min_depth = isset($_POST['min_depth']) ? floatval($_POST['min_depth']) : '';
        $max_depth = isset($_POST['max_depth']) ? floatval($_POST['max_depth']) : '';
        $min_area = isset($_POST['min_area']) ? floatval($_POST['min_area']) : '';
        $max_area = isset($_POST['max_area']) ? floatval($_POST['max_area']) : '';
        // Use hardcoded WooCommerce attribute taxonomies for dimensions
        $width_taxonomy = 'pa_leveys-cm';
        $depth_taxonomy = 'pa_syvyys-cm';
        $area_taxonomy = 'pa_pinta-ala-m2';

        // Sanity: ensure min <= max for dimensions
        if ($min_width !== '' && $max_width !== '' && $min_width > $max_width) {
            $tmp = $min_width; $min_width = $max_width; $max_width = $tmp;
        }
        if ($min_depth !== '' && $max_depth !== '' && $min_depth > $max_depth) {
            $tmp = $min_depth; $min_depth = $max_depth; $max_depth = $tmp;
        }
        if ($min_area !== '' && $max_area !== '' && $min_area > $max_area) {
            $tmp = $min_area; $min_area = $max_area; $max_area = $tmp;
        }
        
        // Build query arguments for counting
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        // Apply same filters as main query
        if (!empty($category)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category
            );
        }
        
        if (!empty($attributes)) {
            foreach ($attributes as $attribute_value) {
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                foreach ($attribute_taxonomies as $attribute) {
                    $taxonomy = 'pa_' . $attribute->attribute_name;
                    if (term_exists($attribute_value, $taxonomy)) {
                        $args['tax_query'][] = array(
                            'taxonomy' => $taxonomy,
                            'field' => 'slug',
                            'terms' => $attribute_value
                        );
                        break;
                    }
                }
            }
        }
        
        if (!empty($min_price) || !empty($max_price)) {
            $price_query = array('key' => '_price');
            
            if (!empty($min_price) && !empty($max_price)) {
                $price_query['value'] = array($min_price, $max_price);
                $price_query['type'] = 'DECIMAL';
                $price_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_price)) {
                $price_query['value'] = $min_price;
                $price_query['type'] = 'DECIMAL';
                $price_query['compare'] = '>=';
            } elseif (!empty($max_price)) {
                $price_query['value'] = $max_price;
                $price_query['type'] = 'DECIMAL';
                $price_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $price_query;
        }

        // Width filter (WC attribute taxonomy)
        if (!empty($min_width) || !empty($max_width)) {
            $width_terms = $this->get_attribute_terms_in_range($width_taxonomy, $min_width, $max_width);
            if (!empty($width_terms)) {
                $args['tax_query'][] = array(
                    'taxonomy' => $width_taxonomy,
                    'field' => 'slug',
                    'terms' => $width_terms
                );
            }
        }

        // Depth filter (WC attribute taxonomy)
        if (!empty($min_depth) || !empty($max_depth)) {
            $depth_terms = $this->get_attribute_terms_in_range($depth_taxonomy, $min_depth, $max_depth);
            if (!empty($depth_terms)) {
                $args['tax_query'][] = array(
                    'taxonomy' => $depth_taxonomy,
                    'field' => 'slug',
                    'terms' => $depth_terms
                );
            }
        }

        // Area filter (WC attribute taxonomy)
        if (!empty($min_area) || !empty($max_area)) {
            $area_terms = $this->get_attribute_terms_in_range($area_taxonomy, $min_area, $max_area);
            if (!empty($area_terms)) {
                $args['tax_query'][] = array(
                    'taxonomy' => $area_taxonomy,
                    'field' => 'slug',
                    'terms' => $area_terms
                );
            }
        }
        
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        $products = new WP_Query($args);
        
        wp_send_json(array(
            'success' => true,
            'count' => $products->found_posts
        ));
    }
    
    /**
     * Get attribute terms (slugs) that fall within the specified numeric range
     */
    private function get_attribute_terms_in_range($taxonomy, $min_val, $max_val) {
        // Get all terms from the attribute taxonomy
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true
        ));
        
        if (empty($terms) || is_wp_error($terms)) {
            return array();
        }
        
        $matching_slugs = array();
        
        foreach ($terms as $term) {
            // Convert term name to numeric value
            $numeric_value = floatval(str_replace(',', '.', $term->name));
            
            // Check if the value falls within the specified range
            $in_range = true;
            
            if (!empty($min_val) && $numeric_value < $min_val) {
                $in_range = false;
            }
            
            if (!empty($max_val) && $numeric_value > $max_val) {
                $in_range = false;
            }
            
            if ($in_range && $numeric_value > 0) {
                $matching_slugs[] = $term->slug;
            }
        }
        
        return $matching_slugs;
    }
}