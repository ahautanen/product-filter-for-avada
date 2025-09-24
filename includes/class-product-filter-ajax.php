<?php
/**
 * Product Filter AJAX Class
 *
 * @package Product_Filter_Avada
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product_Filter_Ajax Class.
 */
class Product_Filter_Ajax {

    /**
     * Constructor.
     */
    public function __construct() {
        // AJAX hooks for logged in and guest users
        add_action('wp_ajax_filter_products', array($this, 'filter_products'));
        add_action('wp_ajax_nopriv_filter_products', array($this, 'filter_products'));
        
        add_action('wp_ajax_get_filter_counts', array($this, 'get_filter_counts'));
        add_action('wp_ajax_nopriv_get_filter_counts', array($this, 'get_filter_counts'));
    }

    /**
     * Handle AJAX product filtering.
     */
    public function filter_products() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'product_filter_nonce')) {
            wp_die(__('Security check failed', 'product-filter-avada'));
        }

        // Get filter parameters
        $filter_args = array(
            'categories' => isset($_POST['categories']) ? sanitize_text_field($_POST['categories']) : '',
            'attributes' => isset($_POST['attributes']) ? $this->sanitize_attributes($_POST['attributes']) : array(),
            'min_price' => isset($_POST['min_price']) ? sanitize_text_field($_POST['min_price']) : '',
            'max_price' => isset($_POST['max_price']) ? sanitize_text_field($_POST['max_price']) : '',
            'posts_per_page' => isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 12,
            'paged' => isset($_POST['paged']) ? intval($_POST['paged']) : 1,
        );

        // Get filtered products
        $products_query = Product_Filter_Shortcode::get_filtered_products($filter_args);

        // Prepare response
        $response = array(
            'success' => true,
            'data' => array(
                'products' => $this->render_products($products_query),
                'pagination' => $this->render_pagination($products_query),
                'found_posts' => $products_query->found_posts,
                'max_pages' => $products_query->max_num_pages,
            ),
        );

        wp_send_json($response);
    }

    /**
     * Handle AJAX filter counts update.
     */
    public function get_filter_counts() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'product_filter_nonce')) {
            wp_die(__('Security check failed', 'product-filter-avada'));
        }

        // Get current filter parameters
        $current_filters = array(
            'categories' => isset($_POST['categories']) ? sanitize_text_field($_POST['categories']) : '',
            'attributes' => isset($_POST['attributes']) ? $this->sanitize_attributes($_POST['attributes']) : array(),
            'min_price' => isset($_POST['min_price']) ? sanitize_text_field($_POST['min_price']) : '',
            'max_price' => isset($_POST['max_price']) ? sanitize_text_field($_POST['max_price']) : '',
        );

        // Get updated counts
        $counts = $this->calculate_filter_counts($current_filters);

        wp_send_json_success($counts);
    }

    /**
     * Sanitize attributes array.
     *
     * @param array $attributes Raw attributes data.
     * @return array
     */
    private function sanitize_attributes($attributes) {
        $sanitized = array();
        
        if (is_array($attributes)) {
            foreach ($attributes as $taxonomy => $terms) {
                $taxonomy = sanitize_key($taxonomy);
                if (is_array($terms)) {
                    $sanitized[$taxonomy] = array_map('sanitize_text_field', $terms);
                }
            }
        }

        return $sanitized;
    }

    /**
     * Render products HTML.
     *
     * @param WP_Query $products_query Products query object.
     * @return string
     */
    private function render_products($products_query) {
        ob_start();

        if ($products_query->have_posts()) {
            echo '<div class="products-grid">';
            
            while ($products_query->have_posts()) {
                $products_query->the_post();
                global $product;
                
                echo '<div class="product-item">';
                echo '<div class="product-image">';
                echo '<a href="' . get_permalink() . '">';
                echo get_the_post_thumbnail(get_the_ID(), 'woocommerce_thumbnail');
                echo '</a>';
                echo '</div>';
                
                echo '<div class="product-info">';
                echo '<h3 class="product-title">';
                echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                echo '</h3>';
                
                echo '<div class="product-price">';
                echo $product->get_price_html();
                echo '</div>';
                
                echo '<div class="product-actions">';
                woocommerce_template_loop_add_to_cart();
                echo '</div>';
                
                echo '</div>'; // product-info
                echo '</div>'; // product-item
            }
            
            echo '</div>'; // products-grid
            
            wp_reset_postdata();
        } else {
            echo '<div class="no-products-found">';
            echo '<p>' . __('No products found matching your criteria.', 'product-filter-avada') . '</p>';
            echo '</div>';
        }

        return ob_get_clean();
    }

    /**
     * Render pagination HTML.
     *
     * @param WP_Query $products_query Products query object.
     * @return string
     */
    private function render_pagination($products_query) {
        if ($products_query->max_num_pages <= 1) {
            return '';
        }

        ob_start();

        echo '<div class="product-filter-pagination">';
        
        $current_page = max(1, $products_query->get('paged'));
        $max_pages = $products_query->max_num_pages;

        // Previous button
        if ($current_page > 1) {
            echo '<button class="pagination-btn prev-page" data-page="' . ($current_page - 1) . '">';
            echo __('Previous', 'product-filter-avada');
            echo '</button>';
        }

        // Page numbers
        for ($i = 1; $i <= $max_pages; $i++) {
            $class = ($i == $current_page) ? 'pagination-btn current-page' : 'pagination-btn page-number';
            echo '<button class="' . $class . '" data-page="' . $i . '">' . $i . '</button>';
        }

        // Next button
        if ($current_page < $max_pages) {
            echo '<button class="pagination-btn next-page" data-page="' . ($current_page + 1) . '">';
            echo __('Next', 'product-filter-avada');
            echo '</button>';
        }

        echo '</div>';

        return ob_get_clean();
    }

    /**
     * Calculate filter counts based on current filters.
     *
     * @param array $current_filters Current active filters.
     * @return array
     */
    private function calculate_filter_counts($current_filters) {
        $counts = array(
            'categories' => array(),
            'attributes' => array(),
        );

        // Calculate category counts
        $categories = Product_Filter_Shortcode::get_product_categories();
        foreach ($categories as $category) {
            // Create a temporary filter excluding this category
            $temp_filters = $current_filters;
            $temp_filters['categories'] = '';
            
            // Get products with this filter
            $query = Product_Filter_Shortcode::get_filtered_products($temp_filters);
            $counts['categories'][$category['slug']] = $query->found_posts;
        }

        // Calculate attribute counts
        $attributes = Product_Filter_Shortcode::get_product_attributes();
        foreach ($attributes as $attribute) {
            $counts['attributes'][$attribute['taxonomy']] = array();
            
            foreach ($attribute['terms'] as $term) {
                // Create a temporary filter excluding this attribute term
                $temp_filters = $current_filters;
                if (isset($temp_filters['attributes'][$attribute['taxonomy']])) {
                    $temp_filters['attributes'][$attribute['taxonomy']] = array_diff(
                        $temp_filters['attributes'][$attribute['taxonomy']],
                        array($term['slug'])
                    );
                }
                
                // Get products with this filter
                $query = Product_Filter_Shortcode::get_filtered_products($temp_filters);
                $counts['attributes'][$attribute['taxonomy']][$term['slug']] = $query->found_posts;
            }
        }

        return $counts;
    }
}