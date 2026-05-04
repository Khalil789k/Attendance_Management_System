<?php
session_start();
session_destroy();
require_once '../includes/functions.php';
header("Location: " . BASE_URL . "auth/login.php");
exit();
?> 