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

// Ambil 5 transaksi terbaru
$transaksi_terbaru = $conn->query("SELECT s.*, p.nama_lengkap as nama_penyewa, pr.nama_mesin 
                                  FROM sewa s 
                                  JOIN penyewa p ON s.id_penyewa = p.id 
                                  JOIN produk pr ON s.id_produk = pr.id 
                                  ORDER BY s.tanggal_sewa DESC LIMIT 5");
if (!$transaksi_terbaru) {
    $transaksi_terbaru = false;
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
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Transaksi Terbaru</h5>
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
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($transaksi_terbaru && $transaksi_terbaru->num_rows > 0): ?>
                                    <?php while($row = $transaksi_terbaru->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['nama_penyewa']; ?></td>
                                        <td><?php echo $row['nama_mesin']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_sewa'])); ?></td>
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
                                        <td colspan="6" class="text-center">Tidak ada transaksi</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
