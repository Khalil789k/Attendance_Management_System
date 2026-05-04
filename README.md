# Attendance Management System (AMS)

A simple, clean, and professional way to manage student attendance. Built specifically for the Computer Science department, but flexible enough for any class.

---

## What's inside?

*   **Dashboard:** Quick stats on students and classes.
*   **Students:** Manage student profiles and roll numbers.
*   **Classes:** Organize by sections and programs.
*   **Attendance:** Simple interface to mark daily attendance.
*   **Reports:** See who's coming to class and who's skipping (percentage-wise).
*   **Admin:** Manage users (Teachers vs. Admins).

---

## Quick Setup

1.  **Database:**
    *   Create a database named `attendance_system`.
    *   Import `database/setup.sql`.

2.  **Config:**
    *   Rename `config/database.php.example` to `config/database.php`.
    *   Put your database username and password in it.

3.  **Go:**
    *   Drop the folder in your web server (XAMPP/WAMP/etc.).
    *   Open it in your browser.

**Default Login:**
*   **User:** `admin`
*   **Pass:** `password`

---

## Project Structure

*   `admin/` - User management.
*   `auth/` - Login and logout.
*   `views/` - Main pages (Dashboard, Attendance, etc.).
*   `includes/` - Reusable parts like the sidebar and header.
*   `assets/` - CSS and JS.
*   `config/` - Database connection.
*   `database/` - SQL setup files.

---

## Security (Keep it safe)
*   Uses **PDO** to prevent SQL injection.
*   **Password hashing** for user security.
*   Session-based authentication.

---

Developed for educational excellence. Distributed under MIT License.