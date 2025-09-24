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
        $columns = isset($_POST['columns']) ? intval($_POST['columns']) : 3;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
        $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';
        $order = isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC';
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        
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
            echo '<div class="products woocommerce columns-' . esc_attr($columns) . '">';
            
            while ($products->have_posts()) {
                $products->the_post();
                wc_get_template_part('content', 'product');
            }
            
            echo '</div>';
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
        
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        $products = new WP_Query($args);
        
        wp_send_json(array(
            'success' => true,
            'count' => $products->found_posts
        ));
    }
}