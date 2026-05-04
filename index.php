<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: views/dashboard.php");
} else {
    header("Location: auth/login.php");
}
exit();
?>
