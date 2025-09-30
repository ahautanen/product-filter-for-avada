/**
 * Frontend JavaScript for Avada Product Filter (Vanilla JS)
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const AvadaProductFilter = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Filter change events with event delegation
            document.addEventListener('change', function(e) {
                if (e.target.matches('.avada-product-filter-controls select') ||
                    e.target.matches('.avada-product-filter-controls input[type="checkbox"]')) {
                    AvadaProductFilter.handleFilterChange(e);
                }
            });
            
            // Debounced input for number fields
            document.addEventListener('input', AvadaProductFilter.debounce(function(e) {
                if (e.target.matches('.avada-product-filter-controls input[type="number"]')) {
                    AvadaProductFilter.handleFilterChange(e);
                }
            }, 500));
            
            // Clear filters
            document.addEventListener('click', function(e) {
                if (e.target.matches('.avada-filter-clear')) {
                    AvadaProductFilter.clearFilters(e);
                }
            });
            
            // Pagination clicks
            document.addEventListener('click', function(e) {
                if (e.target.matches('.avada-filter-pagination a')) {
                    AvadaProductFilter.handlePagination(e);
                }
            });
        },
        
        handleFilterChange: function(e) {
            const wrapper = e.target.closest('.avada-product-filter-wrapper');
            if (wrapper) {
                AvadaProductFilter.filterProducts(wrapper, 1);
            }
        },
        
        handlePagination: function(e) {
            e.preventDefault();
            
            const link = e.target;
            const wrapper = link.closest('.avada-product-filter-wrapper');
            const href = link.getAttribute('href');
            let page = 1;
            
            // Extract page number from URL
            let matches = href.match(/\/page\/(\d+)/);
            if (matches) {
                page = parseInt(matches[1]);
            } else {
                matches = href.match(/paged=(\d+)/);
                if (matches) {
                    page = parseInt(matches[1]);
                }
            }
            
            AvadaProductFilter.filterProducts(wrapper, page);
        },
        
        filterProducts: function(wrapper, page) {
            page = page || 1;
            
            const categorySelect = wrapper.querySelector('select[name="filter_category"]');
            const minPriceInput = wrapper.querySelector('input[name="min_price"]');
            const maxPriceInput = wrapper.querySelector('input[name="max_price"]');
            const minWidthInput = wrapper.querySelector('input[name="min_width"]');
            const maxWidthInput = wrapper.querySelector('input[name="max_width"]');
            const minDepthInput = wrapper.querySelector('input[name="min_depth"]');
            const maxDepthInput = wrapper.querySelector('input[name="max_depth"]');
            const minAreaInput = wrapper.querySelector('input[name="min_area"]');
            const maxAreaInput = wrapper.querySelector('input[name="max_area"]');
            const checkedAttributes = wrapper.querySelectorAll('input[name="filter_attributes[]"]:checked');
            
            const data = new FormData();
            data.append('action', 'avada_filter_products');
            data.append('nonce', avada_product_filter_ajax.nonce);
            data.append('category', categorySelect ? categorySelect.value : '');
            data.append('min_price', minPriceInput ? minPriceInput.value : '');
            data.append('max_price', maxPriceInput ? maxPriceInput.value : '');
            data.append('min_width', minWidthInput ? minWidthInput.value : '');
            data.append('max_width', maxWidthInput ? maxWidthInput.value : '');
            data.append('min_depth', minDepthInput ? minDepthInput.value : '');
            data.append('max_depth', maxDepthInput ? maxDepthInput.value : '');
            data.append('min_area', minAreaInput ? minAreaInput.value : '');
            data.append('max_area', maxAreaInput ? maxAreaInput.value : '');
            data.append('columns', wrapper.dataset.columns || '3');
            data.append('per_page', wrapper.dataset.perPage || '12');
            data.append('orderby', wrapper.dataset.orderby || 'menu_order');
            data.append('order', wrapper.dataset.order || 'ASC');
            data.append('paged', page);
                // Send meta key names so server can use dynamic keys
                data.append('width_meta_key', wrapper.dataset.widthMetaKey || 'width');
                data.append('depth_meta_key', wrapper.dataset.depthMetaKey || 'depth');
                data.append('area_meta_key', wrapper.dataset.areaMetaKey || 'area');
            
            // Collect checked attributes - send as individual form fields for WordPress
            checkedAttributes.forEach(function(checkbox, index) {
                data.append(`attributes[${index}]`, checkbox.value);
            });
            
            // Show loading state
            const productsContainer = wrapper.querySelector('.avada-product-filter-products');
            if (productsContainer) {
                productsContainer.classList.add('loading');
            }
            
            // Fetch request
            fetch(avada_product_filter_ajax.ajax_url, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(response => {
                if (response.success && productsContainer) {
                    productsContainer.innerHTML = response.products;
                    
                    // Update pagination
                    let pagination = wrapper.querySelector('.avada-filter-pagination');
                    if (response.pagination) {
                        if (pagination) {
                            pagination.innerHTML = response.pagination;
                        } else {
                            const paginationDiv = document.createElement('div');
                            paginationDiv.className = 'avada-filter-pagination';
                            paginationDiv.innerHTML = response.pagination;
                            productsContainer.insertAdjacentElement('afterend', paginationDiv);
                        }
                    } else if (pagination) {
                        pagination.remove();
                    }
                    
                    // Scroll to products if not on first page
                    if (page > 1) {
                        const offsetTop = wrapper.getBoundingClientRect().top + window.pageYOffset - 100;
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                    
                    // Trigger custom event for other scripts
                    const customEvent = new CustomEvent('avada_products_filtered', {
                        detail: {
                            wrapper: wrapper,
                            found_posts: response.found_posts,
                            page: page
                        }
                    });
                    document.dispatchEvent(customEvent);
                }
            })
            .catch(error => {
                console.error('Filter request failed:', error);
            })
            .finally(() => {
                if (productsContainer) {
                    productsContainer.classList.remove('loading');
                }
            });
        },
        
        clearFilters: function(e) {
            e.preventDefault();
            
            const wrapper = e.target.closest('.avada-product-filter-wrapper');
            if (!wrapper) return;
            
            // Reset all form elements
            const categorySelect = wrapper.querySelector('select[name="filter_category"]');
            const attributeCheckboxes = wrapper.querySelectorAll('input[name="filter_attributes[]"]');
            const minPriceInput = wrapper.querySelector('input[name="min_price"]');
            const maxPriceInput = wrapper.querySelector('input[name="max_price"]');
            const minWidthInput = wrapper.querySelector('input[name="min_width"]');
            const maxWidthInput = wrapper.querySelector('input[name="max_width"]');
            const minDepthInput = wrapper.querySelector('input[name="min_depth"]');
            const maxDepthInput = wrapper.querySelector('input[name="max_depth"]');
            const minAreaInput = wrapper.querySelector('input[name="min_area"]');
            const maxAreaInput = wrapper.querySelector('input[name="max_area"]');
            
            if (categorySelect) categorySelect.value = '';
            if (minPriceInput) minPriceInput.value = '';
            if (maxPriceInput) maxPriceInput.value = '';
            if (minWidthInput) minWidthInput.value = '';
            if (maxWidthInput) maxWidthInput.value = '';
            if (minDepthInput) minDepthInput.value = '';
            if (maxDepthInput) maxDepthInput.value = '';
            if (minAreaInput) minAreaInput.value = '';
            if (maxAreaInput) maxAreaInput.value = '';
            
            attributeCheckboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
            
            // Trigger filter update
            AvadaProductFilter.filterProducts(wrapper, 1);
        },
        
        // Utility function to debounce input events
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    if (!immediate) func.apply(this, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(this, args);
            };
        }
    };
    
    // Initialize the filter
    AvadaProductFilter.init();
    
    // Make it globally available
    window.AvadaProductFilter = AvadaProductFilter;
});