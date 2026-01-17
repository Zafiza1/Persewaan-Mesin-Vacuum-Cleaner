<?php
ob_start();

require_once __DIR__ . '/config/paths.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/security.php';

// Auto redirect based on role if already logged in
if(isAdmin()) {
    redirect_with_message(ADMIN_URL . 'dashboard.php', '', 'success');
} elseif(is_user_logged_in()) {
    redirect_with_message(USER_URL . 'orders.php', '', 'success');
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <span class="glass-badge hero-label mb-3 d-inline-flex align-items-center gap-2">
                    <i class="fas fa-bolt"></i> Akasa Teknik
                </span>
                <h1 class="hero-title mb-4">Sewa Vacuum Cleaner Profesional</h1>
                <p class="lead hero-subtitle mb-5">Pilih mesin, tentukan jadwal, kami kerjakan sisanya.</p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?php echo PAGES_URL; ?>produk.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-eye me-2"></i>Lihat Produk
                    </a>
                    <a href="<?php echo PAGES_URL; ?>kontak.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Hubungi Kami
                    </a>
                </div>

            </div>
            <div class="col-lg-6 mt-5 mt-lg-0">
                <div class="hero-visual text-center">
                    <img src="<?php echo IMAGES_URL; ?>logo.png" alt="Akasa Teknik" class="img-fluid" style="max-width: 360px;" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2NjYyIvPjwvc3ZnPg=='">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Kenapa Sewa di Akasa Teknik? -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2">KEUNGGULAN KAMI</span>
            <h2 class="fw-bold">Kenapa Sewa di Akasa Teknik?</h2>
            <p class="text-muted">Solusi terbaik untuk kebutuhan cleaning profesional Anda</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <div class="icon-box bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-bolt fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold">Cepat & Praktis</h4>
                    <p class="text-muted">Proses pemesanan mudah, pengantaran tepat waktu, tanpa ribet. Siap pakai dalam hitungan jam.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <div class="icon-box bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold">Mesin Terawat</h4>
                    <p class="text-muted">Semua unit rutin maintenance, performa optimal, dan selalu dalam kondisi prima.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <div class="icon-box bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-tag fa-2x"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold">Harga Terjangkau</h4>
                    <p class="text-muted">Tarif kompetitif untuk sewa harian dan bulanan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust Elements -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="trust-stats">
                    <h3 class="fw-bold mb-4">Dipercaya oleh 500+ Pelanggan</h3>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-box text-center p-3 bg-light rounded">
                                <h4 class="fw-bold text-primary mb-0">500+</h4>
                                <p class="text-muted mb-0">Pelanggan Puas</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box text-center p-3 bg-light rounded">
                                <h4 class="fw-bold text-success mb-0">5+ Tahun</h4>
                                <p class="text-muted mb-0">Pengalaman</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box text-center p-3 bg-light rounded">
                                <h4 class="fw-bold text-warning mb-0">50+</h4>
                                <p class="text-muted mb-0">Unit Tersedia</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box text-center p-3 bg-light rounded">
                                <h4 class="fw-bold text-info mb-0">24/7</h4>
                                <p class="text-muted mb-0">Support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="testimonials">
                    <h4 class="fw-bold mb-3">Apa Kata Pelanggan</h4>
                    <div class="testimonial-item p-3 bg-light rounded mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stars text-warning me-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-muted">5.0/5.0</span>
                        </div>
                        <p class="mb-2">"Mesin selalu dalam kondisi baik, pengantaran tepat waktu. Sangat membantu proyek cleaning kami!"</p>
                        <p class="mb-0 fw-bold">- PT. Clean Indonesia</p>
                    </div>
                    <div class="testimonial-item p-3 bg-light rounded">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stars text-warning me-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-muted">5.0/5.0</span>
                        </div>
                        <p class="mb-2">"Harga terjangkau, pelayanan memuaskan. Recommended untuk jasa cleaning service."</p>
                        <p class="mb-0 fw-bold">- Budi Santoso, Cleaning Service</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Siap Mulai Proyek Cleaning Anda?</h2>
        <p class="lead mb-4">Dapatkan vacuum cleaner terbaik dengan harga terjangkau</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?php echo PAGES_URL; ?>produk.php" class="btn btn-light btn-lg text-primary fw-bold">
                <i class="fas fa-shopping-cart me-2"></i>SEWA SEKARANG
            </a>
            <a href="<?php echo PAGES_URL; ?>kontak.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-phone me-2"></i>KONSULTASI GRATIS
            </a>
        </div>
    </div>
</section>

<!-- Konten Beranda -->
<div class="container my-5 pb-5">

    <section class="section-heading text-center">
        <span class="glass-badge mb-2 text-dark">Varian Mesin</span>
        <h2>Paket favorit pelanggan</h2>
        <p>Tersedia tangki 21L sampai 80L dengan aksesori lengkap.</p>
    </section>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 border-0">
                <img src="<?php echo IMAGES_URL; ?>products/vc2.jpg" class="card-img-top" alt="Vacuum Cleaner 21 L" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2NjYyIvPjwvc3ZnPg=='">
                <div class="card-body">
                    <h5 class="card-title mb-0">Vacuum 21 L</h5>
                    <p class="text-muted mb-3">Untuk rumah, apartemen, kantor.</p>
                    <a href="<?php echo PAGES_URL; ?>produk.php" class="btn btn-outline-primary w-100">Detail Produk</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0">
                <img src="<?php echo IMAGES_URL; ?>products/vc3.jpg" class="card-img-top" alt="Vacuum Cleaner 40 L" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVqdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2NjYyIvPjwvc3ZnPg=='">
                <div class="card-body">
                    <h5 class="card-title mb-0">Vacuum 40 L</h5>
                    <p class="text-muted mb-3">Favorit mall, gudang, jasa cleaning.</p>
                    <a href="<?php echo PAGES_URL; ?>produk.php" class="btn btn-outline-primary w-100">Detail Produk</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0">
                <img src="<?php echo IMAGES_URL; ?>products/vc4.jpg" class="card-img-top" alt="Vacuum Cleaner 80 L" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2NjYyIvPjwvc3ZnPg=='">
                <div class="card-body">
                    <h5 class="card-title mb-0">Vacuum 80 L</h5>
                    <p class="text-muted mb-3">Heavy-duty proyek industri &amp; event.</p>
                    <a href="<?php echo PAGES_URL; ?>produk.php" class="btn btn-outline-primary w-100">Detail Produk</a>
                </div>
            </div>
        </div>
    </div>

    <section class="cta-banner text-center">
        <p class="text-uppercase text-muted mb-2">Butuh unit hari ini?</p>
        <h3 class="fw-bold mb-3">Booking sekarang, kami antar.</h3>
        <p class="text-muted mb-4">Tim siap rekomendasikan mesin &amp; jadwal antar.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?php echo PAGES_URL; ?>produk.php" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar-plus me-2"></i>Mulai Pesan
            </a>
            <a href="<?php echo PAGES_URL; ?>kontak.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-comments me-2"></i>Hubungi Konsultan
            </a>
        </div>
    </section>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
