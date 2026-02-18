<?php
/**
 * Admin Dashboard Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$stats = EAM_Reports::get_dashboard_stats();
$trends = EAM_Reports::get_attendance_trends(30);
?>

<div class="wrap eam-dashboard">
    <h1><?php _e('Attendance Dashboard', 'employee-attendance'); ?></h1>
    
    <div class="eam-stats-grid">
        <!-- Total Employees -->
        <div class="eam-stat-card">
            <div class="eam-stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="eam-stat-content">
                <h3><?php echo $stats['total_employees']; ?></h3>
                <p><?php _e('Total Employees', 'employee-attendance'); ?></p>
            </div>
        </div>
        
        <!-- Today Present -->
        <div class="eam-stat-card">
            <div class="eam-stat-icon success">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="eam-stat-content">
                <h3><?php echo $stats['today']['present']; ?></h3>
                <p><?php _e('Present Today', 'employee-attendance'); ?></p>
            </div>
        </div>
        
        <!-- Today Absent -->
        <div class="eam-stat-card">
            <div class="eam-stat-icon danger">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <div class="eam-stat-content">
                <h3><?php echo $stats['today']['absent']; ?></h3>
                <p><?php _e('Absent Today', 'employee-attendance'); ?></p>
            </div>
        </div>
        
        <!-- Today Late -->
        <div class="eam-stat-card">
            <div class="eam-stat-icon warning">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="eam-stat-content">
                <h3><?php echo $stats['today']['late']; ?></h3>
                <p><?php _e('Late Today', 'employee-attendance'); ?></p>
            </div>
        </div>
        
        <!-- Attendance Rate -->
        <div class="eam-stat-card">
            <div class="eam-stat-icon info">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="eam-stat-content">
                <h3><?php echo $stats['today']['attendance_rate']; ?>%</h3>
                <p><?php _e('Attendance Rate', 'employee-attendance'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="eam-charts-row">
        <div class="eam-chart-container">
            <h2><?php _e('Attendance Trends (Last 30 Days)', 'employee-attendance'); ?></h2>
            <canvas id="attendanceTrendsChart"></canvas>
        </div>
    </div>
    
    <div class="eam-quick-actions">
        <h2><?php _e('Quick Actions', 'employee-attendance'); ?></h2>
        <div class="eam-action-buttons">
            <a href="<?php echo admin_url('admin.php?page=eam-employees'); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus"></span> <?php _e('Add Employee', 'employee-attendance'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=eam-records'); ?>" class="button">
                <span class="dashicons dashicons-list-view"></span> <?php _e('View Records', 'employee-attendance'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=eam-reports'); ?>" class="button">
                <span class="dashicons dashicons-chart-bar"></span> <?php _e('Generate Report', 'employee-attendance'); ?>
            </a>
        </div>
    </div>
    
    <div class="eam-recent-activity">
        <h2><?php _e("Today's Attendance", 'employee-attendance'); ?></h2>
        <?php
        $today_attendance = EAM_Attendance::get_all_attendance_by_date(date('Y-m-d'));
        if ($today_attendance) {
            ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Employee', 'employee-attendance'); ?></th>
                        <th><?php _e('Department', 'employee-attendance'); ?></th>
                        <th><?php _e('Check In', 'employee-attendance'); ?></th>
                        <th><?php _e('Check Out', 'employee-attendance'); ?></th>
                        <th><?php _e('Status', 'employee-attendance'); ?></th>
                        <th><?php _e('Hours', 'employee-attendance'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($today_attendance as $record) : ?>
                    <tr>
                        <td><?php echo esc_html($record['first_name'] . ' ' . $record['last_name']); ?></td>
                        <td><?php echo esc_html($record['department']); ?></td>
                        <td>
                            <?php 
                            if ($record['check_in_time']) {
                                echo date('h:i A', strtotime($record['check_in_time']));
                                if ($record['check_in_status'] == 'late') {
                                    echo ' <span class="eam-badge warning">Late</span>';
                                } elseif ($record['check_in_status'] == 'very_late') {
                                    echo ' <span class="eam-badge danger">Very Late</span>';
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
                                    echo ' <span class="eam-badge warning">Auto</span>';
                                }
                            } else {
                                echo '<span class="eam-badge info">In Progress</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            $status_class = 'success';
                            if ($record['status'] == 'absent') $status_class = 'danger';
                            elseif ($record['status'] == 'half_day') $status_class = 'warning';
                            ?>
                            <span class="eam-badge <?php echo $status_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $record['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo $record['total_hours'] ? number_format($record['total_hours'], 2) : '-'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        } else {
            echo '<p>' . __('No attendance records for today yet.', 'employee-attendance') . '</p>';
        }
        ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Prepare data for chart
    var trendsData = <?php echo json_encode($trends); ?>;
    var labels = trendsData.map(function(item) {
        return new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    var presentData = trendsData.map(function(item) { return item.present; });
    var absentData = trendsData.map(function(item) { return item.absent; });
    var lateData = trendsData.map(function(item) { return item.late; });
    
    // Create chart
    var ctx = document.getElementById('attendanceTrendsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Present',
                    data: presentData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Absent',
                    data: absentData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Late',
                    data: lateData,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
