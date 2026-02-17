<?php
/**
 * EAM Setup Tool - Upload this file to your WordPress root directory
 * Access via: https://medlinkanalytics.com/blog/eam-setup.php
 * DELETE THIS FILE after setup is complete!
 */

// Load WordPress
$wp_load_path = dirname(__FILE__) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    // Try one level up
    $wp_load_path = dirname(dirname(__FILE__)) . '/wp-load.php';
}
if (!file_exists($wp_load_path)) {
    die('ERROR: Could not find wp-load.php. Place this file in the same folder as wp-load.php');
}
require_once($wp_load_path);

// Security check
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('ERROR: You must be logged in as admin to use this tool. <a href="' . wp_login_url() . '">Login here</a>');
}

global $wpdb;
$prefix = $wpdb->prefix;

$action = isset($_POST['action']) ? $_POST['action'] : '';
$message = '';
$error = '';

// ============================================================
// ACTION: CREATE TABLES
// ============================================================
if ($action === 'create_tables') {
    $results = array();

    // Drop employees table and recreate fresh
    $wpdb->query("DROP TABLE IF EXISTS {$prefix}eam_employees");

    $sql1 = "CREATE TABLE {$prefix}eam_employees (
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
        status varchar(20) DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_email (email),
        KEY idx_employee_id (employee_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $r1 = $wpdb->query($sql1);
    $results[] = 'eam_employees: ' . ($r1 !== false ? '✓ Created' : '✗ Failed: ' . $wpdb->last_error);

    $sql2 = "CREATE TABLE IF NOT EXISTS {$prefix}eam_attendance (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        employee_id bigint(20) NOT NULL,
        date date NOT NULL,
        check_in_time datetime DEFAULT NULL,
        check_out_time datetime DEFAULT NULL,
        check_in_status varchar(20) DEFAULT NULL,
        check_out_status varchar(20) DEFAULT 'normal',
        total_hours decimal(5,2) DEFAULT NULL,
        status varchar(20) DEFAULT 'present',
        notes text DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_employee_id (employee_id),
        KEY idx_date (date),
        UNIQUE KEY employee_date (employee_id, date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $r2 = $wpdb->query($sql2);
    $results[] = 'eam_attendance: ' . ($r2 !== false ? '✓ Created/Exists' : '✗ Failed: ' . $wpdb->last_error);

    $sql3 = "CREATE TABLE IF NOT EXISTS {$prefix}eam_leaves (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        employee_id bigint(20) NOT NULL,
        leave_type varchar(20) NOT NULL,
        start_date date NOT NULL,
        end_date date NOT NULL,
        total_days int(11) NOT NULL,
        reason text DEFAULT NULL,
        status varchar(20) DEFAULT 'pending',
        approved_by bigint(20) DEFAULT NULL,
        approved_at datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_employee_id (employee_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $r3 = $wpdb->query($sql3);
    $results[] = 'eam_leaves: ' . ($r3 !== false ? '✓ Created/Exists' : '✗ Failed: ' . $wpdb->last_error);

    $sql4 = "CREATE TABLE IF NOT EXISTS {$prefix}eam_monthly_summary (
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
        PRIMARY KEY (id),
        KEY idx_employee_id (employee_id),
        UNIQUE KEY employee_period (employee_id, salary_period_start)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $r4 = $wpdb->query($sql4);
    $results[] = 'eam_monthly_summary: ' . ($r4 !== false ? '✓ Created/Exists' : '✗ Failed: ' . $wpdb->last_error);

    $message = implode('<br>', $results);
}

// ============================================================
// ACTION: ADD EMPLOYEE
// ============================================================
if ($action === 'add_employee') {
    $first_name  = sanitize_text_field($_POST['first_name']);
    $last_name   = sanitize_text_field($_POST['last_name']);
    $email       = sanitize_email($_POST['email']);
    $phone       = sanitize_text_field($_POST['phone']);
    $department  = sanitize_text_field($_POST['department']);
    $position    = sanitize_text_field($_POST['position']);
    $joining     = sanitize_text_field($_POST['joining_date']);
    $password    = $_POST['password'];
    $emp_status  = sanitize_text_field($_POST['emp_status']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = 'First Name, Last Name, Email and Password are required.';
    } else {
        // Check table exists
        $table = $prefix . 'eam_employees';
        $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
        if (!$exists) {
            $error = 'Table does not exist! Create tables first.';
        } else {
            // Check duplicate email
            $dup = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE email = %s", $email));
            if ($dup) {
                $error = 'Email already exists: ' . $email;
            } else {
                // Auto employee ID
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                $emp_id = 'EMP' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $inserted = $wpdb->insert($table, array(
                    'employee_id'  => $emp_id,
                    'first_name'   => $first_name,
                    'last_name'    => $last_name,
                    'email'        => $email,
                    'password'     => $hashed,
                    'phone'        => $phone,
                    'department'   => $department,
                    'position'     => $position,
                    'joining_date' => $joining ?: date('Y-m-d'),
                    'status'       => $emp_status ?: 'active',
                ));

                if ($inserted) {
                    $message = '✓ Employee added successfully! ID: ' . $emp_id . ' | Name: ' . $first_name . ' ' . $last_name;
                } else {
                    $error = 'Insert failed: ' . $wpdb->last_error;
                }
            }
        }
    }
}

// ============================================================
// ACTION: DELETE EMPLOYEE
// ============================================================
if ($action === 'delete_employee' && isset($_POST['emp_db_id'])) {
    $del_id = intval($_POST['emp_db_id']);
    $deleted = $wpdb->delete($prefix . 'eam_employees', array('id' => $del_id));
    $message = $deleted ? '✓ Employee deleted.' : '✗ Delete failed: ' . $wpdb->last_error;
}

// ============================================================
// FETCH current state
// ============================================================
$tables = array('eam_employees','eam_attendance','eam_leaves','eam_monthly_summary');
$table_status = array();
foreach ($tables as $t) {
    $tn = $prefix . $t;
    $ex = $wpdb->get_var("SHOW TABLES LIKE '$tn'") == $tn;
    $cnt = $ex ? $wpdb->get_var("SELECT COUNT(*) FROM $tn") : 0;
    $table_status[$t] = array('exists' => $ex, 'count' => $cnt);
}

$employees = array();
if ($table_status['eam_employees']['exists']) {
    $employees = $wpdb->get_results("SELECT * FROM {$prefix}eam_employees ORDER BY id DESC", ARRAY_A);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EAM Setup Tool</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; background: #f1f1f1; color: #333; }
.wrap { max-width: 1100px; margin: 30px auto; padding: 0 15px; }
h1 { background: #0073aa; color: #fff; padding: 15px 20px; border-radius: 6px 6px 0 0; margin-bottom: 0; }
.warning-box { background: #fff3cd; border: 1px solid #ffc107; padding: 12px 20px; border-radius: 0; font-size: 13px; }
.warning-box strong { color: #856404; }
.card { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 20px; margin: 20px 0; }
.card h2 { font-size: 18px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #0073aa; color: #0073aa; }
.msg-ok  { background: #d4edda; border: 1px solid #28a745; color: #155724; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; }
.msg-err { background: #f8d7da; border: 1px solid #dc3545; color: #721c24; padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; }
table.status { width: 100%; border-collapse: collapse; }
table.status td, table.status th { padding: 8px 12px; border: 1px solid #ddd; font-size: 14px; }
table.status th { background: #f5f5f5; font-weight: bold; }
.badge-ok  { color: #155724; background: #d4edda; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
.badge-err { color: #721c24; background: #f8d7da; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-group label { font-weight: bold; font-size: 13px; }
.form-group input, .form-group select { padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
.form-group input:focus, .form-group select:focus { outline: none; border-color: #0073aa; box-shadow: 0 0 0 2px rgba(0,115,170,0.2); }
.btn { padding: 10px 20px; border: none; border-radius: 4px; font-size: 14px; font-weight: bold; cursor: pointer; }
.btn-primary { background: #0073aa; color: #fff; }
.btn-primary:hover { background: #005a87; }
.btn-danger  { background: #dc3545; color: #fff; font-size: 12px; padding: 5px 10px; }
.btn-danger:hover { background: #c82333; }
.btn-success { background: #28a745; color: #fff; }
.btn-success:hover { background: #218838; }
emp-table { width: 100%; }
table.emp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.emp-table th { background: #0073aa; color: #fff; padding: 10px; text-align: left; }
table.emp-table td { padding: 9px 10px; border-bottom: 1px solid #eee; }
table.emp-table tr:hover td { background: #f9f9f9; }
.delete-form { display: inline; }
@media(max-width:600px) { .form-row { grid-template-columns: 1fr; } }
</style>
</head>
<body>
<div class="wrap">
    <h1>🛠 EAM Setup Tool — Employee Attendance Management</h1>
    <div class="warning-box">
        <strong>⚠ SECURITY WARNING:</strong> Delete this file from your server after completing setup!
        File location: <code><?php echo __FILE__; ?></code>
    </div>

    <?php if ($message): ?>
        <div class="msg-ok" style="margin-top:15px;">✅ <?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg-err" style="margin-top:15px;">❌ <?php echo esc_html($error); ?></div>
    <?php endif; ?>

    <!-- DATABASE STATUS -->
    <div class="card">
        <h2>📊 Database Table Status</h2>
        <table class="status">
            <tr><th>Table</th><th>Status</th><th>Records</th></tr>
            <?php foreach ($table_status as $t => $info): ?>
            <tr>
                <td><code><?php echo $prefix . $t; ?></code></td>
                <td>
                    <?php if ($info['exists']): ?>
                        <span class="badge-ok">✓ EXISTS</span>
                    <?php else: ?>
                        <span class="badge-err">✗ MISSING</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $info['count']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <form method="post" style="margin-top:15px;">
            <input type="hidden" name="action" value="create_tables">
            <button type="submit" class="btn btn-success">
                🔨 Create / Fix ALL Tables (Drops & Recreates employees table)
            </button>
        </form>
    </div>

    <!-- ADD EMPLOYEE FORM -->
    <div class="card">
        <h2>➕ Add New Employee</h2>
        <form method="post">
            <input type="hidden" name="action" value="add_employee">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" name="first_name" required placeholder="e.g. Sardar">
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" required placeholder="e.g. Ali">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="employee@company.com">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" placeholder="03XXXXXXXXX">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" placeholder="e.g. IT, HR, Operations">
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" placeholder="e.g. Developer, Manager">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Joining Date</label>
                    <input type="date" name="joining_date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="emp_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password *</label>
                    <input type="text" name="password" required placeholder="Minimum 8 characters">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">💾 Save Employee</button>
        </form>
    </div>

    <!-- EMPLOYEE LIST -->
    <div class="card">
        <h2>👥 Current Employees (<?php echo count($employees); ?>)</h2>
        <?php if ($employees): ?>
        <table class="emp-table">
            <tr>
                <th>#</th><th>EMP ID</th><th>Name</th>
                <th>Email</th><th>Dept</th><th>Position</th>
                <th>Status</th><th>Joined</th><th>Action</th>
            </tr>
            <?php foreach ($employees as $i => $emp): ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><?php echo esc_html($emp['employee_id']); ?></td>
                <td><?php echo esc_html($emp['first_name'].' '.$emp['last_name']); ?></td>
                <td><?php echo esc_html($emp['email']); ?></td>
                <td><?php echo esc_html($emp['department']); ?></td>
                <td><?php echo esc_html($emp['position']); ?></td>
                <td>
                    <span class="badge-<?php echo $emp['status']==='active'?'ok':'err'; ?>">
                        <?php echo esc_html($emp['status']); ?>
                    </span>
                </td>
                <td><?php echo esc_html($emp['joining_date']); ?></td>
                <td>
                    <form method="post" class="delete-form"
                          onsubmit="return confirm('Delete <?php echo esc_js($emp['first_name']); ?>?');">
                        <input type="hidden" name="action" value="delete_employee">
                        <input type="hidden" name="emp_db_id" value="<?php echo $emp['id']; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <p style="color:#888; padding:10px;">No employees yet. Add one above.</p>
        <?php endif; ?>
    </div>

    <div class="card" style="background:#fff3cd; border-color:#ffc107;">
        <h2 style="color:#856404; border-color:#ffc107;">🗑 Delete This File When Done</h2>
        <p>For security, delete <strong>eam-setup.php</strong> from your server after adding all employees.</p>
        <p style="margin-top:8px;">File path: <code><?php echo __FILE__; ?></code></p>
    </div>
</div>
</body>
</html>
