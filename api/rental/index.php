<?php
// API endpoint for rental operations
require_once __DIR__ . '/../../config/paths.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../controllers/RentalController.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Get the action from URL
$action = $_GET['action'] ?? '';
$controller = new RentalController($conn);

switch ($action) {
    case 'calculate_price':
        $controller->calculatePrice();
        break;
        
    case 'store':
        $controller->store();
        break;
        
    case 'detail':
        // Get rental details
        $rental_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$rental_id) {
            echo json_encode(['error' => 'ID tidak valid']);
            exit();
        }
        
        $stmt = $conn->prepare("
            SELECT s.*, p.nama_mesin, p.gambar 
            FROM sewa s 
            JOIN produk p ON s.id_produk = p.id 
            WHERE s.id = ? AND s.id_penyewa = ?
        ");
        $stmt->bind_param("ii", $rental_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $rental = $result->fetch_assoc();
            $rental['total_harga_formatted'] = formatRupiah($rental['total_harga']);
            echo json_encode(['rental' => $rental]);
        } else {
            echo json_encode(['error' => 'Data tidak ditemukan']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Action tidak dikenali']);
        break;
}
?>
