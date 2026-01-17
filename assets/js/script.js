// Slider header otomatis
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi carousel
    const headerCarouselEl = document.getElementById('headerCarousel');
    if (headerCarouselEl) {
        var myCarousel = new bootstrap.Carousel(headerCarouselEl, {
            interval: 5000, // Ganti gambar setiap 5 detik
            wrap: true,
            keyboard: false // Disable keyboard to prevent errors
        });
    }

    // Inisialisasi testimoni carousel
    const testimoniCarouselEl = document.getElementById('testimoniCarousel');
    if (testimoniCarouselEl) {
        var testimoniCarousel = new bootstrap.Carousel(testimoniCarouselEl, {
            interval: 3000, // Ganti testimoni setiap 3 detik
            wrap: true,
            keyboard: false // Disable keyboard to prevent errors
        });
    }
    
    // Validasi form sewa
    const sewaForm = document.querySelector('form[action*="sewa.php"]');
    if(sewaForm) {
        sewaForm.addEventListener('submit', function(e) {
            const tanggalSewa = document.querySelector('input[name="tanggal_sewa"]');
            const tanggalKembali = document.querySelector('input[name="tanggal_kembali"]');
            const jumlah = document.querySelector('input[name="jumlah"]');
            
            // Validasi tanggal
            if(new Date(tanggalKembali.value) < new Date(tanggalSewa.value)) {
                e.preventDefault();
                showAlert('Tanggal kembali tidak boleh sebelum tanggal sewa', 'danger');
                return;
            }
            
            // Validasi jumlah minimal
            if(parseInt(jumlah.value) < 1) {
                e.preventDefault();
                showAlert('Jumlah unit harus minimal 1', 'danger');
                return;
            }
        });
    }
    
    // Hitung total harga saat mengisi form sewa
    const jumlahInput = document.querySelector('input[name="jumlah"]');
    const tanggalSewaInput = document.querySelector('input[name="tanggal_sewa"]');
    const tanggalKembaliInput = document.querySelector('input[name="tanggal_kembali"]');
    const produkSelect = document.querySelector('select[name="produk_id"]');
    
    // Add event listeners for all relevant inputs
    const inputsToWatch = [jumlahInput, tanggalSewaInput, tanggalKembaliInput];
    if (produkSelect) inputsToWatch.push(produkSelect);
    
    inputsToWatch.forEach(input => {
        if(input) {
            input.addEventListener('change', hitungTotalHarga);
            input.addEventListener('input', hitungTotalHarga);
        }
    });
    
    // Function to show alert messages
    function showAlert(message, type = 'info') {
        // Remove any existing alerts
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} custom-alert alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert at the top of the form
        const form = document.querySelector('form[action*="sewa.php"]');
        if(form) {
            form.insertBefore(alertDiv, form.firstChild);
        }
    }
    
    async function hitungTotalHarga() {
        let produkId;
        if(document.querySelector('input[name="produk_id"]')) {
            produkId = document.querySelector('input[name="produk_id"]').value;
        } else if(document.querySelector('select[name="produk_id"]')) {
            produkId = document.querySelector('select[name="produk_id"]').value;
        }
        
        const jumlah = jumlahInput?.value;
        const tanggalSewa = tanggalSewaInput?.value;
        const tanggalKembali = tanggalKembaliInput?.value;
        
        if(produkId && jumlah && tanggalSewa && tanggalKembali && produkId !== '') {
            try {
                // Fetch product details via AJAX
const response = await fetch(`/akasa_teknik/api/products/get_product.php?id=${produkId}`);
                const product = await response.json();
                
                if(product.success) {
                    const diff = new Date(tanggalKembali) - new Date(tanggalSewa);
                    const hari = Math.floor(diff / (1000 * 60 * 60 * 24)) + 1;
                    const totalHarga = hari * product.harga_sewa_per_hari * jumlah;
                    
                    // Create or update total price display
                    let totalElement = document.getElementById('total-harga');
                    if(!totalElement) {
                        totalElement = document.createElement('div');
                        totalElement.id = 'total-harga';
                        totalElement.className = 'alert alert-success mt-3';
                        const form = document.querySelector('form[action*="sewa.php"]');
                        if(form) {
                            form.insertBefore(totalElement, form.querySelector('button[type="submit"]'));
                        }
                    }
                    
                    totalElement.innerHTML = `
                        <strong>Total Harga Sewa:</strong> Rp ${totalHarga.toLocaleString('id-ID')}
                        <br><small>(${hari} hari × ${jumlah} unit × Rp ${product.harga_sewa_per_hari.toLocaleString('id-ID')}/hari)</small>
                    `;
                    
                    // Check stock availability
                    if(jumlah > product.stok) {
                        showAlert(`Stok tidak mencukupi. Stok tersedia: ${product.stok} unit`, 'warning');
                    } else {
                        // Remove any stock warnings
                        const stockWarnings = document.querySelectorAll('.custom-alert.alert-warning');
                        stockWarnings.forEach(warning => warning.remove());
                    }
                }
            } catch (error) {
                console.error('Error fetching product details:', error);
            }
        }
    }
    
    // Initialize calculation on page load if values are pre-filled
    setTimeout(hitungTotalHarga, 100);
});
