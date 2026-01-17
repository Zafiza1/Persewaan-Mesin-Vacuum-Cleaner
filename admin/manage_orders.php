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

if (!is_admin()) {
    redirect_with_message(ADMIN_URL . 'login.php', 'Silakan login sebagai admin.', 'warning');
}

$page_title = "Kelola Pesanan";

$status_labels = [
    'pending' => 'Menunggu',
    'diproses' => 'Diproses',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan'
];

$badge_classes = [
    'pending' => 'bg-warning text-dark',
    'diproses' => 'bg-info text-white',
    'selesai' => 'bg-success text-white',
    'dibatalkan' => 'bg-danger text-white'
];

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Token keamanan tidak valid.";
        redirect(ADMIN_URL . 'manage_orders.php');
    }

    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $status = $_POST['status'] ?? '';

    if ($order_id <= 0 || !array_key_exists($status, $status_labels)) {
        $_SESSION['error_message'] = "Data pesanan atau status tidak valid.";
        redirect(ADMIN_URL . 'manage_orders.php');
    }
    
    $stmt = $conn->prepare("UPDATE sewa SET status = ? WHERE id = ?");
    if (!$stmt) {
        $_SESSION['error_message'] = "Prepare failed: " . $conn->error;
        redirect(ADMIN_URL . 'manage_orders.php');
    }

    $stmt->bind_param("si", $status, $order_id);
    if (!$stmt->execute()) {
        $_SESSION['error_message'] = "Failed to update status";
        $stmt->close();
        redirect(ADMIN_URL . 'manage_orders.php');
    }
    $stmt->close();
    
    $_SESSION['success_message'] = "Status pesanan berhasil diperbarui";
    redirect(ADMIN_URL . 'manage_orders.php');
}

// Get all orders with customer and product info
$orders = $conn->query("
    SELECT s.*, p.nama_lengkap as nama_penyewa, pr.nama_mesin, pr.harga_sewa_per_hari
    FROM sewa s
    JOIN penyewa p ON s.id_penyewa = p.id
    JOIN produk pr ON s.id_produk = pr.id
    ORDER BY s.id DESC
");

if (!$orders) {
    die("Error fetching orders: " . $conn->error);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
        <div class="col-md-9">
            <h2 class="mb-4">Kelola Pesanan</h2>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Penyewa</th>
                            <th>Produk</th>
                            <th>Tanggal Sewa</th>
                            <th>Tanggal Kembali</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            while ($order = $orders->fetch_assoc()) {
                                $status_key = $order['status'] ?? '';
                                $status_label = $status_labels[$status_key] ?? ucfirst($status_key);
                                $badge_class = $badge_classes[$status_key] ?? 'bg-secondary text-white';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['nama_penyewa']); ?></td>
                            <td><?php echo htmlspecialchars($order['nama_mesin']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($order['tanggal_sewa'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($order['tanggal_kembali'])); ?></td>
                            <td><?php echo formatRupiah($order['total_harga']); ?></td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($status_label); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="showStatusModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['status']); ?>')">
                                    <i class="fas fa-edit"></i> Ubah Status
                                </button>
                            </td>
                        </tr>
                        <?php
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Diagnostic static test modal -->
            <!- Removed to avoid possible interference ->

        </div>
    </div>
</div>

<!-- Simple Modal for Status Change -->
<div id="statusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: relative; margin: 10% auto; width: 90%; max-width: 500px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <h5 style="margin: 0; color: #333;">Ubah Status Pesanan #<span id="orderId"></span></h5>
            <button type="button" onclick="closeStatusModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
        </div>
        <form method="POST" id="statusForm">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="order_id" id="orderIdInput">
            <div style="margin-bottom: 15px;">
                <label for="statusSelect" style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Status Pesanan</label>
                <select name="status" id="statusSelect" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                    <option value="">Pilih Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" onclick="closeStatusModal()" style="background: #6c757d; color: white; border: none; padding: 8px 16px; margin-right: 10px; border-radius: 4px; cursor: pointer;">Tutup</button>
                <button type="submit" name="update_status" style="background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Simple modal styles */
</style>

<script>
function showStatusModal(orderId, currentStatus) {
    document.getElementById('orderId').textContent = orderId;
    document.getElementById('orderIdInput').value = orderId;
    
    var statusSelect = document.getElementById('statusSelect');
    statusSelect.value = '';
    
    if (currentStatus) {
        statusSelect.value = currentStatus;
    }
    
    document.getElementById('statusModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('statusModal');
    if (event.target == modal) {
        closeStatusModal();
    }
}

// Handle form submission
document.getElementById('statusForm').addEventListener('submit', function(e) {
    var statusSelect = document.getElementById('statusSelect');
    if (!statusSelect.value) {
        e.preventDefault();
        alert('Silakan pilih status terlebih dahulu');
        return false;
    }
    
    // Close modal and let form submit normally
    closeStatusModal();
});
</script>

<?php ob_end_flush(); ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
