# 🕐 Employee Attendance Management System

<div align="center">

![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-21759B?style=for-the-badge&logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.6%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-GPL%20v2-green?style=for-the-badge)
![Version](https://img.shields.io/badge/Version-1.0.0-orange?style=for-the-badge)

**A complete WordPress plugin for employee attendance management.**  
Built for night-shift teams — employees check in/out via a clean dedicated page,  
admins get a full HR analytics dashboard with monthly salary-period reports.

[📥 Download Latest Release](https://github.com/sardaralikhamosh/employee-attendance-management/releases) · [🌐 Live Site](https://medlinkanalytics.com) · [👤 Author](https://sardaralikhamosh.github.io)

</div>

---

## 📋 Table of Contents

- [Features](#-features)
- [Screenshots](#-screenshots)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [How It Works](#-how-it-works)
- [Plugin Structure](#-plugin-structure)
- [Database Tables](#-database-tables)
- [Automated Tasks](#-automated-tasks-cron)
- [Troubleshooting](#-troubleshooting)
- [Changelog](#-changelog)
- [License](#-license)

---

## ✨ Features

### 👨‍💼 For Employees
- Clean **check-in / check-out page** at `/attendance`
- Login with **email & password**
- See real-time **check-in status** (On Time / Late / Very Late)
- View **total hours** worked for the day
- Fully **mobile responsive**

### 🖥️ For Admins
- **Live dashboard** with attendance stats and 30-day trend chart
- **Employee management** — add, edit, delete unlimited employees
- **Daily records** — filter attendance by any date
- **Monthly HR reports** — salary period (27th–26th) summaries per employee
- **Department analytics** — attendance breakdown by team
- **CSV export** — download records and reports
- **Settings panel** — configure all timings and rules
- **Database repair tool** — one-click table creation / fix

### ⚙️ Smart Automation
- 🌙 **Night shift support** — default 7:00 PM to 4:00 AM
- ⏰ **Grace period** — 20 min before & after shift start (configurable)
- 🔄 **Auto-checkout** — forgotten sessions closed after 6 hours, labeled *"Forgotten Checkout"*
- 📅 **Auto-absence** — marks absent daily for missed working days (Mon–Fri)
- 📊 **Auto-reports** — monthly summaries generated on the 27th automatically
- 💰 **Salary period** — 27th of month to 26th of next month

---

## 📸 Screenshots

<table>
  <tr>
    <td align="center"><b>Admin Dashboard</b></td>
    <td align="center"><b>Employee Management</b></td>
  </tr>
  <tr>
    <td>Live stats cards, 30-day attendance trend chart, today's check-in table with status badges</td>
    <td>Full employee list with Add/Edit modal — name, email, password, department, position, joining date</td>
  </tr>
  <tr>
    <td align="center"><b>Monthly Reports</b></td>
    <td align="center"><b>Attendance Page (Frontend)</b></td>
  </tr>
  <tr>
    <td>Salary-period summary per employee: present, absent, late, leave days, total & overtime hours, attendance %</td>
    <td>Clean login + check-in/out interface employees access at <code>/attendance</code></td>
  </tr>
</table>

---

## 📌 Requirements

| Requirement | Minimum Version |
|---|---|
| WordPress | 5.0 |
| PHP | 7.4 |
| MySQL | 5.6 |
| Browser | Any modern browser |

---

## 📥 Installation

### ✅ Method 1 — Upload via WordPress Admin (Recommended)

1. Go to **[Releases](https://github.com/sardaralikhamosh/employee-attendance-management/releases)**
2. Download **`employee-attendance-management-v1.0.0.zip`**
3. In your WordPress admin: **Plugins → Add New → Upload Plugin**
4. Choose the downloaded ZIP → **Install Now**
5. Click **Activate Plugin**
6. ⚠️ **Go to Settings → Permalinks → click Save Changes** (makes `/attendance` URL work)

### 🛠️ Method 2 — Manual Upload (FTP / File Manager)

```bash
# Navigate to your WordPress plugins directory
cd /wp-content/plugins/

# Clone the repository
git clone https://github.com/sardaralikhamosh/employee-attendance-management.git

# Then activate from WordPress → Plugins → Installed Plugins
```

### 🔧 First-Time Setup After Activation

```
1. Attendance → Database Setup   →  Click "Create / Fix All Tables"
2. Attendance → Settings         →  Configure office hours & grace times
3. Attendance → Employees        →  Add your team members
4. Share URL with employees      →  yoursite.com/attendance
```

---

## ⚙️ Configuration

All settings are at **Attendance → Settings** in your WordPress admin.

| Setting | Default | Description |
|---|---|---|
| Office Start Time | `19:00:00` | 7:00 PM shift start |
| Office End Time | `04:00:00` | 4:00 AM shift end |
| Grace Time Before | `20 min` | Early check-in allowed |
| Grace Time After | `20 min` | Late check-in before marked "Late" |
| Auto Checkout | `6 hours` | Max open session duration |
| Salary Period Start | `27th` | Monthly salary cycle start day |
| Standard Hours/Day | `9 hours` | Used for overtime calculation |
| Working Days | `Mon–Fri` | Days absence is tracked |

---

## 🔄 How It Works

```
EMPLOYEE FLOW
──────────────────────────────────────────────────────
  Employee → visits /attendance
           → enters email + password
           → clicks [Check In]  ← recorded with timestamp & status
           → works their shift
           → clicks [Check Out] ← total hours calculated
──────────────────────────────────────────────────────

CHECK-IN STATUS LOGIC
──────────────────────────────────────────────────────
  Before 7:00 PM (grace start 6:40 PM)  →  On Time ✅
  7:01 PM – 7:20 PM                     →  Late ⚠️
  After 7:20 PM                         →  Very Late ❌
──────────────────────────────────────────────────────

FORGOT TO CHECKOUT?
──────────────────────────────────────────────────────
  System auto-checks out after 6 hours
  Record labeled: "Forgotten Checkout"
──────────────────────────────────────────────────────

DIDN'T SHOW UP?
──────────────────────────────────────────────────────
  System marks "Absent" at 2:00 AM next day
  Only on working days (Mon–Fri)
──────────────────────────────────────────────────────
```

---

## 📁 Plugin Structure

```
employee-attendance-management/
│
├── 📄 employee-attendance-management.php   ← Main plugin bootstrap
├── 📄 readme.txt                           ← WordPress.org format readme
├── 📄 README.md                            ← This file
│
├── 📁 includes/                            ← Core business logic
│   ├── class-eam-database.php             ← DB table creation & management
│   ├── class-eam-employee.php             ← Employee CRUD operations
│   ├── class-eam-attendance.php           ← Check-in/out logic & auto tasks
│   ├── class-eam-reports.php              ← HR analytics & summaries
│   ├── class-eam-settings.php             ← Plugin configuration
│   └── class-eam-cron.php                 ← Scheduled background tasks
│
├── 📁 admin/                               ← WordPress admin pages
│   ├── dashboard.php                      ← Main dashboard with charts
│   ├── employees.php                      ← Employee management table
│   ├── records.php                        ← Daily attendance records
│   ├── reports.php                        ← Monthly HR reports
│   ├── settings.php                       ← Settings form
│   ├── db-setup.php                       ← Database repair tool
│   ├── diagnostics.php                    ← System health check
│   └── direct-add.php                     ← AJAX-bypass employee add
│
├── 📁 templates/                           ← Frontend templates
│   ├── attendance-page.php                ← /attendance page
│   └── attendance-form.php                ← [employee_attendance] shortcode
│
└── 📁 assets/
    ├── css/admin.css                      ← Admin styles
    ├── css/frontend.css                   ← Attendance page styles
    ├── js/admin.js                        ← Admin scripts
    └── js/frontend.js                     ← Frontend scripts
```

---

## 🗄️ Database Tables

| Table | Purpose | Key Columns |
|---|---|---|
| `wp_eam_employees` | Employee profiles | id, employee_id, email, password, department |
| `wp_eam_attendance` | Daily records | employee_id, date, check_in_time, check_out_time, status |
| `wp_eam_leaves` | Leave requests | employee_id, leave_type, start_date, end_date, status |
| `wp_eam_monthly_summary` | HR summaries | employee_id, present_days, absent_days, total_hours |

---

## ⏱️ Automated Tasks (Cron)

| Job | Runs | What It Does |
|---|---|---|
| Auto Checkout | Every hour | Closes sessions open longer than 6 hours |
| Mark Absences | Daily at 2:00 AM | Creates absent records for yesterday's no-shows |
| Monthly Reports | 27th at 1:00 AM | Generates salary period summaries for all employees |

---

## 🛠️ Troubleshooting

<details>
<summary><b>/attendance page shows 404 Not Found</b></summary>

Go to **WordPress Admin → Settings → Permalinks** and click **Save Changes**.  
This flushes the rewrite rules and registers the custom URL.
</details>

<details>
<summary><b>Database tables missing after activation</b></summary>

Go to **Attendance → Database Setup** and click **"Create / Fix All Tables"**.  
This runs the table creation SQL directly.
</details>

<details>
<summary><b>Add Employee button not saving</b></summary>

Try the bypass method: **Attendance → Direct Add (Test)** — this adds employees directly without AJAX.  
Also check **Attendance → Diagnostics** for system health info.
</details>

<details>
<summary><b>Auto-checkout not running</b></summary>

WordPress cron relies on site traffic. On low-traffic sites, add a real cron job:  
`*/30 * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron >/dev/null 2>&1`
</details>

---

## 📜 Changelog

### v1.0.0 — Initial Release
- ✅ Employee management with modal form
- ✅ Check-in / check-out with timestamp & status
- ✅ Auto-checkout for forgotten sessions (labeled "Forgotten Checkout")
- ✅ Auto-absence marking for working days
- ✅ Monthly salary period reports (27th–26th)
- ✅ Department statistics
- ✅ 30-day attendance trend chart
- ✅ CSV export for records and reports
- ✅ Admin dashboard with live stats
- ✅ Configurable settings panel
- ✅ Database setup / repair tool
- ✅ System diagnostics page
- ✅ Background cron automation
- ✅ Mobile-responsive attendance page

---

## 🤝 Contributing

Contributions, issues and feature requests are welcome!

1. Fork the repository
2. Create your branch: `git checkout -b feature/your-feature`
3. Commit changes: `git commit -m "Add: your feature description"`
4. Push: `git push origin feature/your-feature`
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **GPL v2 or later**.  
See [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html) for details.

---

<div align="center">

**Built with ❤️ for [MedLink Analytics](https://medlinkanalytics.com)**

👤 **Author:** [Sardar Ali Khamosh](https://sardaralikhamosh.github.io)  
🐙 **GitHub:** [@sardaralikhamosh](https://github.com/sardaralikhamosh)

⭐ If this plugin helped you, please give it a star on GitHub!

</div>
