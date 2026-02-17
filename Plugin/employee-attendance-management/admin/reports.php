<?php
/**
 * Reports Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Generate summaries if not exists
EAM_Reports::generate_all_monthly_summaries($selected_month, $selected_year);

$monthly_summaries = EAM_Reports::get_all_monthly_summaries($selected_month, $selected_year);
$department_stats = EAM_Reports::get_department_wise_stats($selected_month, $selected_year);
?>

<div class="wrap eam-reports">
    <h1><?php _e('Attendance Reports', 'employee-attendance'); ?></h1>
    
    <div class="eam-report-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="eam-reports">
            
            <label><?php _e('Select Period:', 'employee-attendance'); ?></label>
            
            <select name="month">
                <?php for ($m = 1; $m <= 12; $m++) : ?>
                <option value="<?php echo $m; ?>" <?php selected($m, $selected_month); ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
                <?php endfor; ?>
            </select>
            
            <select name="year">
                <?php 
                $current_year = date('Y');
                for ($y = $current_year - 2; $y <= $current_year; $y++) : 
                ?>
                <option value="<?php echo $y; ?>" <?php selected($y, $selected_year); ?>>
                    <?php echo $y; ?>
                </option>
                <?php endfor; ?>
            </select>
            
            <button type="submit" class="button button-primary"><?php _e('Generate Report', 'employee-attendance'); ?></button>
        </form>
    </div>
    
    <h2>
        <?php 
        echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); 
        $salary_start = date('M d, Y', strtotime("$selected_year-$selected_month-27"));
        $next_month = $selected_month == 12 ? 1 : $selected_month + 1;
        $next_year = $selected_month == 12 ? $selected_year + 1 : $selected_year;
        $salary_end = date('M d, Y', strtotime("$next_year-$next_month-26"));
        ?>
        <small>(Salary Period: <?php echo $salary_start . ' - ' . $salary_end; ?>)</small>
    </h2>
    
    <!-- Department-wise Statistics -->
    <div class="eam-department-stats">
        <h3><?php _e('Department-wise Statistics', 'employee-attendance'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Department', 'employee-attendance'); ?></th>
                    <th><?php _e('Employees', 'employee-attendance'); ?></th>
                    <th><?php _e('Present Days', 'employee-attendance'); ?></th>
                    <th><?php _e('Absent Days', 'employee-attendance'); ?></th>
                    <th><?php _e('Avg Hours/Day', 'employee-attendance'); ?></th>
                    <th><?php _e('Attendance Rate', 'employee-attendance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($department_stats) : ?>
                    <?php foreach ($department_stats as $dept) : ?>
                    <tr>
                        <td><strong><?php echo esc_html($dept['department'] ? $dept['department'] : 'Unassigned'); ?></strong></td>
                        <td><?php echo $dept['total_employees']; ?></td>
                        <td><?php echo $dept['present_days']; ?></td>
                        <td><?php echo $dept['absent_days']; ?></td>
                        <td><?php echo number_format($dept['avg_hours'], 2); ?></td>
                        <td>
                            <?php 
                            $total_days = $dept['present_days'] + $dept['absent_days'];
                            $rate = $total_days > 0 ? ($dept['present_days'] / $total_days) * 100 : 0;
                            echo number_format($rate, 2) . '%';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6"><?php _e('No data available.', 'employee-attendance'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Employee-wise Summary -->
    <div class="eam-monthly-summary">
        <h3><?php _e('Employee-wise Monthly Summary', 'employee-attendance'); ?></h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Employee', 'employee-attendance'); ?></th>
                    <th><?php _e('Department', 'employee-attendance'); ?></th>
                    <th><?php _e('Working Days', 'employee-attendance'); ?></th>
                    <th><?php _e('Present', 'employee-attendance'); ?></th>
                    <th><?php _e('Absent', 'employee-attendance'); ?></th>
                    <th><?php _e('Late', 'employee-attendance'); ?></th>
                    <th><?php _e('Leaves', 'employee-attendance'); ?></th>
                    <th><?php _e('Total Hours', 'employee-attendance'); ?></th>
                    <th><?php _e('Overtime', 'employee-attendance'); ?></th>
                    <th><?php _e('Attendance %', 'employee-attendance'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($monthly_summaries) : ?>
                    <?php foreach ($monthly_summaries as $summary) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($summary['first_name'] . ' ' . $summary['last_name']); ?></strong>
                            <br><small><?php echo esc_html($summary['emp_id']); ?></small>
                        </td>
                        <td><?php echo esc_html($summary['department']); ?></td>
                        <td><?php echo $summary['total_working_days']; ?></td>
                        <td><span class="eam-badge success"><?php echo $summary['present_days']; ?></span></td>
                        <td><span class="eam-badge danger"><?php echo $summary['absent_days']; ?></span></td>
                        <td><span class="eam-badge warning"><?php echo $summary['late_days']; ?></span></td>
                        <td><span class="eam-badge info"><?php echo $summary['leave_days']; ?></span></td>
                        <td><?php echo number_format($summary['total_hours'], 2); ?></td>
                        <td><?php echo number_format($summary['overtime_hours'], 2); ?></td>
                        <td>
                            <?php 
                            $attendance_rate = $summary['total_working_days'] > 0 
                                ? ($summary['present_days'] / $summary['total_working_days']) * 100 
                                : 0;
                            $color = $attendance_rate >= 90 ? 'success' : ($attendance_rate >= 75 ? 'warning' : 'danger');
                            ?>
                            <span class="eam-badge <?php echo $color; ?>">
                                <?php echo number_format($attendance_rate, 2); ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="10"><?php _e('No data available for this period.', 'employee-attendance'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="eam-export-actions">
        <h3><?php _e('Export Report', 'employee-attendance'); ?></h3>
        <button class="button button-primary" id="eam-export-report-csv">
            <span class="dashicons dashicons-download"></span> <?php _e('Download as CSV', 'employee-attendance'); ?>
        </button>
        <button class="button" id="eam-export-report-pdf">
            <span class="dashicons dashicons-pdf"></span> <?php _e('Download as PDF', 'employee-attendance'); ?>
        </button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#eam-export-report-csv').on('click', function() {
        var csv = [];
        
        // Add header
        csv.push('Employee Attendance Report - <?php echo date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year)); ?>');
        csv.push('Salary Period: <?php echo $salary_start . ' to ' . $salary_end; ?>');
        csv.push('');
        
        // Add table data
        $('.eam-monthly-summary table tr').each(function() {
            var row = [];
            $(this).find('th, td').each(function() {
                var text = $(this).text().trim().replace(/\s+/g, ' ');
                row.push('"' + text.replace(/"/g, '""') + '"');
            });
            csv.push(row.join(','));
        });
        
        var csvContent = csv.join('\n');
        var blob = new Blob([csvContent], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'attendance_report_<?php echo $selected_month . '_' . $selected_year; ?>.csv';
        a.click();
    });
});
</script>
