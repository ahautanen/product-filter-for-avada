<?php
/**
 * Fusion Builder Preview Template for Product Filter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<script type="text/template" id="fusion-builder-block-module-avada-product-filter-preview-template">
    <div class="fusion-builder-module-preview">
        <div class="fusion-builder-module-preview-content">
            <div class="avada-product-filter-preview">
                <div class="preview-header">
                    <h4><?php _e('Product Filter for Avada', 'product-filter-for-avada'); ?></h4>
                    <p><?php _e('Advanced WooCommerce product filtering with category and attribute support', 'product-filter-for-avada'); ?></p>
                </div>
                
                <div class="preview-filters">
                    <div class="preview-filter-group">
                        <label><?php _e('Category Filter', 'product-filter-for-avada'); ?></label>
                        <div class="preview-select"><?php _e('All Categories', 'product-filter-for-avada'); ?> ▼</div>
                    </div>
                    
                    <div class="preview-filter-group">
                        <label><?php _e('Attributes', 'product-filter-for-avada'); ?></label>
                        <div class="preview-checkboxes">
                            <span class="preview-checkbox">□ <?php _e('Color', 'product-filter-for-avada'); ?></span>
                            <span class="preview-checkbox">□ <?php _e('Size', 'product-filter-for-avada'); ?></span>
                            <span class="preview-checkbox">□ <?php _e('Brand', 'product-filter-for-avada'); ?></span>
                        </div>
                    </div>
                    
                    <div class="preview-filter-group">
                        <label><?php _e('Price Range', 'product-filter-for-avada'); ?></label>
                        <div class="preview-price-inputs">
                            <input type="text" placeholder="Min" readonly>
                            <span>-</span>
                            <input type="text" placeholder="Max" readonly>
                        </div>
                    </div>
                    
                    <div class="preview-actions">
                        <button class="preview-button"><?php _e('Clear Filters', 'product-filter-for-avada'); ?></button>
                    </div>
                </div>
                
                <div class="preview-products">
                    <div class="preview-product">
                        <div class="preview-product-image"></div>
                        <div class="preview-product-title"><?php _e('Product Name', 'product-filter-for-avada'); ?></div>
                        <div class="preview-product-price">$29.99</div>
                    </div>
                    <div class="preview-product">
                        <div class="preview-product-image"></div>
                        <div class="preview-product-title"><?php _e('Product Name', 'product-filter-for-avada'); ?></div>
                        <div class="preview-product-price">$39.99</div>
                    </div>
                    <div class="preview-product">
                        <div class="preview-product-image"></div>
                        <div class="preview-product-title"><?php _e('Product Name', 'product-filter-for-avada'); ?></div>
                        <div class="preview-product-price">$49.99</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<style>
.avada-product-filter-preview {
    padding: 20px;
    background: #f8f8f8;
    border-radius: 6px;
    font-family: Arial, sans-serif;
}

.preview-header h4 {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 16px;
}

.preview-header p {
    margin: 0 0 20px 0;
    color: #666;
    font-size: 12px;
}

.preview-filters {
    background: #fff;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
}

.preview-filter-group {
    margin-bottom: 15px;
}

.preview-filter-group:last-child {
    margin-bottom: 0;
}

.preview-filter-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    font-size: 12px;
}

.preview-select {
    background: #fff;
    border: 1px solid #ddd;
    padding: 6px 10px;
    border-radius: 3px;
    font-size: 11px;
    width: 150px;
}

.preview-checkboxes {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.preview-checkbox {
    font-size: 11px;
    color: #666;
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 10px;
    border: 1px solid #ddd;
}

.preview-price-inputs {
    display: flex;
    align-items: center;
    gap: 8px;
}

.preview-price-inputs input {
    width: 60px;
    padding: 4px 6px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 11px;
}

.preview-price-inputs span {
    color: #666;
    font-size: 11px;
}

.preview-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e5e5e5;
}

.preview-button {
    background: #65BCE7;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 3px;
    font-size: 11px;
    cursor: pointer;
}

.preview-products {
    display: flex;
    gap: 10px;
}

.preview-product {
    flex: 1;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px;
    text-align: center;
}

.preview-product-image {
    width: 100%;
    height: 60px;
    background: #f0f0f0;
    border-radius: 3px;
    margin-bottom: 8px;
}

.preview-product-title {
    font-size: 11px;
    color: #333;
    margin-bottom: 5px;
}

.preview-product-price {
    font-size: 12px;
    font-weight: 600;
    color: #65BCE7;
}
</style>