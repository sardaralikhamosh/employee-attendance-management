<?php
/**
 * Attendance Management Class
 * All times use WordPress local timezone (set in Settings > General > Timezone)
 */

if (!defined('ABSPATH')) {
    exit;
}

class EAM_Attendance {

    public function __construct() {
        add_action('wp_ajax_eam_mark_attendance', array($this, 'ajax_mark_attendance'));
    }

    /**
     * Helper: Current local date Y-m-d (respects WordPress timezone — e.g. Asia/Karachi)
     */
    private static function local_date() {
        return current_time('Y-m-d');
    }

    /**
     * Helper: Current local datetime Y-m-d H:i:s (respects WordPress timezone)
     */
    private static function local_now() {
        return current_time('mysql');
    }

    /**
     * Helper: Local date offset by N days (negative = past)
     */
    private static function local_date_offset($days = -1) {
        $ts = strtotime("{$days} days", current_time('timestamp'));
        return date('Y-m-d', $ts);
    }

    /**
     * Helper: Day of week for a date string (1=Mon...7=Sun)
     */
    private static function local_day_of_week($date_string) {
        return date('N', strtotime($date_string));
    }

    // ─────────────────────────────────────────────────────
    // CHECK IN
    // ─────────────────────────────────────────────────────
    public static function check_in($email, $password) {
        $employee = EAM_Employee::verify_credentials($email, $password);

        if (!$employee) {
            return array('success' => false, 'message' => 'Invalid credentials');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        $today = self::local_date();
        $now   = self::local_now();

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE employee_id = %d AND date = %s",
            $employee['id'], $today
        ), ARRAY_A);

        if ($existing && $existing['check_in_time']) {
            return array(
                'success'       => false,
                'message'       => 'Already checked in today',
                'check_in_time' => $existing['check_in_time']
            );
        }

        $office_start = get_option('eam_office_start_time', '19:00:00');
        $grace_before = intval(get_option('eam_grace_time_before', 20));
        $check_in_status = self::calculate_check_in_status($now, $office_start, $grace_before);
        $ip_address = self::get_client_ip();

        if ($existing) {
            $result = $wpdb->update(
                $table,
                array(
                    'check_in_time'   => $now,
                    'check_in_status' => $check_in_status,
                    'status'          => 'present',
                    'ip_address'      => $ip_address,
                    'updated_at'      => $now,
                ),
                array('employee_id' => $employee['id'], 'date' => $today)
            );
        } else {
            $result = $wpdb->insert(
                $table,
                array(
                    'employee_id'     => $employee['id'],
                    'date'            => $today,
                    'check_in_time'   => $now,
                    'check_in_status' => $check_in_status,
                    'status'          => 'present',
                    'ip_address'      => $ip_address,
                )
            );
        }

        if ($result !== false) {
            return array(
                'success'       => true,
                'message'       => 'Check-in successful',
                'check_in_time' => $now,
                'status'        => $check_in_status,
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name']
            );
        }

        return array('success' => false, 'message' => 'Failed to check in');
    }

    // ─────────────────────────────────────────────────────
    // CHECK OUT
    // ─────────────────────────────────────────────────────
    public static function check_out($email, $password) {
        $employee = EAM_Employee::verify_credentials($email, $password);

        if (!$employee) {
            return array('success' => false, 'message' => 'Invalid credentials');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        $today = self::local_date();
        $now   = self::local_now();

        $attendance = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE employee_id = %d AND date = %s",
            $employee['id'], $today
        ), ARRAY_A);

        if (!$attendance || !$attendance['check_in_time']) {
            return array('success' => false, 'message' => 'No check-in record found for today');
        }

        if ($attendance['check_out_time']) {
            return array(
                'success'        => false,
                'message'        => 'Already checked out',
                'check_out_time' => $attendance['check_out_time']
            );
        }

        $check_in    = new DateTime($attendance['check_in_time']);
        $check_out   = new DateTime($now);
        $interval    = $check_in->diff($check_out);
        $total_hours = ($interval->days * 24) + $interval->h + ($interval->i / 60);

        $result = $wpdb->update(
            $table,
            array(
                'check_out_time'   => $now,
                'check_out_status' => 'normal',
                'total_hours'      => round($total_hours, 2),
                'updated_at'       => $now,
            ),
            array('id' => $attendance['id'])
        );

        if ($result !== false) {
            return array(
                'success'        => true,
                'message'        => 'Check-out successful',
                'check_out_time' => $now,
                'total_hours'    => round($total_hours, 2),
                'employee_name'  => $employee['first_name'] . ' ' . $employee['last_name']
            );
        }

        return array('success' => false, 'message' => 'Failed to check out');
    }

    // ─────────────────────────────────────────────────────
    // GET STATUS
    // ─────────────────────────────────────────────────────
    public static function get_status($email, $password) {
        $employee = EAM_Employee::verify_credentials($email, $password);

        if (!$employee) {
            return array('success' => false, 'message' => 'Invalid credentials');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';
        $today = self::local_date();

        $attendance = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE employee_id = %d AND date = %s",
            $employee['id'], $today
        ), ARRAY_A);

        $status = array(
            'success'       => true,
            'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
            'employee_id'   => $employee['employee_id'],
            'date'          => $today,
            'checked_in'    => false,
            'checked_out'   => false,
        );

        if ($attendance) {
            if ($attendance['check_in_time']) {
                $status['checked_in']      = true;
                $status['check_in_time']   = $attendance['check_in_time'];
                $status['check_in_status'] = $attendance['check_in_status'];
            }
            if ($attendance['check_out_time']) {
                $status['checked_out']    = true;
                $status['check_out_time'] = $attendance['check_out_time'];
                $status['total_hours']    = $attendance['total_hours'];
            }
        }

        return $status;
    }

    // ─────────────────────────────────────────────────────
    // AUTO CHECKOUT (cron: hourly)
    // ─────────────────────────────────────────────────────
    public static function auto_checkout_forgotten() {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';

        $six_hours_ago = date(
            'Y-m-d H:i:s',
            strtotime('-6 hours', current_time('timestamp'))
        );

        $records = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
             WHERE check_in_time IS NOT NULL
               AND check_out_time IS NULL
               AND check_in_time < %s",
            $six_hours_ago
        ), ARRAY_A);

        foreach ($records as $record) {
            $check_in      = new DateTime($record['check_in_time']);
            $auto_checkout = clone $check_in;
            $auto_checkout->modify('+6 hours');

            $wpdb->update(
                $table,
                array(
                    'check_out_time'   => $auto_checkout->format('Y-m-d H:i:s'),
                    'check_out_status' => 'forgotten',
                    'total_hours'      => 6.00,
                    'notes'            => 'Auto checkout — session exceeded 6 hours',
                    'updated_at'       => self::local_now(),
                ),
                array('id' => $record['id'])
            );
        }

        return count($records);
    }

    // ─────────────────────────────────────────────────────
    // MARK ABSENCES (cron: daily)
    // ─────────────────────────────────────────────────────
    public static function mark_absences() {
        global $wpdb;
        $table_attendance = $wpdb->prefix . 'eam_attendance';
        $table_employees  = $wpdb->prefix . 'eam_employees';

        $yesterday   = self::local_date_offset(-1);
        $day_of_week = self::local_day_of_week($yesterday);

        if ($day_of_week >= 1 && $day_of_week <= 5) {
            $employees = $wpdb->get_results(
                "SELECT id FROM $table_employees WHERE status = 'active'",
                ARRAY_A
            );

            foreach ($employees as $employee) {
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM $table_attendance WHERE employee_id = %d AND date = %s",
                    $employee['id'], $yesterday
                ));

                if (!$exists) {
                    $wpdb->insert(
                        $table_attendance,
                        array(
                            'employee_id' => $employee['id'],
                            'date'        => $yesterday,
                            'status'      => 'absent',
                            'notes'       => 'Marked absent automatically',
                        )
                    );
                }
            }
        }
    }

    // ─────────────────────────────────────────────────────
    // CHECK-IN STATUS LOGIC
    // ─────────────────────────────────────────────────────
    private static function calculate_check_in_status($check_in_time, $office_start, $grace_before) {
        $local_today     = current_time('Y-m-d');
        $check_in        = new DateTime($check_in_time);
        $office_start_dt = new DateTime($local_today . ' ' . $office_start);
        $grace_after_dt  = clone $office_start_dt;
        $grace_after_dt->modify('+20 minutes');

        if ($check_in <= $office_start_dt) {
            return 'on_time';
        } elseif ($check_in <= $grace_after_dt) {
            return 'late';
        } else {
            return 'very_late';
        }
    }

    // ─────────────────────────────────────────────────────
    // QUERY HELPERS
    // ─────────────────────────────────────────────────────
    public static function get_attendance_by_date_range($employee_id, $start_date, $end_date) {
        global $wpdb;
        $table = $wpdb->prefix . 'eam_attendance';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table
             WHERE employee_id = %d AND date BETWEEN %s AND %s
             ORDER BY date DESC",
            $employee_id, $start_date, $end_date
        ), ARRAY_A);
    }

    public static function get_all_attendance_by_date($date) {
        global $wpdb;
        $table_a = $wpdb->prefix . 'eam_attendance';
        $table_e = $wpdb->prefix . 'eam_employees';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT a.*, e.first_name, e.last_name, e.employee_id as emp_id, e.department
             FROM $table_a a
             LEFT JOIN $table_e e ON a.employee_id = e.id
             WHERE a.date = %s
             ORDER BY e.first_name ASC",
            $date
        ), ARRAY_A);
    }

    // ─────────────────────────────────────────────────────
    // CLIENT IP
    // ─────────────────────────────────────────────────────
    private static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    // ─────────────────────────────────────────────────────
    // ADMIN: MANUAL MARK ATTENDANCE
    // ─────────────────────────────────────────────────────
    public function ajax_mark_attendance() {
        check_ajax_referer('eam_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table       = $wpdb->prefix . 'eam_attendance';
        $employee_id = intval($_POST['employee_id']);
        $date        = sanitize_text_field($_POST['date']);
        $status      = sanitize_text_field($_POST['status']);

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE employee_id = %d AND date = %s",
            $employee_id, $date
        ));

        if ($exists) {
            $result = $wpdb->update($table, array('status' => $status), array('id' => $exists));
        } else {
            $result = $wpdb->insert($table, array(
                'employee_id' => $employee_id,
                'date'        => $date,
                'status'      => $status,
            ));
        }

        if ($result !== false) {
            wp_send_json_success('Attendance marked successfully');
        } else {
            wp_send_json_error('Failed to mark attendance');
        }
    }
}
