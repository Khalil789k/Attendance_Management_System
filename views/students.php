<?php
require_once '../includes/functions.php';
requireLogin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $roll_number = sanitizeInput($_POST['roll_number']);
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $class_id = (int)$_POST['class_id'];
        $gender = sanitizeInput($_POST['gender']);
        $parent_name = sanitizeInput($_POST['parent_name']);
        $parent_phone = sanitizeInput($_POST['parent_phone']);
        
        if (empty($roll_number) || empty($full_name) || empty($class_id)) {
            $message = 'Please fill all required fields.';
        } else {
            $check_query = "SELECT id FROM students WHERE roll_number = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$roll_number]);
            
            if ($check_stmt->fetch()) {
                $message = 'Roll number already exists.';
            } else {
                $query = "INSERT INTO students (roll_number, full_name, email, phone, class_id, gender, parent_name, parent_phone) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$roll_number, $full_name, $email, $phone, $class_id, $gender, $parent_name, $parent_phone])) {
                    setFlashMessage('success', 'Student added successfully!');
                    header("Location: students.php");
                    exit();
                } else {
                    $message = 'Error adding student.';
                }
            }
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $roll_number = sanitizeInput($_POST['roll_number']);
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $class_id = (int)$_POST['class_id'];
        $gender = sanitizeInput($_POST['gender']);
        $parent_name = sanitizeInput($_POST['parent_name']);
        $parent_phone = sanitizeInput($_POST['parent_phone']);
        
        $check_query = "SELECT id FROM students WHERE roll_number = ? AND id != ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$roll_number, $id]);
        
        if ($check_stmt->fetch()) {
            $message = 'Roll number already exists.';
        } else {
            $query = "UPDATE students SET roll_number = ?, full_name = ?, email = ?, phone = ?, 
                     class_id = ?, gender = ?, parent_name = ?, parent_phone = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$roll_number, $full_name, $email, $phone, $class_id, $gender, $parent_name, $parent_phone, $id])) {
                setFlashMessage('success', 'Student updated successfully!');
                header("Location: students.php");
                exit();
            } else {
                $message = 'Error updating student.';
            }
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM students WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$id])) {
        setFlashMessage('success', 'Student deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting student.');
    }
    header("Location: students.php");
    exit();
}

$student = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        setFlashMessage('error', 'Student not found.');
        header("Location: students.php");
        exit();
    }
}

$classes_query = "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_name, section";
$classes_stmt = $db->prepare($classes_query);
$classes_stmt->execute();
$classes = $classes_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($action === 'list') {
    $query = "SELECT s.*, c.class_name, c.section 
              FROM students s 
              JOIN classes c ON s.class_id = c.id 
              WHERE s.is_active = 1 
              ORDER BY s.full_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = ucfirst($action) . ' Students';
$current_page = 'students';
$nav_title = ($action === 'add' ? 'Add Student' : ($action === 'edit' ? 'Edit Student' : 'Students'));
$nav_subtitle = 'Manage student information';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="main-content">
    <?php 
    require_once '../includes/top-nav.php';
    displayFlashMessage(); 
    ?>

    <?php if ($message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    All Students
                </h5>
                <a href="students.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Add Student
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No students found.</p>
                        <a href="students.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Add First Student
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Roll Number</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Contact</th>
                                    <th>Parent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['roll_number']); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                                <?php if ($student['gender']): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-<?php echo $student['gender'] === 'male' ? 'mars' : 'venus'; ?> me-1"></i>
                                                        <?php echo ucfirst($student['gender']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo htmlspecialchars($student['class_name'] . ' - ' . $student['section']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($student['email']): ?>
                                                <div><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($student['email']); ?></div>
                                            <?php endif; ?>
                                            <?php if ($student['phone']): ?>
                                                <div><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($student['phone']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['parent_name']): ?>
                                                <div><strong><?php echo htmlspecialchars($student['parent_name']); ?></strong></div>
                                            <?php endif; ?>
                                            <?php if ($student['parent_phone']): ?>
                                                <div><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($student['parent_phone']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="students.php?action=edit&id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="students.php?action=delete&id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this student?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> me-2"></i>
                    <?php echo $action === 'add' ? 'Add New Student' : 'Edit Student'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="col-md-6">
                        <label for="roll_number" class="form-label">Roll Number *</label>
                        <input type="text" class="form-control" id="roll_number" name="roll_number" 
                               value="<?php echo $student['roll_number'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo $student['full_name'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo $student['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo $student['phone'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="class_id" class="form-label">Class *</label>
                        <select class="form-control" id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" 
                                        <?php echo ($student['class_id'] ?? '') == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-control" id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo ($student['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="parent_name" class="form-label">Parent Name</label>
                        <input type="text" class="form-control" id="parent_name" name="parent_name" 
                               value="<?php echo $student['parent_name'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="parent_phone" class="form-label">Parent Phone</label>
                        <input type="tel" class="form-control" id="parent_phone" name="parent_phone" 
                               value="<?php echo $student['parent_phone'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-12">
                        <hr>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $action === 'add' ? 'Add Student' : 'Update Student'; ?>
                            </button>
                            <a href="students.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>