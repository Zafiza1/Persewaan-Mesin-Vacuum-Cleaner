<?php
// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

// Redirect if already logged in
if(is_user_logged_in()) {
    redirect(USER_URL . 'orders.php');
}

// Initialize login attempts counter
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_login_attempt'] = time();
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Token keamanan tidak valid.";
    }
    
    // Sanitize input
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Username harus diisi.";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    }
    
    // Proceed with login if no errors
    if (empty($errors)) {
        // Check database connection
        if ($conn->connect_error) {
            $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, email, no_hp, alamat FROM penyewa WHERE username = ?");
            if (!$stmt) {
                $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            } else {
                $stmt->bind_param("s", $username);
                if (!$stmt->execute()) {
                    $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
                } else {
                    $result = $stmt->get_result();
                    
                    if($result->num_rows == 1) {
                        $user = $result->fetch_assoc();
                        if(password_verify($password, $user['password'])) {
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            // Clear any admin session to prevent mixing
                            unset($_SESSION['admin_id']);
                            unset($_SESSION['admin_username']);
                            
                            // Set user session data
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['no_hp'] = $user['no_hp'];
                            $_SESSION['alamat'] = $user['alamat'];
                            
                            // Reset login attempts
                            $_SESSION['login_attempts'] = 0;
                            
                            // Redirect based on previous destination or user dashboard
                            if(isset($_SESSION['login_redirect'])) {
                                $redirect = $_SESSION['login_redirect'];
                                unset($_SESSION['login_redirect']);
                                redirect_with_message($redirect, '', 'success');
                            } else {
                                redirect_with_message(USER_URL . 'orders.php', '', 'success');
                            }
                        } else {
                            $errors[] = "Username atau password salah.";
                            $_SESSION['login_attempts']++;
                            $_SESSION['last_login_attempt'] = time();
                        }
                    } else {
                        $errors[] = "Username atau password salah.";
                        $_SESSION['login_attempts']++;
                        $_SESSION['last_login_attempt'] = time();
                    }
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
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login Penyewa</h4>
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['login_message'])): ?>
                        <div class="alert alert-info"><?php echo $_SESSION['login_message']; unset($_SESSION['login_message']); ?></div>
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
                    
                    <form method="POST" action="<?php echo USER_URL; ?>login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text"
                                   class="form-control"
                                   id="username"
                                   name="username"
                                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                                   autocomplete="username"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   autocomplete="current-password"
                                   required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <a href="<?php echo USER_URL; ?>register.php" class="btn btn-outline-primary">Belum punya akun? Daftar</a>
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
