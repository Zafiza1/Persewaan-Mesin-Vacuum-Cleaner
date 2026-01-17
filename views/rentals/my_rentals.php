<?php
require_once BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Riwayat Penyewaan Saya</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($rentals)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada riwayat penyewaan</h5>
                            <p class="text-muted">Anda belum pernah melakukan penyewaan mesin.</p>
                            <a href="<?= BASE_URL ?>pages/produk.php" class="btn btn-primary">Lihat Produk</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal Sewa</th>
                                        <th>Mesin</th>
                                        <th>Lama Sewa</th>
                                        <th>Jumlah</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rentals as $rental): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($rental['tanggal_sewa'])) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($rental['gambar']): ?>
                                                        <img src="<?= htmlspecialchars($rental['gambar']) ?>" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;" alt="">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($rental['nama_mesin']) ?></strong>
                                                    </div>
                                                </div>
                                            </td>
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
                                                    'pending' => 'Menunggu Konfirmasi',
                                                    'disewa' => 'Sedang Disewa',
                                                    'selesai' => 'Selesai',
                                                    'dibatalkan' => 'Dibatalkan'
                                                ];
                                                ?>
                                                <span class="badge bg-<?= $statusClass[$rental['status']] ?? 'secondary' ?>">
                                                    <?= $statusText[$rental['status']] ?? $rental['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="showDetails(<?= $rental['id'] ?>)">
                                                    <i class="fas fa-eye"></i> Detail
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
    <div class="modal-dialog">
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

<script>
function showDetails(rentalId) {
    $.get('<?= BASE_URL ?>api/rental/detail', {id: rentalId}, function(response) {
        if (response.rental) {
            const rental = response.rental;
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID Transaksi:</strong> #${rental.id}</p>
                        <p><strong>Tanggal Sewa:</strong> ${new Date(rental.tanggal_sewa).toLocaleString('id-ID')}</p>
                        <p><strong>Mesin:</strong> ${rental.nama_mesin}</p>
                        <p><strong>Lama Sewa:</strong> ${rental.lama_sewa} hari</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Jumlah:</strong> ${rental.jumlah} unit</p>
                        <p><strong>Total Harga:</strong> ${rental.total_harga_formatted}</p>
                        <p><strong>Status:</strong> <span class="badge bg-info">${rental.status}</span></p>
                        ${rental.catatan ? `<p><strong>Catatan:</strong><br>${rental.catatan}</p>` : ''}
                    </div>
                </div>
            `;
            $('#modalContent').html(content);
            $('#detailModal').modal('show');
        }
    }).fail(function() {
        showAlert('Gagal memuat detail', 'danger');
    });
}
</script>

<?php
require_once BASE_PATH . 'includes/footer.php';
?>
