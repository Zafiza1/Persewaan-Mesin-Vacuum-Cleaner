<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to dashboard if already logged in
if(isset($_SESSION['admin_id'])) {
    header("Location: " . ADMIN_URL . "dashboard.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';

$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Token keamanan tidak valid.";
    }
    
    // Sanitize input
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nama_lengkap = sanitize_input($_POST['nama_lengkap'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Username harus diisi.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore.";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }
    
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi.";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Username sudah digunakan.";
        }
        $stmt->close();
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email sudah digunakan.";
        }
        $stmt->close();
    }
    
    // Proceed with registration if no errors
    if (empty($errors)) {
        // Check database connection
        if ($conn->connect_error) {
            $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new admin
            $stmt = $conn->prepare("INSERT INTO admin (username, password, nama_lengkap, email) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            } else {
                $stmt->bind_param("ssss", $username, $hashed_password, $nama_lengkap, $email);
                if ($stmt->execute()) {
                    $success = "Registrasi berhasil! Silakan login.";
                    // Clear form
                    $username = $password = $confirm_password = $nama_lengkap = $email = '';
                } else {
                    $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
                }
                $stmt->close();
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Register Admin</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                            <br><br>
                            <a href="<?php echo ADMIN_URL; ?>login.php" class="btn btn-primary btn-sm">Login Sekarang</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(empty($success)): ?>
                    <form method="POST" action="<?php echo ADMIN_URL; ?>register.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text"
                                   class="form-control"
                                   id="username"
                                   name="username"
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                   autocomplete="username"
                                   required
                                   minlength="3"
                                   pattern="[a-zA-Z0-9_]+"
                                   title="Username hanya boleh mengandung huruf, angka, dan underscore">
                            <small class="form-text text-muted">Minimal 3 karakter, hanya huruf/angka/underscore</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text"
                                   class="form-control"
                                   id="nama_lengkap"
                                   name="nama_lengkap"
                                   value="<?php echo htmlspecialchars($nama_lengkap ?? ''); ?>"
                                   autocomplete="name"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                   autocomplete="email"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   autocomplete="new-password"
                                   required
                                   minlength="6">
                            <small class="form-text text-muted">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="confirm_password"
                                   name="confirm_password"
                                   autocomplete="new-password"
                                   required
                                   minlength="6">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Register</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Sudah punya akun? <a href="<?php echo ADMIN_URL; ?>login.php">Login di sini</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
