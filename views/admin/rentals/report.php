<?php
require_once BASE_PATH . 'includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <?php include ADMIN_PATH . 'sidebar.php'; ?>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Laporan Penyewaan</h4>
                    <div>
                        <small><?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></small>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($rentals)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada data penyewaan</h5>
                            <p class="text-muted">Tidak ada transaksi penyewaan dalam periode yang dipilih.</p>
                        </div>
                    <?php else: ?>
                        <!-- Summary Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= count($rentals) ?></h5>
                                        <p class="card-text">Total Transaksi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <?php 
                                        $total_revenue = array_sum(array_column($rentals, 'total_harga'));
                                        ?>
                                        <h5 class="card-title"><?= formatRupiah($total_revenue) ?></h5>
                                        <p class="card-text">Total Pendapatan</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <?php 
                                        $pending_count = count(array_filter($rentals, function($r) { return $r['status'] == 'pending'; }));
                                        ?>
                                        <h5 class="card-title"><?= $pending_count ?></h5>
                                        <p class="card-text">Menunggu Konfirmasi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <?php 
                                        $active_count = count(array_filter($rentals, function($r) { return $r['status'] == 'disewa'; }));
                                        ?>
                                        <h5 class="card-title"><?= $active_count ?></h5>
                                        <p class="card-text">Sedang Disewa</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Print Button -->
                        <div class="mb-3 no-print">
                            <button onclick="window.print()" class="btn btn-primary">
                                <i class="fas fa-print me-2"></i>Cetak Laporan
                            </button>
                            <button onclick="exportToExcel()" class="btn btn-success">
                                <i class="fas fa-file-excel me-2"></i>Export Excel
                            </button>
                        </div>
                        
                        <!-- Report Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="reportTable">
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

<script>
// Export to Excel function
function exportToExcel() {
    const table = document.getElementById('reportTable');
    const rows = table.getElementsByTagName('tr');
    let csv = [];
    
    // Add headers
    const headers = [];
    for (let i = 0; i < rows[0].cells.length; i++) {
        headers.push(rows[0].cells[i].textContent.trim());
    }
    csv.push(headers.join(','));
    
    // Add data rows
    for (let i = 1; i < rows.length; i++) {
        const rowData = [];
        for (let j = 0; j < rows[i].cells.length; j++) {
            rowData.push('"' + rows[i].cells[j].textContent.trim() + '"');
        }
        csv.push(rowData.join(','));
    }
    
    // Create download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'laporan_penyewaan_<?= date('Y-m-d') ?>.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Initialize DataTable for better display
$(document).ready(function() {
    $('#reportTable').DataTable({
        responsive: true,
        ordering: true,
        pageLength: 25
    });
});
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    .badge {
        border: 1px solid #000 !important;
    }
}
</style>

<?php
require_once BASE_PATH . 'includes/footer.php';
?>
