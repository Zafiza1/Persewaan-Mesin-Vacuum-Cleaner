<?php
/**
 * Server-side validation utilities
 * Provides comprehensive validation functions for form data
 */

/**
 * Validate rental form data
 * @param array $data Form data to validate
 * @param mysqli $conn Database connection
 * @return array Validation result with errors and sanitized data
 */
function validate_rental_form($data, $conn) {
    $errors = [];
    $sanitized = [];
    
    // Validate product ID
    $produk_id = filter_var($data['produk_id'] ?? 0, FILTER_VALIDATE_INT);
    if (!$produk_id || $produk_id <= 0) {
        $errors['produk_id'] = 'Produk tidak valid.';
    } else {
        // Check if product exists and is available using helper
        $product = getProductById($conn, $produk_id);
        
        if (!$product || $product['stok'] <= 0) {
            $errors['produk_id'] = 'Produk tidak tersedia atau stok habis.';
        } else {
            $sanitized['produk'] = $product;
        }
    }
    
    // Validate start date
    $start_date = $data['start_date'] ?? '';
    if (empty($start_date)) {
        $errors['start_date'] = 'Tanggal mulai sewa harus diisi.';
    } else {
        $date_obj = DateTime::createFromFormat('Y-m-d', $start_date);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $start_date) {
            $errors['start_date'] = 'Format tanggal mulai tidak valid.';
        } else {
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            if ($date_obj < $today) {
                $errors['start_date'] = 'Tanggal mulai tidak boleh kurang dari hari ini.';
            } else {
                $sanitized['start_date'] = $start_date;
            }
        }
    }
    
    // Validate end date
    $end_date = $data['end_date'] ?? '';
    if (empty($end_date)) {
        $errors['end_date'] = 'Tanggal selesai sewa harus diisi.';
    } else {
        $date_obj = DateTime::createFromFormat('Y-m-d', $end_date);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $end_date) {
            $errors['end_date'] = 'Format tanggal selesai tidak valid.';
        } else {
            $sanitized['end_date'] = $end_date;
        }
    }
    
    // Validate date range
    if (isset($sanitized['start_date']) && isset($sanitized['end_date'])) {
        $start = new DateTime($sanitized['start_date']);
        $end = new DateTime($sanitized['end_date']);
        
        if ($end < $start) {
            $errors['end_date'] = 'Tanggal selesai tidak boleh kurang dari tanggal mulai.';
        } else {
            $interval = $start->diff($end);
            $sanitized['duration_days'] = $interval->days + 1; // +1 because end date is inclusive
            
            if ($sanitized['duration_days'] > 30) {
                $errors['end_date'] = 'Maksimal sewa adalah 30 hari.';
            }
        }
    }
    
    // Validate quantity
    $quantity = filter_var($data['quantity'] ?? 0, FILTER_VALIDATE_INT);
    if (!$quantity || $quantity <= 0) {
        $errors['quantity'] = 'Jumlah harus diisi dengan angka positif.';
    } elseif ($quantity > 10) {
        $errors['quantity'] = 'Maksimal sewa adalah 10 unit per transaksi.';
    } elseif (isset($sanitized['produk']) && $quantity > $sanitized['produk']['stok']) {
        $errors['quantity'] = 'Jumlah melebihi stok yang tersedia (' . $sanitized['produk']['stok'] . ' unit).';
    } else {
        $sanitized['quantity'] = $quantity;
    }
    
    // Validate notes (optional)
    $notes = trim($data['notes'] ?? '');
    if (!empty($notes)) {
        if (strlen($notes) > 500) {
            $errors['notes'] = 'Catatan maksimal 500 karakter.';
        } else {
            $sanitized['notes'] = htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');
        }
    } else {
        $sanitized['notes'] = '';
    }
    
    // Calculate total price
    if (isset($sanitized['produk'], $sanitized['duration_days'], $sanitized['quantity'])) {
        $product = $sanitized['produk'];
        $subtotal = $product['harga_sewa_per_hari'] * $sanitized['duration_days'] * $sanitized['quantity'];
        $discount_amount = 0;
        $total_price = $subtotal;
        $sanitized['discount_percentage'] = 0;
        $sanitized['subtotal'] = $subtotal;
        $sanitized['discount_amount'] = $discount_amount;
        $sanitized['total_price'] = $total_price;
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}

/**
 * Validate user registration form
 * @param array $data Form data
 * @param mysqli $conn Database connection
 * @return array Validation result
 */
function validate_registration_form($data, $conn) {
    $errors = [];
    $sanitized = [];
    
    // Validate name
    $name = trim($data['name'] ?? '');
    if (empty($name)) {
        $errors['name'] = 'Nama lengkap harus diisi.';
    } elseif (strlen($name) < 3) {
        $errors['name'] = 'Nama minimal 3 karakter.';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Nama maksimal 100 karakter.';
    } elseif (!preg_match('/^[a-zA-Z\s.,-]+$/', $name)) {
        $errors['name'] = 'Nama hanya boleh mengandung huruf, spasi, titik, koma, dan strip.';
    } else {
        $sanitized['name'] = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }
    
    // Validate email
    $email = trim($data['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email maksimal 100 karakter.';
    } else {
        // Check if email already exists in penyewa table
        $stmt = $conn->prepare("SELECT id FROM penyewa WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['email'] = 'Email sudah terdaftar.';
        } else {
            $sanitized['email'] = strtolower($email);
        }
        $stmt->close();
    }
    
    // Validate phone
    $phone = trim($data['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Nomor telepon harus diisi.';
    } else {
        // Remove common separators
        $clean_phone = preg_replace('/[-\s]/', '', $phone);
        if (!preg_match('/^(\+62|62|0)[0-9]{9,13}$/', $clean_phone)) {
            $errors['phone'] = 'Format nomor telepon tidak valid. Gunakan format: +62812345678 atau 0812345678';
        } else {
            $sanitized['phone'] = $clean_phone;
        }
    }
    
    // Validate address
    $address = trim($data['address'] ?? '');
    if (empty($address)) {
        $errors['address'] = 'Alamat harus diisi.';
    } elseif (strlen($address) < 10) {
        $errors['address'] = 'Alamat terlalu singkat (minimal 10 karakter).';
    } elseif (strlen($address) > 500) {
        $errors['address'] = 'Alamat maksimal 500 karakter.';
    } else {
        $sanitized['address'] = htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
    }
    
    // Validate password
    $password = $data['password'] ?? '';
    if (empty($password)) {
        $errors['password'] = 'Password harus diisi.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }
    
    // Validate password confirmation
    $password_confirm = $data['password_confirm'] ?? '';
    if (empty($password_confirm)) {
        $errors['password_confirm'] = 'Konfirmasi password harus diisi.';
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Konfirmasi password tidak cocok.';
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}

/**
 * Validate login form
 * @param array $data Form data
 * @param mysqli $conn Database connection
 * @return array Validation result
 */
function validate_login_form($data, $conn) {
    $errors = [];
    $sanitized = [];
    
    // Validate email
    $email = trim($data['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    } else {
        $sanitized['email'] = strtolower($email);
    }
    
    // Validate password
    $password = $data['password'] ?? '';
    if (empty($password)) {
        $errors['password'] = 'Password harus diisi.';
    } elseif (strlen($password) < 1) {
        $errors['password'] = 'Password tidak valid.';
    }
    
    return [
        'success' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}

/**
 * Sanitize input data
 * @param mixed $input Input to sanitize
 * @param string $type Type of input (string, int, float, email, url)
 * @return mixed Sanitized input
 */
function sanitize_input($input, $type = 'string') {
    if ($input === null) {
        return null;
    }
    
    switch ($type) {
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT);
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT);
        case 'email':
            return filter_var(trim($input), FILTER_VALIDATE_EMAIL);
        case 'url':
            return filter_var(trim($input), FILTER_VALIDATE_URL);
        case 'string':
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Generate detailed error message for display
 * @param array $errors Validation errors
 * @return string Formatted error message
 */
function format_validation_errors($errors) {
    if (empty($errors)) {
        return '';
    }
    
    $error_messages = [];
    foreach ($errors as $field => $messages) {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $error_messages[] = ucfirst($field) . ': ' . $message;
            }
        } else {
            $error_messages[] = ucfirst($field) . ': ' . $messages;
        }
    }
    
    return '<div class="alert alert-danger"><strong>Perbaiki kesalahan berikut:</strong><ul><li>' . 
           implode('</li><li>', $error_messages) . '</li></ul></div>';
}
?>
