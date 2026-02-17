/**
 * Frontend JavaScript for Employee Attendance
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Prevent form submission on Enter key (except in textareas)
    $('form').on('keypress', function(e) {
        if (e.which === 13 && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });
    
    // Real-time clock
    function updateClock() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        
        var timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        
        if ($('#eam-current-time').length) {
            $('#eam-current-time').text(timeString);
        }
    }
    
    // Update clock every second
    setInterval(updateClock, 1000);
    updateClock();
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.hash);
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 20
            }, 500);
        }
    });
    
    // Auto-hide messages
    $('.eam-message').delay(5000).fadeOut('slow');
    
    // Disable button after click to prevent double submission
    $('form').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('<span class="eam-loading"></span> Processing...');
    });
    
    // Password visibility toggle (if needed in future)
    $('.eam-toggle-password').on('click', function() {
        var input = $(this).siblings('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).text('Hide');
        } else {
            input.attr('type', 'password');
            $(this).text('Show');
        }
    });
    
    // Input validation
    $('input[type="email"]').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).css('border-color', 'red');
            alert('Please enter a valid email address.');
        } else {
            $(this).css('border-color', '');
        }
    });
    
    // Focus first input on page load
    setTimeout(function() {
        $('input:visible:first').focus();
    }, 500);
});
