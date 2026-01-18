<?php
// Start session
session_start();

// Start output buffering to prevent headers already sent errors
ob_start();

// Include path configuration
require_once __DIR__ . '/../config/paths.php';

// Use path constants for includes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

// Redirect if not logged in as user
if(!is_user_logged_in()) {
    $_SESSION['login_redirect'] = 'user/profile.php';
    redirect('login.php', 'Silakan login untuk mengakses profil Anda.', 'warning');
}

$user_id = $_SESSION['user_id'];
$user = [];
$error = '';
$success = '';

// Get user profile information
try {
    $stmt = $conn->prepare("SELECT username, nama_lengkap, email, no_hp, alamat FROM penyewa WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Terjadi kesalahan sistem. Silakan coba lagi.");
    }
    
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Terjadi kesalahan sistem. Silakan coba lagi.");
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
    } else {
        throw new Exception("Pengguna tidak ditemukan.");
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = "Token keamanan tidak valid. Silakan coba lagi.";
    } else {
        // Sanitize and validate input
        $nama_lengkap = sanitize_input($_POST['nama_lengkap'] ?? '');
        $no_hp = sanitize_input($_POST['no_hp'] ?? '');
        $alamat = sanitize_input($_POST['alamat'] ?? '');
        
        // Validation
        if (empty($nama_lengkap)) {
            $error = "Nama lengkap harus diisi.";
        } elseif (!validate_phone($no_hp)) {
            $error = "Format nomor HP tidak valid.";
        } elseif (empty($alamat)) {
            $error = "Alamat harus diisi.";
        } else {
            // Update user profile if no errors
            try {
                $stmt = $conn->prepare("UPDATE penyewa SET nama_lengkap = ?, no_hp = ?, alamat = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nama_lengkap, $no_hp, $alamat, $user_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Terjadi kesalahan saat memperbarui profil. Silakan coba lagi.");
                }
                
                $success = "Profil berhasil diperbarui.";
                $stmt->close();
                
                // Refresh user data
                $user['nama_lengkap'] = $nama_lengkap;
                $user['no_hp'] = $no_hp;
                $user['alamat'] = $alamat;
                
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}
?>

<?php
// Include header after processing
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
    <h2>Profil Saya</h2>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="profile.php">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
            <small class="text-muted">Username tidak dapat diubah</small>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
            <small class="text-muted">Email tidak dapat diubah</small>
        </div>
        
        <div class="mb-3">
            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                   value="<?php echo htmlspecialchars($user['nama_lengkap'] ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="no_hp" class="form-label">Nomor HP</label>
            <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                   value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Perbarui Profil</button>
        </div>
    </form>
    
    <hr>
    
    <h3>Ganti Password</h3>
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
