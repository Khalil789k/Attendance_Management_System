<?php
require_once '../includes/functions.php';
requireLogin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$stats = [];

$query = "SELECT COUNT(*) as count FROM students WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM classes WHERE is_active = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['classes'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$today = date('Y-m-d');
$query = "SELECT COUNT(*) as count FROM attendance WHERE date = ?";
$stmt = $db->prepare($query);
$stmt->execute([$today]);
$stats['today_attendance'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT COUNT(*) as count FROM attendance WHERE date = ? AND status = 'present'";
$stmt = $db->prepare($query);
$stmt->execute([$today]);
$stats['present_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$query = "SELECT a.*, s.full_name, s.roll_number, c.class_name, c.section 
          FROM attendance a 
          JOIN students s ON a.student_id = s.id 
          JOIN classes c ON a.class_id = c.id 
          ORDER BY a.created_at DESC 
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Dashboard';
$current_page = 'dashboard';
$nav_title = 'Computer Science Dashboard';
$nav_subtitle = 'Welcome back, ' . $_SESSION['full_name'] . '!';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="main-content">
    <?php 
    require_once '../includes/top-nav.php';
    displayFlashMessage(); 
    ?>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-number"><?php echo $stats['students']; ?></div>
                <div class="stats-label">Total Students</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card success">
                <div class="stats-icon success">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <div class="stats-number"><?php echo $stats['classes']; ?></div>
                <div class="stats-label">Total Classes</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card info">
                <div class="stats-icon info">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stats-number"><?php echo $stats['today_attendance']; ?></div>
                <div class="stats-label">Today's Attendance</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card warning">
                <div class="stats-icon warning">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stats-number"><?php echo $stats['present_today']; ?></div>
                <div class="stats-label">Present Today</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="attendance.php" class="btn btn-primary w-100">
                                <i class="fas fa-clipboard-check me-2"></i>
                                Mark Attendance
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="students.php?action=add" class="btn btn-success w-100">
                                <i class="fas fa-user-plus me-2"></i>
                                Add Student
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="reports.php" class="btn btn-info w-100">
                                <i class="fas fa-chart-bar me-2"></i>
                                View Reports
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="classes.php" class="btn btn-warning w-100">
                                <i class="fas fa-chalkboard me-2"></i>
                                Manage Classes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Attendance Activity
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_attendance)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent attendance records found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_attendance as $record): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($record['full_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($record['roll_number']); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['class_name'] . ' - ' . $record['section']); ?></td>
                                            <td><?php echo formatDate($record['date']); ?></td>
                                            <td><?php echo getAttendanceStatus($record['status']); ?></td>
                                            <td><?php echo formatDateTime($record['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>