<?php
/**
 * Uninstall Product Filter for Avada
 * 
 * This file is executed when the plugin is uninstalled via WordPress admin.
 * It cleans up plugin data and options.
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('avada_product_filter_settings');

// Delete any transients or cache
delete_transient('avada_product_filter_categories');
delete_transient('avada_product_filter_attributes');
delete_transient('avada_product_filter_price_range');

// Clear any scheduled hooks (if any were set)
wp_clear_scheduled_hook('avada_product_filter_cleanup');

// If multisite, delete options from all sites
if (is_multisite()) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    $original_blog_id = get_current_blog_id();
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        
        delete_option('avada_product_filter_settings');
        delete_transient('avada_product_filter_categories');
        delete_transient('avada_product_filter_attributes');
        delete_transient('avada_product_filter_price_range');
    }
    
    switch_to_blog($original_blog_id);
}

// Note: We don't delete any custom post types, taxonomies, or user data
// as this could be destructive. Only plugin-specific options are removed.