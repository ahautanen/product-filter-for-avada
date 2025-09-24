/**
 * Frontend JavaScript for Avada Product Filter
 */

jQuery(document).ready(function($) {
    'use strict';
    
    var AvadaProductFilter = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Filter change events
            $(document).on('change', '.avada-product-filter-controls select', this.handleFilterChange);
            $(document).on('change', '.avada-product-filter-controls input[type="checkbox"]', this.handleFilterChange);
            $(document).on('input', '.avada-product-filter-controls input[type="number"]', this.debounce(this.handleFilterChange, 500));
            
            // Clear filters
            $(document).on('click', '.avada-filter-clear', this.clearFilters);
            
            // Pagination clicks
            $(document).on('click', '.avada-filter-pagination a', this.handlePagination);
        },
        
        handleFilterChange: function(e) {
            var $wrapper = $(this).closest('.avada-product-filter-wrapper');
            AvadaProductFilter.filterProducts($wrapper, 1);
        },
        
        handlePagination: function(e) {
            e.preventDefault();
            
            var $link = $(this);
            var $wrapper = $link.closest('.avada-product-filter-wrapper');
            var href = $link.attr('href');
            var page = 1;
            
            // Extract page number from URL
            var matches = href.match(/\/page\/(\d+)/);
            if (matches) {
                page = parseInt(matches[1]);
            } else {
                matches = href.match(/paged=(\d+)/);
                if (matches) {
                    page = parseInt(matches[1]);
                }
            }
            
            AvadaProductFilter.filterProducts($wrapper, page);
        },
        
        filterProducts: function($wrapper, page) {
            page = page || 1;
            
            var data = {
                action: 'avada_filter_products',
                nonce: avada_product_filter_ajax.nonce,
                category: $wrapper.find('select[name="filter_category"]').val() || '',
                attributes: [],
                min_price: $wrapper.find('input[name="min_price"]').val() || '',
                max_price: $wrapper.find('input[name="max_price"]').val() || '',
                columns: $wrapper.data('columns') || 3,
                per_page: $wrapper.data('per-page') || 12,
                orderby: $wrapper.data('orderby') || 'menu_order',
                order: $wrapper.data('order') || 'ASC',
                paged: page
            };
            
            // Collect checked attributes
            $wrapper.find('input[name="filter_attributes[]"]:checked').each(function() {
                data.attributes.push($(this).val());
            });
            
            // Show loading state
            var $productsContainer = $wrapper.find('.avada-product-filter-products');
            $productsContainer.addClass('loading');
            
            // AJAX request
            $.ajax({
                url: avada_product_filter_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $productsContainer.html(response.products);
                        
                        // Update pagination
                        var $pagination = $wrapper.find('.avada-filter-pagination');
                        if (response.pagination) {
                            if ($pagination.length) {
                                $pagination.html(response.pagination);
                            } else {
                                $productsContainer.after('<div class="avada-filter-pagination">' + response.pagination + '</div>');
                            }
                        } else {
                            $pagination.remove();
                        }
                        
                        // Scroll to products if not on first page
                        if (page > 1) {
                            $('html, body').animate({
                                scrollTop: $wrapper.offset().top - 100
                            }, 500);
                        }
                        
                        // Trigger custom event for other scripts
                        $(document).trigger('avada_products_filtered', {
                            wrapper: $wrapper,
                            found_posts: response.found_posts,
                            page: page
                        });
                    }
                },
                error: function() {
                    console.error('Filter request failed');
                },
                complete: function() {
                    $productsContainer.removeClass('loading');
                }
            });
        },
        
        clearFilters: function(e) {
            e.preventDefault();
            
            var $wrapper = $(this).closest('.avada-product-filter-wrapper');
            
            // Reset all form elements
            $wrapper.find('select[name="filter_category"]').val('');
            $wrapper.find('input[name="filter_attributes[]"]').prop('checked', false);
            $wrapper.find('input[name="min_price"]').val('');
            $wrapper.find('input[name="max_price"]').val('');
            
            // Trigger filter update
            AvadaProductFilter.filterProducts($wrapper, 1);
        },
        
        // Utility function to debounce input events
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };
    
    // Initialize the filter
    AvadaProductFilter.init();
    
    // Make it globally available
    window.AvadaProductFilter = AvadaProductFilter;
});