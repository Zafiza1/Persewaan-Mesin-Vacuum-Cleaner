<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Include path configuration
require_once __DIR__ . '/../config/paths.php';

// Use path constants for includes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/header.php';

// Redirect if not logged in as user
if(!isset($_SESSION['user_id'])) {
    redirect_with_message('login.php', 'Silakan login untuk mengakses halaman ini.', 'warning');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Semua kolom harus diisi.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } else {
        // Check current password
        $stmt = $conn->prepare("SELECT password FROM penyewa WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (!password_verify($current_password, $user['password'])) {
                $error = "Password saat ini salah.";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE penyewa SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $success = "Password berhasil diperbarui.";
                } else {
                    $error = "Terjadi kesalahan saat memperbarui password. Silakan coba lagi.";
                }
            }
        } else {
            $error = "Pengguna tidak ditemukan.";
        }
        $stmt->close();
    }
}
?>

<div class="container mt-5">
    <h2>Ganti Password</h2>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="change_password.php">
        <div class="mb-3">
            <label for="current_password" class="form-label">Password Saat Ini</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        
        <div class="mb-3">
            <label for="new_password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-warning">Ganti Password</button>
        </div>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
