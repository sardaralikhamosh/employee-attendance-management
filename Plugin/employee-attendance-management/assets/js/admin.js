/**
 * Admin JavaScript for Employee Attendance Management
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize datepicker if available
    if ($.fn.datepicker) {
        $('.eam-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: new Date()
        });
    }
    
    // Confirm delete actions
    $('.eam-delete-employee, .eam-delete-record').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Table row highlighting
    $('table.widefat tbody tr').hover(
        function() {
            $(this).css('background-color', '#f0f0f0');
        },
        function() {
            $(this).css('background-color', '');
        }
    );
    
    // Auto-hide success messages
    $('.notice.is-dismissible').delay(5000).fadeOut();
    
    // Print functionality
    $('.eam-print-report').on('click', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Export table data
    $('.eam-export-table').on('click', function(e) {
        e.preventDefault();
        var tableId = $(this).data('table');
        exportTableToCSV(tableId);
    });
    
    function exportTableToCSV(tableId) {
        var csv = [];
        var rows = $('#' + tableId + ' tr');
        
        rows.each(function() {
            var row = [];
            $(this).find('th, td').each(function() {
                var text = $(this).text().trim().replace(/\s+/g, ' ');
                row.push('"' + text.replace(/"/g, '""') + '"');
            });
            csv.push(row.join(','));
        });
        
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'export_' + Date.now() + '.csv';
        a.click();
    }
    
    // Form validation
    $('form[data-validate="true"]').on('submit', function(e) {
        var valid = true;
        
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                valid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '');
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
    });
    
    // Loading indicator for AJAX requests
    $(document).ajaxStart(function() {
        $('body').addClass('eam-loading');
    }).ajaxStop(function() {
        $('body').removeClass('eam-loading');
    });
});
