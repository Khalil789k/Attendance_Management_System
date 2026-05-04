<div class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>views/dashboard.php" class="sidebar-brand">
            <i class="fas fa-laptop-code me-2"></i>
            CS-AMS
        </a>
    </div>
    
    <div class="sidebar-menu">
        <a href="<?php echo BASE_URL; ?>views/dashboard.php" class="sidebar-item <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>views/students.php" class="sidebar-item <?php echo $current_page === 'students' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            Students
        </a>
        <a href="<?php echo BASE_URL; ?>views/classes.php" class="sidebar-item <?php echo $current_page === 'classes' ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard"></i>
            Classes
        </a>
        <a href="<?php echo BASE_URL; ?>views/attendance.php" class="sidebar-item <?php echo $current_page === 'attendance' ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-check"></i>
            Mark Attendance
        </a>
        <a href="<?php echo BASE_URL; ?>views/reports.php" class="sidebar-item <?php echo $current_page === 'reports' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            Reports
        </a>
        <?php if (isAdmin()): ?>
        <a href="<?php echo BASE_URL; ?>admin/users.php" class="sidebar-item <?php echo $current_page === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i>
            Users
        </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>auth/logout.php" class="sidebar-item">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
    </div>
</div>
