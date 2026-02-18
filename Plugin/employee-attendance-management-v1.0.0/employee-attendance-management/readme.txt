=== Employee Attendance Management System ===
Contributors: sardaralikhamosh
Tags: attendance, employee, hr, check-in, check-out, management
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete employee attendance management system with check-in/out, automated tracking, and comprehensive HR analytics dashboard.

== Description ==

**Employee Attendance Management System** is a complete HR solution for managing employee attendance with night-shift support, automated tracking, and detailed monthly reports.

= Key Features =
* Dedicated Attendance Page — Employees check in/out at /attendance URL
* Secure Login — Email & password authentication per employee
* Night Shift Support — Configurable hours (default 7:00 PM – 4:00 AM)
* Grace Period — 20 minutes before and after shift start
* Auto Checkout — Checks out after 6 hours if forgotten
* Auto Absence — Marks absent for missed working days (Mon–Fri)
* Salary Period — Reports based on 27th–26th monthly cycle
* Admin Dashboard — Real-time stats and charts
* Monthly Reports — Full HR analytics per employee
* Department Stats — Attendance breakdown by department
* CSV Export — Download attendance and report data
* Unlimited Employees — No cap on employee count

== Installation ==
1. Upload the plugin folder to /wp-content/plugins/
2. Activate via Plugins menu
3. Go to Settings → Permalinks and click Save Changes
4. Configure in Attendance → Settings
5. Add employees in Attendance → Employees

== Frequently Asked Questions ==

= The /attendance page shows 404 =
Go to Settings → Permalinks and click Save Changes.

= Tables are missing after activation =
Go to Attendance → Database Setup and click Create / Fix All Tables.

== Changelog ==
= 1.0.0 =
* Initial release
