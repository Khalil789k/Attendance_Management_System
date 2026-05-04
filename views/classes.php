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
        $class_name = sanitizeInput($_POST['class_name']);
        $section = sanitizeInput($_POST['section']);
        $program = sanitizeInput($_POST['program']);
        $academic_year = sanitizeInput($_POST['academic_year']);
        
        if (empty($class_name) || empty($section) || empty($program)) {
            $message = 'Please fill all required fields.';
        } else {
            $check_query = "SELECT id FROM classes WHERE class_name = ? AND section = ? AND academic_year = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->execute([$class_name, $section, $academic_year]);
            
            if ($check_stmt->fetch()) {
                $message = 'Class with this section and year already exists.';
            } else {
                $query = "INSERT INTO classes (class_name, section, program, academic_year) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$class_name, $section, $program, $academic_year])) {
                    setFlashMessage('success', 'Class added successfully!');
                    header("Location: classes.php");
                    exit();
                } else {
                    $message = 'Error adding class.';
                }
            }
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $class_name = sanitizeInput($_POST['class_name']);
        $section = sanitizeInput($_POST['section']);
        $program = sanitizeInput($_POST['program']);
        $academic_year = sanitizeInput($_POST['academic_year']);
        
        $check_query = "SELECT id FROM classes WHERE class_name = ? AND section = ? AND academic_year = ? AND id != ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$class_name, $section, $academic_year, $id]);
        
        if ($check_stmt->fetch()) {
            $message = 'Class with this section and year already exists.';
        } else {
            $query = "UPDATE classes SET class_name = ?, section = ?, program = ?, academic_year = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$class_name, $section, $program, $academic_year, $id])) {
                setFlashMessage('success', 'Class updated successfully!');
                header("Location: classes.php");
                exit();
            } else {
                $message = 'Error updating class.';
            }
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM classes WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$id])) {
        setFlashMessage('success', 'Class deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting class.');
    }
    header("Location: classes.php");
    exit();
}

$class_edit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "SELECT * FROM classes WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $class_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($action === 'list') {
    $query = "SELECT * FROM classes WHERE is_active = 1 ORDER BY academic_year DESC, class_name ASC, section ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = ucfirst($action) . ' Classes';
$current_page = 'classes';
$nav_title = ($action === 'add' ? 'Add Class' : ($action === 'edit' ? 'Edit Class' : 'Classes'));
$nav_subtitle = 'Manage academic classes and sections';

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
                    <i class="fas fa-chalkboard me-2"></i>
                    All Classes
                </h5>
                <a href="classes.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Add Class
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($classes)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-chalkboard fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No classes found.</p>
                        <a href="classes.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>
                            Add First Class
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Section</th>
                                    <th>Program</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($class['section']); ?></span></td>
                                        <td><?php echo htmlspecialchars($class['program']); ?></td>
                                        <td><?php echo htmlspecialchars($class['academic_year']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="classes.php?action=edit&id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="classes.php?action=delete&id=<?php echo $class['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this class?')">
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
                    <?php echo $action === 'add' ? 'Add New Class' : 'Edit Class'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $class_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="col-md-6">
                        <label class="form-label">Class Name *</label>
                        <input type="text" class="form-control" name="class_name" value="<?php echo $class_edit['class_name'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Section *</label>
                        <input type="text" class="form-control" name="section" value="<?php echo $class_edit['section'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Program *</label>
                        <input type="text" class="form-control" name="program" value="<?php echo $class_edit['program'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Academic Year</label>
                        <input type="text" class="form-control" name="academic_year" value="<?php echo $class_edit['academic_year'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            <?php echo $action === 'add' ? 'Add Class' : 'Update Class'; ?>
                        </button>
                        <a href="classes.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>