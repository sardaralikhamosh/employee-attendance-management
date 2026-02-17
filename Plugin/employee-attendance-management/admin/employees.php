<?php
/**
 * Employees Management Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$employees = EAM_Employee::get_all_employees('all');
?>

<div class="wrap eam-employees">
    <h1>
        <?php _e('Employee Management', 'employee-attendance'); ?>
        <button class="button button-primary" id="eam-add-employee-btn">
            <span class="dashicons dashicons-plus"></span> <?php _e('Add Employee', 'employee-attendance'); ?>
        </button>
    </h1>
    
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
                    <?php foreach ($employees as $employee) : ?>
                    <tr data-employee-id="<?php echo $employee['id']; ?>">
                        <td><?php echo esc_html($employee['employee_id']); ?></td>
                        <td><?php echo esc_html($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                        <td><?php echo esc_html($employee['email']); ?></td>
                        <td><?php echo esc_html($employee['phone']); ?></td>
                        <td><?php echo esc_html($employee['department']); ?></td>
                        <td><?php echo esc_html($employee['position']); ?></td>
                        <td><?php echo $employee['joining_date'] ? date('M d, Y', strtotime($employee['joining_date'])) : '-'; ?></td>
                        <td>
                            <span class="eam-badge <?php echo $employee['status'] == 'active' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($employee['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="button button-small eam-edit-employee" data-id="<?php echo $employee['id']; ?>">
                                <?php _e('Edit', 'employee-attendance'); ?>
                            </button>
                            <button class="button button-small button-link-delete eam-delete-employee" data-id="<?php echo $employee['id']; ?>">
                                <?php _e('Delete', 'employee-attendance'); ?>
                            </button>
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

<!-- Add/Edit Employee Modal -->
<div id="eam-employee-modal" class="eam-modal" style="display: none;">
    <div class="eam-modal-content">
        <span class="eam-modal-close">&times;</span>
        <h2 id="eam-modal-title"><?php _e('Add Employee', 'employee-attendance'); ?></h2>
        
        <form id="eam-employee-form">
            <input type="hidden" id="employee-id" name="id">
            
            <div class="eam-form-row">
                <div class="eam-form-group">
                    <label><?php _e('First Name', 'employee-attendance'); ?> *</label>
                    <input type="text" name="first_name" id="first-name" required>
                </div>
                
                <div class="eam-form-group">
                    <label><?php _e('Last Name', 'employee-attendance'); ?> *</label>
                    <input type="text" name="last_name" id="last-name" required>
                </div>
            </div>
            
            <div class="eam-form-row">
                <div class="eam-form-group">
                    <label><?php _e('Email', 'employee-attendance'); ?> *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="eam-form-group">
                    <label><?php _e('Phone', 'employee-attendance'); ?></label>
                    <input type="tel" name="phone" id="phone">
                </div>
            </div>
            
            <div class="eam-form-row">
                <div class="eam-form-group">
                    <label><?php _e('Department', 'employee-attendance'); ?></label>
                    <input type="text" name="department" id="department">
                </div>
                
                <div class="eam-form-group">
                    <label><?php _e('Position', 'employee-attendance'); ?></label>
                    <input type="text" name="position" id="position">
                </div>
            </div>
            
            <div class="eam-form-row">
                <div class="eam-form-group">
                    <label><?php _e('Joining Date', 'employee-attendance'); ?></label>
                    <input type="date" name="joining_date" id="joining-date">
                </div>
                
                <div class="eam-form-group">
                    <label><?php _e('Status', 'employee-attendance'); ?></label>
                    <select name="status" id="status">
                        <option value="active"><?php _e('Active', 'employee-attendance'); ?></option>
                        <option value="inactive"><?php _e('Inactive', 'employee-attendance'); ?></option>
                        <option value="suspended"><?php _e('Suspended', 'employee-attendance'); ?></option>
                    </select>
                </div>
            </div>
            
            <div class="eam-form-group">
                <label><?php _e('Password', 'employee-attendance'); ?></label>
                <input type="password" name="password" id="password" placeholder="<?php _e('Leave blank to keep existing', 'employee-attendance'); ?>">
                <small><?php _e('Minimum 8 characters. Leave blank for auto-generated password.', 'employee-attendance'); ?></small>
            </div>
            
            <div class="eam-form-actions">
                <button type="submit" class="button button-primary"><?php _e('Save Employee', 'employee-attendance'); ?></button>
                <button type="button" class="button eam-modal-cancel"><?php _e('Cancel', 'employee-attendance'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var modal = $('#eam-employee-modal');
    var form = $('#eam-employee-form');
    
    // Add employee button
    $('#eam-add-employee-btn').on('click', function() {
        $('#eam-modal-title').text('<?php _e('Add Employee', 'employee-attendance'); ?>');
        form[0].reset();
        $('#employee-id').val('');
        modal.show();
    });
    
    // Edit employee button
    $('.eam-edit-employee').on('click', function() {
        var employeeId = $(this).data('id');
        $('#eam-modal-title').text('<?php _e('Edit Employee', 'employee-attendance'); ?>');
        
        // Load employee data
        $.ajax({
            url: eamAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'eam_get_employee',
                nonce: eamAdmin.nonce,
                id: employeeId
            },
            success: function(response) {
                if (response.success) {
                    var emp = response.data;
                    $('#employee-id').val(emp.id);
                    $('#first-name').val(emp.first_name);
                    $('#last-name').val(emp.last_name);
                    $('#email').val(emp.email);
                    $('#phone').val(emp.phone);
                    $('#department').val(emp.department);
                    $('#position').val(emp.position);
                    $('#joining-date').val(emp.joining_date);
                    $('#status').val(emp.status);
                    modal.show();
                }
            }
        });
    });
    
    // Close modal
    $('.eam-modal-close, .eam-modal-cancel').on('click', function() {
        modal.hide();
    });
    
    // Submit form
    form.on('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        
        var employeeId = $('#employee-id').val();
        var action = employeeId ? 'eam_update_employee' : 'eam_add_employee';
        
        var formData = $(this).serialize() + '&action=' + action + '&nonce=' + eamAdmin.nonce;
        
        console.log('Action:', action);
        console.log('Form data:', formData);
        
        // Disable submit button
        $(this).find('button[type="submit"]').prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: eamAdmin.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    alert(response.data.message || 'Employee saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (response.data || 'Unknown error occurred'));
                    form.find('button[type="submit"]').prop('disabled', false).text('<?php _e('Save Employee', 'employee-attendance'); ?>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                alert('Error: Failed to save employee. Check console for details.');
                form.find('button[type="submit"]').prop('disabled', false).text('<?php _e('Save Employee', 'employee-attendance'); ?>');
            }
        });
    });
    
    // Delete employee
    $('.eam-delete-employee').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to delete this employee?', 'employee-attendance'); ?>')) {
            return;
        }
        
        var employeeId = $(this).data('id');
        
        $.ajax({
            url: eamAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'eam_delete_employee',
                nonce: eamAdmin.nonce,
                id: employeeId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });
});
</script>
