<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/database_helper.php';
require_once __DIR__ . '/../includes/header.php';

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Get products using safe query
if (!empty($search)) {
    $products = searchProducts($conn, $search);
} else {
    $products = getAllProducts($conn);
}

// Check for errors
$error_message = '';
if ($products === false) {
    $error_message = "Terjadi kesalahan dalam mengambil data produk.";
    if (ENVIRONMENT === 'development') {
        $error_message .= " Error: " . $conn->error;
    }
}
?>

<div class="container mt-4">
    <header class="page-header mb-4">
        <h1 class="fw-bold mb-3">Daftar Mesin Vacuum Cleaner</h1>
        <p class="lead text-muted">Pilih vacuum cleaner terbaik untuk kebutuhan cleaning Anda</p>
    </header>
    
    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <section class="products-section">
        <div class="row">
            <?php if(!empty($products)): ?>
                <?php foreach($products as $row): ?>
                    <article class="col-md-4 mb-4">
                        <div class="card h-100 product-card">
                            <img src="<?php echo IMAGES_URL; ?>products/<?php echo $row['gambar']; ?>" class="card-img-top w-100" style="object-fit: cover; height: 250px;" alt="<?php echo htmlspecialchars($row['nama_mesin']); ?>" onerror="this.src='<?php echo IMAGES_URL; ?>placeholder.jpg'">
                            <div class="card-body">
                                <h3 class="card-title h5"><?php echo htmlspecialchars($row['nama_mesin']); ?></h3>
                                <p class="card-text"><?php echo htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 100)); ?>...</p>
                                <div class="price-info mb-3">
                                    <p class="text-primary fw-bold">Rp <?php echo number_format($row['harga_sewa_per_hari'], 0, ',', '.'); ?>/hari</p>
                                    <p class="text-muted small">Stok: <?php echo (int)$row['stok']; ?> unit tersedia</p>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="/akasa_teknik/form_sewa_simple.php?produk_id=<?php echo (int)$row['id']; ?>" class="btn btn-primary w-100">
                                    <i class="fas fa-shopping-cart me-1"></i> Sewa Sekarang
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php if(!empty($error_message)): ?>
                            <?php echo htmlspecialchars($error_message); ?>
                        <?php else: ?>
                            Tidak ada produk yang ditemukan.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
