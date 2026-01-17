</main>

<footer class="bg-dark text-white pt-4 pb-2 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Layanan Pelanggan</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-phone-alt me-2"></i> 085854145578</li>
                    <li><i class="fas fa-envelope me-2"></i> @gmail.com</li>
                    <li><i class="fas fa-clock me-2"></i> Buka: Senin - Sabtu, 08:00 - 17:00</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5>Ikuti Kami</h5>
                <div class="social-links">
                    <a href="https://wa.me/6285854145578" class="text-white me-3"><i class="fab fa-whatsapp fa-2x"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                </div>
                <p class="mt-3">&copy; <?php echo date('Y'); ?> Akasa Teknik. Semua Hak Dilindungi.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="<?php echo JS_URL; ?>script.js"></script>
<script src="<?php echo JS_URL; ?>validation.js"></script>

<?php if (basename($_SERVER['PHP_SELF']) === 'form_sewa_simple.php'): ?>
<script>
// Price calculation for rental form
class PriceCalculator {
    constructor() {
        this.dailyPrice = <?= $produk['harga_sewa_per_hari'] ?>;
        this.init();
    }
    
    init() {
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const quantity = document.getElementById('quantity');
        
        if (startDate && endDate && quantity) {
            startDate.addEventListener('change', () => this.calculate());
            endDate.addEventListener('change', () => this.calculate());
            quantity.addEventListener('input', () => this.calculate());
            
            // Set minimum end date when start date changes
            startDate.addEventListener('change', () => {
                endDate.min = startDate.value;
                if (endDate.value && endDate.value < startDate.value) {
                    endDate.value = startDate.value;
                }
                this.calculate();
            });
        }
    }
    
    calculate() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const quantity = parseInt(document.getElementById('quantity').value) || 0;
        
        if (!startDate || !endDate || quantity <= 0) {
            this.updateDisplay(0, 0, 0, 0, 0, '');
            return;
        }
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (end < start) {
            this.updateDisplay(0, 0, 0, 0, 0, '');
            return;
        }
        
        const duration = Math.floor((end - start) / (1000 * 60 * 60 * 24)) + 1; // +1 inclusive
        
        if (duration > 30) {
            this.updateDisplay(0, 0, 0, 0, 0, function calculatePrice(pricePerDay, startDate, endDate, quantity) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        
        // Simple calculation - no discounts
        const subtotal = pricePerDay * days * quantity;
        const discount = 0;
        const discountAmount = 0;
        const total = subtotal;
        
        return {
            days: days,
            subtotal: subtotal,
            discount: discount,
            discountAmount: discountAmount,
            total: total
        };
    });
            return;
        }
        
        const subtotal = this.dailyPrice * duration * quantity;
        
        // No discounts - simple pricing
        const discountAmount = 0;
        const total = subtotal;
        
        this.updateDisplay(
            duration,
            subtotal,
            discountAmount,
            total,
            0,
            ''
        );
    }
    
    updateDisplay(duration, subtotal, discount, total, discountPercent, discountInfo) {
        const durationDisplay = document.getElementById('durationDisplay');
        const subtotalEl = document.getElementById('subtotal');
        const discountEl = document.getElementById('discount');
        const totalEl = document.getElementById('totalPrice');
        const discountInfoEl = document.getElementById('discountInfo');
        
        if (duration > 0) {
            durationDisplay.textContent = duration + ' hari';
            subtotalEl.textContent = 'Rp ' + this.formatNumber(subtotal);
            discountEl.textContent = '-Rp ' + this.formatNumber(discount);
            totalEl.textContent = 'Rp ' + this.formatNumber(total);
            
            if (discountInfo) {
                discountInfoEl.textContent = '(' + discountInfo + ')';
            } else {
                discountInfoEl.textContent = '';
            }
        } else {
            durationDisplay.textContent = '-';
            subtotalEl.textContent = 'Rp 0';
            discountEl.textContent = 'Rp 0';
            totalEl.textContent = 'Rp 0';
            discountInfoEl.textContent = discountInfo || '';
        }
    }
    
    formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
}

// Initialize price calculator when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new PriceCalculator();
});
</script>
<?php endif; ?>

</body>
</html>
