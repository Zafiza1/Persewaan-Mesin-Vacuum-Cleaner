<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Include path configuration
require_once __DIR__ . '/../config/paths.php';

// Use path constants for includes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/database_helper.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

// Check if user is logged in (session already started in header.php)
if (!is_user_logged_in()) {
    header("Location: /akasa_teknik/user/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];
$error = '';

// Get user's rental orders
try {
    // Get all products first to build price mapping
    $all_products = getAllProducts($conn);
    $price_map = [];
    foreach ($all_products as $product) {
        $price_map[$product['id']] = $product['harga_sewa_per_hari'];
    }
    
    $stmt = $conn->prepare("
        SELECT s.*, p.nama_mesin, p.gambar, py.nama_lengkap
        FROM sewa s
        JOIN produk p ON s.id_produk = p.id
        JOIN penyewa py ON s.id_penyewa = py.id
        WHERE s.id_penyewa = ?
        ORDER BY s.tanggal_sewa DESC
    ");

    if (!$stmt) {
        throw new Exception("Terjadi kesalahan sistem. Silakan coba lagi.");
    }

    $stmt->bind_param("i", $user_id);

    if (!$stmt->execute()) {
        throw new Exception("Terjadi kesalahan sistem. Silakan coba lagi.");
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }

    $stmt->close();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Riwayat Sewa</h2>
                    <p class="text-muted mb-0">Kelola dan pantau semua penyewaan Anda</p>
                </div>
                <a href="<?php echo BASE_URL; ?>pages/produk.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i> Sewa Baru
                </a>
            </div>

            <!-- Filter dan Sort Options -->
            <?php if(!empty($orders)): ?>
            <div class="card mb-4 border-0 bg-light">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filterStatus" class="form-label small text-muted">Filter Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="diproses">Diproses</option>
                                <option value="selesai">Selesai</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sortBy" class="form-label small text-muted">Urutkan</label>
                            <select class="form-select" id="sortBy">
                                <option value="tanggal_sewa">Tanggal Sewa</option>
                                <option value="total_harga">Total Harga</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sortOrder" class="form-label small text-muted">Urutan</label>
                            <select class="form-select" id="sortOrder">
                                <option value="desc">Terbaru</option>
                                <option value="asc">Terlama</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-outline-secondary w-100" id="resetFilter">
                                <i class="fas fa-redo me-2"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php if(empty($orders)): ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-clipboard-list fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Belum Ada Riwayat Sewa</h4>
                    <p class="text-muted mb-4">Anda belum memiliki riwayat penyewaan. Mulai sewa vacuum cleaner sekarang!</p>
                    <a href="<?php echo BASE_URL; ?>pages/produk.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i> Mulai Sewa Sekarang
                    </a>
                </div>
            <?php else: ?>
                <div class="row" id="ordersContainer">
                    <?php foreach($orders as $index => $order): ?>
                        <div class="col-lg-6 mb-4 order-item" 
                             data-status="<?php echo htmlspecialchars($order['status']); ?>"
                             data-date="<?php echo $order['tanggal_sewa']; ?>"
                             data-price="<?php echo $order['total_harga']; ?>"
                             data-index="<?php echo $index; ?>">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <?php if(!empty($order['gambar'])): ?>
                                            <img src="<?php echo IMAGES_URL . 'products/' . htmlspecialchars($order['gambar']); ?>"
                                                 class="img-fluid rounded-start h-100" 
                                                 alt="<?php echo htmlspecialchars($order['nama_mesin']); ?>"
                                                 style="object-fit: cover; min-height: 200px;"
                                                 onerror="this.src='<?php echo IMAGES_URL; ?>placeholder.jpg'">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center h-100 rounded-start" style="min-height: 200px;">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($order['nama_mesin']); ?></h5>
                                                    <span class="badge bg-<?php
                                                        switch($order['status']) {
                                                            case 'pending': echo 'warning'; break;
                                                            case 'diproses': echo 'info'; break;
                                                            case 'selesai': echo 'success'; break;
                                                            case 'dibatalkan': echo 'danger'; break;
                                                            default: echo 'secondary';
                                                        }
                                                    ?> fs-6">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    <?php
                                                    if (!empty($order['created_at']) && $order['created_at'] !== null) {
                                                        echo date('d M Y H:i', strtotime($order['created_at']));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </small>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Tanggal Mulai</small>
                                                    <strong><?php echo date('d M Y', strtotime($order['tanggal_sewa'])); ?></strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Tanggal Selesai</small>
                                                    <strong><?php echo date('d M Y', strtotime($order['tanggal_kembali'])); ?></strong>
                                                </div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Jumlah</small>
                                                    <span class="badge bg-light text-dark fs-6"><?php echo $order['jumlah']; ?> unit</span>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Harga/Unit</small>
                                                    <strong>Rp <?php echo number_format($price_map[$order['id_produk']] ?? 0, 0, ',', '.'); ?></strong>
                                                </div>
                                            </div>

                                            <?php if(!empty($order['catatan'])): ?>
                                                <div class="mb-3">
                                                    <small class="text-muted d-block">Catatan</small>
                                                    <div class="bg-light p-2 rounded small"><?php echo htmlspecialchars($order['catatan']); ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <div class="border-top pt-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">Total Pembayaran</small>
                                                    <h4 class="text-primary mb-0">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="text-center py-5" style="display: none;">
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Tidak Ada Hasil</h4>
                    <p class="text-muted mb-4">Tidak ada riwayat sewa yang sesuai dengan filter yang dipilih.</p>
                    <button class="btn btn-outline-primary" onclick="resetFilters()">
                        <i class="fas fa-redo me-2"></i> Reset Filter
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
ob_end_flush();
?>

<script>
// Filter and Sort functionality for user orders
document.addEventListener('DOMContentLoaded', function() {
    const filterStatus = document.getElementById('filterStatus');
    const sortBy = document.getElementById('sortBy');
    const sortOrder = document.getElementById('sortOrder');
    const resetFilter = document.getElementById('resetFilter');
    const ordersContainer = document.getElementById('ordersContainer');
    const noResults = document.getElementById('noResults');

    // Global resetFilters function
    window.resetFilters = function() {
        const filterStatus = document.getElementById('filterStatus');
        const sortBy = document.getElementById('sortBy');
        const sortOrder = document.getElementById('sortOrder');

        if (filterStatus) filterStatus.value = '';
        if (sortBy) sortBy.value = 'tanggal_sewa';
        if (sortOrder) sortOrder.value = 'desc';

        // Call applyFilters if it exists
        if (typeof applyFilters === 'function') {
            applyFilters();
        }
    };

    if (filterStatus && sortBy && sortOrder && resetFilter) {
        // Event listeners
        filterStatus.addEventListener('change', applyFilters);
        sortBy.addEventListener('change', applyFilters);
        sortOrder.addEventListener('change', applyFilters);
        resetFilter.addEventListener('click', resetFilters);

        function applyFilters() {
            const statusFilter = filterStatus.value.toLowerCase();
            const sortField = sortBy.value;
            const sortDirection = sortOrder.value;

            const orderItems = Array.from(document.querySelectorAll('.order-item'));

            // Filter
            let filteredItems = orderItems.filter(item => {
                if (!statusFilter) return true;
                return item.dataset.status.toLowerCase() === statusFilter;
            });

            // Sort
            filteredItems.sort((a, b) => {
                let aVal, bVal;

                switch(sortField) {
                    case 'tanggal_sewa':
                        aVal = new Date(a.dataset.date);
                        bVal = new Date(b.dataset.date);
                        break;
                    case 'total_harga':
                        aVal = parseFloat(a.dataset.price);
                        bVal = parseFloat(b.dataset.price);
                        break;
                    case 'status':
                        aVal = a.dataset.status;
                        bVal = b.dataset.status;
                        break;
                    default:
                        return 0;
                }

                if (sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });

            // Hide all items first
            orderItems.forEach(item => item.style.display = 'none');

            // Show and reorder filtered items
            if (filteredItems.length === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
                filteredItems.forEach(item => {
                    item.style.display = 'block';
                    ordersContainer.appendChild(item);
                });
            }
        }
    }
});
</script>
