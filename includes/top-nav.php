<div class="top-nav">
    <div>
        <h1 class="nav-title"><?php echo $nav_title ?? 'Dashboard'; ?></h1>
        <p class="text-muted mb-0"><?php echo $nav_subtitle ?? 'Attendance Management System'; ?></p>
    </div>
    
    <div class="user-menu">
        <div class="user-info">
            <p class="user-name"><?php echo $_SESSION['full_name']; ?></p>
            <p class="user-role"><?php echo ucfirst($_SESSION['role']); ?></p>
        </div>
        <div class="user-avatar">
            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
        </div>
    </div>
</div>
