<?php
/**
 * Attendance Form Shortcode Template
 * Use shortcode: [employee_attendance]
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="eam-shortcode-container">
    <div class="eam-attendance-form">
        <h3><?php _e('Employee Attendance', 'employee-attendance'); ?></h3>
        
        <div id="eam-shortcode-message" class="eam-message" style="display: none;"></div>
        
        <div id="eam-shortcode-login">
            <form id="eam-shortcode-login-form">
                <div class="eam-field">
                    <label><?php _e('Email', 'employee-attendance'); ?></label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="eam-field">
                    <label><?php _e('Password', 'employee-attendance'); ?></label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="eam-btn"><?php _e('Login', 'employee-attendance'); ?></button>
            </form>
        </div>
        
        <div id="eam-shortcode-status" style="display: none;">
            <div class="eam-info">
                <p><strong><?php _e('Name:', 'employee-attendance'); ?></strong> <span id="sc-employee-name"></span></p>
                <p><strong><?php _e('Status:', 'employee-attendance'); ?></strong> <span id="sc-status"></span></p>
            </div>
            
            <div class="eam-buttons">
                <button id="sc-checkin-btn" class="eam-btn eam-btn-success" style="display: none;">
                    <?php _e('Check In', 'employee-attendance'); ?>
                </button>
                <button id="sc-checkout-btn" class="eam-btn eam-btn-warning" style="display: none;">
                    <?php _e('Check Out', 'employee-attendance'); ?>
                </button>
                <button id="sc-logout-btn" class="eam-btn eam-btn-secondary">
                    <?php _e('Logout', 'employee-attendance'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.eam-shortcode-container {
    max-width: 500px;
    margin: 20px auto;
    padding: 20px;
}

.eam-attendance-form {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
}

.eam-attendance-form h3 {
    margin-top: 0;
    text-align: center;
}

.eam-field {
    margin-bottom: 15px;
}

.eam-field label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.eam-field input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
}

.eam-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    margin-top: 10px;
}

.eam-btn-success {
    background: #28a745;
    color: #fff;
}

.eam-btn-warning {
    background: #ffc107;
    color: #333;
}

.eam-btn-secondary {
    background: #6c757d;
    color: #fff;
}

.eam-info {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.eam-info p {
    margin: 5px 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    var currentEmail = '';
    var currentPassword = '';
    
    $('#eam-shortcode-login-form').on('submit', function(e) {
        e.preventDefault();
        currentEmail = $(this).find('[name="email"]').val();
        currentPassword = $(this).find('[name="password"]').val();
        checkStatus();
    });
    
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
                    $('#eam-shortcode-login').hide();
                    $('#eam-shortcode-status').show();
                    $('#sc-employee-name').text(response.employee_name);
                    
                    if (response.checked_in && !response.checked_out) {
                        $('#sc-status').html('<span style="color: green;">Checked In</span>');
                        $('#sc-checkout-btn').show();
                    } else if (response.checked_out) {
                        $('#sc-status').html('<span style="color: blue;">Checked Out</span>');
                    } else {
                        $('#sc-status').html('<span style="color: gray;">Not Checked In</span>');
                        $('#sc-checkin-btn').show();
                    }
                } else {
                    showMessage(response.message, 'error');
                }
            }
        });
    }
    
    $('#sc-checkin-btn').on('click', function() {
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
                showMessage(response.message, response.success ? 'success' : 'error');
                if (response.success) {
                    setTimeout(function() { location.reload(); }, 1500);
                }
            }
        });
    });
    
    $('#sc-checkout-btn').on('click', function() {
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
                showMessage(response.message, response.success ? 'success' : 'error');
                if (response.success) {
                    setTimeout(function() { location.reload(); }, 1500);
                }
            }
        });
    });
    
    $('#sc-logout-btn').on('click', function() {
        location.reload();
    });
    
    function showMessage(message, type) {
        var msgBox = $('#eam-shortcode-message');
        msgBox.removeClass('success error warning info');
        msgBox.addClass(type);
        msgBox.text(message);
        msgBox.show();
        setTimeout(function() { msgBox.fadeOut(); }, 5000);
    }
});
</script>
