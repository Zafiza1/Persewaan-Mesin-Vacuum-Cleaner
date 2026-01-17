<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/database_helper.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

if (!is_user_logged_in()) {
    echo json_encode(['error' => 'Anda harus login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /akasa_teknik/pages/produk.php");
    exit();
}

// Validate CSRF
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['error' => 'Token tidak valid']);
    exit();
}

$produk_id = filter_input(INPUT_POST, 'produk_id', FILTER_VALIDATE_INT);
$start_date = sanitize_input($_POST['start_date'] ?? '');
$end_date = sanitize_input($_POST['end_date'] ?? '');
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$notes = sanitize_input($_POST['notes'] ?? '');

// Validate
if (!$produk_id || !$start_date || !$end_date || !$quantity) {
    echo json_encode(['error' => 'Data tidak lengkap']);
    exit();
}

if ($start_date > $end_date) {
    echo json_encode(['error' => 'Tanggal selesai harus setelah tanggal mulai']);
    exit();
}

// Get product using helper
$produk = getProductById($conn, $produk_id);

if (!$produk || $produk['stok'] < $quantity) {
    echo json_encode(['error' => 'Stok tidak mencukupi']);
    exit();
}

// Calculate price
$start = new DateTime($start_date);
$end = new DateTime($end_date);
$days = $start->diff($end)->days + 1;
$base_price = $produk['harga_sewa_per_hari'] * $days * $quantity;

// No discounts - simple pricing
$total_price = $base_price;

// Insert rental
$stmt = $conn->prepare("
    INSERT INTO sewa (id_penyewa, id_produk, tanggal_sewa, tanggal_kembali, jumlah, total_harga, status) 
    VALUES (?, ?, ?, ?, ?, ?, 'pending')
");
$stmt->bind_param("iissid", 
    $_SESSION['user_id'], 
    $produk_id, 
    $start_date, 
    $end_date, 
    $quantity, 
    $total_price
);

if ($stmt->execute()) {
        $_SESSION['success'] = "Penyewaan berhasil diajukan! Menunggu konfirmasi admin.";
        header("Location: /akasa_teknik/user/orders.php");
    } else {
        $_SESSION['error'] = "Terjadi kesalahan: " . $stmt->error;
        header("Location: /akasa_teknik/form_sewa_simple.php?produk_id=$produk_id");
    }
    $stmt->close();
?>
