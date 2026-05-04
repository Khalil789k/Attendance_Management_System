<?php
require_once '../includes/functions.php';
requireLogin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$type = $_GET['type'] ?? 'overview';
$class_id = $_GET['class_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$classes_query = "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_name, section";
$classes_stmt = $db->prepare($classes_query);
$classes_stmt->execute();
$classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);

$report_data = [];

if ($type === 'class' && $class_id) {
    $query = "SELECT s.full_name, s.roll_number,
              COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
              COUNT(a.id) as total_days
              FROM students s
              LEFT JOIN attendance a ON s.id = a.student_id AND a.date BETWEEN ? AND ?
              WHERE s.class_id = ? AND s.is_active = 1
              GROUP BY s.id
              ORDER BY s.full_name";
    $stmt = $db->prepare($query);
    $stmt->execute([$start_date, $end_date, $class_id]);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($type === 'student' && $student_id) {
    $query = "SELECT a.date, a.status, c.class_name, c.section
              FROM attendance a
              JOIN classes c ON a.class_id = c.id
              WHERE a.student_id = ? AND a.date BETWEEN ? AND ?
              ORDER BY a.date DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$student_id, $start_date, $end_date]);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Attendance Reports';
$current_page = 'reports';
$nav_title = 'Attendance Reports';
$nav_subtitle = 'Analyze attendance trends and records';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="main-content">
    <?php 
    require_once '../includes/top-nav.php';
    displayFlashMessage(); 
    ?>

    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $type === 'overview' ? 'active' : ''; ?>" href="reports.php?type=overview">Overview</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $type === 'class' ? 'active' : ''; ?>" href="reports.php?type=class">Class Report</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $type === 'student' ? 'active' : ''; ?>" href="reports.php?type=student">Student Report</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                
                <?php if ($type === 'class'): ?>
                    <div class="col-md-3">
                        <label class="form-label">Class</label>
                        <select name="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php elseif ($type === 'student'): ?>
                    <div class="col-md-3">
                        <label class="form-label">Student ID / Roll No</label>
                        <input type="text" name="student_id" class="form-control" value="<?php echo $student_id; ?>" required>
                    </div>
                <?php endif; ?>

                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-file-alt me-2"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($type === 'class' && $class_id): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Class Attendance Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Roll Number</th>
                                <th>Name</th>
                                <th>Present Days</th>
                                <th>Total Days</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['roll_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><?php echo $row['present_count']; ?></td>
                                    <td><?php echo $row['total_days']; ?></td>
                                    <td>
                                        <?php 
                                        $perc = calculateAttendancePercentage($row['present_count'], $row['total_days']);
                                        $color = $perc < 75 ? 'danger' : 'success';
                                        echo "<span class='badge bg-{$color}'>{$perc}%</span>";
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>