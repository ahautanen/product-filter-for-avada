<?php
/**
 * Shortcode functionality for Product Filter
 * 
 * EXAMPLE PRODUCT ELEMENT STRUCTURE:
 * <li class="product type-product post-3713 status-publish first instock product_cat-helppa-mallisto product_cat-muut-tuotteet product_tag-helppa product_tag-helppa-elementti product_tag-kukkalaatikko product_tag-muut-tuotteet has-post-thumbnail taxable shipping-taxable purchasable product-type-simple product-grid-view">
 *   <div class="fusion-product-wrapper">
 *     <div class="fusion-clean-product-image-wrapper">
 *       <div class="fusion-image-wrapper fusion-image-size-fixed">
 *         <img src="..." class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image">
 *         <div class="fusion-rollover">
 *           <div class="fusion-product-buttons">
 *             <a href="/?add-to-cart=3713" class="button product_type_simple add_to_cart_button ajax_add_to_cart">Lisää ostoskoriin</a>
 *             <a href="/tuote/helppa-kukkalaatikko/" class="show_details_button">Lisätiedot</a>
 *           </div>
 *         </div>
 *       </div>
 *     </div>
 *     <div class="fusion-product-content">
 *       <div class="product-details">
 *         <h3 class="product-title">
 *           <a href="/tuote/helppa-kukkalaatikko/">Helppå Kukkalaatikko</a>
 *         </h3>
 *         <div class="fusion-price-rating">
 *           <div class="custom-short-description">Product description...</div>
 *           <span class="price">445,00 € sis. alv 25,5%</span>
 *         </div>
 *       </div>
 *     </div>
 *   </div>
 *   <div class="custom-product-link">
 *     <a href="/tuote/helppa-kukkalaatikko/" class="button">Tutustu tuotteeseen</a>
 *   </div>
 * </li>
 * 
 * Key classes for filtering:
 * - product_cat-{category-slug} (categories)
 * - product_tag-{tag-slug} (tags/attributes)  
 * - Post ID: post-{id}
 * - Product type: product-type-{type}
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Avada_Product_Filter_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('avada_product_filter', array($this, 'render_shortcode'));
    }
    
    /**
     * Render the product filter shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'categories' => '',
            'attributes' => '',
            'show_categories' => 'no',
            'show_attributes' => 'yes',
            'show_price_filter' => 'yes',
            'show_dimension_filter' => 'yes',
            'columns' => '3',
            'products_per_page' => '12',
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ), $atts, 'avada_product_filter');
        
        // Start output buffering
        ob_start();
        
        // Render the filter
        $this->render_filter($atts);
        
        return ob_get_clean();
    }
    
    /**
     * Render the complete filter interface
     */
    private function render_filter($atts) {
        // Get current query parameters
        $current_category = isset($_GET['filter_category']) ? sanitize_text_field($_GET['filter_category']) : '';
        $current_attributes = isset($_GET['filter_attributes']) ? array_map('sanitize_text_field', (array)$_GET['filter_attributes']) : array();
        $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
        $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';
        
    echo '<div class="avada-product-filter-wrapper" data-columns="' . esc_attr($atts['columns']) . '" data-per-page="' . esc_attr($atts['products_per_page']) . '" data-orderby="' . esc_attr($atts['orderby']) . '" data-order="' . esc_attr($atts['order']) . '" data-show-dimensions="' . esc_attr($atts['show_dimension_filter']) . '" data-categories="' . esc_attr($atts['categories']) . '">';
        
        // Filter controls
        echo '<div class="avada-product-filter-controls">';
        
        // Category filter
        if ($atts['show_categories'] === 'yes') {
            $this->render_category_filter($atts['categories'], $current_category);
        }
        
        // Attribute filters
        if ($atts['show_attributes'] === 'yes') {
            $this->render_attribute_filters($atts['attributes'], $current_attributes);
        }
        
        // Price filter
        if ($atts['show_price_filter'] === 'yes') {
            $this->render_price_filter($min_price, $max_price);
        }

        // Dimension filter
        if (!empty($atts['show_dimension_filter']) && $atts['show_dimension_filter'] === 'yes') {
            // Use hardcoded WooCommerce attribute taxonomies
            $width_range = $this->get_attribute_range('pa_leveys-cm');
            $depth_range = $this->get_attribute_range('pa_syvyys-cm');
            $area_range = $this->get_attribute_range('pa_pinta-ala-m2');

            $min_vals = array('width' => $width_range['min'], 'depth' => $depth_range['min'], 'area' => $area_range['min']);
            $max_vals = array('width' => $width_range['max'], 'depth' => $depth_range['max'], 'area' => $area_range['max']);

            $this->render_dimension_filter($atts, $min_vals, $max_vals);
        }
        
        // Clear filters button
        echo '<div class="filter-actions">';
        echo '<button type="button" class="avada-filter-clear button">' . __('Clear Filters', 'product-filter-for-avada') . '</button>';
        echo '</div>';
        
        echo '</div>'; // .avada-product-filter-controls
        
        // Products container
        echo '<div class="avada-product-filter-products">';
        $this->render_products($atts, $current_category, $current_attributes, $min_price, $max_price);
        echo '</div>';
        
        echo '</div>'; // .avada-product-filter-wrapper
    }
    
    /**
     * Render category filter dropdown
     */
    private function render_category_filter($categories, $current_category) {
        $category_ids = array();
        
        if (!empty($categories)) {
            $category_ids = array_map('intval', explode(',', $categories));
        }
        
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        );
        
        if (!empty($category_ids)) {
            $args['include'] = $category_ids;
        }
        
        $product_categories = get_terms($args);
        
        if (!empty($product_categories) && !is_wp_error($product_categories)) {
            echo '<div class="filter-group filter-categories">';
            echo '<label for="filter_category">' . __('Category', 'product-filter-for-avada') . '</label>';
            echo '<select name="filter_category" id="filter_category" class="avada-filter-select">';
            echo '<option value="">' . __('All Categories', 'product-filter-for-avada') . '</option>';
            
            foreach ($product_categories as $category) {
                $selected = ($current_category == $category->slug) ? 'selected' : '';
                echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>';
                echo esc_html($category->name) . ' (' . $category->count . ')';
                echo '</option>';
            }
            
            echo '</select>';
            echo '</div>';
        }
    }
    
    /**
     * Render attribute filters
     */
    private function render_attribute_filters($attributes, $current_attributes) {
        $attribute_names = array();
        
        if (!empty($attributes)) {
            $attribute_names = array_map('trim', explode(',', $attributes));
        } else {
            // Get all product attributes
            $attribute_taxonomies = wc_get_attribute_taxonomies();
            foreach ($attribute_taxonomies as $attribute) {
                $attribute_names[] = $attribute->attribute_name;
            }
        }
        
        foreach ($attribute_names as $attribute_name) {
            $taxonomy = 'pa_' . $attribute_name;
            
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }
            
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
                'orderby' => 'name',
                'order' => 'ASC'
            ));
            
            if (!empty($terms) && !is_wp_error($terms)) {
                echo '<div class="filter-group filter-attribute" data-attribute="' . esc_attr($attribute_name) . '">';
                echo '<label>' . esc_html(wc_attribute_label($taxonomy)) . '</label>';
                echo '<div class="attribute-options">';
                
                foreach ($terms as $term) {
                    $checked = in_array($term->slug, $current_attributes) ? 'checked' : '';
                    echo '<label class="attribute-option">';
                    echo '<input type="checkbox" name="filter_attributes[]" value="' . esc_attr($term->slug) . '" ' . $checked . '>';
                    echo '<span>' . esc_html($term->name) . ' (' . $term->count . ')</span>';
                    echo '</label>';
                }
                
                echo '</div>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Render price filter
     */
    private function render_price_filter($min_price, $max_price) {
        // Get price range
        $prices = $this->get_price_range();
        
        echo '<div class="filter-group filter-price">';
        echo '<label>' . __('Price Range', 'product-filter-for-avada') . '</label>';
        echo '<div class="price-range-inputs">';
        echo '<input type="number" name="min_price" placeholder="' . __('Min', 'product-filter-for-avada') . '" value="' . esc_attr($min_price) . '" min="' . esc_attr($prices['min']) . '" max="' . esc_attr($prices['max']) . '" step="0.01">';
        echo '<span class="price-separator">-</span>';
        echo '<input type="number" name="max_price" placeholder="' . __('Max', 'product-filter-for-avada') . '" value="' . esc_attr($max_price) . '" min="' . esc_attr($prices['min']) . '" max="' . esc_attr($prices['max']) . '" step="0.01">';
        echo '</div>';
        echo '<div class="price-range-info">';
        echo '<span class="price-min">' . wc_price($prices['min']) . '</span>';
        echo '<span class="price-max">' . wc_price($prices['max']) . '</span>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Render dimension filter (width, depth, area)
     */
    private function render_dimension_filter($atts, $min_vals, $max_vals) {
        echo '<div class="filter-group filter-dimensions">';
        echo '<label>' . __('Dimensions', 'product-filter-for-avada') . '</label>';
        
        // Width filter with checkbox
        echo '<div class="dimension-filter-row">';
        echo '<label class="dimension-checkbox">';
        echo '<input type="checkbox" name="enable_width_filter" value="1" class="dimension-toggle">';
        echo '<strong>' . __('Leveys (cm)', 'product-filter-for-avada') . '</strong>';
        echo '</label>';
        echo '<div class="price-range-inputs dimension-inputs" style="display: none;">';
        echo '<input type="number" name="min_width" placeholder="' . __('Min', 'product-filter-for-avada') . '" value="" min="' . esc_attr($min_vals['width']) . '" max="' . esc_attr($max_vals['width']) . '" step="0.01">';
        echo '<span class="price-separator">-</span>';
        echo '<input type="number" name="max_width" placeholder="' . __('Max', 'product-filter-for-avada') . '" value="" min="' . esc_attr($min_vals['width']) . '" max="' . esc_attr($max_vals['width']) . '" step="0.01">';
        echo '<span class="dimension-range">(' . number_format($min_vals['width'], 1) . ' - ' . number_format($max_vals['width'], 1) . ' cm)</span>';
        echo '</div>';
        echo '</div>';

        // Depth filter with checkbox
        echo '<div class="dimension-filter-row">';
        echo '<label class="dimension-checkbox">';
        echo '<input type="checkbox" name="enable_depth_filter" value="1" class="dimension-toggle">';
        echo '<strong>' . __('Syvyys (cm)', 'product-filter-for-avada') . '</strong>';
        echo '</label>';
        echo '<div class="price-range-inputs dimension-inputs" style="display: none;">';
        echo '<input type="number" name="min_depth" placeholder="' . __('Min', 'product-filter-for-avada') . '" value="" min="' . esc_attr($min_vals['depth']) . '" max="' . esc_attr($max_vals['depth']) . '" step="0.01">';
        echo '<span class="price-separator">-</span>';
        echo '<input type="number" name="max_depth" placeholder="' . __('Max', 'product-filter-for-avada') . '" value="" min="' . esc_attr($min_vals['depth']) . '" max="' . esc_attr($max_vals['depth']) . '" step="0.01">';
        echo '<span class="dimension-range">(' . number_format($min_vals['depth'], 1) . ' - ' . number_format($max_vals['depth'], 1) . ' cm)</span>';
        echo '</div>';
        echo '</div>';

        // Area filter with checkbox
        echo '<div class="dimension-filter-row">';
        echo '<label class="dimension-checkbox">';
        echo '<input type="checkbox" name="enable_area_filter" value="1" class="dimension-toggle">';
        echo '<strong>' . __('Pinta-ala (m²)', 'product-filter-for-avada') . '</strong>';
        echo '</label>';
        echo '<div class="price-range-inputs dimension-inputs" style="display: none;">';
        echo '<input type="number" name="min_area" placeholder="' . __('Min', 'product-filter-for-avada') . '" value="" min="' . esc_attr($min_vals['area']) . '" max="' . esc_attr($max_vals['area']) . '" step="0.01">';
        echo '<span class="price-separator">-</span>';
        echo '<input type="number" name="max_area" placeholder="' . __('Max', 'product-filter-for-avada') . '" value="" min="' . esc_attr($min_vals['area']) . '" max="' . esc_attr($max_vals['area']) . '" step="0.01">';
        echo '<span class="dimension-range">(' . number_format($min_vals['area'], 1) . ' - ' . number_format($max_vals['area'], 1) . ' m²)</span>';
        echo '</div>';
        echo '</div>';

        echo '</div>';
    }

    /**
     * Get min/max for a WooCommerce attribute taxonomy (dimension)
     */
    private function get_attribute_range($taxonomy) {
        // Get all terms from the attribute taxonomy
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'fields' => 'names'
        ));
        
        if (empty($terms) || is_wp_error($terms)) {
            return array('min' => 0, 'max' => 100);
        }
        
        // Convert term names to numeric values and find min/max
        $numeric_values = array();
        foreach ($terms as $term_name) {
            // Convert to float, handle both comma and dot decimals
            $numeric_value = floatval(str_replace(',', '.', $term_name));
            if ($numeric_value > 0) {
                $numeric_values[] = $numeric_value;
            }
        }
        
        if (empty($numeric_values)) {
            return array('min' => 0, 'max' => 100);
        }
        
        return array(
            'min' => min($numeric_values),
            'max' => max($numeric_values)
        );
    }
    
    /**
     * Get product price range
     */
    private function get_price_range() {
        global $wpdb;
        
        $sql = "
            SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price, 
                   MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_price' 
            AND meta_value != ''
        ";
        
        $results = $wpdb->get_row($sql);
        
        return array(
            'min' => $results->min_price ? floatval($results->min_price) : 0,
            'max' => $results->max_price ? floatval($results->max_price) : 1000
        );
    }
    
    /**
     * Render products based on filters
     */
    private function render_products($atts, $current_category, $current_attributes, $min_price, $max_price) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['products_per_page']),
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        // Shortcode category restriction (if specified)
        if (!empty($atts['categories'])) {
            $category_ids = array_map('intval', explode(',', $atts['categories']));
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_ids
            );
        }
        
        // Current category filter (from user selection)
        if (!empty($current_category)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $current_category
            );
        }
        
        // Attribute filters
        if (!empty($current_attributes)) {
            foreach ($current_attributes as $attribute_value) {
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
        
        $products = new WP_Query($args);
        
        if ($products->have_posts()) {
            echo '<ul class="products products-' . esc_attr($atts['columns']) . ' woocommerce columns-' . esc_attr($atts['columns']) . ' fusion-woo-product-grid fusion-columns-' . esc_attr($atts['columns']) . ' fusion-columns-total-' . esc_attr($atts['columns']) . '">';
            
            while ($products->have_posts()) {
                $products->the_post();
                wc_get_template_part('content', 'product');
            }
            
            echo '</ul>';
            
            // Pagination
            if ($products->max_num_pages > 1) {
                echo '<div class="avada-filter-pagination">';
                echo paginate_links(array(
                    'total' => $products->max_num_pages,
                    'current' => max(1, get_query_var('paged')),
                    'prev_text' => __('&laquo; Previous', 'product-filter-for-avada'),
                    'next_text' => __('Next &raquo;', 'product-filter-for-avada')
                ));
                echo '</div>';
            }
        } else {
            echo '<div class="no-products-found">';
            echo '<p>' . __('No products found matching your criteria.', 'product-filter-for-avada') . '</p>';
            echo '</div>';
        }
        
        wp_reset_postdata();
    }
}