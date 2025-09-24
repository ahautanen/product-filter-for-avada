/**
 * Product Filter for Avada - Admin JavaScript
 *
 * @package Product_Filter_Avada
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize admin functionality
        initializeAdminSettings();
    });

    function initializeAdminSettings() {
        // Handle conditional field display
        handleConditionalFields();
        
        // Add confirmation for reset actions
        addResetConfirmation();
        
        // Initialize tooltips if needed
        initializeTooltips();
    }

    function handleConditionalFields() {
        // Show/hide fields based on other field values
        const ajaxField = $('input[name="product_filter_avada_options[ajax_filtering]"]');
        
        if (ajaxField.length) {
            // Example: Show additional options when AJAX is enabled
            ajaxField.on('change', function() {
                // Add conditional logic here if needed in the future
            });
        }
    }

    function addResetConfirmation() {
        // Add confirmation dialog for any reset buttons
        $('.reset-settings').on('click', function(e) {
            if (!confirm('Are you sure you want to reset all settings to default values?')) {
                e.preventDefault();
                return false;
            }
        });
    }

    function initializeTooltips() {
        // Initialize tooltips for help text
        $('.help-tip').each(function() {
            $(this).attr('title', $(this).data('tip'));
        });
    }

    // Settings validation
    $('form').on('submit', function(e) {
        let isValid = true;
        const errors = [];

        // Validate products per page
        const productsPerPage = $('input[name="product_filter_avada_options[products_per_page]"]');
        if (productsPerPage.length) {
            const value = parseInt(productsPerPage.val());
            if (value < 1 || value > 100) {
                isValid = false;
                errors.push('Products per page must be between 1 and 100.');
                productsPerPage.css('border-color', '#dc3232');
            } else {
                productsPerPage.css('border-color', '');
            }
        }

        // Show errors if validation fails
        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }
    });

})(jQuery);