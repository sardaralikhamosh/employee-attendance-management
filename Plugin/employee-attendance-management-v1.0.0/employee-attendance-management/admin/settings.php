<?php
/**
 * Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$settings = EAM_Settings::get_all_settings();
?>

<div class="wrap eam-settings">
    <h1><?php _e('Attendance Settings', 'employee-attendance'); ?></h1>
    
    <form id="eam-settings-form">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label><?php _e('Office Start Time', 'employee-attendance'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="office_start_time" value="<?php echo esc_attr($settings['office_start_time']); ?>" required>
                        <p class="description"><?php _e('Default: 19:00:00 (7:00 PM)', 'employee-attendance'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php _e('Office End Time', 'employee-attendance'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="office_end_time" value="<?php echo esc_attr($settings['office_end_time']); ?>" required>
                        <p class="description"><?php _e('Default: 04:00:00 (4:00 AM)', 'employee-attendance'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php _e('Grace Time Before (minutes)', 'employee-attendance'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="grace_time_before" value="<?php echo esc_attr($settings['grace_time_before']); ?>" min="0" max="60" required>
                        <p class="description"><?php _e('Employees can check in this many minutes before office start time. Default: 20 minutes', 'employee-attendance'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php _e('Grace Time After (minutes)', 'employee-attendance'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="grace_time_after" value="<?php echo esc_attr($settings['grace_time_after']); ?>" min="0" max="60" required>
                        <p class="description"><?php _e('Employees can check in this many minutes after office start time without being marked late. Default: 20 minutes', 'employee-attendance'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php _e('Auto Checkout After (hours)', 'employee-attendance'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="auto_checkout_hours" value="<?php echo esc_attr($settings['auto_checkout_hours']); ?>" min="1" max="24" required>
                        <p class="description"><?php _e('Automatically checkout employees who forgot to checkout after this many hours. Default: 6 hours', 'employee-attendance'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php _e('Salary Period Start Day', 'employee-attendance'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="salary_period_start_day" value="<?php echo esc_attr($settings['salary_period_start_day']); ?>" min="1" max="28" required>
                        <p class="description"><?php _e('Salary period starts from this day of each month. Default: 27 (27th to 26th)', 'employee-attendance'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label><?php _e('Standard Working Hours per Day', 'employee-attendance'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="standard_working_hours" value="<?php echo esc_attr($settings['standard_working_hours']); ?>" min="1" max="24" required>
                        <p class="description"><?php _e('Standard working hours per day for overtime calculation. Default: 9 hours', 'employee-attendance'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php _e('Save Settings', 'employee-attendance'); ?>
            </button>
        </p>
    </form>
    
    <hr>
    
    <div class="eam-system-info">
        <h2><?php _e('System Information', 'employee-attendance'); ?></h2>
        <table class="widefat">
            <tbody>
                <tr>
                    <td><strong><?php _e('Plugin Version:', 'employee-attendance'); ?></strong></td>
                    <td><?php echo EAM_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('WordPress Version:', 'employee-attendance'); ?></strong></td>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('PHP Version:', 'employee-attendance'); ?></strong></td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Database Version:', 'employee-attendance'); ?></strong></td>
                    <td><?php global $wpdb; echo $wpdb->db_version(); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Attendance Page URL:', 'employee-attendance'); ?></strong></td>
                    <td>
                        <a href="<?php echo home_url('/attendance'); ?>" target="_blank">
                            <?php echo home_url('/attendance'); ?>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <hr>
    
    <div class="eam-cron-info">
        <h2><?php _e('Scheduled Tasks', 'employee-attendance'); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Task', 'employee-attendance'); ?></th>
                    <th><?php _e('Schedule', 'employee-attendance'); ?></th>
                    <th><?php _e('Next Run', 'employee-attendance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Auto Checkout', 'employee-attendance'); ?></td>
                    <td><?php _e('Hourly', 'employee-attendance'); ?></td>
                    <td>
                        <?php 
                        $next = wp_next_scheduled('eam_auto_checkout_cron');
                        echo $next ? date('Y-m-d H:i:s', $next) : __('Not scheduled', 'employee-attendance');
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e('Mark Absences', 'employee-attendance'); ?></td>
                    <td><?php _e('Daily at 2:00 AM', 'employee-attendance'); ?></td>
                    <td>
                        <?php 
                        $next = wp_next_scheduled('eam_mark_absences_cron');
                        echo $next ? date('Y-m-d H:i:s', $next) : __('Not scheduled', 'employee-attendance');
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><?php _e('Generate Monthly Reports', 'employee-attendance'); ?></td>
                    <td><?php _e('Monthly on 27th', 'employee-attendance'); ?></td>
                    <td>
                        <?php 
                        $next = wp_next_scheduled('eam_generate_monthly_reports_cron');
                        echo $next ? date('Y-m-d H:i:s', $next) : __('Not scheduled', 'employee-attendance');
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#eam-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize() + '&action=eam_save_settings&nonce=' + eamAdmin.nonce;
        
        $.ajax({
            url: eamAdmin.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Settings saved successfully!', 'employee-attendance'); ?>');
                } else {
                    alert('<?php _e('Failed to save settings.', 'employee-attendance'); ?>');
                }
            }
        });
    });
});
</script>
