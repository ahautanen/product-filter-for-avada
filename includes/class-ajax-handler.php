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

        // Raw meta key inputs (from shortcode data-attributes)
        $raw_width_meta = isset($_POST['width_meta_key']) ? $_POST['width_meta_key'] : 'width';
        $raw_depth_meta = isset($_POST['depth_meta_key']) ? $_POST['depth_meta_key'] : 'depth';
        $raw_area_meta = isset($_POST['area_meta_key']) ? $_POST['area_meta_key'] : 'area';

        // Validate meta key names (allow letters, numbers, underscore, hyphen)
        $width_meta_key = preg_match('/^[A-Za-z0-9_\-]+$/', $raw_width_meta) ? sanitize_text_field($raw_width_meta) : 'width';
        $depth_meta_key = preg_match('/^[A-Za-z0-9_\-]+$/', $raw_depth_meta) ? sanitize_text_field($raw_depth_meta) : 'depth';
        $area_meta_key = preg_match('/^[A-Za-z0-9_\-]+$/', $raw_area_meta) ? sanitize_text_field($raw_area_meta) : 'area';

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

    // Width filter
        if (!empty($min_width) || !empty($max_width)) {
            $width_query = array('key' => $width_meta_key);
            if (!empty($min_width) && !empty($max_width)) {
                $width_query['value'] = array($min_width, $max_width);
                $width_query['type'] = 'DECIMAL';
                $width_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_width)) {
                $width_query['value'] = $min_width;
                $width_query['type'] = 'DECIMAL';
                $width_query['compare'] = '>=';
            } elseif (!empty($max_width)) {
                $width_query['value'] = $max_width;
                $width_query['type'] = 'DECIMAL';
                $width_query['compare'] = '<=';
            }
            $args['meta_query'][] = $width_query;
        }

        // Depth filter
        if (!empty($min_depth) || !empty($max_depth)) {
            $depth_query = array('key' => $depth_meta_key);
            if (!empty($min_depth) && !empty($max_depth)) {
                $depth_query['value'] = array($min_depth, $max_depth);
                $depth_query['type'] = 'DECIMAL';
                $depth_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_depth)) {
                $depth_query['value'] = $min_depth;
                $depth_query['type'] = 'DECIMAL';
                $depth_query['compare'] = '>=';
            } elseif (!empty($max_depth)) {
                $depth_query['value'] = $max_depth;
                $depth_query['type'] = 'DECIMAL';
                $depth_query['compare'] = '<=';
            }
            $args['meta_query'][] = $depth_query;
        }

        // Area filter
        if (!empty($min_area) || !empty($max_area)) {
            $area_query = array('key' => $area_meta_key);
            if (!empty($min_area) && !empty($max_area)) {
                $area_query['value'] = array($min_area, $max_area);
                $area_query['type'] = 'DECIMAL';
                $area_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_area)) {
                $area_query['value'] = $min_area;
                $area_query['type'] = 'DECIMAL';
                $area_query['compare'] = '>=';
            } elseif (!empty($max_area)) {
                $area_query['value'] = $max_area;
                $area_query['type'] = 'DECIMAL';
                $area_query['compare'] = '<=';
            }
            $args['meta_query'][] = $area_query;
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
        // Raw meta key inputs
        $raw_width_meta = isset($_POST['width_meta_key']) ? $_POST['width_meta_key'] : 'width';
        $raw_depth_meta = isset($_POST['depth_meta_key']) ? $_POST['depth_meta_key'] : 'depth';
        $raw_area_meta = isset($_POST['area_meta_key']) ? $_POST['area_meta_key'] : 'area';

        // Validate meta key names (allow letters, numbers, underscore, hyphen)
        $width_meta_key = preg_match('/^[A-Za-z0-9_\-]+$/', $raw_width_meta) ? sanitize_text_field($raw_width_meta) : 'width';
        $depth_meta_key = preg_match('/^[A-Za-z0-9_\-]+$/', $raw_depth_meta) ? sanitize_text_field($raw_depth_meta) : 'depth';
        $area_meta_key = preg_match('/^[A-Za-z0-9_\-]+$/', $raw_area_meta) ? sanitize_text_field($raw_area_meta) : 'area';

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

        // Width filter
        if (!empty($min_width) || !empty($max_width)) {
            $width_query = array('key' => $width_meta_key);
            if (!empty($min_width) && !empty($max_width)) {
                $width_query['value'] = array($min_width, $max_width);
                $width_query['type'] = 'DECIMAL';
                $width_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_width)) {
                $width_query['value'] = $min_width;
                $width_query['type'] = 'DECIMAL';
                $width_query['compare'] = '>=';
            } elseif (!empty($max_width)) {
                $width_query['value'] = $max_width;
                $width_query['type'] = 'DECIMAL';
                $width_query['compare'] = '<=';
            }
            $args['meta_query'][] = $width_query;
        }

        // Depth filter
        if (!empty($min_depth) || !empty($max_depth)) {
            $depth_query = array('key' => $depth_meta_key);
            if (!empty($min_depth) && !empty($max_depth)) {
                $depth_query['value'] = array($min_depth, $max_depth);
                $depth_query['type'] = 'DECIMAL';
                $depth_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_depth)) {
                $depth_query['value'] = $min_depth;
                $depth_query['type'] = 'DECIMAL';
                $depth_query['compare'] = '>=';
            } elseif (!empty($max_depth)) {
                $depth_query['value'] = $max_depth;
                $depth_query['type'] = 'DECIMAL';
                $depth_query['compare'] = '<=';
            }
            $args['meta_query'][] = $depth_query;
        }

        // Area filter
        if (!empty($min_area) || !empty($max_area)) {
            $area_query = array('key' => $area_meta_key);
            if (!empty($min_area) && !empty($max_area)) {
                $area_query['value'] = array($min_area, $max_area);
                $area_query['type'] = 'DECIMAL';
                $area_query['compare'] = 'BETWEEN';
            } elseif (!empty($min_area)) {
                $area_query['value'] = $min_area;
                $area_query['type'] = 'DECIMAL';
                $area_query['compare'] = '>=';
            } elseif (!empty($max_area)) {
                $area_query['value'] = $max_area;
                $area_query['type'] = 'DECIMAL';
                $area_query['compare'] = '<=';
            }
            $args['meta_query'][] = $area_query;
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