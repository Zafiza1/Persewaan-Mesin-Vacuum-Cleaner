<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Admin Rental Controller
 */
class AdminRentalController extends BaseController {
    private $rentalModel;
    
    public function __construct($database) {
        parent::__construct($database);
        $this->rentalModel = $this->model('RentalModel');
    }
    
    /**
     * Display all rentals
     */
    public function index() {
        if (!is_admin()) {
            $this->redirect(ADMIN_URL . 'login.php');
        }
        
        $rentals = $this->rentalModel->getAllRentals();
        $this->view('admin/rentals/index', [
            'rentals' => $rentals,
            'title' => 'Kelola Penyewaan'
        ]);
    }
    
    /**
     * Update rental status
     */
    public function updateStatus() {
        if (!is_admin()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }
        
        // Validate CSRF
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $this->json(['error' => 'Token keamanan tidak valid'], 400);
        }
        
        $rental_id = filter_input(INPUT_POST, 'rental_id', FILTER_VALIDATE_INT);
        $status = sanitize_input($_POST['status'] ?? '');
        
        if (!$rental_id || !in_array($status, ['pending', 'disewa', 'selesai', 'dibatalkan'])) {
            $this->json(['error' => 'Parameter tidak valid'], 400);
        }
        
        if ($this->rentalModel->updateStatus($rental_id, $status)) {
            $this->json([
                'success' => true,
                'message' => 'Status penyewaan berhasil diperbarui'
            ]);
        } else {
            $this->json(['error' => 'Gagal memperbarui status'], 500);
        }
    }
    
    /**
     * Generate rental report
     */
    public function report() {
        if (!is_admin()) {
            $this->redirect(ADMIN_URL . 'login.php');
        }
        
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        
        // Get rentals within date range
        $stmt = $this->db->prepare("
            SELECT s.*, p.nama_mesin, u.nama_lengkap as nama_penyewa, u.email 
            FROM sewa s 
            JOIN produk p ON s.id_produk = p.id 
            JOIN penyewa u ON s.id_penyewa = u.id 
            WHERE DATE(s.tanggal_sewa) BETWEEN ? AND ?
            ORDER BY s.tanggal_sewa DESC
        ");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $rentals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $this->view('admin/rentals/report', [
            'rentals' => $rentals,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'title' => 'Laporan Penyewaan'
        ]);
    }
}
