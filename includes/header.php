<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include path configuration
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/../includes/seo.php';

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Determine current page for SEO
$current_page = 'home';
$request_uri = $_SERVER['REQUEST_URI'] ?? '';

if (strpos($request_uri, '/admin/') !== false) {
    $current_page = 'admin';
} elseif (strpos($request_uri, 'produk.php') !== false) {
    $current_page = 'produk';
} elseif (strpos($request_uri, 'sewa.php') !== false) {
    $current_page = 'sewa';
} elseif (strpos($request_uri, 'kontak.php') !== false) {
    $current_page = 'kontak';
}

// Generate SEO meta tags
$seo_data = generate_seo_meta($current_page);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php output_meta_tags($seo_data); ?>
    
    <!-- Structured Data -->
    <?php echo generate_structured_data('LocalBusiness'); ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>style.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>mobile.css">
    <!-- Favicon -->
    <link rel="icon" href="<?php echo IMAGES_URL; ?>favicon.ico" type="image/x-icon">
    
    <!-- Prevent form resubmission alert -->
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'navbar.php'; ?>
    
    <main class="flex-grow-1">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="container mt-3">
                <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
            <?php 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
        <?php endif; ?>
