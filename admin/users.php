<?php
require_once '../includes/functions.php';
requireAdmin();

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'list';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $role = sanitizeInput($_POST['role']);
        
        if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
            $message = 'Please fill all required fields.';
        } elseif ($password !== $confirm_password) {
            $message = 'Passwords do not match.';
        } else {
            $query = "SELECT id FROM users WHERE username = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $message = 'Username already exists.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$username, $hashed_password, $full_name, $email, $role])) {
                    setFlashMessage('success', 'User added successfully!');
                    header("Location: users.php");
                    exit();
                } else {
                    $message = 'Error adding user.';
                }
            }
        }
    }
}

$users = [];
if ($action === 'list') {
    $query = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'User Management';
$current_page = 'users';
$nav_title = 'User Settings';
$nav_subtitle = 'Manage system administrators and staff';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<div class="main-content">
    <?php 
    require_once '../includes/top-nav.php';
    displayFlashMessage(); 
    ?>

    <?php if ($action === 'list'): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>System Users</h5>
                <a href="users.php?action=add" class="btn btn-primary">Add User</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo strtoupper($user['role']); ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action === 'add'): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New User</h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control">
                            <option value="teacher">Teacher</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">Create User</button>
                        <a href="users.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>