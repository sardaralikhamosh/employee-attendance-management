<?php
/**
 * Plugin Diagnostics Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Test AJAX endpoint
if (isset($_POST['test_ajax'])) {
    check_admin_referer('eam_test_ajax');
    
    $test_result = array();
    
    // Test adding employee via AJAX
    $_POST['first_name'] = 'AJAX';
    $_POST['last_name'] = 'Test';
    $_POST['email'] = 'ajaxtest_' . time() . '@medlinkanalytics.com';
    $_POST['password'] = 'testpass123';
    $_POST['department'] = 'Testing';
    $_POST['position'] = 'Test Position';
    
    // Simulate AJAX request
    $result = EAM_Employee::add_employee($_POST);
    $test_result = $result;
}
?>

<div class="wrap">
    <h1>Plugin Diagnostics</h1>
    
    <div class="eam-diagnostic-section">
        <h2>1. PHP Version</h2>
        <p><strong>Current PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
        <p><strong>Required:</strong> PHP 7.4+</p>
        <?php if (version_compare(PHP_VERSION, '7.4.0', '>=')) : ?>
            <p style="color: green;">✓ PHP version is compatible</p>
        <?php else : ?>
            <p style="color: red;">✗ PHP version is too old. Please upgrade.</p>
        <?php endif; ?>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>2. WordPress Version</h2>
        <p><strong>Current WordPress Version:</strong> <?php echo get_bloginfo('version'); ?></p>
        <p><strong>Required:</strong> WordPress 5.0+</p>
        <?php if (version_compare(get_bloginfo('version'), '5.0', '>=')) : ?>
            <p style="color: green;">✓ WordPress version is compatible</p>
        <?php else : ?>
            <p style="color: red;">✗ WordPress version is too old. Please upgrade.</p>
        <?php endif; ?>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>3. Database Connection</h2>
        <?php 
        global $wpdb;
        $db_test = $wpdb->query("SELECT 1");
        ?>
        <?php if ($db_test !== false) : ?>
            <p style="color: green;">✓ Database connection is working</p>
            <p><strong>Database Name:</strong> <?php echo DB_NAME; ?></p>
            <p><strong>Table Prefix:</strong> <?php echo $wpdb->prefix; ?></p>
        <?php else : ?>
            <p style="color: red;">✗ Database connection failed</p>
        <?php endif; ?>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>4. Plugin Tables</h2>
        <?php
        $tables = array(
            'eam_employees',
            'eam_attendance',
            'eam_leaves',
            'eam_monthly_summary'
        );
        
        $all_exist = true;
        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
            $exists = $wpdb->get_var($query) == $table_name;
            
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                echo "<p style='color: green;'>✓ $table_name ($count records)</p>";
            } else {
                echo "<p style='color: red;'>✗ $table_name (missing)</p>";
                $all_exist = false;
            }
        }
        
        if (!$all_exist) {
            echo '<p><a href="' . admin_url('admin.php?page=eam-db-setup') . '" class="button button-primary">Go to Database Setup</a></p>';
        }
        ?>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>5. AJAX Configuration</h2>
        <p><strong>AJAX URL:</strong> <?php echo admin_url('admin-ajax.php'); ?></p>
        <p><strong>Nonce System:</strong> <?php echo function_exists('wp_create_nonce') ? '✓ Available' : '✗ Not Available'; ?></p>
        
        <h3>Test AJAX Handler</h3>
        <form method="post">
            <?php wp_nonce_field('eam_test_ajax'); ?>
            <button type="submit" name="test_ajax" class="button">Test AJAX Employee Creation</button>
        </form>
        
        <?php if (isset($test_result)) : ?>
            <div style="margin-top: 15px; padding: 10px; background: #f0f0f0; border-left: 4px solid <?php echo $test_result['success'] ? 'green' : 'red'; ?>">
                <strong>Test Result:</strong>
                <pre><?php print_r($test_result); ?></pre>
            </div>
        <?php endif; ?>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>6. Required Classes</h2>
        <?php
        $classes = array(
            'EAM_Database',
            'EAM_Employee',
            'EAM_Attendance',
            'EAM_Reports',
            'EAM_Settings',
            'EAM_Cron'
        );
        
        foreach ($classes as $class) {
            if (class_exists($class)) {
                echo "<p style='color: green;'>✓ $class is loaded</p>";
            } else {
                echo "<p style='color: red;'>✗ $class is missing</p>";
            }
        }
        ?>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>7. Permalinks</h2>
        <p><strong>Permalink Structure:</strong> <?php echo get_option('permalink_structure') ?: 'Default (plain)'; ?></p>
        <p><strong>Attendance URL:</strong> <a href="<?php echo home_url('/attendance'); ?>" target="_blank"><?php echo home_url('/attendance'); ?></a></p>
        <p><em>Note: If /attendance shows 404, go to Settings > Permalinks and click "Save Changes"</em></p>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>8. JavaScript & jQuery</h2>
        <p><strong>jQuery Status:</strong> <span id="jquery-status">Checking...</span></p>
        <p><strong>Admin Scripts:</strong> <span id="admin-scripts-status">Checking...</span></p>
        
        <script>
        jQuery(document).ready(function($) {
            $('#jquery-status').html('<span style="color: green;">✓ jQuery is loaded (v' + $.fn.jquery + ')</span>');
            
            if (typeof eamAdmin !== 'undefined') {
                $('#admin-scripts-status').html('<span style="color: green;">✓ Admin scripts loaded</span>');
            } else {
                $('#admin-scripts-status').html('<span style="color: red;">✗ Admin scripts not loaded</span>');
            }
            
            console.log('EAM Diagnostics - jQuery Version:', $.fn.jquery);
            console.log('EAM Diagnostics - eamAdmin object:', typeof eamAdmin !== 'undefined' ? eamAdmin : 'Not defined');
        });
        </script>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>9. File Permissions</h2>
        <?php
        $upload_dir = wp_upload_dir();
        $writable = is_writable($upload_dir['basedir']);
        ?>
        <p><strong>Uploads Directory:</strong> <?php echo $upload_dir['basedir']; ?></p>
        <?php if ($writable) : ?>
            <p style="color: green;">✓ Uploads directory is writable</p>
        <?php else : ?>
            <p style="color: red;">✗ Uploads directory is not writable</p>
        <?php endif; ?>
    </div>
    
    <hr>
    
    <div class="eam-diagnostic-section">
        <h2>10. Browser Console Check</h2>
        <p>Open your browser's developer console (F12) and check for JavaScript errors.</p>
        <button type="button" class="button" onclick="console.log('EAM: Test console log'); alert('Check console for test message');">Test Console</button>
    </div>
</div>

<style>
.eam-diagnostic-section {
    background: #fff;
    padding: 15px 20px;
    margin: 20px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.eam-diagnostic-section h2 {
    margin-top: 0;
}

.eam-diagnostic-section pre {
    background: #f5f5f5;
    padding: 10px;
    overflow-x: auto;
}
</style>
