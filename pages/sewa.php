<?php
echo "DEBUG: sewa.php start<br>";

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

echo "DEBUG: files loaded<br>";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "DEBUG: session started, user logged in: " . (is_user_logged_in() ? "yes" : "no") . "<br>";

require_once __DIR__ . '/../controllers/RentalController.php';
$controller = new RentalController($conn);
echo "DEBUG: controller created<br>";
$controller->create();
echo "DEBUG: done<br>";
?>
