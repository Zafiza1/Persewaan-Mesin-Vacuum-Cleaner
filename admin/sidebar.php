<?php
// Admin sidebar navigation
// Clear any flash messages to prevent alerts
unset($_SESSION['flash_message']);
unset($_SESSION['flash_type']);
?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0">Menu Admin</h6>
    </div>
    <div class="list-group list-group-flush">
        <a href="dashboard.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a href="manage_products.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'manage_products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box me-2"></i>Kelola Produk
        </a>
        <a href="add_product.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'add_product.php' ? 'active' : ''; ?>">
            <i class="fas fa-plus me-2"></i>Tambah Produk
        </a>
        <a href="manage_rentals.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'manage_rentals.php' ? 'active' : ''; ?>">
            <i class="fas fa-handshake me-2"></i>Kelola Penyewaan
        </a>
        <a href="manage_orders.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'manage_orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan
        </a>
    </div>
</div>
