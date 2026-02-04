(function($) {
    'use strict';
    
    $(document).ready(function() {
        const $courseResults = $('.course-results');
        const $filterForm = $('#courseFilters');
        const $applyBtn = $('#applyFilters');
        const $resetBtn = $('#resetFilters');
        
        // Enhanced select with better accessibility
        $('select[multiple]').each(function() {
            const $select = $(this);
            const label = $select.prev('label').text() || 'Multi-select';
            
            // Add ARIA attributes
            $select.attr({
                'aria-label': label + ' (use Ctrl/Cmd + click to select multiple options)',
                'aria-multiselectable': 'true'
            });
            
            // Add keyboard navigation
            $select.on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $(this).blur();
                }
            });
        });
        
        // Apply filters
        $applyBtn.on('click', function(e) {
            e.preventDefault();
            applyFilters();
        });
        
        // Reset filters
        $resetBtn.on('click', function(e) {
            e.preventDefault();
            $filterForm[0].reset();
            applyFilters();
        });
        
        // Handle Enter key in search
        $('#textSearch').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });
        
        function applyFilters() {
            const formData = $filterForm.serializeArray();
            const filters = {};
            
            // Convert form data to filter object
            formData.forEach(item => {
                if (item.value) {
                    if (!filters[item.name]) {
                        filters[item.name] = [];
                    }
                    if (Array.isArray(filters[item.name])) {
                        filters[item.name].push(item.value);
                    }
                }
            });
            
            // Show loading state
            $courseResults.addClass('loading');
            
            // AJAX request
            $.ajax({
                url: oxford_cds_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_courses',
                    nonce: oxford_cds_ajax.nonce,
                    ...filters
                },
                success: function(response) {
                    if (response.success) {
                        $courseResults.html(response.data.html);
                        announceResults($courseResults.find('.course-card').length);
                    } else {
                        $courseResults.html('<p class="no-results">Error loading courses. Please try again.</p>');
                    }
                },
                error: function() {
                    $courseResults.html('<p class="no-results">Error loading courses. Please try again.</p>');
                },
                complete: function() {
                    $courseResults.removeClass('loading');
                }
            });
        }
        
        function announceResults(count) {
            const message = count + ' courses found';
            const $announcement = $('<div class="screen-reader-text" role="status" aria-live="polite"></div>')
                .text(message)
                .appendTo('body');
            
            // Remove after announcement
            setTimeout(() => {
                $announcement.remove();
            }, 1000);
        }
        
        // Initial load
        applyFilters();
    });
})(jQuery);