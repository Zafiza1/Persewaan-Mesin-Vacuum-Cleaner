<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: Start<br>";

try {
    echo "Step 2: Including paths.php<br>";
    require_once __DIR__ . '/config/paths.php';
    
    echo "Step 3: Including database.php<br>";
    require_once __DIR__ . '/config/database.php';
    
    echo "Step 4: Including security.php<br>";
    require_once __DIR__ . '/includes/security.php';
    
    echo "Step 5: Including functions.php<br>";
    require_once __DIR__ . '/includes/functions.php';
    
    echo "Step 6: Starting session<br>";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "Step 7: Including RentalController<br>";
    require_once __DIR__ . '/controllers/RentalController.php';
    
    echo "Step 8: Creating controller<br>";
    $controller = new RentalController($conn);
    
    echo "Step 9: Calling create() method<br>";
    $controller->create();
    
    echo "Step 10: Done<br>";
    
} catch (Error $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "FILE: " . $e->getFile() . "<br>";
    echo "LINE: " . $e->getLine() . "<br>";
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "<br>";
    echo "FILE: " . $e->getFile() . "<br>";
    echo "LINE: " . $e->getLine() . "<br>";
}
?>
