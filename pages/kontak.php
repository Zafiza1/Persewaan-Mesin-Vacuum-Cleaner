<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../includes/header.php';

?>

<div class="container mt-4">
    <h2 class="mb-4">Hubungi Kami</h2>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Kontak</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><i class="fas fa-map-marker-alt text-primary me-2"></i> Alamat</h6>
                        <p>Jl.Mahoni BB No. 9 Komp Merpati Kehutanan, Dalas Tipis, Pabean, Kec. Sedati, Kabupaten Sidoarjo, Jawa Timur 61253</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-phone-alt text-primary me-2"></i> Telepon</h6>
                        <p>085854145578</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-envelope text-primary me-2"></i> Email</h6>
                        <p>info@akasateknik.com</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6><i class="fas fa-clock text-primary me-2"></i> Jam Operasional</h6>
                        <p>Senin - Jumat: 08:00 - 17:00 WIB<br>Sabtu: 08:00 - 13:00 WIB</p>
                    </div>
                </div>
            </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Lokasi Kami</h5>
        </div>
        <div class="card-body p-0">
            <div class="ratio ratio-16x9">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.889562233002!2d112.7523830740027!3d-7.36627439264289!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7e4dd91bdd571%3A0xe4f31c03d1c657bc!2sAkasa%20Teknik%20*2ASewa%20Vacuum%20Cleaner*2A!5e0!3m2!1sen!2sid!4v1755737485090!5m2!1sen!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
