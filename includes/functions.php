<?php
session_start();

define('BASE_URL', '/AMS/');


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "auth/login.php");
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: " . BASE_URL . "views/dashboard.php?error=access_denied");
        exit();
    }
}


function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length));
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

function getAttendanceStatus($status) {
    $statusMap = [
        'present' => '<span class="badge bg-success">Present</span>',
        'absent' => '<span class="badge bg-danger">Absent</span>',
        'late' => '<span class="badge bg-warning">Late</span>',
        'excused' => '<span class="badge bg-info">Excused</span>'
    ];
    return $statusMap[$status] ?? $status;
}

function calculateAttendancePercentage($present, $total) {
    if ($total == 0) return 0;
    return round(($present / $total) * 100, 2);
}


function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = $flash['type'] === 'success' ? 'alert-success' : 
                     ($flash['type'] === 'error' ? 'alert-danger' : 'alert-info');
        echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}
?> 