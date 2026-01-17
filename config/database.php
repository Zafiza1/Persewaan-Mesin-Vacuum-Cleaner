<?php
/**
 * Database Configuration
 * 
 * Update these values with your actual database credentials
 */

// Include environment configuration
require_once 'environment.php';

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "akasa";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Don't expose connection errors in production
    if (ENVIRONMENT === 'development') {
        die("Koneksi database gagal: " . $conn->connect_error);
    } else {
        die("Terjadi kesalahan sistem. Silakan coba lagi nanti.");
    }
}

// Set charset to utf8
$conn->set_charset("utf8mb4");
?>
