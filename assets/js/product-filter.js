/**
 * Product Filter for Avada - Frontend JavaScript
 *
 * @package Product_Filter_Avada
 */

(function($) {
    'use strict';

    class ProductFilter {
        constructor(wrapper) {
            this.wrapper = $(wrapper);
            this.form = this.wrapper.find('.product-filter-form');
            this.productsContainer = this.wrapper.find('.products-container');
            this.resultsInfo = this.wrapper.find('.results-count');
            this.loadingIndicator = this.wrapper.find('.products-loading');
            this.activeFiltersContainer = this.wrapper.find('.active-filters');
            this.activeFiltersList = this.wrapper.find('.active-filters-list');
            this.currentPage = 1;
            this.isAjax = this.wrapper.data('ajax') === 'yes';
            this.perPage = this.wrapper.data('per-page') || 12;
            this.isLoading = false;
            
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeCollapsibleGroups();
            this.initializeModal();
            this.updateResultsCount();
        }

        bindEvents() {
            // Filter option changes
            this.form.on('change', 'input[type="checkbox"]', (e) => {
                if (this.isAjax) {
                    this.handleFilterChange();
                }
                this.updateActiveFilters();
            });

            // Price filter
            this.wrapper.on('click', '.apply-price-filter', () => {
                if (this.isAjax) {
                    this.handleFilterChange();
                }
                this.updateActiveFilters();
            });

            // Price inputs enter key
            this.form.on('keypress', 'input[name="min_price"], input[name="max_price"]', (e) => {
                if (e.which === 13) {
                    if (this.isAjax) {
                        this.handleFilterChange();
                    }
                    this.updateActiveFilters();
                }
            });

            // Sorting
            this.wrapper.on('change', '#products-sort', () => {
                if (this.isAjax) {
                    this.handleFilterChange();
                }
            });

            // Pagination
            this.wrapper.on('click', '.pagination-btn', (e) => {
                e.preventDefault();
                const page = $(e.target).data('page');
                if (page && page !== this.currentPage) {
                    this.currentPage = page;
                    if (this.isAjax) {
                        this.handleFilterChange();
                    }
                }
            });

            // Form submission (non-AJAX)
            this.form.on('submit', (e) => {
                if (this.isAjax) {
                    e.preventDefault();
                    this.handleFilterChange();
                }
            });

            // Reset filters
            this.wrapper.on('click', '.filter-reset-btn', (e) => {
                e.preventDefault();
                this.resetFilters();
            });

            // Clear all active filters
            this.wrapper.on('click', '.clear-all-filters', (e) => {
                e.preventDefault();
                this.resetFilters();
            });

            // Remove individual active filter
            this.wrapper.on('click', '.active-filter-remove', (e) => {
                e.preventDefault();
                const filterTag = $(e.target).closest('.active-filter-tag');
                const filterType = filterTag.data('type');
                const filterValue = filterTag.data('value');
                const filterTaxonomy = filterTag.data('taxonomy');
                
                this.removeFilter(filterType, filterValue, filterTaxonomy);
            });

            // Filter group collapsing
            this.wrapper.on('click', '.filter-group-title', (e) => {
                const group = $(e.target).closest('.filter-group');
                group.toggleClass('collapsed');
            });
        }

        initializeCollapsibleGroups() {
            // Initialize all filter groups as expanded by default
            this.wrapper.find('.filter-group').removeClass('collapsed');
        }

        initializeModal() {
            if (this.wrapper.hasClass('layout-modal')) {
                const modal = this.wrapper.find('.filter-modal');
                const toggleBtn = this.wrapper.find('.filter-toggle-btn');
                const closeBtn = this.wrapper.find('.filter-modal-close');
                const overlay = this.wrapper.find('.filter-modal-overlay');
                const applyBtn = this.wrapper.find('.filter-modal-apply');

                // Show modal
                toggleBtn.on('click', () => {
                    modal.addClass('active');
                    $('body').addClass('modal-open');
                });

                // Hide modal
                const hideModal = () => {
                    modal.removeClass('active');
                    $('body').removeClass('modal-open');
                };

                closeBtn.on('click', hideModal);
                overlay.on('click', hideModal);

                // Apply filters from modal
                applyBtn.on('click', () => {
                    if (this.isAjax) {
                        this.handleFilterChange();
                    }
                    hideModal();
                });

                // ESC key to close modal
                $(document).on('keydown', (e) => {
                    if (e.keyCode === 27 && modal.hasClass('active')) {
                        hideModal();
                    }
                });
            }
        }

        handleFilterChange() {
            if (this.isLoading) {
                return;
            }

            this.currentPage = 1; // Reset to first page on filter change
            this.showLoading();
            
            const filterData = this.getFilterData();
            
            $.ajax({
                url: product_filter_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_products',
                    nonce: product_filter_ajax.nonce,
                    ...filterData,
                    paged: this.currentPage,
                    posts_per_page: this.perPage,
                    orderby: this.wrapper.find('#products-sort').val()
                },
                success: (response) => {
                    if (response.success) {
                        this.updateProducts(response.data);
                        this.updateResultsCount(response.data.found_posts);
                        this.updateActiveFilters();
                        this.scrollToResults();
                    } else {
                        console.error('Filter error:', response);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX error:', error);
                    this.showError();
                },
                complete: () => {
                    this.hideLoading();
                }
            });
        }

        getFilterData() {
            const data = {
                categories: [],
                attributes: {},
                min_price: '',
                max_price: ''
            };

            // Get selected categories
            this.form.find('input[name="categories[]"]:checked').each(function() {
                data.categories.push($(this).val());
            });
            data.categories = data.categories.join(',');

            // Get selected attributes
            this.form.find('input[name^="attributes["]:checked').each(function() {
                const name = $(this).attr('name');
                const match = name.match(/attributes\[([^\]]+)\]\[\]/);
                if (match) {
                    const taxonomy = match[1];
                    if (!data.attributes[taxonomy]) {
                        data.attributes[taxonomy] = [];
                    }
                    data.attributes[taxonomy].push($(this).val());
                }
            });

            // Get price range
            data.min_price = this.form.find('input[name="min_price"]').val();
            data.max_price = this.form.find('input[name="max_price"]').val();

            return data;
        }

        updateProducts(data) {
            this.productsContainer.html(data.products);
            
            // Update pagination if provided
            if (data.pagination) {
                let paginationContainer = this.wrapper.find('.product-filter-pagination');
                if (paginationContainer.length) {
                    paginationContainer.replaceWith(data.pagination);
                } else {
                    this.productsContainer.append(data.pagination);
                }
            }
        }

        updateResultsCount(count) {
            if (count !== undefined) {
                const text = count === 1 
                    ? `${count} product found`
                    : `${count} products found`;
                this.resultsInfo.text(text);
            } else {
                // Count current products
                const currentCount = this.wrapper.find('.product-item').length;
                const text = currentCount === 1 
                    ? `${currentCount} product found`
                    : `${currentCount} products found`;
                this.resultsInfo.text(text);
            }
        }

        updateActiveFilters() {
            const activeFilters = [];
            
            // Categories
            this.form.find('input[name="categories[]"]:checked').each(function() {
                const label = $(this).siblings('.filter-option-text').text().replace(/\s*\(\d+\)\s*$/, '');
                activeFilters.push({
                    type: 'category',
                    value: $(this).val(),
                    label: label,
                    taxonomy: 'product_cat'
                });
            });

            // Attributes
            this.form.find('input[name^="attributes["]:checked').each(function() {
                const name = $(this).attr('name');
                const match = name.match(/attributes\[([^\]]+)\]\[\]/);
                if (match) {
                    const taxonomy = match[1];
                    const label = $(this).siblings('.filter-option-text').text().replace(/\s*\(\d+\)\s*$/, '');
                    activeFilters.push({
                        type: 'attribute',
                        value: $(this).val(),
                        label: label,
                        taxonomy: taxonomy
                    });
                }
            });

            // Price
            const minPrice = this.form.find('input[name="min_price"]').val();
            const maxPrice = this.form.find('input[name="max_price"]').val();
            
            if (minPrice || maxPrice) {
                let priceLabel = 'Price: ';
                if (minPrice && maxPrice) {
                    priceLabel += `$${minPrice} - $${maxPrice}`;
                } else if (minPrice) {
                    priceLabel += `From $${minPrice}`;
                } else {
                    priceLabel += `Up to $${maxPrice}`;
                }
                
                activeFilters.push({
                    type: 'price',
                    value: 'price-range',
                    label: priceLabel,
                    taxonomy: null
                });
            }

            // Update active filters display
            if (activeFilters.length > 0) {
                let html = '';
                activeFilters.forEach(filter => {
                    html += `<span class="active-filter-tag" data-type="${filter.type}" data-value="${filter.value}" data-taxonomy="${filter.taxonomy || ''}">
                        ${filter.label}
                        <button type="button" class="active-filter-remove">&times;</button>
                    </span>`;
                });
                
                this.activeFiltersList.html(html);
                this.activeFiltersContainer.show();
            } else {
                this.activeFiltersContainer.hide();
            }
        }

        removeFilter(type, value, taxonomy) {
            if (type === 'category') {
                this.form.find(`input[name="categories[]"][value="${value}"]`).prop('checked', false);
            } else if (type === 'attribute') {
                this.form.find(`input[name="attributes[${taxonomy}][]"][value="${value}"]`).prop('checked', false);
            } else if (type === 'price') {
                this.form.find('input[name="min_price"], input[name="max_price"]').val('');
            }

            if (this.isAjax) {
                this.handleFilterChange();
            } else {
                this.updateActiveFilters();
            }
        }

        resetFilters() {
            this.form[0].reset();
            this.currentPage = 1;
            
            if (this.isAjax) {
                this.handleFilterChange();
            } else {
                this.updateActiveFilters();
            }
        }

        showLoading() {
            this.isLoading = true;
            this.loadingIndicator.show();
            this.productsContainer.css('opacity', '0.5');
        }

        hideLoading() {
            this.isLoading = false;
            this.loadingIndicator.hide();
            this.productsContainer.css('opacity', '1');
        }

        showError() {
            const errorHtml = '<div class="filter-error"><p>An error occurred while filtering products. Please try again.</p></div>';
            this.productsContainer.html(errorHtml);
        }

        scrollToResults() {
            const resultsSection = this.wrapper.find('.product-results-section');
            if (resultsSection.length) {
                $('html, body').animate({
                    scrollTop: resultsSection.offset().top - 20
                }, 500);
            }
        }
    }

    // Initialize product filters when DOM is ready
    $(document).ready(function() {
        $('.product-filter-wrapper').each(function() {
            new ProductFilter(this);
        });

        // Handle browser back/forward buttons
        $(window).on('popstate', function() {
            // Reload the page to handle back/forward navigation
            location.reload();
        });
    });

})(jQuery);

// Add CSS for modal body lock
jQuery(document).ready(function($) {
    $('<style>').text(`
        body.modal-open {
            overflow: hidden;
        }
        .filter-error {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .filter-error p {
            margin: 0;
            font-size: 16px;
        }
    `).appendTo('head');
});