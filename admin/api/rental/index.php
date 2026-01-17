<?php
// Admin API endpoint for rental operations
require_once __DIR__ . '/../../../config/paths.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../controllers/AdminRentalController.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Get the action from URL
$action = $_GET['action'] ?? '';
$controller = new AdminRentalController($conn);

switch ($action) {
    case 'detail':
        // Get rental details
        $rental_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$rental_id) {
            echo json_encode(['error' => 'ID tidak valid']);
            exit();
        }
        
        // Debug: Log the rental_id
        error_log("API: Fetching rental details for ID: " . $rental_id);
        
        $stmt = $conn->prepare("
            SELECT s.*, p.nama_mesin, p.gambar, u.nama_lengkap as nama_penyewa, u.email 
            FROM sewa s 
            JOIN produk p ON s.id_produk = p.id 
            JOIN penyewa u ON s.id_penyewa = u.id 
            WHERE s.id = ?
        ");
        $stmt->bind_param("i", $rental_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Debug: Log the result count
        error_log("API: Result count: " . $result->num_rows);
        
        if ($result->num_rows > 0) {
            $rental = $result->fetch_assoc();
            // Calculate duration
            $start = new DateTime($rental['tanggal_sewa']);
            $end = new DateTime($rental['tanggal_kembali']);
            $rental['lama_sewa'] = $start->diff($end)->days + 1;
            
            // Debug: Log success
            error_log("API: Rental found: " . json_encode($rental));
            
            echo json_encode(['rental' => $rental]);
        } else {
            // Debug: Log no data found
            error_log("API: No rental found for ID: " . $rental_id);
            
            // Check if rental exists at all
            $check_stmt = $conn->prepare("SELECT id FROM sewa WHERE id = ?");
            $check_stmt->bind_param("i", $rental_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            error_log("API: Rental exists check: " . $check_result->num_rows);
            
            echo json_encode(['error' => 'Data tidak ditemukan']);
        }
        break;
        
    case 'update_status':
        $controller->updateStatus();
        break;
        
    default:
        echo json_encode(['error' => 'Action tidak dikenali']);
        break;
}
?>
