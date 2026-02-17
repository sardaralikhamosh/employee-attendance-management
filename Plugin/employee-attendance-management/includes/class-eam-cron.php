<?php
/**
 * Cron Jobs Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EAM_Cron {
    
    public function __construct() {
        // Hook into scheduled events
        add_action('eam_auto_checkout_cron', array($this, 'run_auto_checkout'));
        add_action('eam_mark_absences_cron', array($this, 'run_mark_absences'));
        add_action('eam_generate_monthly_reports_cron', array($this, 'run_generate_monthly_reports'));
    }
    
    public static function schedule_jobs() {
        // Schedule auto checkout (runs every hour)
        if (!wp_next_scheduled('eam_auto_checkout_cron')) {
            wp_schedule_event(time(), 'hourly', 'eam_auto_checkout_cron');
        }
        
        // Schedule marking absences (runs daily at 2 AM)
        if (!wp_next_scheduled('eam_mark_absences_cron')) {
            wp_schedule_event(strtotime('02:00:00'), 'daily', 'eam_mark_absences_cron');
        }
        
        // Schedule monthly report generation (runs on 27th of each month at 1 AM)
        if (!wp_next_scheduled('eam_generate_monthly_reports_cron')) {
            $next_27th = self::get_next_27th();
            wp_schedule_event(strtotime($next_27th . ' 01:00:00'), 'monthly', 'eam_generate_monthly_reports_cron');
        }
    }
    
    public static function clear_jobs() {
        wp_clear_scheduled_hook('eam_auto_checkout_cron');
        wp_clear_scheduled_hook('eam_mark_absences_cron');
        wp_clear_scheduled_hook('eam_generate_monthly_reports_cron');
    }
    
    public function run_auto_checkout() {
        $count = EAM_Attendance::auto_checkout_forgotten();
        error_log("EAM: Auto-checkout completed for $count records");
    }
    
    public function run_mark_absences() {
        EAM_Attendance::mark_absences();
        error_log("EAM: Absences marked for previous working day");
    }
    
    public function run_generate_monthly_reports() {
        $month = date('n');
        $year = date('Y');
        
        EAM_Reports::generate_all_monthly_summaries($month, $year);
        error_log("EAM: Monthly reports generated for $month/$year");
        
        // Schedule next month's report
        $next_27th = self::get_next_27th();
        wp_schedule_single_event(strtotime($next_27th . ' 01:00:00'), 'eam_generate_monthly_reports_cron');
    }
    
    private static function get_next_27th() {
        $today = date('Y-m-d');
        $current_day = date('j');
        $current_month = date('n');
        $current_year = date('Y');
        
        if ($current_day >= 27) {
            // Next 27th is in the next month
            if ($current_month == 12) {
                return ($current_year + 1) . '-01-27';
            } else {
                return $current_year . '-' . str_pad($current_month + 1, 2, '0', STR_PAD_LEFT) . '-27';
            }
        } else {
            // Next 27th is in the current month
            return $current_year . '-' . str_pad($current_month, 2, '0', STR_PAD_LEFT) . '-27';
        }
    }
}
