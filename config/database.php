<?php
/**
 * Database Configuration
 *
 * Using SQLite for easier setup (no password required)
 */

// Include environment configuration
require_once 'environment.php';
require_once __DIR__ . '/../includes/sqlite_helper.php';

// Database file path
$db_file = __DIR__ . '/../database/akasa.db';

// Create database directory if it doesn't exist
$db_dir = dirname($db_file);
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}

// Create SQLite connection
try {
    $sqlite_conn = new SQLite3($db_file);
    $sqlite_conn->enableExceptions(true);

    // Set SQLite pragmas for better performance
    $sqlite_conn->exec("PRAGMA journal_mode = WAL");
    $sqlite_conn->exec("PRAGMA synchronous = NORMAL");
    $sqlite_conn->exec("PRAGMA foreign_keys = ON");

    // Wrap connection for MySQLi compatibility
    $conn = wrap_sqlite_connection($sqlite_conn);

    // Also expose raw connection for direct SQLite operations
    $raw_conn = $sqlite_conn;

} catch (Exception $e) {
    if (ENVIRONMENT === 'development') {
        die("Koneksi database gagal: " . $e->getMessage());
    } else {
        die("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
    }
}
?>
