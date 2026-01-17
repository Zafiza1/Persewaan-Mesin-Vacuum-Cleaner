<?php
// API endpoint to get product details
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Include path configuration
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/database_helper.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID required']);
    exit();
}

$product_id = intval($_GET['id']);

// Fetch product using helper
$product = getProductById($conn, $product_id);

if ($product) {
    echo json_encode([
        'success' => true,
        'id' => $product['id'],
        'nama_mesin' => $product['nama_mesin'],
        'harga_sewa_per_hari' => (float)$product['harga_sewa_per_hari'],
        'stok' => (int)$product['stok']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}

$conn->close();
?>
