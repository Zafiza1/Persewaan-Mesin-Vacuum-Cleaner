<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Rental Transaction Model
 */
class RentalModel extends BaseModel {
    protected $table = 'sewa';
    
    /**
     * Create new rental transaction
     * @param array $data
     * @return int|bool
     */
    public function createRental($data) {
        // Calculate total price
        $total_harga = $this->calculatePrice($data['id_produk'], $data['tanggal_sewa'], $data['tanggal_kembali'], $data['jumlah']);
        $data['total_harga'] = $total_harga;
        $data['status'] = 'pending';
        
        return $this->create($data);
    }
    
    /**
     * Calculate rental price
     * @param int $product_id
     * @param string $start_date
     * @param string $end_date
     * @param int $quantity
     * @return float
     */
    public function calculatePrice($product_id, $start_date, $end_date, $quantity) {
        $product = getProductById($this->db, $product_id);
        
        if (!$product) {
            return 0;
        }
        
        $daily_price = $product['harga_sewa_per_hari'];
        
        // Calculate duration in days
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $duration_days = $start->diff($end)->days + 1; // +1 to include both start and end day
        
        // Simple pricing - no discounts
        $daily_price = $product['harga_sewa_per_hari'];
        
        return $daily_price * $duration_days * $quantity;
    }
    
    /**
     * Get rental transactions by user
     * @param int $user_id
     * @return array
     */
    public function getUserRentals($user_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, p.nama_mesin, p.gambar 
            FROM sewa s 
            JOIN produk p ON s.id_produk = p.id 
            WHERE s.id_penyewa = ? 
            ORDER BY s.tanggal_sewa DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get all rentals with details
     * @return array
     */
    public function getAllRentals() {
        $stmt = $this->db->prepare("
            SELECT s.*, p.nama_mesin, u.nama_lengkap as nama_penyewa, u.email 
            FROM sewa s 
            JOIN produk p ON s.id_produk = p.id 
            JOIN penyewa u ON s.id_penyewa = u.id 
            ORDER BY s.tanggal_sewa DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Update rental status
     * @param int $rental_id
     * @param string $status
     * @return bool
     */
    public function updateStatus($rental_id, $status) {
        $stmt = $this->db->prepare("UPDATE sewa SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $rental_id);
        
        if ($stmt->execute()) {
            // Update product stock based on status
            $rental = $this->getById($rental_id);
            if ($rental) {
                $productModel = new ProductModel($this->db);
                if ($status === 'disewa') {
                    $productModel->updateStock($rental['id_produk'], $rental['jumlah'], 'subtract');
                } elseif ($status === 'selesai') {
                    $productModel->updateStock($rental['id_produk'], $rental['jumlah'], 'add');
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * Get rental statistics
     * @return array
     */
    public function getStatistics() {
        $stats = [];
        
        // Total rentals
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM sewa");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_rentals'] = $result->fetch_assoc()['total'];
        
        // Active rentals
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM sewa WHERE status = 'disewa'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['active_rentals'] = $result->fetch_assoc()['total'];
        
        // Monthly revenue
        $stmt = $this->db->prepare("
            SELECT SUM(total_harga) as revenue 
            FROM sewa 
            WHERE MONTH(tanggal_sewa) = MONTH(CURRENT_DATE()) 
            AND YEAR(tanggal_sewa) = YEAR(CURRENT_DATE())
            AND status IN ('disewa', 'selesai')
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['monthly_revenue'] = $result->fetch_assoc()['revenue'] ?? 0;
        
        return $stats;
    }
}
