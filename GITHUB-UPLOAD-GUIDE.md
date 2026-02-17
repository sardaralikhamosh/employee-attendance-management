# GitHub Upload & WordPress Install Guide

## PART 1 — Upload to GitHub
─────────────────────────────────────────────

### Step 1: Create a new GitHub repository

1. Open: https://github.com/new
2. Fill in:
   - Repository name:  employee-attendance-management
   - Description:      WordPress plugin for employee attendance management
   - Visibility:       ✅ Public
   - ✅ Add a README file  → UNCHECK this (we have our own)
3. Click: [Create repository]

─────────────────────────────────────────────

### Step 2: Upload plugin files to GitHub

On the new empty repo page:

1. Click: "uploading an existing file" (link in the middle)
   OR click: [Add file] → [Upload files]

2. Extract the ZIP: employee-attendance-management-v1.0.0.zip

3. Drag ALL these files/folders into the GitHub upload box:
   ┌─────────────────────────────────────┐
   │  employee-attendance-management.php │
   │  readme.txt                         │
   │  README.md                          │
   │  .gitignore                         │
   │  includes/   (folder)               │
   │  admin/      (folder)               │
   │  assets/     (folder)               │
   │  templates/  (folder)               │
   │  languages/  (folder)               │
   │  .github/    (folder)               │
   └─────────────────────────────────────┘
   NOTE: Drag the CONTENTS of the zip, not the zip itself!

4. Scroll down to "Commit changes"
   - Title: Initial release v1.0.0
   - Description: Employee Attendance Management System for WordPress
5. Click: [Commit changes]

─────────────────────────────────────────────

### Step 3: Create a Release with ZIP download

1. On your repo page, click "Releases" (right sidebar)
   OR go to: https://github.com/sardaralikhamosh/employee-attendance-management/releases

2. Click: [Create a new release]

3. Fill in:
   - Tag:         v1.0.0  (type it and select "Create new tag: v1.0.0")
   - Title:       Employee Attendance Management v1.0.0
   - Description: (paste this)
     
     ## Employee Attendance Management System
     
     Complete WordPress plugin for employee attendance with:
     - Night shift support (7 PM – 4 AM)
     - Check-in / Check-out system at /attendance
     - Auto checkout for forgotten sessions
     - Monthly HR reports (27th–26th salary period)
     - Admin dashboard with charts
     
     ### Installation
     1. Download ZIP below
     2. WordPress → Plugins → Add New → Upload Plugin
     3. Activate and go to Settings → Permalinks → Save Changes

4. Under "Attach binaries":
   - Upload: employee-attendance-management-v1.0.0.zip

5. Click: [Publish release]

✅ Your plugin is now on GitHub!
   URL: https://github.com/sardaralikhamosh/employee-attendance-management

─────────────────────────────────────────────
─────────────────────────────────────────────

## PART 2 — Install in WordPress from GitHub ZIP
─────────────────────────────────────────────

### Method: Upload Plugin ZIP

1. Download the ZIP from your GitHub release page:
   https://github.com/sardaralikhamosh/employee-attendance-management/releases

2. Log in to your WordPress admin:
   https://medlinkanalytics.com/blog/wp-admin

3. Go to: Plugins → Add New Plugin

4. Click: [Upload Plugin] (button at top)

5. Click: [Choose File] → select the ZIP file

6. Click: [Install Now]

7. After install: Click [Activate Plugin]

8. ⚠️ IMPORTANT: Go to Settings → Permalinks → click [Save Changes]
   (This makes the /attendance URL work)

9. Go to: Attendance → Database Setup → click [Create / Fix All Tables]

10. Go to: Attendance → Employees → Add your staff

11. Share this URL with employees:
    https://medlinkanalytics.com/blog/attendance

─────────────────────────────────────────────

## PART 3 — Future Updates (How to release v1.0.1, v1.0.2...)
─────────────────────────────────────────────

When you make changes to the plugin:

1. Edit the version in employee-attendance-management.php:
   Change:  Version: 1.0.0
   To:      Version: 1.0.1

2. Update readme.txt changelog section

3. Upload changed files to GitHub

4. Create a new Release with tag v1.0.1

5. Attach the new ZIP to the release

Users can then download and re-upload to update.

─────────────────────────────────────────────

## Quick Links

- Your GitHub Profile:  https://github.com/sardaralikhamosh
- Plugin Repo (after create): https://github.com/sardaralikhamosh/employee-attendance-management
- Your Website:         https://sardaralikhamosh.github.io
- WordPress Admin:      https://medlinkanalytics.com/blog/wp-admin
- Attendance Page:      https://medlinkanalytics.com/blog/attendance
