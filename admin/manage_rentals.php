<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../controllers/AdminRentalController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    redirect_with_message(ADMIN_URL . 'login.php', 'Silakan login terlebih dahulu.', 'warning');
}

$controller = new AdminRentalController($conn);

// Handle report generation
if (isset($_GET['report']) && $_GET['report'] == '1') {
    $controller->report();
} else {
    $controller->index();
}
?>
