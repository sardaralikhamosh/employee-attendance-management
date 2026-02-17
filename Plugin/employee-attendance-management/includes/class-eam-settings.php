<?php
/**
 * Settings Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EAM_Settings {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_eam_save_settings', array($this, 'ajax_save_settings'));
    }
    
    public static function set_defaults() {
        $defaults = array(
            'eam_office_start_time' => '19:00:00',  // 7:00 PM
            'eam_office_end_time' => '04:00:00',    // 4:00 AM
            'eam_grace_time_before' => 20,          // 20 minutes
            'eam_grace_time_after' => 20,           // 20 minutes
            'eam_auto_checkout_hours' => 6,         // 6 hours
            'eam_working_days' => array(1, 2, 3, 4, 5), // Monday to Friday
            'eam_salary_period_start_day' => 27,    // 27th of each month
            'eam_standard_working_hours' => 9,      // 9 hours per day
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }
    
    public function register_settings() {
        // Office timing settings
        register_setting('eam_settings', 'eam_office_start_time');
        register_setting('eam_settings', 'eam_office_end_time');
        register_setting('eam_settings', 'eam_grace_time_before');
        register_setting('eam_settings', 'eam_grace_time_after');
        register_setting('eam_settings', 'eam_auto_checkout_hours');
        register_setting('eam_settings', 'eam_working_days');
        register_setting('eam_settings', 'eam_salary_period_start_day');
        register_setting('eam_settings', 'eam_standard_working_hours');
    }
    
    public static function get_all_settings() {
        return array(
            'office_start_time' => get_option('eam_office_start_time', '19:00:00'),
            'office_end_time' => get_option('eam_office_end_time', '04:00:00'),
            'grace_time_before' => get_option('eam_grace_time_before', 20),
            'grace_time_after' => get_option('eam_grace_time_after', 20),
            'auto_checkout_hours' => get_option('eam_auto_checkout_hours', 6),
            'working_days' => get_option('eam_working_days', array(1, 2, 3, 4, 5)),
            'salary_period_start_day' => get_option('eam_salary_period_start_day', 27),
            'standard_working_hours' => get_option('eam_standard_working_hours', 9),
        );
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('eam_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $settings = array(
            'eam_office_start_time' => sanitize_text_field($_POST['office_start_time']),
            'eam_office_end_time' => sanitize_text_field($_POST['office_end_time']),
            'eam_grace_time_before' => intval($_POST['grace_time_before']),
            'eam_grace_time_after' => intval($_POST['grace_time_after']),
            'eam_auto_checkout_hours' => intval($_POST['auto_checkout_hours']),
            'eam_salary_period_start_day' => intval($_POST['salary_period_start_day']),
            'eam_standard_working_hours' => intval($_POST['standard_working_hours']),
        );
        
        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        wp_send_json_success('Settings saved successfully');
    }
}
