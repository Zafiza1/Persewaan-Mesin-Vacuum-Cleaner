<?php
require_once __DIR__ . '/../config/paths.php';
session_start();

// Hanya unset session variables terkait user
unset($_SESSION['user_id']);
unset($_SESSION['username']);
unset($_SESSION['nama_lengkap']);
unset($_SESSION['email']);
unset($_SESSION['no_hp']);
unset($_SESSION['alamat']);

// Redirect to home page
header("Location: " . BASE_URL . "index.php");
exit();
?>
