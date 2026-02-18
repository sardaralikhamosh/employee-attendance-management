# 🕐 Employee Attendance Management System

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue?logo=wordpress)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-GPL%20v2-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange)](https://github.com/sardaralikhamosh/employee-attendance-management/releases)

A complete **WordPress plugin** for employee attendance management — built for night-shift teams. Employees check in/out via a dedicated page, admins get a full HR analytics dashboard.

**Live Site:** [medlinkanalytics.com](https://medlinkanalytics.com)  
**Author:** [Sardar Ali Khamosh](https://sardaralikhamosh.github.io)

---

## ✨ Features

| Feature | Details |
|---|---|
| 🌙 Night Shift Support | Default hours: 7:00 PM – 4:00 AM (configurable) |
| ⏰ Grace Period | 20 min before & after shift start |
| 🔄 Auto Checkout | Forgotten → auto-closes after 6 hours |
| 📅 Auto Absence | Marks absent for missed working days (Mon–Fri) |
| 💰 Salary Period | 27th–26th monthly cycle |
| 📊 HR Dashboard | Live stats, trends chart, today's attendance |
| 👥 Unlimited Employees | No employee cap |
| 🏢 Department Analytics | Stats by department |
| 📤 CSV Export | Export attendance and monthly reports |
| 🔒 Secure Login | Email + password per employee |

---

## 🚀 Installation

### From GitHub Releases (Upload to WordPress)
1. Go to **[Releases](https://github.com/sardaralikhamosh/employee-attendance-management/releases)**
2. Download `employee-attendance-management-x.x.x.zip`
3. In WordPress: **Plugins → Add New → Upload Plugin**
4. Upload the ZIP → **Install Now → Activate**
5. Go to **Settings → Permalinks → Save Changes** ⚠️ Required!

### Manual (FTP/File Manager)
```bash
cd wp-content/plugins/
git clone https://github.com/sardaralikhamosh/employee-attendance-management.git
```

---

## ⚙️ Default Settings

| Setting | Default |
|---|---|
| Office Start | 7:00 PM (19:00) |
| Office End | 4:00 AM (04:00) |
| Grace Period | 20 minutes |
| Auto Checkout | After 6 hours |
| Salary Period | 27th – 26th |
| Working Days | Monday – Friday |

---

## 🗄️ Database Tables

- `wp_eam_employees` — Employee records  
- `wp_eam_attendance` — Daily check-in/out  
- `wp_eam_leaves` — Leave requests  
- `wp_eam_monthly_summary` — Monthly HR summaries  

---

## 🛠️ Troubleshooting

- **404 on /attendance** → Settings → Permalinks → Save Changes  
- **Tables missing** → Attendance → Database Setup → Create Tables  
- **Save Employee fails** → Attendance → Direct Add (Test) page  

---

## 📜 Changelog

### v1.0.0
- Initial release with all core features

---

## 📄 License

[GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html)

**Author:** [Sardar Ali Khamosh](https://sardaralikhamosh.github.io) | [GitHub](https://github.com/sardaralikhamosh)
