<?php
/**
 * Database Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EAM_Database {
    
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Employees table
        $table_employees = $wpdb->prefix . 'eam_employees';
        $sql_employees = "CREATE TABLE IF NOT EXISTS $table_employees (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            employee_id varchar(50) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            password varchar(255) NOT NULL,
            phone varchar(20) DEFAULT NULL,
            department varchar(100) DEFAULT NULL,
            position varchar(100) DEFAULT NULL,
            joining_date date DEFAULT NULL,
            status enum('active','inactive','suspended') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_email (email),
            KEY employee_id (employee_id)
        ) $charset_collate;";
        
        // Attendance records table
        $table_attendance = $wpdb->prefix . 'eam_attendance';
        $sql_attendance = "CREATE TABLE IF NOT EXISTS $table_attendance (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            employee_id bigint(20) NOT NULL,
            date date NOT NULL,
            check_in_time datetime DEFAULT NULL,
            check_out_time datetime DEFAULT NULL,
            check_in_status enum('on_time','late','very_late') DEFAULT NULL,
            check_out_status enum('normal','auto','forgotten') DEFAULT 'normal',
            total_hours decimal(5,2) DEFAULT NULL,
            status enum('present','absent','half_day','leave') DEFAULT 'present',
            notes text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY employee_id (employee_id),
            KEY date (date),
            KEY status (status),
            UNIQUE KEY employee_date (employee_id, date)
        ) $charset_collate;";
        
        // Leave requests table
        $table_leaves = $wpdb->prefix . 'eam_leaves';
        $sql_leaves = "CREATE TABLE IF NOT EXISTS $table_leaves (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            employee_id bigint(20) NOT NULL,
            leave_type enum('sick','casual','annual','unpaid') NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            total_days int(11) NOT NULL,
            reason text DEFAULT NULL,
            status enum('pending','approved','rejected') DEFAULT 'pending',
            approved_by bigint(20) DEFAULT NULL,
            approved_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY employee_id (employee_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Monthly summary table
        $table_summary = $wpdb->prefix . 'eam_monthly_summary';
        $sql_summary = "CREATE TABLE IF NOT EXISTS $table_summary (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            employee_id bigint(20) NOT NULL,
            month int(2) NOT NULL,
            year int(4) NOT NULL,
            salary_period_start date NOT NULL,
            salary_period_end date NOT NULL,
            total_working_days int(11) DEFAULT 0,
            present_days int(11) DEFAULT 0,
            absent_days int(11) DEFAULT 0,
            late_days int(11) DEFAULT 0,
            half_days int(11) DEFAULT 0,
            leave_days int(11) DEFAULT 0,
            total_hours decimal(8,2) DEFAULT 0,
            overtime_hours decimal(8,2) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY employee_id (employee_id),
            KEY month_year (month, year),
            UNIQUE KEY employee_period (employee_id, salary_period_start)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_employees);
        dbDelta($sql_attendance);
        dbDelta($sql_leaves);
        dbDelta($sql_summary);
    }
    
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'eam_employees',
            $wpdb->prefix . 'eam_attendance',
            $wpdb->prefix . 'eam_leaves',
            $wpdb->prefix . 'eam_monthly_summary'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
}
