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
                
                // Handle dimension toggle checkboxes
                if (e.target.matches('.dimension-toggle')) {
                    AvadaProductFilter.handleDimensionToggle(e);
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
            // Only send dimension values if their checkboxes are enabled
            const enableWidthFilter = wrapper.querySelector('input[name="enable_width_filter"]:checked');
            const enableDepthFilter = wrapper.querySelector('input[name="enable_depth_filter"]:checked');
            const enableAreaFilter = wrapper.querySelector('input[name="enable_area_filter"]:checked');
            
            const widthMin = (enableWidthFilter && minWidthInput) ? minWidthInput.value : '';
            const widthMax = (enableWidthFilter && maxWidthInput) ? maxWidthInput.value : '';
            const depthMin = (enableDepthFilter && minDepthInput) ? minDepthInput.value : '';
            const depthMax = (enableDepthFilter && maxDepthInput) ? maxDepthInput.value : '';
            const areaMin = (enableAreaFilter && minAreaInput) ? minAreaInput.value : '';
            const areaMax = (enableAreaFilter && maxAreaInput) ? maxAreaInput.value : '';
            
            data.append('min_width', widthMin);
            data.append('max_width', widthMax);
            data.append('min_depth', depthMin);
            data.append('max_depth', depthMax);
            data.append('min_area', areaMin);
            data.append('max_area', areaMax);
            
            // Debug log for dimension filters
            if (enableWidthFilter || enableDepthFilter || enableAreaFilter) {
                console.log('Dimension filters active:', {
                    width: enableWidthFilter ? {min: widthMin, max: widthMax} : 'disabled',
                    depth: enableDepthFilter ? {min: depthMin, max: depthMax} : 'disabled',
                    area: enableAreaFilter ? {min: areaMin, max: areaMax} : 'disabled'
                });
            }
            data.append('columns', wrapper.dataset.columns || '3');
            data.append('per_page', wrapper.dataset.perPage || '12');
            data.append('orderby', wrapper.dataset.orderby || 'menu_order');
            data.append('order', wrapper.dataset.order || 'ASC');
            data.append('shortcode_categories', wrapper.dataset.categories || '');
            data.append('paged', page);
            
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
            
            // Dimension checkboxes
            const enableWidthCheckbox = wrapper.querySelector('input[name="enable_width_filter"]');
            const enableDepthCheckbox = wrapper.querySelector('input[name="enable_depth_filter"]');
            const enableAreaCheckbox = wrapper.querySelector('input[name="enable_area_filter"]');
            
            if (categorySelect) categorySelect.value = '';
            if (minPriceInput) minPriceInput.value = '';
            if (maxPriceInput) maxPriceInput.value = '';
            if (minWidthInput) minWidthInput.value = '';
            if (maxWidthInput) maxWidthInput.value = '';
            if (minDepthInput) minDepthInput.value = '';
            if (maxDepthInput) maxDepthInput.value = '';
            if (minAreaInput) minAreaInput.value = '';
            if (maxAreaInput) maxAreaInput.value = '';
            
            // Uncheck dimension checkboxes and hide inputs
            if (enableWidthCheckbox) {
                enableWidthCheckbox.checked = false;
                const widthInputs = enableWidthCheckbox.closest('.dimension-filter-row').querySelector('.dimension-inputs');
                if (widthInputs) widthInputs.style.display = 'none';
            }
            if (enableDepthCheckbox) {
                enableDepthCheckbox.checked = false;
                const depthInputs = enableDepthCheckbox.closest('.dimension-filter-row').querySelector('.dimension-inputs');
                if (depthInputs) depthInputs.style.display = 'none';
            }
            if (enableAreaCheckbox) {
                enableAreaCheckbox.checked = false;
                const areaInputs = enableAreaCheckbox.closest('.dimension-filter-row').querySelector('.dimension-inputs');
                if (areaInputs) areaInputs.style.display = 'none';
            }
            
            attributeCheckboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
            
            // Trigger filter update
            AvadaProductFilter.filterProducts(wrapper, 1);
        },
        
        handleDimensionToggle: function(e) {
            const checkbox = e.target;
            const row = checkbox.closest('.dimension-filter-row');
            const inputs = row.querySelector('.dimension-inputs');
            
            if (checkbox.checked) {
                inputs.style.display = 'block';
            } else {
                inputs.style.display = 'none';
                // Clear inputs when disabled
                const numberInputs = inputs.querySelectorAll('input[type="number"]');
                numberInputs.forEach(input => input.value = '');
            }
            // Always trigger filter update when checkbox state changes
            const wrapper = checkbox.closest('.avada-product-filter-wrapper');
            if (wrapper) {
                AvadaProductFilter.filterProducts(wrapper, 1);
            }
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