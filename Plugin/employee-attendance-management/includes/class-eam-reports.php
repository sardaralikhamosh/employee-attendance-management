<?php
/**
 * Reports and Analytics Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EAM_Reports {
    
    public function __construct() {
        add_action('wp_ajax_eam_generate_report', array($this, 'ajax_generate_report'));
    }
    
    public static function generate_monthly_summary($employee_id, $month, $year) {
        global $wpdb;
        $table_attendance = $wpdb->prefix . 'eam_attendance';
        $table_summary = $wpdb->prefix . 'eam_monthly_summary';
        
        // Calculate salary period dates (27th to 26th)
        $salary_period_start = date('Y-m-d', strtotime("$year-$month-27"));
        
        // Handle year change
        if ($month == 12) {
            $next_month = 1;
            $next_year = $year + 1;
        } else {
            $next_month = $month + 1;
            $next_year = $year;
        }
        $salary_period_end = date('Y-m-d', strtotime("$next_year-$next_month-26"));
        
        // Count working days (Monday to Friday) in the period
        $total_working_days = self::count_working_days($salary_period_start, $salary_period_end);
        
        // Get attendance data for the period
        $attendance_data = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_days,
                SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leave_days,
                SUM(CASE WHEN check_in_status IN ('late', 'very_late') THEN 1 ELSE 0 END) as late_days,
                SUM(total_hours) as total_hours
            FROM $table_attendance
            WHERE employee_id = %d
            AND date BETWEEN %s AND %s",
            $employee_id,
            $salary_period_start,
            $salary_period_end
        ), ARRAY_A);
        
        $data = $attendance_data[0];
        
        // Calculate overtime (assuming 9 hours standard per day)
        $standard_hours = $data['present_days'] * 9;
        $overtime_hours = max(0, $data['total_hours'] - $standard_hours);
        
        // Check if summary already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_summary 
            WHERE employee_id = %d AND salary_period_start = %s",
            $employee_id,
            $salary_period_start
        ));
        
        $summary_data = array(
            'employee_id' => $employee_id,
            'month' => $month,
            'year' => $year,
            'salary_period_start' => $salary_period_start,
            'salary_period_end' => $salary_period_end,
            'total_working_days' => $total_working_days,
            'present_days' => $data['present_days'],
            'absent_days' => $data['absent_days'],
            'late_days' => $data['late_days'],
            'half_days' => $data['half_days'],
            'leave_days' => $data['leave_days'],
            'total_hours' => round($data['total_hours'], 2),
            'overtime_hours' => round($overtime_hours, 2)
        );
        
        if ($existing) {
            $wpdb->update($table_summary, $summary_data, array('id' => $existing));
            return $existing;
        } else {
            $wpdb->insert($table_summary, $summary_data);
            return $wpdb->insert_id;
        }
    }
    
    public static function generate_all_monthly_summaries($month, $year) {
        $employees = EAM_Employee::get_all_employees('active');
        
        foreach ($employees as $employee) {
            self::generate_monthly_summary($employee['id'], $month, $year);
        }
    }
    
    public static function get_employee_monthly_summary($employee_id, $month, $year) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_monthly_summary';
        
        $salary_period_start = date('Y-m-d', strtotime("$year-$month-27"));
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE employee_id = %d AND salary_period_start = %s",
            $employee_id,
            $salary_period_start
        ), ARRAY_A);
    }
    
    public static function get_all_monthly_summaries($month, $year) {
        global $wpdb;
        $table_summary = $wpdb->prefix . 'eam_monthly_summary';
        $table_employees = $wpdb->prefix . 'eam_employees';
        
        $salary_period_start = date('Y-m-d', strtotime("$year-$month-27"));
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, e.first_name, e.last_name, e.employee_id as emp_id, e.department
            FROM $table_summary s
            LEFT JOIN $table_employees e ON s.employee_id = e.id
            WHERE s.salary_period_start = %s
            ORDER BY e.first_name ASC",
            $salary_period_start
        ), ARRAY_A);
    }
    
    public static function get_dashboard_stats() {
        global $wpdb;
        $table_employees = $wpdb->prefix . 'eam_employees';
        $table_attendance = $wpdb->prefix . 'eam_attendance';
        
        $today = date('Y-m-d');
        
        // Total active employees
        $total_employees = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_employees WHERE status = 'active'"
        );
        
        // Today's attendance
        $today_present = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_attendance 
            WHERE date = %s AND status = 'present'",
            $today
        ));
        
        $today_absent = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_attendance 
            WHERE date = %s AND status = 'absent'",
            $today
        ));
        
        $today_late = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_attendance 
            WHERE date = %s AND check_in_status IN ('late', 'very_late')",
            $today
        ));
        
        // This month stats
        $current_month = date('n');
        $current_year = date('Y');
        $salary_period_start = date('Y-m-d', strtotime("$current_year-$current_month-27"));
        
        if ($current_month == 12) {
            $next_month = 1;
            $next_year = $current_year + 1;
        } else {
            $next_month = $current_month + 1;
            $next_year = $current_year;
        }
        $salary_period_end = date('Y-m-d', strtotime("$next_year-$next_month-26"));
        
        $month_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(DISTINCT employee_id) as active_employees,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as total_present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as total_absent,
                AVG(total_hours) as avg_hours
            FROM $table_attendance
            WHERE date BETWEEN %s AND %s",
            $salary_period_start,
            min($salary_period_end, $today)
        ), ARRAY_A);
        
        return array(
            'total_employees' => $total_employees,
            'today' => array(
                'present' => $today_present,
                'absent' => $today_absent,
                'late' => $today_late,
                'attendance_rate' => $total_employees > 0 ? round(($today_present / $total_employees) * 100, 2) : 0
            ),
            'this_month' => array(
                'total_present' => $month_stats['total_present'],
                'total_absent' => $month_stats['total_absent'],
                'avg_hours' => round($month_stats['avg_hours'], 2)
            )
        );
    }
    
    public static function get_attendance_trends($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        
        $start_date = date('Y-m-d', strtotime("-$days days"));
        $end_date = date('Y-m-d');
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN check_in_status IN ('late', 'very_late') THEN 1 ELSE 0 END) as late
            FROM $table
            WHERE date BETWEEN %s AND %s
            GROUP BY date
            ORDER BY date ASC",
            $start_date,
            $end_date
        ), ARRAY_A);
    }
    
    public static function get_department_wise_stats($month, $year) {
        global $wpdb;
        $table_attendance = $wpdb->prefix . 'eam_attendance';
        $table_employees = $wpdb->prefix . 'eam_employees';
        
        $salary_period_start = date('Y-m-d', strtotime("$year-$month-27"));
        
        if ($month == 12) {
            $next_month = 1;
            $next_year = $year + 1;
        } else {
            $next_month = $month + 1;
            $next_year = $year;
        }
        $salary_period_end = date('Y-m-d', strtotime("$next_year-$next_month-26"));
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT 
                e.department,
                COUNT(DISTINCT e.id) as total_employees,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                AVG(a.total_hours) as avg_hours
            FROM $table_employees e
            LEFT JOIN $table_attendance a ON e.id = a.employee_id 
                AND a.date BETWEEN %s AND %s
            WHERE e.status = 'active'
            GROUP BY e.department
            ORDER BY e.department ASC",
            $salary_period_start,
            $salary_period_end
        ), ARRAY_A);
    }
    
    private static function count_working_days($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end = $end->modify('+1 day');
        
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);
        
        $working_days = 0;
        foreach ($period as $date) {
            $day_of_week = $date->format('N'); // 1 (Monday) to 7 (Sunday)
            if ($day_of_week >= 1 && $day_of_week <= 5) {
                $working_days++;
            }
        }
        
        return $working_days;
    }
    
    public function ajax_generate_report() {
        check_ajax_referer('eam_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $report_type = sanitize_text_field($_POST['report_type']);
        $month = intval($_POST['month']);
        $year = intval($_POST['year']);
        
        switch ($report_type) {
            case 'monthly_summary':
                self::generate_all_monthly_summaries($month, $year);
                $data = self::get_all_monthly_summaries($month, $year);
                wp_send_json_success($data);
                break;
                
            case 'department_stats':
                $data = self::get_department_wise_stats($month, $year);
                wp_send_json_success($data);
                break;
                
            default:
                wp_send_json_error('Invalid report type');
        }
    }
}
