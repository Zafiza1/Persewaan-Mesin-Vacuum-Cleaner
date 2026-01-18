<?php
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

// Clear any flash messages to prevent showing login success alerts
unset($_SESSION['flash_message']);
unset($_SESSION['flash_type']);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin_id'])) {
    redirect_with_message(ADMIN_URL . 'login.php', 'Silakan login terlebih dahulu.', 'warning');
}

// Hitung total produk
$result = $conn->query("SELECT COUNT(*) as total FROM produk");
$total_produk = $result ? $result->fetch_assoc()['total'] : 0;

// Hitung total penyewa
$result = $conn->query("SELECT COUNT(*) as total FROM penyewa");
$total_penyewa = $result ? $result->fetch_assoc()['total'] : 0;

// Hitung total transaksi
$result = $conn->query("SELECT COUNT(*) as total FROM sewa");
$total_transaksi = $result ? $result->fetch_assoc()['total'] : 0;

// Hitung pendapatan bulan ini
$result = $conn->query("SELECT SUM(total_harga) as total FROM sewa WHERE MONTH(tanggal_sewa) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_sewa) = YEAR(CURRENT_DATE())");
$pendapatan_bulan_ini = $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;

// Pagination settings for all transactions
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total transactions for pagination
$total_result = $conn->query("SELECT COUNT(*) as total FROM sewa");
$total_transaksi = $total_result ? $total_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_transaksi / $per_page);

// Get all transactions with pagination
$semua_transaksi = $conn->query("SELECT s.*, p.nama_lengkap as nama_penyewa, pr.nama_mesin 
                                  FROM sewa s 
                                  JOIN penyewa p ON s.id_penyewa = p.id 
                                  JOIN produk pr ON s.id_produk = pr.id 
                                  ORDER BY s.tanggal_sewa DESC 
                                  LIMIT $per_page OFFSET $offset");
if (!$semua_transaksi) {
    $semua_transaksi = false;
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <?php include 'sidebar.php'; ?>
        </div>
        
        <div class="col-md-9">
            <h2 class="mb-4">Dashboard Admin</h2>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Produk</h5>
                            <h2><?php echo $total_produk; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Penyewa</h5>
                            <h2><?php echo $total_penyewa; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Transaksi</h5>
                            <h2><?php echo $total_transaksi; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Pendapatan Bulan Ini</h5>
                            <h2>Rp <?php echo number_format($pendapatan_bulan_ini, 0, ',', '.'); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Semua Transaksi</h5>
                    <span class="badge bg-light text-dark">Total: <?php echo $total_transaksi; ?> transaksi</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Penyewa</th>
                                    <th>Produk</th>
                                    <th>Tanggal Sewa</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Jumlah</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($semua_transaksi && $semua_transaksi->num_rows > 0): ?>
                                    <?php while($row = $semua_transaksi->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['nama_penyewa']; ?></td>
                                        <td><?php echo $row['nama_mesin']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_sewa'])); ?></td>
                                        <td><?php echo $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-'; ?></td>
                                        <td><?php echo $row['jumlah']; ?></td>
                                        <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = '';
                                            switch($row['status']) {
                                                case 'pending': $badge_class = 'bg-warning text-dark'; break;
                                                case 'diproses': $badge_class = 'bg-info text-white'; break;
                                                case 'selesai': $badge_class = 'bg-success text-white'; break;
                                                case 'dibatalkan': $badge_class = 'bg-danger text-white'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada transaksi</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
