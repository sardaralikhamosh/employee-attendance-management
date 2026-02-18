<?php
/**
 * Attendance Records Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$selected_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');
$attendance_records = EAM_Attendance::get_all_attendance_by_date($selected_date);
?>

<div class="wrap eam-records">
    <h1><?php _e('Attendance Records', 'employee-attendance'); ?></h1>
    
    <div class="eam-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="eam-records">
            <label><?php _e('Select Date:', 'employee-attendance'); ?></label>
            <input type="date" name="date" value="<?php echo esc_attr($selected_date); ?>" max="<?php echo date('Y-m-d'); ?>">
            <button type="submit" class="button"><?php _e('Filter', 'employee-attendance'); ?></button>
            <a href="<?php echo admin_url('admin.php?page=eam-records&date=' . date('Y-m-d')); ?>" class="button">
                <?php _e('Today', 'employee-attendance'); ?>
            </a>
        </form>
    </div>
    
    <h2><?php echo date('F d, Y', strtotime($selected_date)); ?></h2>
    
    <div class="eam-records-table">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Employee ID', 'employee-attendance'); ?></th>
                    <th><?php _e('Employee Name', 'employee-attendance'); ?></th>
                    <th><?php _e('Department', 'employee-attendance'); ?></th>
                    <th><?php _e('Check In', 'employee-attendance'); ?></th>
                    <th><?php _e('Check Out', 'employee-attendance'); ?></th>
                    <th><?php _e('Total Hours', 'employee-attendance'); ?></th>
                    <th><?php _e('Status', 'employee-attendance'); ?></th>
                    <th><?php _e('Notes', 'employee-attendance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($attendance_records) : ?>
                    <?php foreach ($attendance_records as $record) : ?>
                    <tr>
                        <td><?php echo esc_html($record['emp_id']); ?></td>
                        <td><?php echo esc_html($record['first_name'] . ' ' . $record['last_name']); ?></td>
                        <td><?php echo esc_html($record['department']); ?></td>
                        <td>
                            <?php 
                            if ($record['check_in_time']) {
                                echo date('h:i A', strtotime($record['check_in_time']));
                                echo '<br>';
                                if ($record['check_in_status'] == 'on_time') {
                                    echo '<span class="eam-badge success">On Time</span>';
                                } elseif ($record['check_in_status'] == 'late') {
                                    echo '<span class="eam-badge warning">Late</span>';
                                } elseif ($record['check_in_status'] == 'very_late') {
                                    echo '<span class="eam-badge danger">Very Late</span>';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($record['check_out_time']) {
                                echo date('h:i A', strtotime($record['check_out_time']));
                                if ($record['check_out_status'] == 'forgotten') {
                                    echo '<br><span class="eam-badge warning">Auto Checkout</span>';
                                }
                            } else {
                                if ($record['check_in_time']) {
                                    echo '<span class="eam-badge info">In Progress</span>';
                                } else {
                                    echo '-';
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($record['total_hours']) {
                                echo number_format($record['total_hours'], 2) . ' hrs';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $status_class = 'success';
                            if ($record['status'] == 'absent') $status_class = 'danger';
                            elseif ($record['status'] == 'half_day') $status_class = 'warning';
                            elseif ($record['status'] == 'leave') $status_class = 'info';
                            ?>
                            <span class="eam-badge <?php echo $status_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($record['notes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8"><?php _e('No attendance records found for this date.', 'employee-attendance'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="eam-export-actions">
        <h3><?php _e('Export Options', 'employee-attendance'); ?></h3>
        <button class="button" id="eam-export-csv">
            <span class="dashicons dashicons-download"></span> <?php _e('Export to CSV', 'employee-attendance'); ?>
        </button>
        <button class="button" id="eam-export-pdf">
            <span class="dashicons dashicons-pdf"></span> <?php _e('Export to PDF', 'employee-attendance'); ?>
        </button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Export to CSV
    $('#eam-export-csv').on('click', function() {
        var csv = [];
        var rows = $('.eam-records-table table tr');
        
        rows.each(function() {
            var row = [];
            $(this).find('th, td').each(function() {
                row.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
            });
            csv.push(row.join(','));
        });
        
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'attendance_<?php echo $selected_date; ?>.csv';
        a.click();
    });
    
    // Export to PDF (placeholder - would need PDF library)
    $('#eam-export-pdf').on('click', function() {
        alert('<?php _e('PDF export feature coming soon!', 'employee-attendance'); ?>');
    });
});
</script>
