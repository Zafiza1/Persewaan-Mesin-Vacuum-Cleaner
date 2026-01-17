<?php
/**
 * Database Helper Functions
 * Provides safe column detection and query building
 */

/**
 * Get safe product query with auto-detection of price column
 * @param mysqli $conn Database connection
 * @param string $where Optional WHERE clause
 * @param array $params Optional parameters for WHERE clause
 * @return mysqli_result|false
 */
function getSafeProductQuery($conn, $where = '', $params = []) {
    // First, check which price column exists
    $price_column = detectPriceColumn($conn);
    
    // Base columns that we know exist
    $base_columns = ['id', 'nama_mesin', 'deskripsi', 'stok', 'gambar'];
    
    // Build query with detected price column
    $query = "SELECT " . implode(', ', $base_columns) . ", $price_column as harga_sewa_per_hari FROM produk";
    
    if (!empty($where)) {
        $query .= " WHERE $where";
    }
    
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        }
    } else {
        return $conn->query($query);
    }
    
    return false;
}

/**
 * Detect which price column exists in the database
 * @param mysqli $conn Database connection
 * @return string Column name to use
 */
function detectPriceColumn($conn) {
    // Check for harga_sewa_per_hari first
    $result = $conn->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'produk' AND COLUMN_NAME = 'harga_sewa_per_hari'");
    
    if ($result && $result->fetch_row()[0] > 0) {
        return 'harga_sewa_per_hari';
    }
    
    // Check for harga_sewa
    $result = $conn->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'produk' AND COLUMN_NAME = 'harga_sewa'");
    
    if ($result && $result->fetch_row()[0] > 0) {
        return 'harga_sewa';
    }
    
    // Fallback to a constant value
    return '0 as harga_sewa_per_hari';
}

/**
 * Get product by ID with safe query
 * @param mysqli $conn Database connection
 * @param int $id Product ID
 * @return array|null
 */
function getProductById($conn, $id) {
    $result = getSafeProductQuery($conn, 'id = ?', [$id]);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Get all products with safe query
 * @param mysqli $conn Database connection
 * @return array
 */
function getAllProducts($conn) {
    $result = getSafeProductQuery($conn);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

/**
 * Search products with safe query
 * @param mysqli $conn Database connection
 * @param string $keyword Search keyword
 * @return array
 */
function searchProducts($conn, $keyword) {
    $search_param = "%$keyword%";
    $result = getSafeProductQuery($conn, 'nama_mesin LIKE ? OR deskripsi LIKE ?', [$search_param, $search_param]);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}
?>
