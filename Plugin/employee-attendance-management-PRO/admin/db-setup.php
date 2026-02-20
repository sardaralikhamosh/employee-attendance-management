<?php
/**
 * Database Setup and Repair Tool
 * Visit: yourdomain.com/wp-admin/admin.php?page=eam-db-setup
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if tables exist
global $wpdb;
$tables_to_check = array(
    'eam_employees',
    'eam_attendance',
    'eam_leaves',
    'eam_monthly_summary'
);

$missing_tables = array();
foreach ($tables_to_check as $table) {
    $table_name = $wpdb->prefix . $table;
    $query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
    if ($wpdb->get_var($query) != $table_name) {
        $missing_tables[] = $table;
    }
}

if (isset($_POST['setup_database'])) {
    check_admin_referer('eam_db_setup');
    
    // Create tables
    EAM_Database::create_tables();
    
    echo '<div class="notice notice-success"><p><strong>Database tables created successfully!</strong></p></div>';
    
    // Recheck
    $missing_tables = array();
    foreach ($tables_to_check as $table) {
        $table_name = $wpdb->prefix . $table;
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
        if ($wpdb->get_var($query) != $table_name) {
            $missing_tables[] = $table;
        }
    }
}

if (isset($_POST['force_recreate_employees'])) {
    check_admin_referer('eam_force_recreate');
    
    // Drop and recreate employees table
    $table_name = $wpdb->prefix . 'eam_employees';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    EAM_Database::create_tables();
    
    echo '<div class="notice notice-success"><p><strong>Employees table recreated successfully!</strong></p></div>';
    
    // Recheck
    $missing_tables = array();
    foreach ($tables_to_check as $table) {
        $table_name = $wpdb->prefix . $table;
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
        if ($wpdb->get_var($query) != $table_name) {
            $missing_tables[] = $table;
        }
    }
}
?>

<div class="wrap">
    <h1>Database Setup & Repair</h1>
    
    <div class="eam-db-status">
        <h2>Database Status</h2>
        
        <table class="widefat">
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tables_to_check as $table) : ?>
                    <?php 
                    $table_name = $wpdb->prefix . $table;
                    $query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
                    $exists = $wpdb->get_var($query) == $table_name;
                    ?>
                    <tr>
                        <td><code><?php echo $table_name; ?></code></td>
                        <td>
                            <?php if ($exists) : ?>
                                <span style="color: green;">✓ Exists</span>
                                <?php 
                                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                                echo " ($count records)";
                                ?>
                            <?php else : ?>
                                <span style="color: red;">✗ Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (!empty($missing_tables)) : ?>
            <div class="notice notice-error" style="margin-top: 20px;">
                <p><strong>Warning:</strong> Some database tables are missing!</p>
            </div>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('eam_db_setup'); ?>
                <button type="submit" name="setup_database" class="button button-primary button-large">
                    Create Missing Tables
                </button>
            </form>
            
            <?php if (in_array('eam_employees', $missing_tables)) : ?>
                <div style="margin-top: 20px;">
                    <p><strong>Alternative:</strong> If the employees table keeps failing, you can force recreate it:</p>
                    <form method="post">
                        <?php wp_nonce_field('eam_force_recreate'); ?>
                        <button type="submit" name="force_recreate_employees" class="button button-secondary" onclick="return confirm('This will delete and recreate the employees table. Are you sure?');">
                            Force Recreate Employees Table
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="notice notice-success" style="margin-top: 20px;">
                <p><strong>All database tables exist!</strong></p>
            </div>
        <?php endif; ?>
    </div>
    
    <hr>
    
    <div class="eam-test-employee">
        <h2>Quick Test - Add Test Employee</h2>
        <p>Click the button below to add a test employee to verify everything is working:</p>
        
        <form method="post">
            <?php wp_nonce_field('eam_test_employee'); ?>
            <button type="submit" name="add_test_employee" class="button">Add Test Employee</button>
        </form>
        
        <?php
        if (isset($_POST['add_test_employee'])) {
            check_admin_referer('eam_test_employee');
            
            $test_data = array(
                'first_name' => 'Test',
                'last_name' => 'Employee',
                'email' => 'test@medlinkanalytics.com',
                'password' => 'test123456',
                'department' => 'Testing',
                'position' => 'Test User',
                'status' => 'active'
            );
            
            $result = EAM_Employee::add_employee($test_data);
            
            if ($result['success']) {
                echo '<div class="notice notice-success"><p><strong>Test employee created!</strong><br>';
                echo 'Email: test@medlinkanalytics.com<br>';
                echo 'Password: test123456</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Error: ' . $result['message'] . '</p></div>';
            }
        }
        ?>
    </div>
</div>

<style>
.eam-db-status {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin: 20px 0;
}

.eam-test-employee {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin: 20px 0;
}
</style>
