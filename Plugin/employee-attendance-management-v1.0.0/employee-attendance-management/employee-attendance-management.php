<?php
/**
 * Plugin Name: Employee Attendance Management System
 * Plugin URI: https://github.com/sardaralikhamosh/employee-attendance-management
 * Description: Complete employee attendance management system with check-in/out, automated tracking, HR analytics, and monthly salary period reports. Built for MedLink Analytics.
 * Version: 1.0.0
 * Author: Sardar Ali Khamosh
 * Author URI: https://sardaralikhamosh.github.io
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: employee-attendance
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Tested up to: 6.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EAM_VERSION', '1.0.0');
define('EAM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EAM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once EAM_PLUGIN_DIR . 'includes/class-eam-database.php';
require_once EAM_PLUGIN_DIR . 'includes/class-eam-employee.php';
require_once EAM_PLUGIN_DIR . 'includes/class-eam-attendance.php';
require_once EAM_PLUGIN_DIR . 'includes/class-eam-reports.php';
require_once EAM_PLUGIN_DIR . 'includes/class-eam-settings.php';
require_once EAM_PLUGIN_DIR . 'includes/class-eam-cron.php';

// Main plugin class
class Employee_Attendance_Management {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // Register shortcodes
        add_shortcode('employee_attendance', array($this, 'attendance_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_eam_checkin', array($this, 'ajax_checkin'));
        add_action('wp_ajax_nopriv_eam_checkin', array($this, 'ajax_checkin'));
        add_action('wp_ajax_eam_checkout', array($this, 'ajax_checkout'));
        add_action('wp_ajax_nopriv_eam_checkout', array($this, 'ajax_checkout'));
        add_action('wp_ajax_eam_get_status', array($this, 'ajax_get_status'));
        add_action('wp_ajax_nopriv_eam_get_status', array($this, 'ajax_get_status'));
        
        // Custom rewrite rules for /attendance
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_attendance_page'));
    }
    
    public function activate() {
        // Create database tables
        EAM_Database::create_tables();
        
        // Set default settings
        EAM_Settings::set_defaults();
        
        // Schedule cron jobs
        EAM_Cron::schedule_jobs();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clear scheduled cron jobs
        EAM_Cron::clear_jobs();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function init() {
        // Initialize classes
        new EAM_Employee();
        new EAM_Attendance();
        new EAM_Reports();
        new EAM_Settings();
        new EAM_Cron();
    }
    
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Attendance Management', 'employee-attendance'),
            __('Attendance', 'employee-attendance'),
            'manage_options',
            'eam-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-clock',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            'eam-dashboard',
            __('Dashboard', 'employee-attendance'),
            __('Dashboard', 'employee-attendance'),
            'manage_options',
            'eam-dashboard',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'eam-dashboard',
            __('Employees', 'employee-attendance'),
            __('Employees', 'employee-attendance'),
            'manage_options',
            'eam-employees',
            array($this, 'employees_page')
        );
        
        add_submenu_page(
            'eam-dashboard',
            __('Attendance Records', 'employee-attendance'),
            __('Attendance Records', 'employee-attendance'),
            'manage_options',
            'eam-records',
            array($this, 'records_page')
        );
        
        add_submenu_page(
            'eam-dashboard',
            __('Reports', 'employee-attendance'),
            __('Reports', 'employee-attendance'),
            'manage_options',
            'eam-reports',
            array($this, 'reports_page')
        );
        
        add_submenu_page(
            'eam-dashboard',
            __('Settings', 'employee-attendance'),
            __('Settings', 'employee-attendance'),
            'manage_options',
            'eam-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'eam-dashboard',
            __('Database Setup', 'employee-attendance'),
            __('Database Setup', 'employee-attendance'),
            'manage_options',
            'eam-db-setup',
            array($this, 'db_setup_page')
        );
        
        add_submenu_page(
            'eam-dashboard',
            __('Diagnostics', 'employee-attendance'),
            __('Diagnostics', 'employee-attendance'),
            'manage_options',
            'eam-diagnostics',
            array($this, 'diagnostics_page')
        );
        
        add_submenu_page(
            'eam-dashboard',
            __('Direct Add (Test)', 'employee-attendance'),
            __('Direct Add (Test)', 'employee-attendance'),
            'manage_options',
            'eam-direct-add',
            array($this, 'direct_add_page')
        );
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'eam-') !== false) {
            wp_enqueue_style('eam-admin-css', EAM_PLUGIN_URL . 'assets/css/admin.css', array(), EAM_VERSION);
            wp_enqueue_script('eam-admin-js', EAM_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-datepicker'), EAM_VERSION, true);
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.9.1', true);
            
            wp_localize_script('eam-admin-js', 'eamAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eam_admin_nonce')
            ));
        }
    }
    
    public function frontend_enqueue_scripts() {
        if (is_page() || get_query_var('attendance_page')) {
            wp_enqueue_style('eam-frontend-css', EAM_PLUGIN_URL . 'assets/css/frontend.css', array(), EAM_VERSION);
            wp_enqueue_script('eam-frontend-js', EAM_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), EAM_VERSION, true);
            
            wp_localize_script('eam-frontend-js', 'eamFrontend', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eam_frontend_nonce')
            ));
        }
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^attendance/?$', 'index.php?attendance_page=1', 'top');
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'attendance_page';
        return $vars;
    }
    
    public function handle_attendance_page() {
        if (get_query_var('attendance_page')) {
            include EAM_PLUGIN_DIR . 'templates/attendance-page.php';
            exit;
        }
    }
    
    public function attendance_shortcode($atts) {
        ob_start();
        include EAM_PLUGIN_DIR . 'templates/attendance-form.php';
        return ob_get_clean();
    }
    
    public function ajax_checkin() {
        check_ajax_referer('eam_frontend_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        
        $result = EAM_Attendance::check_in($email, $password);
        
        wp_send_json($result);
    }
    
    public function ajax_checkout() {
        check_ajax_referer('eam_frontend_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        
        $result = EAM_Attendance::check_out($email, $password);
        
        wp_send_json($result);
    }
    
    public function ajax_get_status() {
        check_ajax_referer('eam_frontend_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        
        $result = EAM_Attendance::get_status($email, $password);
        
        wp_send_json($result);
    }
    
    public function dashboard_page() {
        include EAM_PLUGIN_DIR . 'admin/dashboard.php';
    }
    
    public function employees_page() {
        include EAM_PLUGIN_DIR . 'admin/employees.php';
    }
    
    public function records_page() {
        include EAM_PLUGIN_DIR . 'admin/records.php';
    }
    
    public function reports_page() {
        include EAM_PLUGIN_DIR . 'admin/reports.php';
    }
    
    public function settings_page() {
        include EAM_PLUGIN_DIR . 'admin/settings.php';
    }
    
    public function db_setup_page() {
        include EAM_PLUGIN_DIR . 'admin/db-setup.php';
    }
    
    public function diagnostics_page() {
        include EAM_PLUGIN_DIR . 'admin/diagnostics.php';
    }
    
    public function direct_add_page() {
        include EAM_PLUGIN_DIR . 'admin/direct-add.php';
    }
}

// Initialize plugin
function eam_init() {
    return Employee_Attendance_Management::get_instance();
}

add_action('plugins_loaded', 'eam_init');
