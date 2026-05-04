<?php
require_once '../includes/functions.php';

if (isLoggedIn()) {
    header("Location: " . BASE_URL . "views/dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $query = "SELECT id, username, password, full_name, role FROM users WHERE username = ? AND is_active = 1";
        $stmt = $db->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: " . BASE_URL . "views/dashboard.php");
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="container-fluid">
        <div class="row min-vh-100">

            <div class="col-lg-8 d-none d-lg-block login-bg">
                <div class="overlay"></div>
                <div class="content">
                    <h1 class="display-4 text-white fw-bold mb-4">
                        <i class="fas fa-graduation-cap me-3"></i>
                        Computer Science Department
                    </h1>
                    <p class="lead text-white-50 mb-4">
                        Attendance Management System for Computer Science Students
                    </p>
                                            <div class="features">
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Computer Science Students</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Programming & Development</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Database Management</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Software Engineering</span>
                            </div>
                        </div>
                </div>
            </div>
            

            <div class="col-lg-4 col-md-12 d-flex align-items-center justify-content-center">
                <div class="login-form-container">
                    <div class="text-center mb-4">
                        <div class="logo-circle mb-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h2 class="fw-bold text-primary">Welcome Back</h2>
                        <p class="text-muted">Sign in to your account</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="login-form">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Username" required>
                            <label for="username">
                                <i class="fas fa-user me-2"></i>Username
                            </label>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Password" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Default: admin / password
                            </small>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                <i class="fas fa-university me-1"></i>
                                Computer Science Department
                            </small>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted small">
                            &copy; 2024 Attendance Management System. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/login.js"></script>
</body>
</html> 