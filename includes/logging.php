<?php
/**
 * Logging and Audit Trail System
 * Provides comprehensive logging for security, user activities, and system events
 */

// Log levels
define('LOG_LEVEL_EMERGENCY', 0);
define('LOG_LEVEL_ALERT', 1);
define('LOG_LEVEL_CRITICAL', 2);
define('LOG_LEVEL_ERROR', 3);
define('LOG_LEVEL_WARNING', 4);
define('LOG_LEVEL_NOTICE', 5);
define('LOG_LEVEL_INFO', 6);
define('LOG_LEVEL_DEBUG', 7);

// Log categories
define('LOG_CATEGORY_SECURITY', 'security');
define('LOG_CATEGORY_USER', 'user');
define('LOG_CATEGORY_SYSTEM', 'system');
define('LOG_CATEGORY_RENTAL', 'rental');
define('LOG_CATEGORY_PRODUCT', 'product');
define('LOG_CATEGORY_ADMIN', 'admin');

/**
 * Create logs table if not exists
 * @param mysqli $conn Database connection
 */
function ensure_logs_table($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level TINYINT NOT NULL,
        category VARCHAR(50) NOT NULL,
        action VARCHAR(100) NOT NULL,
        message TEXT,
        details JSON,
        user_id INT NULL,
        user_role VARCHAR(20) NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        request_uri VARCHAR(500) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_category (category),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at),
        INDEX idx_level (level)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->query($sql);
}

/**
 * Log general event
 * @param int $level Log level
 * @param string $category Log category
 * @param string $action Action performed
 * @param string $message Log message
 * @param array $details Additional details
 * @param int|null $user_id User ID (optional)
 */
function log_event($level, $category, $action, $message = '', $details = [], $user_id = null) {
    global $conn;
    
    if (!$conn) {
        return false;
    }
    
    // Ensure logs table exists
    ensure_logs_table($conn);
    
    // Get user info if not provided
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    // Get user role based on session
    $user_role = null;
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        $user_role = ROLE_ADMIN;
    } elseif (isset($_SESSION['user_id'])) {
        $user_role = ROLE_USER;
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $request_uri = $_SERVER['REQUEST_URI'] ?? null;
    
    // Sanitize and prepare data
    $details_json = !empty($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : null;
    
    $stmt = $conn->prepare("INSERT INTO logs (level, category, action, message, details, user_id, user_role, ip_address, user_agent, request_uri) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("issssissss", 
            $level, 
            $category, 
            $action, 
            $message, 
            $details_json, 
            $user_id, 
            $user_role, 
            $ip_address, 
            $user_agent, 
            $request_uri
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    return false;
}

/**
 * Log security events
 * @param string $action Security action
 * @param array $details Security details
 */
function log_security_event($action, $details = []) {
    log_event(LOG_LEVEL_WARNING, LOG_CATEGORY_SECURITY, $action, 
        'Security event: ' . $action, $details);
}

/**
 * Log user activities
 * @param string $action User action
 * @param array $details Activity details
 */
function log_user_activity($action, $details = []) {
    log_event(LOG_LEVEL_INFO, LOG_CATEGORY_USER, $action, 
        'User activity: ' . $action, $details);
}

/**
 * Log rental activities
 * @param string $action Rental action
 * @param array $details Rental details
 */
function log_rental_activity($action, $details = []) {
    log_event(LOG_LEVEL_INFO, LOG_CATEGORY_RENTAL, $action, 
        'Rental activity: ' . $action, $details);
}

/**
 * Log admin activities
 * @param string $action Admin action
 * @param array $details Admin details
 */
function log_admin_activity($action, $details = []) {
    log_event(LOG_LEVEL_INFO, LOG_CATEGORY_ADMIN, $action, 
        'Admin activity: ' . $action, $details);
}

/**
 * Log system events
 * @param string $action System action
 * @param string $message System message
 * @param array $details System details
 */
function log_system_event($action, $message = '', $details = []) {
    log_event(LOG_LEVEL_INFO, LOG_CATEGORY_SYSTEM, $action, $message, $details);
}

/**
 * Log errors
 * @param string $message Error message
 * @param array $details Error details
 */
function log_error($message, $details = []) {
    log_event(LOG_LEVEL_ERROR, LOG_CATEGORY_SYSTEM, 'error', $message, $details);
}

/**
 * Get logs with filtering
 * @param mysqli $conn Database connection
 * @param array $filters Filter options
 * @return array Logs data
 */
function get_logs($conn, $filters = []) {
    ensure_logs_table($conn);
    
    $where_conditions = [];
    $params = [];
    $types = '';
    
    // Build WHERE conditions
    if (!empty($filters['category'])) {
        $where_conditions[] = "category = ?";
        $params[] = $filters['category'];
        $types .= 's';
    }
    
    if (!empty($filters['action'])) {
        $where_conditions[] = "action LIKE ?";
        $params[] = '%' . $filters['action'] . '%';
        $types .= 's';
    }
    
    if (!empty($filters['user_id'])) {
        $where_conditions[] = "user_id = ?";
        $params[] = $filters['user_id'];
        $types .= 'i';
    }
    
    if (!empty($filters['level'])) {
        $where_conditions[] = "level <= ?";
        $params[] = $filters['level'];
        $types .= 'i';
    }
    
    if (!empty($filters['date_from'])) {
        $where_conditions[] = "DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
        $types .= 's';
    }
    
    if (!empty($filters['date_to'])) {
        $where_conditions[] = "DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
        $types .= 's';
    }
    
    // Build SQL query
    $sql = "SELECT * FROM logs";
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(' AND ', $where_conditions);
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    // Add pagination
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT ?";
        $params[] = $filters['limit'];
        $types .= 'i';
        
        if (!empty($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = $filters['offset'];
            $types .= 'i';
        }
    }
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Decode JSON details
        foreach ($logs as &$log) {
            if (!empty($log['details'])) {
                $log['details'] = json_decode($log['details'], true);
            }
        }
        
        return $logs;
    }
    
    return [];
}

/**
 * Get log statistics
 * @param mysqli $conn Database connection
 * @param array $filters Filter options
 * @return array Statistics
 */
function get_log_statistics($conn, $filters = []) {
    ensure_logs_table($conn);
    
    $where_conditions = [];
    $params = [];
    $types = '';
    
    // Build WHERE conditions
    if (!empty($filters['date_from'])) {
        $where_conditions[] = "DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
        $types .= 's';
    }
    
    if (!empty($filters['date_to'])) {
        $where_conditions[] = "DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
        $types .= 's';
    }
    
    $where_clause = !empty($where_conditions) ? " WHERE " . implode(' AND ', $where_conditions) : '';
    
    $stats = [];
    
    // Total logs
    $sql = "SELECT COUNT(*) as total FROM logs" . $where_clause;
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }
    
    // Logs by category
    $sql = "SELECT category, COUNT(*) as count FROM logs" . $where_clause . " GROUP BY category";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['by_category'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
    // Logs by level
    $sql = "SELECT level, COUNT(*) as count FROM logs" . $where_clause . " GROUP BY level";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['by_level'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
    // Recent activities
    $sql = "SELECT action, COUNT(*) as count FROM logs" . $where_clause . " GROUP BY action ORDER BY count DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['top_activities'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    
    return $stats;
}

/**
 * Clean old logs
 * @param mysqli $conn Database connection
 * @param int $days_old Number of days to keep
 * @return int Number of deleted logs
 */
function clean_old_logs($conn, $days_old = 90) {
    ensure_logs_table($conn);
    
    $sql = "DELETE FROM logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $days_old);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        
        log_system_event('log_cleanup', "Cleaned logs older than {$days_old} days", [
            'deleted_count' => $deleted
        ]);
        
        return $deleted;
    }
    
    return 0;
}

/**
 * Export logs to CSV
 * @param mysqli $conn Database connection
 * @param array $filters Filter options
 * @return string CSV content
 */
function export_logs_csv($conn, $filters = []) {
    $logs = get_logs($conn, $filters);
    
    $csv = "ID,Tanggal,Level,Kategori,Action,Pesan,User ID,User Role,IP Address,Request URI\n";
    
    foreach ($logs as $log) {
        $level_names = [
            LOG_LEVEL_EMERGENCY => 'EMERGENCY',
            LOG_LEVEL_ALERT => 'ALERT',
            LOG_LEVEL_CRITICAL => 'CRITICAL',
            LOG_LEVEL_ERROR => 'ERROR',
            LOG_LEVEL_WARNING => 'WARNING',
            LOG_LEVEL_NOTICE => 'NOTICE',
            LOG_LEVEL_INFO => 'INFO',
            LOG_LEVEL_DEBUG => 'DEBUG'
        ];
        
        $level = $level_names[$log['level']] ?? 'UNKNOWN';
        
        $csv .= sprintf(
            "%d,%s,%s,%s,%s,\"%s\",%s,%s,%s,\"%s\"\n",
            $log['id'],
            $log['created_at'],
            $level,
            $log['category'],
            $log['action'],
            str_replace('"', '""', $log['message'] ?? ''),
            $log['user_id'] ?? '',
            $log['user_role'] ?? '',
            $log['ip_address'] ?? '',
            $log['request_uri'] ?? ''
        );
    }
    
    return $csv;
}

// Auto-cleanup old logs (run occasionally)
function auto_cleanup_logs($conn) {
    // Clean logs older than 90 days
    clean_old_logs($conn, 90);
}
?>
