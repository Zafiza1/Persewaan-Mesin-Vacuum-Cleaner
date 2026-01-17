<?php
require_once __DIR__ . '/../config/paths.php';
session_start();

// Hanya unset session variables terkait admin
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_nama']);

// Redirect to login page
header("Location: " . ADMIN_URL . "login.php");
exit();
?>
