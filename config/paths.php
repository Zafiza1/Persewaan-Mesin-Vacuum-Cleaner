<?php
/**
 * Path Configuration
 * 
 * Centralized path management for the application
 * Use these constants instead of hardcoding paths
 */

// Base directory paths - use __DIR__ for better portability
$base_dir = dirname(__DIR__);
define('BASE_PATH', $base_dir . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', BASE_PATH . 'config/');
define('INCLUDES_PATH', BASE_PATH . 'includes/');
define('ASSETS_PATH', BASE_PATH . 'assets/');
define('USER_PATH', BASE_PATH . 'user/');
define('ADMIN_PATH', BASE_PATH . 'admin/');
define('PAGES_PATH', BASE_PATH . 'pages/');
define('API_PATH', BASE_PATH . 'api/');

// File paths
define('DATABASE_FILE', CONFIG_PATH . 'database.php');
define('ENVIRONMENT_FILE', CONFIG_PATH . 'environment.php');
define('SECURITY_FILE', INCLUDES_PATH . 'security.php');
define('FUNCTIONS_FILE', INCLUDES_PATH . 'functions.php');
define('HEADER_FILE', INCLUDES_PATH . 'header.php');
define('FOOTER_FILE', INCLUDES_PATH . 'footer.php');
define('NAVBAR_FILE', INCLUDES_PATH . 'navbar.php');

// Page paths
define('BERANDA_FILE', PAGES_PATH . 'beranda.php');
define('PRODUK_FILE', PAGES_PATH . 'produk.php');
define('KONTAK_FILE', PAGES_PATH . 'kontak.php');
define('SEWA_FILE', PAGES_PATH . 'sewa.php');

// User paths
define('USER_LOGIN_FILE', USER_PATH . 'login.php');
define('USER_REGISTER_FILE', USER_PATH . 'register.php');
define('USER_LOGOUT_FILE', USER_PATH . 'logout.php');
define('USER_PROFILE_FILE', USER_PATH . 'profile.php');
define('USER_ORDERS_FILE', USER_PATH . 'orders.php');

// Admin paths
define('ADMIN_LOGIN_FILE', ADMIN_PATH . 'login.php');
define('ADMIN_REGISTER_FILE', ADMIN_PATH . 'register.php');
define('ADMIN_DASHBOARD_FILE', ADMIN_PATH . 'dashboard.php');
define('ADMIN_LOGOUT_FILE', ADMIN_PATH . 'logout.php');
define('ADMIN_MANAGE_PRODUCTS_FILE', ADMIN_PATH . 'manage_products.php');
define('ADMIN_MANAGE_ORDERS_FILE', ADMIN_PATH . 'manage_orders.php');
define('ADMIN_ADD_PRODUCT_FILE', ADMIN_PATH . 'add_product.php');
define('ADMIN_EDIT_PRODUCT_FILE', ADMIN_PATH . 'edit_product.php');

// Asset paths
define('CSS_PATH', ASSETS_PATH . 'css/');
define('JS_PATH', ASSETS_PATH . 'js/');
define('IMAGES_PATH', ASSETS_PATH . 'images/');

// URL paths (for use in HTML)
// Detect base URL automatically for better portability
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname(dirname($script_name));

// Remove trailing slash from base_path if present
$base_path = rtrim($base_path, '/');

define('BASE_URL', $protocol . '://' . $host . $base_path . '/');
define('CSS_URL', BASE_URL . 'assets/css/');
define('JS_URL', BASE_URL . 'assets/js/');
define('IMAGES_URL', BASE_URL . 'assets/images/');
define('USER_URL', BASE_URL . 'user/');
define('ADMIN_URL', BASE_URL . 'admin/');
define('PAGES_URL', BASE_URL . 'pages/');
