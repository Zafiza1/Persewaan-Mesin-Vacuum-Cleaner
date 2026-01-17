<?php
require_once BASE_PATH . 'includes/header.php';
?>

<!-- Cache busting meta tag -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php include ADMIN_PATH . 'sidebar.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Kelola Penyewaan</h4>
                    <div>
                        <div class="input-group" style="width: 250px;">
                            <input type="text" class="form-control" id="searchPenyewa" placeholder="Cari penyewa..." onkeyup="searchPenyewa()">
                            <button class="btn btn-light" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($rentals)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada data penyewaan</h5>
                            <p class="text-muted">Belum ada transaksi penyewaan yang dilakukan.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="rentalsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tanggal</th>
                                        <th>Penyewa</th>
                                        <th>Mesin</th>
                                        <th>Lama</th>
                                        <th>Jumlah</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rentals as $rental): ?>
                                        <tr>
                                            <td>#<?= $rental['id'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($rental['tanggal_sewa'])) ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($rental['nama_penyewa']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($rental['email']) ?></small>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($rental['nama_mesin']) ?></td>
                                            <td>
                                                <?php 
                                                $start = new DateTime($rental['tanggal_sewa']);
                                                $end = new DateTime($rental['tanggal_kembali']);
                                                $duration = $start->diff($end)->days + 1;
                                                echo $duration . ' hari';
                                                ?>
                                            </td>
                                            <td><?= $rental['jumlah'] ?> unit</td>
                                            <td><?= formatRupiah($rental['total_harga']) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'disewa' => 'info',
                                                    'selesai' => 'success',
                                                    'dibatalkan' => 'danger'
                                                ];
                                                $statusText = [
                                                    'pending' => 'Menunggu',
                                                    'disewa' => 'Disewa',
                                                    'selesai' => 'Selesai',
                                                    'dibatalkan' => 'Dibatalkan'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$rental['status']] ?? 'secondary' ?>">
                                                    <?= $statusText[$rental['status']] ?? $rental['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-success btn-sm" onclick="printIndividualRental(<?= $rental['id'] ?>)">
                                                    <i class="fas fa-print"></i> Cetak
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Penyewaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm">
                    <div class="mb-3">
                        <label for="startDate" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="startDate" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="endDate" class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="endDate" name="end_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="reportFormat" class="form-label">Format</label>
                        <select class="form-select" id="reportFormat" name="format">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="downloadReport()">Download</button>
            </div>
        </div>
    </div>
</div>

<script>
// Cache busting parameter - force reload
console.log('Loading admin rentals script v' + Date.now());

// Search functionality
window.searchPenyewa = function() {
    var searchTerm = document.getElementById('searchPenyewa').value.toLowerCase();
    var table = document.getElementById('rentalsTable');
    var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (var i = 0; i < rows.length; i++) {
        var penyewaCell = rows[i].getElementsByTagName('td')[2]; // Penyewa column (index 2)
        if (penyewaCell) {
            var penyewaText = penyewaCell.textContent || penyewaCell.innerText;
            var row = rows[i];
            
            if (searchTerm === '') {
                row.style.display = '';
            } else if (penyewaText.toLowerCase().indexOf(searchTerm) === -1) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
            }
        }
    }
};

// Simple functions without complex concatenation
window.printIndividualRental = function(rentalId) {
    console.log('Opening kwitansi for rental:', rentalId);
    
    // Open kwitansi in new window
    var kwitansiUrl = '<?= ADMIN_URL ?>cetak_kwitansi.php?id=' + rentalId;
    window.open(kwitansiUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
};

window.showAlert = function(message, type) {
    var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">';
    alertHtml += message;
    alertHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    alertHtml += '</div>';
    
    // Use vanilla JS if jQuery not available
    if (typeof $ !== 'undefined') {
        $('.card-body').prepend(alertHtml);
    } else {
        var cardBody = document.querySelector('.card-body');
        if (cardBody) {
            cardBody.insertAdjacentHTML('afterbegin', alertHtml);
        }
    }
};

window.formatRupiah = function(angka) {
    return 'Rp ' + angka.toLocaleString('id-ID');
};

window.calculateDuration = function(startDate, endDate) {
    var start = new Date(startDate);
    var end = new Date(endDate);
    return Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
};

// Initialize DataTable
function initDataTable() {
    if (typeof $ !== 'undefined') {
        $('#rentalsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']]
        });
        console.log('DataTable initialized');
    } else {
        console.log('jQuery not available, retrying...');
        setTimeout(initDataTable, 500);
    }
}

// Wait for jQuery to be ready
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        initDataTable();
    });
} else {
    // Fallback: wait for jQuery
    var checkJQuery = setInterval(function() {
        if (typeof $ !== 'undefined') {
            clearInterval(checkJQuery);
            $(document).ready(function() {
                initDataTable();
            });
        }
    }, 100);
}
</script>

<?php
require_once BASE_PATH . 'includes/footer.php';
?>
