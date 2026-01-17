<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../includes/database_helper.php';

/**
 * Product/Machine Model
 */
class ProductModel extends BaseModel {
    protected $table = 'produk';
    
    /**
     * Get available products for rental
     * @return array
     */
    public function getAvailableProducts() {
        return getAllProducts($this->db);
    }
    
    /**
     * Get product with rental status
     * @param int $id
     * @return array|null
     */
    public function getProductWithStatus($id) {
        $base_product = getProductById($this->db, $id);
        if (!$base_product) {
            return null;
        }
        
        // Add rental status counts
        $stmt = $this->db->prepare("
            SELECT 
                   (SELECT COUNT(*) FROM sewa s WHERE s.id_produk = ? AND s.status = 'disewa') as disewa_count,
                   (SELECT COUNT(*) FROM sewa s WHERE s.id_produk = ? AND s.status = 'selesai') as selesai_count
        ");
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $status_counts = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Merge base product with status counts
        return array_merge($base_product, $status_counts);
    }
    
    /**
     * Update product stock
     * @param int $id
     * @param int $quantity
     * @param string $operation (add/subtract)
     * @return bool
     */
    public function updateStock($id, $quantity, $operation = 'subtract') {
        $operator = $operation === 'add' ? '+' : '-';
        $stmt = $this->db->prepare("UPDATE produk SET stok = stok {$operator} ? WHERE id = ? AND stok >= 0");
        $stmt->bind_param("ii", $quantity, $id);
        return $stmt->execute();
    }
    
    /**
     * Search products
     * @param string $keyword
     * @return array
     */
    public function searchProducts($keyword) {
        return searchProducts($this->db, $keyword);
    }
}
