<?php
/**
 * Employees Management Page
 * All operations use direct PHP POST — zero dependency on admin-ajax.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$message      = '';
$message_type = 'success';
$edit_employee = null;
$show_form    = false;

// ── Handle DELETE ────────────────────────────────────────────────────────────
if (isset($_POST['eam_delete_employee']) && current_user_can('manage_options')) {
    check_admin_referer('eam_delete_employee_' . intval($_POST['employee_db_id']), 'eam_nonce');
    $result = EAM_Employee::delete_employee(intval($_POST['employee_db_id']));
    $message      = $result['success'] ? __('Employee deleted successfully.', 'employee-attendance') : $result['message'];
    $message_type = $result['success'] ? 'success' : 'error';
}

// ── Handle ADD ───────────────────────────────────────────────────────────────
if (isset($_POST['eam_add_employee']) && current_user_can('manage_options')) {
    check_admin_referer('eam_add_employee', 'eam_nonce');
    $result = EAM_Employee::add_employee($_POST);
    if ($result['success']) {
        $message      = __('Employee added successfully.', 'employee-attendance');
        $message_type = 'success';
    } else {
        $message      = $result['message'];
        $message_type = 'error';
        $show_form    = 'add';
    }
}

// ── Handle UPDATE ────────────────────────────────────────────────────────────
if (isset($_POST['eam_update_employee']) && current_user_can('manage_options')) {
    $edit_id = intval($_POST['employee_db_id']);
    check_admin_referer('eam_edit_employee_' . $edit_id, 'eam_nonce');
    $result = EAM_Employee::update_employee($edit_id, $_POST);
    if ($result['success']) {
        $message      = __('Employee updated successfully.', 'employee-attendance');
        $message_type = 'success';
    } else {
        $message      = $result['message'];
        $message_type = 'error';
        $show_form    = 'edit';
        $edit_employee = EAM_Employee::get_employee($edit_id);
    }
}

// ── Open edit form via GET ────────────────────────────────────────────────────
if (isset($_GET['eam_action']) && $_GET['eam_action'] === 'edit' && isset($_GET['emp_id'])) {
    $edit_employee = EAM_Employee::get_employee(intval($_GET['emp_id']));
    if ($edit_employee) {
        $show_form = 'edit';
    }
}

// ── Open add form via GET ─────────────────────────────────────────────────────
if (isset($_GET['eam_action']) && $_GET['eam_action'] === 'add') {
    $show_form = 'add';
}

$employees   = EAM_Employee::get_all_employees('all');
$current_url = admin_url('admin.php?page=eam-employees');
?>

<div class="wrap eam-employees">
    <h1>
        <?php _e('Employee Management', 'employee-attendance'); ?>
        <a href="<?php echo esc_url(add_query_arg('eam_action', 'add', $current_url)); ?>"
           class="button button-primary" style="margin-left:10px;">
            <span class="dashicons dashicons-plus" style="margin-top:3px;"></span>
            <?php _e('Add Employee', 'employee-attendance'); ?>
        </a>
    </h1>

    <?php if ($message) : ?>
        <div class="notice notice-<?php echo $message_type === 'success' ? 'success' : 'error'; ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($show_form) : ?>
    <!-- ── Inline Add / Edit Form ── -->
    <div class="eam-inline-form" style="background:#fff;border:1px solid #ccd0d4;padding:20px 24px;margin-bottom:20px;max-width:900px;">
        <h2 style="margin-top:0;">
            <?php echo $show_form === 'add' ? __('Add Employee', 'employee-attendance') : __('Edit Employee', 'employee-attendance'); ?>
        </h2>

        <form method="post" action="<?php echo esc_url($current_url); ?>">
            <?php if ($show_form === 'add') : ?>
                <?php wp_nonce_field('eam_add_employee', 'eam_nonce'); ?>
                <input type="hidden" name="eam_add_employee" value="1">
            <?php else : ?>
                <?php wp_nonce_field('eam_edit_employee_' . $edit_employee['id'], 'eam_nonce'); ?>
                <input type="hidden" name="eam_update_employee" value="1">
                <input type="hidden" name="employee_db_id" value="<?php echo esc_attr($edit_employee['id']); ?>">
            <?php endif; ?>

            <table class="form-table">
                <tr>
                    <th><label><?php _e('First Name', 'employee-attendance'); ?> *</label></th>
                    <td><input type="text" name="first_name" class="regular-text" required
                               value="<?php echo esc_attr($edit_employee ? $edit_employee['first_name'] : (isset($_POST['first_name']) ? $_POST['first_name'] : '')); ?>"></td>
                    <th><label><?php _e('Last Name', 'employee-attendance'); ?> *</label></th>
                    <td><input type="text" name="last_name" class="regular-text" required
                               value="<?php echo esc_attr($edit_employee ? $edit_employee['last_name'] : (isset($_POST['last_name']) ? $_POST['last_name'] : '')); ?>"></td>
                </tr>
                <tr>
                    <th><label><?php _e('Email', 'employee-attendance'); ?> *</label></th>
                    <td><input type="email" name="email" class="regular-text" required
                               value="<?php echo esc_attr($edit_employee ? $edit_employee['email'] : (isset($_POST['email']) ? $_POST['email'] : '')); ?>"></td>
                    <th><label><?php _e('Phone', 'employee-attendance'); ?></label></th>
                    <td><input type="tel" name="phone" class="regular-text"
                               value="<?php echo esc_attr($edit_employee ? $edit_employee['phone'] : (isset($_POST['phone']) ? $_POST['phone'] : '')); ?>"></td>
                </tr>
                <tr>
                    <th><label><?php _e('Department', 'employee-attendance'); ?></label></th>
                    <td><input type="text" name="department" class="regular-text"
                               value="<?php echo esc_attr($edit_employee ? $edit_employee['department'] : (isset($_POST['department']) ? $_POST['department'] : '')); ?>"></td>
                    <th><label><?php _e('Position', 'employee-attendance'); ?></label></th>
                    <td><input type="text" name="position" class="regular-text"
                               value="<?php echo esc_attr($edit_employee ? $edit_employee['position'] : (isset($_POST['position']) ? $_POST['position'] : '')); ?>"></td>
                </tr>
                <tr>
                    <th><label><?php _e('Joining Date', 'employee-attendance'); ?></label></th>
                    <td><input type="date" name="joining_date"
                               value="<?php echo esc_attr($edit_employee ? $edit_employee['joining_date'] : (isset($_POST['joining_date']) ? $_POST['joining_date'] : date('Y-m-d'))); ?>"></td>
                    <th><label><?php _e('Status', 'employee-attendance'); ?></label></th>
                    <td>
                        <select name="status">
                            <?php
                            $cur_status = $edit_employee ? $edit_employee['status'] : (isset($_POST['status']) ? $_POST['status'] : 'active');
                            foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $val => $label) :
                            ?>
                            <option value="<?php echo $val; ?>" <?php selected($cur_status, $val); ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label><?php _e('Password', 'employee-attendance'); ?></label></th>
                    <td colspan="3">
                        <input type="password" name="password" class="regular-text"
                               placeholder="<?php echo $show_form === 'edit' ? __('Leave blank to keep existing', 'employee-attendance') : __('Min 8 characters or leave blank for auto-generated', 'employee-attendance'); ?>">
                        <p class="description"><?php _e('Minimum 8 characters.', 'employee-attendance'); ?></p>
                    </td>
                </tr>
            </table>

            <p style="margin-top:16px;">
                <button type="submit" class="button button-primary">
                    <?php echo $show_form === 'add' ? __('Add Employee', 'employee-attendance') : __('Update Employee', 'employee-attendance'); ?>
                </button>
                &nbsp;
                <a href="<?php echo esc_url($current_url); ?>" class="button">
                    <?php _e('Cancel', 'employee-attendance'); ?>
                </a>
            </p>
        </form>
    </div>
    <?php endif; ?>

    <!-- ── Employee Table ── -->
    <div class="eam-employees-table">
        <table class="wp-list-table widefat fixed striped" id="employees-table">
            <thead>
                <tr>
                    <th><?php _e('Employee ID', 'employee-attendance'); ?></th>
                    <th><?php _e('Name', 'employee-attendance'); ?></th>
                    <th><?php _e('Email', 'employee-attendance'); ?></th>
                    <th><?php _e('Phone', 'employee-attendance'); ?></th>
                    <th><?php _e('Department', 'employee-attendance'); ?></th>
                    <th><?php _e('Position', 'employee-attendance'); ?></th>
                    <th><?php _e('Joining Date', 'employee-attendance'); ?></th>
                    <th><?php _e('Status', 'employee-attendance'); ?></th>
                    <th><?php _e('Actions', 'employee-attendance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($employees) : ?>
                    <?php foreach ($employees as $emp) : ?>
                    <tr>
                        <td><?php echo esc_html($emp['employee_id']); ?></td>
                        <td><?php echo esc_html($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                        <td><?php echo esc_html($emp['email']); ?></td>
                        <td><?php echo esc_html($emp['phone']); ?></td>
                        <td><?php echo esc_html($emp['department']); ?></td>
                        <td><?php echo esc_html($emp['position']); ?></td>
                        <td><?php echo $emp['joining_date'] ? date('M d, Y', strtotime($emp['joining_date'])) : '-'; ?></td>
                        <td>
                            <span class="eam-badge <?php echo $emp['status'] === 'active' ? 'success' : 'danger'; ?>">
                                <?php echo esc_html(ucfirst($emp['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <!-- Edit: simple GET link, no AJAX needed -->
                            <a href="<?php echo esc_url(add_query_arg(['eam_action' => 'edit', 'emp_id' => $emp['id']], $current_url)); ?>"
                               class="button button-small">
                                <?php _e('Edit', 'employee-attendance'); ?>
                            </a>

                            <!-- Delete: inline POST form with nonce -->
                            <form method="post" action="<?php echo esc_url($current_url); ?>"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this employee?', 'employee-attendance'); ?>');">
                                <?php wp_nonce_field('eam_delete_employee_' . $emp['id'], 'eam_nonce'); ?>
                                <input type="hidden" name="eam_delete_employee" value="1">
                                <input type="hidden" name="employee_db_id" value="<?php echo esc_attr($emp['id']); ?>">
                                <button type="submit" class="button button-small button-link-delete">
                                    <?php _e('Delete', 'employee-attendance'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9"><?php _e('No employees found.', 'employee-attendance'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
