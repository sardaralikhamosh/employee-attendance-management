<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('Employee Attendance', 'employee-attendance'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body class="eam-attendance-page">
    <div class="eam-attendance-container">
        <div class="eam-attendance-card">
            <div class="eam-logo">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <h1><?php bloginfo('name'); ?></h1>
                <?php endif; ?>
            </div>
            
            <h2 class="eam-title"><?php _e('Employee Attendance System', 'employee-attendance'); ?></h2>
            <p class="eam-subtitle"><?php _e('Check in and out to mark your attendance', 'employee-attendance'); ?></p>
            
            <div id="eam-message" class="eam-message" style="display: none;"></div>
            
            <!-- Login Form -->
            <div id="eam-login-section">
                <form id="eam-login-form">
                    <div class="eam-form-group">
                        <label for="eam-email">
                            <span class="dashicons dashicons-email"></span>
                            <?php _e('Email Address', 'employee-attendance'); ?>
                        </label>
                        <input type="email" id="eam-email" name="email" required placeholder="your.email@company.com">
                    </div>
                    
                    <div class="eam-form-group">
                        <label for="eam-password">
                            <span class="dashicons dashicons-lock"></span>
                            <?php _e('Password', 'employee-attendance'); ?>
                        </label>
                        <input type="password" id="eam-password" name="password" required placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="eam-btn eam-btn-primary">
                        <?php _e('Login', 'employee-attendance'); ?>
                    </button>
                </form>
            </div>
            
            <!-- Attendance Section (shown after login) -->
            <div id="eam-attendance-section" style="display: none;">
                <div class="eam-employee-info">
                    <h3><?php _e('Welcome,', 'employee-attendance'); ?> <span id="eam-employee-name"></span></h3>
                    <p><strong><?php _e('Employee ID:', 'employee-attendance'); ?></strong> <span id="eam-employee-id"></span></p>
                    <p><strong><?php _e('Date:', 'employee-attendance'); ?></strong> <span id="eam-current-date"></span></p>
                </div>
                
                <div class="eam-status-display">
                    <div id="eam-status-checkedin" class="eam-status-box" style="display: none;">
                        <div class="eam-status-icon success">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <h4><?php _e('Checked In', 'employee-attendance'); ?></h4>
                        <p class="eam-time" id="eam-checkin-time"></p>
                        <p class="eam-status-badge" id="eam-checkin-status"></p>
                    </div>
                    
                    <div id="eam-status-checkedout" class="eam-status-box" style="display: none;">
                        <div class="eam-status-icon info">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <h4><?php _e('Checked Out', 'employee-attendance'); ?></h4>
                        <p class="eam-time" id="eam-checkout-time"></p>
                        <p><strong><?php _e('Total Hours:', 'employee-attendance'); ?></strong> <span id="eam-total-hours"></span></p>
                    </div>
                </div>
                
                <div class="eam-action-buttons">
                    <button id="eam-checkin-btn" class="eam-btn eam-btn-success" style="display: none;">
                        <span class="dashicons dashicons-arrow-down-alt"></span>
                        <?php _e('Check In', 'employee-attendance'); ?>
                    </button>
                    
                    <button id="eam-checkout-btn" class="eam-btn eam-btn-warning" style="display: none;">
                        <span class="dashicons dashicons-arrow-up-alt"></span>
                        <?php _e('Check Out', 'employee-attendance'); ?>
                    </button>
                    
                    <button id="eam-logout-btn" class="eam-btn eam-btn-secondary">
                        <span class="dashicons dashicons-exit"></span>
                        <?php _e('Logout', 'employee-attendance'); ?>
                    </button>
                </div>
            </div>
            
            <div class="eam-office-hours">
                <p><strong><?php _e('Office Hours:', 'employee-attendance'); ?></strong></p>
                <p>
                    <?php 
                    $start = get_option('eam_office_start_time', '19:00:00');
                    $end = get_option('eam_office_end_time', '04:00:00');
                    echo date('h:i A', strtotime($start)) . ' - ' . date('h:i A', strtotime($end));
                    ?>
                </p>
                <p><small><?php _e('Working Days: Monday to Friday', 'employee-attendance'); ?></small></p>
            </div>
        </div>
        
        <div class="eam-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All rights reserved.', 'employee-attendance'); ?></p>
        </div>
    </div>
    
    <?php wp_footer(); ?>
    
    <script>
    jQuery(document).ready(function($) {
        var currentEmail = '';
        var currentPassword = '';
        
        // Update current date
        var today = new Date();
        $('#eam-current-date').text(today.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }));
        
        // Login form
        $('#eam-login-form').on('submit', function(e) {
            e.preventDefault();
            
            currentEmail = $('#eam-email').val();
            currentPassword = $('#eam-password').val();
            
            checkStatus();
        });
        
        // Check status
        function checkStatus() {
            $.ajax({
                url: eamFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eam_get_status',
                    nonce: eamFrontend.nonce,
                    email: currentEmail,
                    password: currentPassword
                },
                success: function(response) {
                    if (response.success) {
                        $('#eam-login-section').hide();
                        $('#eam-attendance-section').show();
                        
                        $('#eam-employee-name').text(response.employee_name);
                        $('#eam-employee-id').text(response.employee_id);
                        
                        if (response.checked_in) {
                            $('#eam-status-checkedin').show();
                            $('#eam-checkin-time').text(formatTime(response.check_in_time));
                            
                            var statusText = '';
                            var statusClass = '';
                            if (response.check_in_status === 'on_time') {
                                statusText = 'On Time';
                                statusClass = 'success';
                            } else if (response.check_in_status === 'late') {
                                statusText = 'Late';
                                statusClass = 'warning';
                            } else if (response.check_in_status === 'very_late') {
                                statusText = 'Very Late';
                                statusClass = 'danger';
                            }
                            $('#eam-checkin-status').html('<span class="eam-badge ' + statusClass + '">' + statusText + '</span>');
                            
                            if (response.checked_out) {
                                $('#eam-status-checkedout').show();
                                $('#eam-checkout-time').text(formatTime(response.check_out_time));
                                $('#eam-total-hours').text(response.total_hours + ' hours');
                                $('#eam-checkout-btn').hide();
                            } else {
                                $('#eam-checkout-btn').show();
                            }
                        } else {
                            $('#eam-checkin-btn').show();
                        }
                    } else {
                        showMessage(response.message, 'error');
                    }
                }
            });
        }
        
        // Check in
        $('#eam-checkin-btn').on('click', function() {
            if (!confirm('<?php _e('Are you sure you want to check in?', 'employee-attendance'); ?>')) {
                return;
            }
            
            $.ajax({
                url: eamFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eam_checkin',
                    nonce: eamFrontend.nonce,
                    email: currentEmail,
                    password: currentPassword
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage(response.message, 'error');
                    }
                }
            });
        });
        
        // Check out
        $('#eam-checkout-btn').on('click', function() {
            if (!confirm('<?php _e('Are you sure you want to check out?', 'employee-attendance'); ?>')) {
                return;
            }
            
            $.ajax({
                url: eamFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eam_checkout',
                    nonce: eamFrontend.nonce,
                    email: currentEmail,
                    password: currentPassword
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage(response.message, 'error');
                    }
                }
            });
        });
        
        // Logout
        $('#eam-logout-btn').on('click', function() {
            location.reload();
        });
        
        // Helper functions
        function formatTime(datetime) {
            var date = new Date(datetime);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }
        
        function showMessage(message, type) {
            var msgBox = $('#eam-message');
            msgBox.removeClass('success error warning info');
            msgBox.addClass(type);
            msgBox.text(message);
            msgBox.show();
            
            setTimeout(function() {
                msgBox.fadeOut();
            }, 5000);
        }
    });
    </script>
</body>
</html>
