<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/database_helper.php';

// Check admin privileges first
if (!isAdmin()) {
    redirect(ADMIN_URL . 'login.php');
}

// Get product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product using helper
$product = getProductById($conn, $id);

// Redirect if product not found
if (!$product) {
    $_SESSION['error'] = "Produk tidak ditemukan";
    redirect(ADMIN_URL . 'manage_products.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_mesin = $conn->real_escape_string($_POST['nama_mesin']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $harga_sewa_per_hari = (float)$_POST['harga_sewa_per_hari'];
    $stok = (int)$_POST['stok'];
    
    // Check if new image is uploaded
    $gambar = $product['gambar']; // Default to existing image
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $upload_dir = BASE_PATH . 'assets/images/products/';
        $file_name = basename($_FILES['gambar']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_name = uniqid('product_', true).'.'.$file_ext;
        $target_path = $upload_dir.$new_name;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_types)) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_path)) {
                // Delete old image if exists
                if ($product['gambar'] && file_exists($upload_dir.$product['gambar'])) {
                    unlink($upload_dir.$product['gambar']);
                }
                $gambar = $new_name;
            } else {
                $errors[] = "Gagal mengupload gambar";
            }
        } else {
            $errors[] = "Hanya file JPG, JPEG, PNG, atau WEBP yang diperbolehkan";
        }
    }
    
    // Update product
    $price_column = detectPriceColumn($conn);
    // Extract the actual column name from the detected column
    if ($price_column === '0 as harga_sewa_per_hari') {
        $actual_price_column = 'harga_sewa_per_hari'; // fallback
    } elseif (strpos($price_column, ' as ') !== false) {
        $parts = explode(' as ', $price_column);
        $actual_price_column = $parts[0];
    } else {
        $actual_price_column = $price_column;
    }
    
    $stmt = $conn->prepare("UPDATE produk SET nama_mesin = ?, deskripsi = ?, $actual_price_column = ?, stok = ?, gambar = ? WHERE id = ?");
    $stmt->bind_param("ssdisi", $nama_mesin, $deskripsi, $harga_sewa_per_hari, $stok, $gambar, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Produk berhasil diperbarui";
        redirect(ADMIN_URL . 'manage_products.php');
    } else {
        $errors[] = "Error database: ".$conn->error;
    }
}

// Set page title and include HTML headers (only if not redirected)
$page_title = "Edit Produk";
$errors = $errors ?? [];
$success = false;

include '../includes/header.php';
?>

<div class="container py-4">
    <h2>Edit Produk</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nama Mesin</label>
            <input type="text" class="form-control" name="nama_mesin" value="<?= htmlspecialchars($product['nama_mesin']) ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control" name="deskripsi" rows="3" required><?= htmlspecialchars($product['deskripsi']) ?></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Harga Sewa per Hari</label>
                <input type="number" class="form-control" name="harga_sewa_per_hari" value="<?= $product['harga_sewa_per_hari'] ?>" min="0" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Stok Tersedia</label>
                <input type="number" class="form-control" name="stok" value="<?= $product['stok'] ?>" min="0" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar Produk Saat Ini</label>
            <div>
                <img src="<?php echo BASE_URL; ?>assets/images/products/<?= $product['gambar'] ?>" alt="<?= $product['nama_mesin'] ?>" class="img-thumbnail" style="width: 150px;">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar Produk Baru (Opsional)</label>
            <input type="file" class="form-control" name="gambar" accept="image/*">
            <small class="text-muted">Format: JPG, PNG, WEBP (Maks. 2MB)</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Produk</button>
        <a href="<?php echo ADMIN_URL; ?>manage_products.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
