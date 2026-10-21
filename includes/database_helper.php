<?php
/**
 * Database Helper Functions
 * Provides safe column detection and query building for SQLite
 */

/**
 * Get safe product query with auto-detection of price column
 * @param mixed $conn Database connection (SQLiteConnection)
 * @param string $where Optional WHERE clause
 * @param array $params Optional parameters for WHERE clause
 * @return mixed Result or false
 */
function getSafeProductQuery($conn, $where = '', $params = []) {
    // For SQLite, use direct query since we control the schema
    $query = "SELECT id, nama_mesin, merk, spesifikasi, harga_sewa_per_hari, stok, gambar, created_at, updated_at FROM produk";

    if (!empty($where)) {
        $query .= " WHERE $where";
    }

    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
    } else {
        $raw_result = $conn->query($query);
        if ($raw_result) {
            // Wrap the raw SQLite3Result in our SQLiteResult wrapper
            return new SQLiteResult($raw_result);
        }
    }

    return false;
}

/**
 * Get product by ID with safe query
 * @param mixed $conn Database connection
 * @param int $id Product ID
 * @return array|null
 */
function getProductById($conn, $id) {
    $result = getSafeProductQuery($conn, 'id = ?', [$id]);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row ?: null;
    }
    return null;
}

/**
 * Get all products with safe query
 * @param mixed $conn Database connection
 * @return array
 */
function getAllProducts($conn) {
    $result = getSafeProductQuery($conn);
    if ($result) {
        return $result->fetch_all();
    }
    return [];
}

/**
 * Search products with safe query
 * @param mixed $conn Database connection
 * @param string $keyword Search keyword
 * @return array
 */
function searchProducts($conn, $keyword) {
    $search_param = "%$keyword%";
    $result = getSafeProductQuery($conn, 'nama_mesin LIKE ? OR spesifikasi LIKE ?', [$search_param, $search_param]);
    if ($result) {
        return $result->fetch_all();
    }
    return [];
}
?>
