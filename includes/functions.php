<?php
// Include path configuration
require_once __DIR__ . '/../config/paths.php';

/**
 * Redirect to a URL with optional flash message
 * @param string $url URL to redirect to
 * @param string $message Optional flash message
 * @param string $type Message type (info, success, warning, danger)
 */
function redirect($url, $message = '', $type = 'info') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    // Prepend BASE_URL if the URL is relative (starts with /)
    if (strpos($url, '/') === 0 && strpos($url, BASE_URL) !== 0) {
        $url = BASE_URL . ltrim($url, '/');
    }
    
    header("Location: " . $url);
    exit();
}

/**
 * Redirect with message (alias for redirect)
 * @param string $url URL to redirect to
 * @param string $message Optional flash message
 * @param string $type Message type (info, success, warning, danger)
 */
function redirect_with_message($url, $message = '', $type = 'info') {
    redirect($url, $message, $type);
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

/**
 * Format number as Indonesian Rupiah
 * @param float $angka Amount to format
 * @return string Formatted Rupiah string
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Upload image file with validation
 * @param array $file File array from $_FILES
 * @param string $target_dir Target directory
 * @return array Result array with success/error
 */
function uploadGambar($file, $target_dir) {
    $target_file = $target_dir . uniqid() . '_' . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($imageFileType, $allowed)) {
        return ['error' => 'Hanya file JPG, JPEG, PNG & WEBP yang diperbolehkan'];
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => $target_file];
    } else {
        return ['error' => 'Terjadi kesalahan saat mengupload gambar'];
    }
}

/**
 * Sanitize input data
 * @param string $data Input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Indonesian format)
 * @param string $phone Phone number to validate
 * @return bool
 */
function validate_phone($phone) {
    return preg_match('/^(\+62|62|0)[0-9]{9,13}$/', $phone);
}
