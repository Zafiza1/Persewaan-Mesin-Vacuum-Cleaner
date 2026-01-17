<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if(is_user_logged_in()) {
    redirect(BASE_URL . 'user/orders.php');
}

require_once __DIR__ . '/../includes/header.php';


$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Token keamanan tidak valid.";
    }
    
    // Sanitize and validate input
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $nama_lengkap = sanitize_input($_POST['nama_lengkap'] ?? '');
    $alamat = sanitize_input($_POST['alamat'] ?? '');
    $no_hp = sanitize_input($_POST['no_hp'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    
    // Validation
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter.";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi.";
    }
    
    if (!validate_email($email)) {
        $errors[] = "Format email tidak valid.";
    }
    
    if (!validate_phone($no_hp)) {
        $errors[] = "Format nomor HP tidak valid.";
    }
    
    if (empty($alamat)) {
        $errors[] = "Alamat harus diisi.";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM penyewa WHERE username = ?");
        if (!$stmt) {
            $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        } else {
            $stmt->bind_param("s", $username);
            if (!$stmt->execute()) {
                $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            } else {
                $result = $stmt->get_result();
                if($result->num_rows > 0) {
                    $errors[] = "Username sudah digunakan. Silakan pilih username lain.";
                }
            }
            $stmt->close();
        }
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM penyewa WHERE email = ?");
        if (!$stmt) {
            $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        } else {
            $stmt->bind_param("s", $email);
            if (!$stmt->execute()) {
                $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            } else {
                $result = $stmt->get_result();
                if($result->num_rows > 0) {
                    $errors[] = "Email sudah terdaftar.";
                }
            }
            $stmt->close();
        }
    }
    
    // Insert new user if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO penyewa (username, password, nama_lengkap, alamat, no_hp, email) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        } else {
            $stmt->bind_param("ssssss", $username, $hashed_password, $nama_lengkap, $alamat, $no_hp, $email);
            
            if($stmt->execute()) {
                $stmt->close();
                redirect_with_message(USER_URL . 'login.php', 'Pendaftaran berhasil. Silakan login.', 'success');
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan data. Silakan coba lagi.";
                if (ENVIRONMENT === 'development') {
                    $errors[] = "Error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}
?>


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Pendaftaran Penyewa Baru</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo USER_URL; ?>register.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" 
                                       required minlength="3" maxlength="50">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="6">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                   value="<?php echo isset($nama_lengkap) ? htmlspecialchars($nama_lengkap) : ''; ?>" 
                                   required maxlength="100">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" 
                                   required maxlength="100">
                        </div>
                        
                        <div class="mb-3">
                            <label for="no_hp" class="form-label">Nomor HP <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                   value="<?php echo isset($no_hp) ? htmlspecialchars($no_hp) : ''; ?>" 
                                   required pattern="^(\+62|62|0)[0-9]{9,13}$" 
                                   placeholder="Contoh: 081234567890">
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                      required maxlength="500"><?php echo isset($alamat) ? htmlspecialchars($alamat) : ''; ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Daftar</button>
                            <a href="<?php echo USER_URL; ?>login.php" class="btn btn-outline-primary">Sudah punya akun? Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
