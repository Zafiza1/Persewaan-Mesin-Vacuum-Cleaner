<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../includes/security.php';

/**
 * Rental Controller
 */
class RentalController extends BaseController {
    private $rentalModel;
    private $productModel;
    
    public function __construct($database) {
        parent::__construct($database);
        $this->rentalModel = $this->model('RentalModel');
        $this->productModel = $this->model('ProductModel');
    }
    
    /**
     * Display rental form
     */
    public function create() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        echo "DEBUG: Method create() called<br>";
        
        if (!is_user_logged_in()) {
            echo "DEBUG: User not logged in, redirecting to login<br>";
            $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
            $this->redirect(USER_URL . 'login.php');
            return;
        }
        
        echo "DEBUG: User is logged in<br>";
        
        $product_id = $_GET['product_id'] ?? 0;
        echo "DEBUG: Product ID: $product_id<br>";
        
        $product = $this->productModel->getProductWithStatus($product_id);
        echo "DEBUG: Product data: " . ($product ? "found" : "not found") . "<br>";
        
        if (!$product) {
            echo "DEBUG: Product not found, redirecting back<br>";
            $this->redirect(BASE_URL . 'pages/produk.php');
            return;
        }
        
        echo "DEBUG: Loading view<br>";
        $this->view('rentals/create', [
            'product' => $product,
            'title' => 'Form Penyewaan - ' . $product['nama_mesin']
        ]);
        echo "DEBUG: View loaded<br>";
    }
    
    /**
     * Process rental request
     */
    public function store() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!is_user_logged_in()) {
            $this->json(['error' => 'Anda harus login terlebih dahulu'], 401);
        }
        
        // Validate CSRF
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $this->json(['error' => 'Token keamanan tidak valid'], 400);
        }
        
        $data = [
            'id_penyewa' => $_SESSION['user_id'],
            'id_produk' => filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT),
            'tanggal_sewa' => sanitize_input($_POST['start_date'] ?? ''),
            'tanggal_kembali' => sanitize_input($_POST['end_date'] ?? ''),
            'jumlah' => filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT),
            'catatan' => sanitize_input($_POST['notes'] ?? '')
        ];
        
        // Validate
        $errors = $this->validate($data, ['id_penyewa', 'id_produk', 'tanggal_sewa', 'tanggal_kembali', 'jumlah']);
        if (!empty($errors)) {
            $this->json(['errors' => $errors], 400);
        }
        
        // Validate dates
        if ($data['tanggal_sewa'] > $data['tanggal_kembali']) {
            $this->json(['error' => 'Tanggal selesai harus setelah tanggal mulai'], 400);
        }
        
        // Check product availability
        $product = $this->productModel->getProductWithStatus($data['id_produk']);
        if (!$product || $product['stok'] < $data['jumlah']) {
            $this->json(['error' => 'Stok produk tidak mencukupi'], 400);
        }
        
        // Create rental
        $rental_id = $this->rentalModel->createRental($data);
        if ($rental_id) {
            $this->json([
                'success' => true,
                'message' => 'Pengajuan penyewaan berhasil! Menunggu konfirmasi admin.',
                'rental_id' => $rental_id
            ]);
        } else {
            $this->json(['error' => 'Terjadi kesalahan saat mengajukan penyewaan'], 500);
        }
    }
    
    /**
     * Display user rentals
     */
    public function myRentals() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!is_user_logged_in()) {
            $this->redirect(USER_URL . 'login.php');
        }
        
        $rentals = $this->rentalModel->getUserRentals($_SESSION['user_id']);
        $this->view('rentals/my_rentals', [
            'rentals' => $rentals,
            'title' => 'Riwayat Penyewaan Saya'
        ]);
    }
    
    /**
     * Calculate rental price (AJAX)
     */
    public function calculatePrice() {
        $product_id = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
        $start_date = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING);
        $end_date = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_STRING);
        $quantity = filter_input(INPUT_GET, 'quantity', FILTER_VALIDATE_INT);
        
        if (!$product_id || !$start_date || !$end_date || !$quantity) {
            $this->json(['error' => 'Parameter tidak lengkap'], 400);
        }
        
        if ($start_date > $end_date) {
            $this->json(['error' => 'Tanggal selesai harus setelah tanggal mulai'], 400);
        }
        
        $price = $this->rentalModel->calculatePrice($product_id, $start_date, $end_date, $quantity);
        $this->json([
            'price' => $price,
            'formatted_price' => formatRupiah($price)
        ]);
    }
}
