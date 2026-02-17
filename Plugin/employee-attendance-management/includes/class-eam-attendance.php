<?php
/**
 * Attendance Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EAM_Attendance {
    
    public function __construct() {
        add_action('wp_ajax_eam_mark_attendance', array($this, 'ajax_mark_attendance'));
    }
    
    public static function check_in($email, $password) {
        // Verify credentials
        $employee = EAM_Employee::verify_credentials($email, $password);
        
        if (!$employee) {
            return array('success' => false, 'message' => 'Invalid credentials');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        $today = date('Y-m-d');
        $now = current_time('mysql');
        
        // Check if already checked in today
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE employee_id = %d AND date = %s",
            $employee['id'],
            $today
        ), ARRAY_A);
        
        if ($existing && $existing['check_in_time']) {
            return array(
                'success' => false, 
                'message' => 'Already checked in today',
                'check_in_time' => $existing['check_in_time']
            );
        }
        
        // Get office settings
        $office_start = get_option('eam_office_start_time', '19:00:00'); // 7:00 PM
        $grace_before = get_option('eam_grace_time_before', 20); // 20 minutes
        
        // Calculate check-in status
        $check_in_status = self::calculate_check_in_status($now, $office_start, $grace_before);
        
        // Get IP address
        $ip_address = self::get_client_ip();
        
        // Insert or update attendance record
        if ($existing) {
            $result = $wpdb->update(
                $table,
                array(
                    'check_in_time' => $now,
                    'check_in_status' => $check_in_status,
                    'status' => 'present',
                    'ip_address' => $ip_address,
                    'updated_at' => $now
                ),
                array(
                    'employee_id' => $employee['id'],
                    'date' => $today
                )
            );
        } else {
            $result = $wpdb->insert(
                $table,
                array(
                    'employee_id' => $employee['id'],
                    'date' => $today,
                    'check_in_time' => $now,
                    'check_in_status' => $check_in_status,
                    'status' => 'present',
                    'ip_address' => $ip_address
                )
            );
        }
        
        if ($result !== false) {
            return array(
                'success' => true,
                'message' => 'Check-in successful',
                'check_in_time' => $now,
                'status' => $check_in_status,
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name']
            );
        }
        
        return array('success' => false, 'message' => 'Failed to check in');
    }
    
    public static function check_out($email, $password) {
        // Verify credentials
        $employee = EAM_Employee::verify_credentials($email, $password);
        
        if (!$employee) {
            return array('success' => false, 'message' => 'Invalid credentials');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        $today = date('Y-m-d');
        $now = current_time('mysql');
        
        // Get today's attendance record
        $attendance = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE employee_id = %d AND date = %s",
            $employee['id'],
            $today
        ), ARRAY_A);
        
        if (!$attendance || !$attendance['check_in_time']) {
            return array('success' => false, 'message' => 'No check-in record found');
        }
        
        if ($attendance['check_out_time']) {
            return array(
                'success' => false, 
                'message' => 'Already checked out',
                'check_out_time' => $attendance['check_out_time']
            );
        }
        
        // Calculate total hours
        $check_in = new DateTime($attendance['check_in_time']);
        $check_out = new DateTime($now);
        $interval = $check_in->diff($check_out);
        $total_hours = $interval->h + ($interval->i / 60) + ($interval->days * 24);
        
        // Update attendance record
        $result = $wpdb->update(
            $table,
            array(
                'check_out_time' => $now,
                'check_out_status' => 'normal',
                'total_hours' => round($total_hours, 2),
                'updated_at' => $now
            ),
            array('id' => $attendance['id'])
        );
        
        if ($result !== false) {
            return array(
                'success' => true,
                'message' => 'Check-out successful',
                'check_out_time' => $now,
                'total_hours' => round($total_hours, 2),
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name']
            );
        }
        
        return array('success' => false, 'message' => 'Failed to check out');
    }
    
    public static function get_status($email, $password) {
        // Verify credentials
        $employee = EAM_Employee::verify_credentials($email, $password);
        
        if (!$employee) {
            return array('success' => false, 'message' => 'Invalid credentials');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        $today = date('Y-m-d');
        
        // Get today's attendance
        $attendance = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE employee_id = %d AND date = %s",
            $employee['id'],
            $today
        ), ARRAY_A);
        
        $status = array(
            'success' => true,
            'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
            'employee_id' => $employee['employee_id'],
            'date' => $today,
            'checked_in' => false,
            'checked_out' => false
        );
        
        if ($attendance) {
            if ($attendance['check_in_time']) {
                $status['checked_in'] = true;
                $status['check_in_time'] = $attendance['check_in_time'];
                $status['check_in_status'] = $attendance['check_in_status'];
            }
            if ($attendance['check_out_time']) {
                $status['checked_out'] = true;
                $status['check_out_time'] = $attendance['check_out_time'];
                $status['total_hours'] = $attendance['total_hours'];
            }
        }
        
        return $status;
    }
    
    public static function auto_checkout_forgotten() {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        
        // Get attendance records with check-in but no check-out older than 6 hours
        $six_hours_ago = date('Y-m-d H:i:s', strtotime('-6 hours'));
        
        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE check_in_time IS NOT NULL 
            AND check_out_time IS NULL 
            AND check_in_time < %s",
            $six_hours_ago
        ), ARRAY_A);
        
        foreach ($records as $record) {
            $check_in = new DateTime($record['check_in_time']);
            $auto_checkout = clone $check_in;
            $auto_checkout->modify('+6 hours');
            
            $total_hours = 6.00;
            
            $wpdb->update(
                $table,
                array(
                    'check_out_time' => $auto_checkout->format('Y-m-d H:i:s'),
                    'check_out_status' => 'forgotten',
                    'total_hours' => $total_hours,
                    'notes' => 'Auto checkout after 6 hours',
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $record['id'])
            );
        }
        
        return count($records);
    }
    
    public static function mark_absences() {
        global $wpdb;
        $table_attendance = $wpdb->prefix . 'eam_attendance';
        $table_employees = $wpdb->prefix . 'eam_employees';
        
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $day_of_week = date('N', strtotime($yesterday)); // 1 (Monday) to 7 (Sunday)
        
        // Only mark absences for working days (Monday to Friday)
        if ($day_of_week >= 1 && $day_of_week <= 5) {
            // Get all active employees
            $employees = $wpdb->get_results(
                "SELECT id FROM $table_employees WHERE status = 'active'",
                ARRAY_A
            );
            
            foreach ($employees as $employee) {
                // Check if attendance record exists
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_attendance 
                    WHERE employee_id = %d AND date = %s",
                    $employee['id'],
                    $yesterday
                ));
                
                // If no record exists, mark as absent
                if (!$exists) {
                    $wpdb->insert(
                        $table_attendance,
                        array(
                            'employee_id' => $employee['id'],
                            'date' => $yesterday,
                            'status' => 'absent',
                            'notes' => 'Marked absent automatically'
                        )
                    );
                }
            }
        }
    }
    
    private static function calculate_check_in_status($check_in_time, $office_start, $grace_before) {
        $check_in = new DateTime($check_in_time);
        $office_start_time = new DateTime(date('Y-m-d') . ' ' . $office_start);
        
        // Adjust for grace period (20 minutes before)
        $grace_start = clone $office_start_time;
        $grace_start->modify("-{$grace_before} minutes");
        
        // Very late threshold (20 minutes after office start)
        $very_late = clone $office_start_time;
        $very_late->modify("+20 minutes");
        
        if ($check_in <= $office_start_time) {
            return 'on_time';
        } elseif ($check_in <= $very_late) {
            return 'late';
        } else {
            return 'very_late';
        }
    }
    
    public static function get_attendance_by_date_range($employee_id, $start_date, $end_date) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE employee_id = %d 
            AND date BETWEEN %s AND %s
            ORDER BY date DESC",
            $employee_id,
            $start_date,
            $end_date
        ), ARRAY_A);
    }
    
    public static function get_all_attendance_by_date($date) {
        global $wpdb;
        $table_attendance = $wpdb->prefix . 'eam_attendance';
        $table_employees = $wpdb->prefix . 'eam_employees';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, e.first_name, e.last_name, e.employee_id as emp_id, e.department
            FROM $table_attendance a
            LEFT JOIN $table_employees e ON a.employee_id = e.id
            WHERE a.date = %s
            ORDER BY e.first_name ASC",
            $date
        ), ARRAY_A);
    }
    
    private static function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public function ajax_mark_attendance() {
        check_ajax_referer('eam_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        // Admin can manually mark attendance
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        
        $employee_id = intval($_POST['employee_id']);
        $date = sanitize_text_field($_POST['date']);
        $status = sanitize_text_field($_POST['status']);
        
        $data = array(
            'employee_id' => $employee_id,
            'date' => $date,
            'status' => $status
        );
        
        // Check if record exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE employee_id = %d AND date = %s",
            $employee_id,
            $date
        ));
        
        if ($exists) {
            $result = $wpdb->update($table, array('status' => $status), array('id' => $exists));
        } else {
            $result = $wpdb->insert($table, $data);
        }
        
        if ($result !== false) {
            wp_send_json_success('Attendance marked successfully');
        } else {
            wp_send_json_error('Failed to mark attendance');
        }
    }
}
