<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/database_helper.php';

session_start();

if (!is_user_logged_in()) {
    header("Location: /akasa_teknik/user/login.php");
    exit();
}

$produk_id = $_GET['produk_id'] ?? 0;

// Debug output
if (ENVIRONMENT === 'development') {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;'>";
    echo "<strong>DEBUG:</strong><br>";
    echo "produk_id from URL: '$produk_id'<br>";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
    echo "</div>";
}

// Get product using helper
$produk = getProductById($conn, $produk_id);

if (!$produk) {
    echo "<div style='background: #ffe6e6; padding: 20px; margin: 20px; border: 1px solid #ff0000;'>";
    echo "<h3>Produk tidak ditemukan</h3>";
    echo "<p>produk_id: '$produk_id'</p>";
    echo "<p><a href='/akasa_teknik/pages/produk.php'>← Kembali ke Daftar Produk</a></p>";
    if (ENVIRONMENT === 'development') {
        echo "<hr>";
        echo "<h4>Debug Info:</h4>";
        $result = $conn->query("SELECT id, nama_mesin FROM produk ORDER BY id");
        if ($result && $result->num_rows > 0) {
            echo "<p>Available Products:</p>";
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li><a href='?produk_id={$row['id']}'>ID {$row['id']}: {$row['nama_mesin']}</a></li>";
            }
            echo "</ul>";
        }
    }
    echo "</div>";
    exit();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Form Penyewaan - <?= htmlspecialchars($produk['nama_mesin']) ?></h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <?php if ($produk['gambar']): ?>
                                <img src="/akasa_teknik/assets/images/products/<?= htmlspecialchars($produk['gambar']) ?>" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 200px;">
                                    <span class="text-muted">Tidak ada gambar</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5><?= htmlspecialchars($produk['nama_mesin']) ?></h5>
                            <p class="text-muted"><?= htmlspecialchars($produk['deskripsi'] ?? '') ?></p>
                            <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>
                            <p><strong>Harga sewa per hari:</strong> Rp <?= number_format($produk['harga_sewa_per_hari'], 0, ',', '.') ?></p>
                            <p><strong>Stok tersedia:</strong> <?= $produk['stok'] ?> unit</p>
                        </div>
                    </div>
                    
                    <form id="rentalForm" method="POST" action="/akasa_teknik/proses_sewa.php" data-validate="true">
                        <input type="hidden" name="produk_id" value="<?= $produk['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai Sewa *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   min="<?= date('Y-m-d') ?>" required 
                                   data-validation="required">
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai Sewa *</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   min="<?= date('Y-m-d') ?>" required 
                                   data-validation="required">
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Jumlah Unit *</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   min="1" max="<?= $produk['stok'] ?>" value="1" required 
                                   data-validation="required|min:1|max:<?= $produk['stok'] ?>">
                            <small class="text-muted">Stok tersedia: <?= $produk['stok'] ?> unit</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      maxlength="500" placeholder="Catatan khusus untuk penyewaan..."></textarea>
                            <small class="text-muted">Maksimal 500 karakter</small>
                        </div>
                        
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-calculator me-2"></i>Perhitungan Harga</h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Harga per hari:</strong></p>
                                        <p class="h4 text-primary">Rp <?= number_format($produk['harga_sewa_per_hari'], 0, ',', '.') ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Lama sewa:</strong></p>
                                        <p class="h4" id="durationDisplay">-</p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <p class="mb-1"><strong>Subtotal:</strong></p>
                                        <p class="h5 text-primary" id="subtotal">Rp 0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-check-circle me-2"></i>Ajukan Penyewaan
                        </button>
                        <a href="/akasa_teknik/pages/produk.php" class="btn btn-secondary btn-lg w-100 mt-2">
                            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Produk
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
