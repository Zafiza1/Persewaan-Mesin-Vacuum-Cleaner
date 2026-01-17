<?php
require_once BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Form Penyewaan - <?= htmlspecialchars($product['nama_mesin']) ?></h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <?php if ($product['gambar']): ?>
                                <img src="<?= IMAGES_URL ?>products/<?= htmlspecialchars($product['gambar']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($product['nama_mesin']) ?>">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 200px;">
                                    <span class="text-muted">Tidak ada gambar</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5><?= htmlspecialchars($product['nama_mesin']) ?></h5>
                            <p class="text-muted"><?= htmlspecialchars($product['deskripsi'] ?? '') ?></p>
                            <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($product['deskripsi'])) ?></p>
                            <p><strong>Harga sewa per hari:</strong> <?= formatRupiah($product['harga_sewa_per_hari']) ?></p>
                            <p><strong>Stok tersedia:</strong> <?= $product['stok'] ?> unit</p>
                        </div>
                    </div>
                    
                    <form id="rentalForm">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai Sewa</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai Sewa</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Jumlah</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="<?= $product['stok'] ?>" value="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <h5>Perhitungan Harga</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p>Harga per hari: <span id="dailyPrice"><?= formatRupiah($product['harga_sewa_per_hari']) ?></span></p>
                                    <p>Lama sewa: <span id="durationDisplay">1</span> hari</p>
                                    <p>Jumlah: <span id="quantityDisplay">1</span> unit</p>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>Subtotal: <span id="subtotal"><?= formatRupiah($product['harga_sewa_per_hari']) ?></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Total: <span id="totalPrice" class="text-primary"><?= formatRupiah($product['harga_sewa_per_hari']) ?></span></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm d-none" id="submitLoader"></span>
                                Ajukan Penyewaan
                            </button>
                            <a href="<?= BASE_URL ?>pages/produk.php" class="btn btn-secondary">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const productId = <?= $product['id'] ?>;
    const basePrice = <?= $product['harga_sewa_per_hari'] ?>;
    
    function calculatePrice() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const quantity = parseInt($('#quantity').val()) || 1;
        
        if (startDate && endDate && startDate <= endDate) {
            $.get('<?= BASE_URL ?>api/rental/calculate_price', {
                product_id: productId,
                start_date: startDate,
                end_date: endDate,
                quantity: quantity
            }, function(response) {
                if (response.price !== undefined) {
                    $('#totalPrice').text(response.formatted_price);
                    
                    // Calculate duration display
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    
                    $('#durationDisplay').text(duration);
                    $('#quantityDisplay').text(quantity);
                    
                    // Calculate discount display
                    let subtotal = basePrice * duration * quantity;
                    let discount = subtotal - response.price;
                    $('#subtotal').text('Rp ' + subtotal.toLocaleString('id-ID'));
                    $('#discount').text('Rp ' + discount.toLocaleString('id-ID'));
                }
            });
        }
    }
    
    $('#start_date, #end_date, #quantity').on('input change', calculatePrice);
    
    // Set minimum end date when start date changes
    $('#start_date').on('change', function() {
        $('#end_date').attr('min', $(this).val());
        if ($('#end_date').val() < $(this).val()) {
            $('#end_date').val($(this).val());
        }
        calculatePrice();
    });
    
    $('#rentalForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate dates
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (!startDate || !endDate) {
            showAlert('Silakan pilih tanggal mulai dan selesai sewa', 'danger');
            return;
        }
        
        if (startDate > endDate) {
            showAlert('Tanggal selesai harus setelah tanggal mulai', 'danger');
            return;
        }
        
        $('#submitLoader').removeClass('d-none');
        $('button[type="submit"]').prop('disabled', true);
        
        // Prepare form data
        const formData = {
            product_id: $('#product_id').val(),
            start_date: startDate,
            end_date: endDate,
            quantity: $('#quantity').val(),
            notes: $('#notes').val(),
            csrf_token: $('input[name="csrf_token"]').val()
        };
        
        $.post('<?= BASE_URL ?>api/rental/store', formData, function(response) {
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(() => {
                    window.location.href = '<?= USER_URL ?>orders.php';
                }, 2000);
            } else {
                showAlert(response.error || 'Terjadi kesalahan', 'danger');
            }
        }).fail(function() {
            showAlert('Terjadi kesalahan koneksi', 'danger');
        }).always(function() {
            $('#submitLoader').addClass('d-none');
            $('button[type="submit"]').prop('disabled', false);
        });
    });
});

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#rentalForm').prepend(alertHtml);
}
</script>

<?php
require_once BASE_PATH . 'includes/footer.php';
?>
