<?php
/**
 * Direct Employee Add Test (No AJAX)
 */

if (!defined('ABSPATH')) {
    exit;
}

$message = '';
$error = '';

if (isset($_POST['add_employee_direct'])) {
    check_admin_referer('eam_direct_add');
    
    $result = EAM_Employee::add_employee($_POST);
    
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>

<div class="wrap">
    <h1>Direct Employee Add (Bypass AJAX)</h1>
    
    <p>This page adds employees directly without AJAX to help diagnose issues.</p>
    
    <?php if ($message) : ?>
        <div class="notice notice-success">
            <p><strong><?php echo esc_html($message); ?></strong></p>
        </div>
    <?php endif; ?>
    
    <?php if ($error) : ?>
        <div class="notice notice-error">
            <p><strong>Error:</strong> <?php echo esc_html($error); ?></p>
        </div>
    <?php endif; ?>
    
    <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 4px; max-width: 600px;">
        <form method="post">
            <?php wp_nonce_field('eam_direct_add'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label>First Name *</label></th>
                    <td><input type="text" name="first_name" required style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label>Last Name *</label></th>
                    <td><input type="text" name="last_name" required style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label>Email *</label></th>
                    <td><input type="email" name="email" required style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label>Phone</label></th>
                    <td><input type="text" name="phone" style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label>Department</label></th>
                    <td><input type="text" name="department" style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label>Position</label></th>
                    <td><input type="text" name="position" style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label>Joining Date</label></th>
                    <td><input type="date" name="joining_date" value="<?php echo date('Y-m-d'); ?>" style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label>Password</label></th>
                    <td>
                        <input type="password" name="password" style="width: 100%;">
                        <p class="description">Leave blank for auto-generated password</p>
                    </td>
                </tr>
                <tr>
                    <th><label>Status</label></th>
                    <td>
                        <select name="status" style="width: 100%;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p>
                <button type="submit" name="add_employee_direct" class="button button-primary button-large">
                    Add Employee (Direct Method)
                </button>
            </p>
        </form>
    </div>
    
    <hr style="margin: 30px 0;">
    
    <h2>Current Employees</h2>
    <?php
    $employees = EAM_Employee::get_all_employees('all');
    if ($employees) {
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp) : ?>
                <tr>
                    <td><?php echo esc_html($emp['employee_id']); ?></td>
                    <td><?php echo esc_html($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                    <td><?php echo esc_html($emp['email']); ?></td>
                    <td><?php echo esc_html($emp['department']); ?></td>
                    <td><?php echo esc_html($emp['status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    } else {
        echo '<p>No employees found.</p>';
    }
    ?>
</div>
