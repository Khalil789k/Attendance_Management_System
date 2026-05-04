<?php
require_once '../includes/functions.php';
requireLogin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$message = '';
$class_id = $_GET['class_id'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $class_id = (int)$_POST['class_id'];
    $date = $_POST['date'];
    $attendance_data = $_POST['attendance'] ?? [];
    
    try {
        $db->beginTransaction();
        
        $delete_query = "DELETE FROM attendance WHERE class_id = ? AND date = ?";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->execute([$class_id, $date]);
        
        $insert_query = "INSERT INTO attendance (student_id, class_id, date, status) VALUES (?, ?, ?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        
        foreach ($attendance_data as $student_id => $status) {
            $insert_stmt->execute([$student_id, $class_id, $date, $status]);
        }
        
        $db->commit();
        setFlashMessage('success', 'Attendance marked successfully for ' . formatDate($date));
        header("Location: attendance.php?class_id=$class_id&date=$date");
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $message = 'Error saving attendance: ' . $e->getMessage();
    }
}

$classes_query = "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_name, section";
$classes_stmt = $db->prepare($classes_query);
$classes_stmt->execute();
$classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);

$students = [];
if ($class_id) {
    $students_query = "SELECT s.*, a.status as current_status 
                      FROM students s 
                      LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ? 
                      WHERE s.class_id = ? AND s.is_active = 1 
                      ORDER BY s.full_name";
    $students_stmt = $db->prepare($students_query);
    $students_stmt->execute([$date, $class_id]);
    $students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Mark Attendance';
$current_page = 'attendance';
$nav_title = 'Attendance Management';
$nav_subtitle = 'Mark or update daily attendance';

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
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Select Class & Date</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
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
                <div class="col-md-5">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $date; ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($class_id): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Attendance Sheet: <?php echo formatDate($date); ?>
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">Mark All Present</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">Mark All Absent</button>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No students found in this class.</p>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                        <input type="hidden" name="date" value="<?php echo $date; ?>">
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Roll Number</th>
                                        <th>Student Name</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td>
                                                <div class="d-flex gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input status-radio present" type="radio" 
                                                               name="attendance[<?php echo $student['id']; ?>]" 
                                                               value="present" id="p<?php echo $student['id']; ?>"
                                                               <?php echo ($student['current_status'] === 'present' || !$student['current_status']) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label text-success" for="p<?php echo $student['id']; ?>">Present</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input status-radio absent" type="radio" 
                                                               name="attendance[<?php echo $student['id']; ?>]" 
                                                               value="absent" id="a<?php echo $student['id']; ?>"
                                                               <?php echo ($student['current_status'] === 'absent') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label text-danger" for="a<?php echo $student['id']; ?>">Absent</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            <button type="submit" name="mark_attendance" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Save Attendance
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function markAll(status) {
    document.querySelectorAll('.' + status).forEach(radio => radio.checked = true);
}
</script>

<?php require_once '../includes/footer.php'; ?>