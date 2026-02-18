<?php
/**
 * Employee Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class EAM_Employee {
    
    public function __construct() {
        add_action('wp_ajax_eam_add_employee', array($this, 'ajax_add_employee'));
        add_action('wp_ajax_eam_update_employee', array($this, 'ajax_update_employee'));
        add_action('wp_ajax_eam_delete_employee', array($this, 'ajax_delete_employee'));
        add_action('wp_ajax_eam_get_employee', array($this, 'ajax_get_employee'));
    }
    
    public static function add_employee($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_employees';
        
        error_log('EAM: add_employee called with data: ' . print_r($data, true));
        
        // Validate required fields
        if (empty($data['email']) || empty($data['first_name']) || empty($data['last_name'])) {
            error_log('EAM: Missing required fields');
            return array('success' => false, 'message' => 'Required fields are missing: email, first_name, last_name');
        }
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
        if (!$table_exists) {
            error_log('EAM: Table does not exist: ' . $table);
            return array('success' => false, 'message' => 'Database table does not exist. Please go to Attendance > Database Setup to create tables.');
        }
        
        // Check if email already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE email = %s",
            $data['email']
        ));
        
        if ($exists) {
            error_log('EAM: Email already exists: ' . $data['email']);
            return array('success' => false, 'message' => 'Email already exists');
        }
        
        // Generate employee ID if not provided
        if (empty($data['employee_id'])) {
            $data['employee_id'] = self::generate_employee_id();
        }
        
        // Hash password
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            // Generate random password
            $random_password = wp_generate_password(12, false);
            $data['password'] = password_hash($random_password, PASSWORD_DEFAULT);
        }
        
        $insert_data = array(
            'employee_id' => $data['employee_id'],
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'email' => sanitize_email($data['email']),
            'password' => $data['password'],
            'phone' => isset($data['phone']) ? sanitize_text_field($data['phone']) : null,
            'department' => isset($data['department']) ? sanitize_text_field($data['department']) : null,
            'position' => isset($data['position']) ? sanitize_text_field($data['position']) : null,
            'joining_date' => isset($data['joining_date']) ? $data['joining_date'] : date('Y-m-d'),
            'status' => isset($data['status']) ? $data['status'] : 'active'
        );
        
        error_log('EAM: Attempting to insert: ' . print_r($insert_data, true));
        
        $result = $wpdb->insert($table, $insert_data);
        
        if ($result) {
            error_log('EAM: Employee inserted successfully with ID: ' . $wpdb->insert_id);
            return array(
                'success' => true, 
                'message' => 'Employee added successfully',
                'employee_id' => $wpdb->insert_id
            );
        }
        
        error_log('EAM: Failed to insert employee. Error: ' . $wpdb->last_error);
        return array('success' => false, 'message' => 'Failed to add employee: ' . $wpdb->last_error);
    }
    
    public static function update_employee($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_employees';
        
        $update_data = array();
        
        if (isset($data['first_name'])) {
            $update_data['first_name'] = sanitize_text_field($data['first_name']);
        }
        if (isset($data['last_name'])) {
            $update_data['last_name'] = sanitize_text_field($data['last_name']);
        }
        if (isset($data['email'])) {
            $update_data['email'] = sanitize_email($data['email']);
        }
        if (isset($data['phone'])) {
            $update_data['phone'] = sanitize_text_field($data['phone']);
        }
        if (isset($data['department'])) {
            $update_data['department'] = sanitize_text_field($data['department']);
        }
        if (isset($data['position'])) {
            $update_data['position'] = sanitize_text_field($data['position']);
        }
        if (isset($data['status'])) {
            $update_data['status'] = $data['status'];
        }
        if (!empty($data['password'])) {
            $update_data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($update_data)) {
            return array('success' => false, 'message' => 'No data to update');
        }
        
        $result = $wpdb->update($table, $update_data, array('id' => $id));
        
        if ($result !== false) {
            return array('success' => true, 'message' => 'Employee updated successfully');
        }
        
        return array('success' => false, 'message' => 'Failed to update employee');
    }
    
    public static function delete_employee($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_employees';
        
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result) {
            return array('success' => true, 'message' => 'Employee deleted successfully');
        }
        
        return array('success' => false, 'message' => 'Failed to delete employee');
    }
    
    public static function get_employee($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_employees';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ), ARRAY_A);
    }
    
    public static function get_employee_by_email($email) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_employees';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE email = %s",
            $email
        ), ARRAY_A);
    }
    
    public static function get_all_employees($status = 'active') {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_employees';
        
        if ($status === 'all') {
            return $wpdb->get_results("SELECT * FROM $table ORDER BY first_name ASC", ARRAY_A);
        }
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE status = %s ORDER BY first_name ASC",
            $status
        ), ARRAY_A);
    }
    
    public static function verify_credentials($email, $password) {
        $employee = self::get_employee_by_email($email);
        
        if (!$employee) {
            return false;
        }
        
        if ($employee['status'] !== 'active') {
            return false;
        }
        
        if (password_verify($password, $employee['password'])) {
            return $employee;
        }
        
        return false;
    }
    
    private static function generate_employee_id() {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_employees';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $next_id = $count + 1;
        
        return 'EMP' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
    }
    
    public function ajax_add_employee() {
        // Log for debugging
        error_log('EAM: ajax_add_employee called');
        
        try {
            check_ajax_referer('eam_admin_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                error_log('EAM: User not authorized');
                wp_send_json_error('Unauthorized');
                return;
            }
            
            error_log('EAM: Calling add_employee with data: ' . print_r($_POST, true));
            $result = self::add_employee($_POST);
            error_log('EAM: add_employee result: ' . print_r($result, true));
            
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result['message']);
            }
        } catch (Exception $e) {
            error_log('EAM: Exception in ajax_add_employee: ' . $e->getMessage());
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    }
    
    public function ajax_update_employee() {
        check_ajax_referer('eam_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $result = self::update_employee($id, $_POST);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    public function ajax_delete_employee() {
        check_ajax_referer('eam_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $result = self::delete_employee($id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    public function ajax_get_employee() {
        check_ajax_referer('eam_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $employee = self::get_employee($id);
        
        if ($employee) {
            unset($employee['password']);
            wp_send_json_success($employee);
        } else {
            wp_send_json_error('Employee not found');
        }
    }
}
