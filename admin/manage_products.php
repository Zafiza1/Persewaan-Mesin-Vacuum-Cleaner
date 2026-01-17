<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Include path configuration
require_once __DIR__ . '/../config/paths.php';

// Use path constants for includes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

// Check admin privileges first
if (!isAdmin()) {
    redirect('/admin/login.php');
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    
    // Check if product has existing rentals
    $check_rentals = $conn->prepare("SELECT COUNT(*) as rental_count FROM sewa WHERE id_produk = ?");
    $check_rentals->bind_param("i", $product_id);
    $check_rentals->execute();
    $rental_count = $check_rentals->get_result()->fetch_assoc()['rental_count'];
    $check_rentals->close();
    
    if ($rental_count > 0) {
        $_SESSION['error_message'] = "Produk tidak dapat dihapus karena memiliki riwayat penyewaan.";
        redirect_with_message(ADMIN_URL . 'manage_products.php', 'Produk tidak dapat dihapus karena memiliki riwayat penyewaan.', 'danger');
    } else {
        // Get product image to delete
        $stmt = $conn->prepare("SELECT gambar FROM produk WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Delete product
        $stmt = $conn->prepare("DELETE FROM produk WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            // Delete image file if exists
            if ($product && !empty($product['gambar'])) {
                $image_path = $_SERVER['DOCUMENT_ROOT'] . '/akasa_teknik/assets/images/products/' . $product['gambar'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            $_SESSION['success_message'] = "Produk berhasil dihapus";
            redirect_with_message(ADMIN_URL . 'manage_products.php', '', 'success');
        } else {
            $_SESSION['error_message'] = "Gagal menghapus produk: " . $stmt->error;
            redirect_with_message(ADMIN_URL . 'manage_products.php', 'Gagal menghapus produk.', 'danger');
        }
        $stmt->close();
    }
}

// Get all products using helper
$products = getAllProducts($conn);

require_once __DIR__ . '/../includes/header.php';

$page_title = "Kelola Produk";

?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h2 class="mb-4">Kelola Produk</h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Mesin</th>
                            <th>Harga Sewa</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($products)): ?>
                            <?php foreach($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
<img src="<?php echo IMAGES_URL; ?>products/<?php echo $product['gambar']; ?>" alt="<?php echo $product['nama_mesin']; ?>" class="img-thumbnail" style="width: 80px;">
                                </td>
                                <td><?php echo $product['nama_mesin']; ?></td>
                                <td><?php echo formatRupiah($product['harga_sewa_per_hari']); ?></td>
                                <td><?php echo $product['stok']; ?></td>
                                <td>
                                    <a href="<?php echo ADMIN_URL; ?>edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo ADMIN_URL; ?>manage_products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada produk yang ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
