<?php
/**
 * Product Filter Template
 *
 * @package Product_Filter_Avada
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin instance for options
$plugin = product_filter_avada();

// Get filter data
$categories = Product_Filter_Shortcode::get_product_categories($categories);
$attributes_data = Product_Filter_Shortcode::get_product_attributes($attributes);

// Set default values
$layout = !empty($layout) ? $layout : $plugin->get_option('filter_layout', 'sidebar');
$show_count = !empty($show_count) ? $show_count : $plugin->get_option('show_product_count', 'yes');
$ajax = !empty($ajax) ? $ajax : $plugin->get_option('ajax_filtering', 'yes');
$products_per_page = !empty($products_per_page) ? intval($products_per_page) : intval($plugin->get_option('products_per_page', 12));

// Generate unique ID for this filter instance
$filter_id = 'product-filter-' . uniqid();
if (!empty($wrapper_id)) {
    $filter_id = $wrapper_id;
}

// Add custom class if provided
$custom_class = !empty($class) ? ' ' . esc_attr($class) : '';
?>

<div id="<?php echo esc_attr($filter_id); ?>" class="product-filter-wrapper layout-<?php echo esc_attr($layout); ?><?php echo $custom_class; ?>" data-ajax="<?php echo esc_attr($ajax); ?>" data-per-page="<?php echo esc_attr($products_per_page); ?>">
    
    <!-- Filter Section -->
    <div class="product-filter-section">
        <div class="filter-header">
            <h3 class="filter-title"><?php _e('Filter Products', 'product-filter-avada'); ?></h3>
            <?php if ($layout === 'modal'): ?>
                <button class="filter-toggle-btn" type="button">
                    <?php _e('Show Filters', 'product-filter-avada'); ?>
                </button>
            <?php endif; ?>
        </div>

        <div class="filter-content <?php echo $layout === 'modal' ? 'filter-modal' : ''; ?>">
            <?php if ($layout === 'modal'): ?>
                <div class="filter-modal-overlay"></div>
                <div class="filter-modal-content">
                    <div class="filter-modal-header">
                        <h3><?php _e('Filter Products', 'product-filter-avada'); ?></h3>
                        <button class="filter-modal-close">&times;</button>
                    </div>
                    <div class="filter-modal-body">
            <?php endif; ?>

            <form class="product-filter-form">
                <!-- Active Filters Display -->
                <div class="active-filters" style="display: none;">
                    <div class="active-filters-header">
                        <span><?php _e('Active Filters:', 'product-filter-avada'); ?></span>
                        <button type="button" class="clear-all-filters"><?php _e('Clear All', 'product-filter-avada'); ?></button>
                    </div>
                    <div class="active-filters-list"></div>
                </div>

                <!-- Category Filter -->
                <?php if ($plugin->get_option('enable_category_filter', 'yes') === 'yes' && !empty($categories)): ?>
                    <div class="filter-group category-filter">
                        <h4 class="filter-group-title">
                            <?php _e('Categories', 'product-filter-avada'); ?>
                            <span class="filter-toggle">−</span>
                        </h4>
                        <div class="filter-options">
                            <?php foreach ($categories as $category): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="categories[]" value="<?php echo esc_attr($category['slug']); ?>">
                                    <span class="filter-option-text">
                                        <?php echo esc_html($category['name']); ?>
                                        <?php if ($show_count === 'yes'): ?>
                                            <span class="filter-count">(<?php echo intval($category['count']); ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Attribute Filters -->
                <?php if ($plugin->get_option('enable_attribute_filter', 'yes') === 'yes' && !empty($attributes_data)): ?>
                    <?php foreach ($attributes_data as $attribute): ?>
                        <div class="filter-group attribute-filter" data-taxonomy="<?php echo esc_attr($attribute['taxonomy']); ?>">
                            <h4 class="filter-group-title">
                                <?php echo esc_html($attribute['label']); ?>
                                <span class="filter-toggle">−</span>
                            </h4>
                            <div class="filter-options">
                                <?php foreach ($attribute['terms'] as $term): ?>
                                    <label class="filter-option">
                                        <input type="checkbox" name="attributes[<?php echo esc_attr($attribute['taxonomy']); ?>][]" value="<?php echo esc_attr($term['slug']); ?>">
                                        <span class="filter-option-text">
                                            <?php echo esc_html($term['name']); ?>
                                            <?php if ($show_count === 'yes'): ?>
                                                <span class="filter-count">(<?php echo intval($term['count']); ?>)</span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Price Filter -->
                <?php if ($plugin->get_option('enable_price_filter', 'yes') === 'yes'): ?>
                    <div class="filter-group price-filter">
                        <h4 class="filter-group-title">
                            <?php _e('Price Range', 'product-filter-avada'); ?>
                            <span class="filter-toggle">−</span>
                        </h4>
                        <div class="filter-options">
                            <div class="price-range-inputs">
                                <input type="number" name="min_price" placeholder="<?php esc_attr_e('Min Price', 'product-filter-avada'); ?>" min="0" step="0.01">
                                <span class="price-separator">-</span>
                                <input type="number" name="max_price" placeholder="<?php esc_attr_e('Max Price', 'product-filter-avada'); ?>" min="0" step="0.01">
                            </div>
                            <button type="button" class="apply-price-filter"><?php _e('Apply', 'product-filter-avada'); ?></button>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Filter Actions -->
                <div class="filter-actions">
                    <?php if ($ajax !== 'yes'): ?>
                        <button type="submit" class="filter-submit-btn"><?php _e('Apply Filters', 'product-filter-avada'); ?></button>
                    <?php endif; ?>
                    <button type="button" class="filter-reset-btn"><?php _e('Reset Filters', 'product-filter-avada'); ?></button>
                </div>
            </form>

            <?php if ($layout === 'modal'): ?>
                    </div>
                    <div class="filter-modal-footer">
                        <button type="button" class="filter-modal-apply"><?php _e('Apply Filters', 'product-filter-avada'); ?></button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Products Section -->
    <div class="product-results-section">
        <!-- Results Header -->
        <div class="results-header">
            <div class="results-info">
                <span class="results-count"><?php _e('Loading products...', 'product-filter-avada'); ?></span>
            </div>
            <div class="results-sorting">
                <label for="products-sort"><?php _e('Sort by:', 'product-filter-avada'); ?></label>
                <select id="products-sort" name="orderby">
                    <option value="menu_order"><?php _e('Default sorting', 'product-filter-avada'); ?></option>
                    <option value="popularity"><?php _e('Sort by popularity', 'product-filter-avada'); ?></option>
                    <option value="rating"><?php _e('Sort by average rating', 'product-filter-avada'); ?></option>
                    <option value="date"><?php _e('Sort by latest', 'product-filter-avada'); ?></option>
                    <option value="price"><?php _e('Sort by price: low to high', 'product-filter-avada'); ?></option>
                    <option value="price-desc"><?php _e('Sort by price: high to low', 'product-filter-avada'); ?></option>
                </select>
            </div>
        </div>

        <!-- Loading Indicator -->
        <div class="products-loading" style="display: none;">
            <div class="loading-spinner"></div>
            <p><?php _e('Loading products...', 'product-filter-avada'); ?></p>
        </div>

        <!-- Products Grid -->
        <div class="products-container">
            <?php
            // Load initial products
            $initial_products = Product_Filter_Shortcode::get_filtered_products(array(
                'posts_per_page' => $products_per_page,
                'categories' => $categories,
                'attributes' => array(),
            ));
            
            if ($initial_products->have_posts()):
            ?>
                <div class="products-grid">
                    <?php while ($initial_products->have_posts()): $initial_products->the_post(); ?>
                        <?php global $product; ?>
                        <div class="product-item">
                            <div class="product-image">
                                <a href="<?php echo get_permalink(); ?>">
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'woocommerce_thumbnail'); ?>
                                </a>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a>
                                </h3>
                                <div class="product-price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                                <div class="product-actions">
                                    <?php woocommerce_template_loop_add_to_cart(); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($initial_products->max_num_pages > 1): ?>
                    <div class="product-filter-pagination">
                        <?php
                        $current_page = max(1, get_query_var('paged'));
                        $max_pages = $initial_products->max_num_pages;

                        if ($current_page > 1):
                            echo '<button class="pagination-btn prev-page" data-page="' . ($current_page - 1) . '">' . __('Previous', 'product-filter-avada') . '</button>';
                        endif;

                        for ($i = 1; $i <= $max_pages; $i++):
                            $class = ($i == $current_page) ? 'pagination-btn current-page' : 'pagination-btn page-number';
                            echo '<button class="' . $class . '" data-page="' . $i . '">' . $i . '</button>';
                        endfor;

                        if ($current_page < $max_pages):
                            echo '<button class="pagination-btn next-page" data-page="' . ($current_page + 1) . '">' . __('Next', 'product-filter-avada') . '</button>';
                        endif;
                        ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-products-found">
                    <p><?php _e('No products found.', 'product-filter-avada'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
</div>