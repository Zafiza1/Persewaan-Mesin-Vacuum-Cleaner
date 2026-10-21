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

if(isAdmin()) {
    redirect_with_message(ADMIN_URL . 'dashboard.php', '', 'success');
}

require_once __DIR__ . '/../includes/header.php';

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
            $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap FROM admin WHERE username = ?");
            if (!$stmt) {
                $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            } else {
                $stmt->bind_param("s", $username);
                if (!$stmt->execute()) {
                    $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
                } else {
                    $result = $stmt->get_result();

                    if($result->num_rows == 1) {
                        $admin = $result->fetch_assoc();
                        if(password_verify($password, $admin['password'])) {
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);

                            // Clear any user session to prevent mixing
                            unset($_SESSION['user_id']);
                            unset($_SESSION['username']);
                            unset($_SESSION['nama_lengkap']);
                            unset($_SESSION['email']);
                            unset($_SESSION['no_hp']);
                            unset($_SESSION['alamat']);

                            $_SESSION['admin_id'] = $admin['id'];
                            $_SESSION['admin_username'] = $admin['username'];
                            $_SESSION['admin_nama'] = $admin['nama_lengkap'];

                            $stmt->close();
                            redirect_with_message(ADMIN_URL . 'dashboard.php', '', 'success');
                        } else {
                            $errors[] = "Username atau password salah.";
                        }
                    } else {
                        $errors[] = "Username atau password salah.";
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login Admin</h4>
                </div>
                <div class="card-body">
                    <?php if(!empty($errors)): ?>
                    <?php
                        // Also log errors to browser console for easier debugging
                        echo '<script>';
                        foreach ($errors as $e) {
                            $msg = json_encode($e, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                            echo "console.error('Login error: ' + {$msg});";
                        }
                        echo '</script>';
                    ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo ADMIN_URL; ?>login.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text"
                                   class="form-control"
                                   id="username"
                                   name="username"
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
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Belum punya akun? <a href="<?php echo ADMIN_URL; ?>register.php">Register di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
