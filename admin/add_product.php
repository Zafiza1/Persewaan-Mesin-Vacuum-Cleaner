<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Include path configuration
require_once __DIR__ . '/../config/paths.php';

// Use path constants for includes
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/security.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isAdmin()) {
    redirect(ADMIN_URL . 'login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Token keamanan tidak valid. Silakan coba lagi.";
    } else {
        $nama_mesin = trim($_POST['nama_mesin']);
        $deskripsi = trim($_POST['deskripsi']);
        $harga_sewa = (float)$_POST['harga_sewa'];
        $stok = (int)$_POST['stok'];
        
        // Validate inputs
        if (empty($nama_mesin)) {
            $errors[] = "Nama mesin harus diisi";
        }
        
        if (empty($deskripsi)) {
            $errors[] = "Deskripsi harus diisi";
        }
        
        if ($harga_sewa <= 0) {
            $errors[] = "Harga sewa harus lebih dari 0";
        }
        
        if ($stok < 0) {
            $errors[] = "Stok tidak boleh negatif";
        }
        
        // Handle file upload
        if (isset($_FILES['gambar'])) {
            $upload_dir = IMAGES_PATH . 'products/';
            
            // Ensure upload directory exists
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = basename($_FILES['gambar']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_name = uniqid('product_', true).'.'.$file_ext;
            $target_path = $upload_dir.$new_name;
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];
            $max_file_size = 2 * 1024 * 1024; // 2MB
            
            // Check file upload error
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                switch ($_FILES['gambar']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errors[] = "Ukuran file terlalu besar. Maksimal 2MB";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $errors[] = "File hanya terupload sebagian";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $errors[] = "Silakan pilih gambar produk";
                        break;
                    default:
                        $errors[] = "Terjadi kesalahan saat mengupload file";
                }
            }
            // Check file size
            elseif ($_FILES['gambar']['size'] > $max_file_size) {
                $errors[] = "Ukuran file terlalu besar. Maksimal 2MB";
            }
            // Check file type
            elseif (!in_array($file_ext, $allowed_types)) {
                $errors[] = "Hanya file JPG, JPEG, PNG, atau WEBP yang diperbolehkan";
            }
            // Check MIME type
            else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $_FILES['gambar']['tmp_name']);
                finfo_close($finfo);
                
                $allowed_mimes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];
                
                if (!in_array($mime_type, $allowed_mimes)) {
                    $errors[] = "Tipe file tidak valid";
                }
            }
            
            // If no errors, proceed with upload and database insertion
            if (empty($errors)) {
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_path)) {
                    // Insert to database using prepared statement
                    $sql = "INSERT INTO produk (nama_mesin, deskripsi, harga_sewa, stok, gambar) 
                            VALUES (?, ?, ?, ?, ?)";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdis", $nama_mesin, $deskripsi, $harga_sewa, $stok, $new_name);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success'] = "Produk berhasil ditambahkan";
                        redirect(ADMIN_URL . 'manage_products.php');
                    } else {
                        $errors[] = "Error database: ".$stmt->error;
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Gagal mengupload gambar";
                }
            }
        } else {
            $errors[] = "Silakan pilih gambar produk";
        }
    }
}

$page_title = "Tambah Produk";
include '../includes/header.php';
?>

<div class="container py-4">
    <h2>Tambah Produk Baru</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="mb-3">
            <label class="form-label">Nama Mesin</label>
            <input type="text" class="form-control" name="nama_mesin" value="<?php echo isset($_POST['nama_mesin']) ? htmlspecialchars($_POST['nama_mesin']) : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control" name="deskripsi" rows="3" required><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Harga Sewa per Hari</label>
                <input type="number" class="form-control" name="harga_sewa" min="0" step="1000" value="<?php echo isset($_POST['harga_sewa']) ? htmlspecialchars($_POST['harga_sewa']) : ''; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Stok Tersedia</label>
                <input type="number" class="form-control" name="stok" min="0" value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar Produk</label>
            <input type="file" class="form-control" name="gambar" accept="image/*" required>
            <small class="text-muted">Format: JPG, PNG, WEBP (Maks. 2MB)</small>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan Produk</button>
        <a href="<?php echo ADMIN_URL; ?>manage_products.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
