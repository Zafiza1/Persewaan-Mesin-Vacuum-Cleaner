<?php
/**
 * Role-Based Access Control (RBAC) System
 * Provides comprehensive role and permission management
 */

require_once __DIR__ . '/logging.php';

// Define roles and permissions
define('ROLE_GUEST', 'guest');
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPER_ADMIN', 'super_admin');

// Define permissions
define('PERM_VIEW_PRODUCTS', 'view_products');
define('PERM_RENT_PRODUCTS', 'rent_products');
define('PERM_VIEW_OWN_RENTALS', 'view_own_rentals');
define('PERM_MANAGE_PRODUCTS', 'manage_products');
define('PERM_MANAGE_RENTALS', 'manage_rentals');
define('PERM_MANAGE_USERS', 'manage_users');
define('PERM_VIEW_REPORTS', 'view_reports');
define('PERM_SYSTEM_SETTINGS', 'system_settings');

/**
 * Role permissions mapping
 */
$ROLE_PERMISSIONS = [
    ROLE_GUEST => [
        PERM_VIEW_PRODUCTS
    ],
    ROLE_USER => [
        PERM_VIEW_PRODUCTS,
        PERM_RENT_PRODUCTS,
        PERM_VIEW_OWN_RENTALS
    ],
    ROLE_ADMIN => [
        PERM_VIEW_PRODUCTS,
        PERM_RENT_PRODUCTS,
        PERM_VIEW_OWN_RENTALS,
        PERM_MANAGE_PRODUCTS,
        PERM_MANAGE_RENTALS,
        PERM_VIEW_REPORTS
    ],
    ROLE_SUPER_ADMIN => [
        PERM_VIEW_PRODUCTS,
        PERM_RENT_PRODUCTS,
        PERM_VIEW_OWN_RENTALS,
        PERM_MANAGE_PRODUCTS,
        PERM_MANAGE_RENTALS,
        PERM_MANAGE_USERS,
        PERM_VIEW_REPORTS,
        PERM_SYSTEM_SETTINGS
    ]
];

/**
 * Check if current user has specific permission
 * @param string $permission Permission to check
 * @param string|null $user_role User role (optional, will use current session if null)
 * @return bool True if user has permission
 */
function has_permission($permission, $user_role = null) {
    global $ROLE_PERMISSIONS;
    
    if ($user_role === null) {
        $user_role = get_current_user_role();
    }
    
    // Guest has minimal permissions
    if ($user_role === ROLE_GUEST) {
        return in_array($permission, $ROLE_PERMISSIONS[ROLE_GUEST]);
    }
    
    // Check if role exists and has permission
    if (isset($ROLE_PERMISSIONS[$user_role])) {
        return in_array($permission, $ROLE_PERMISSIONS[$user_role]);
    }
    
    return false;
}

/**
 * Get current user role from session
 * @return string Current user role
 */
function get_current_user_role() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        return ROLE_GUEST;
    }
    
    // Check if user is admin
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        return ROLE_ADMIN;
    }
    
    return ROLE_USER;
}

/**
 * Require specific permission to access page
 * @param string $permission Required permission
 * @param string|null $redirect_url URL to redirect if not authorized
 */
function require_permission($permission, $redirect_url = null) {
    if (!has_permission($permission)) {
        $current_role = get_current_user_role();
        
        // Log unauthorized access attempt
        log_security_event('unauthorized_access', [
            'required_permission' => $permission,
            'user_role' => $current_role,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
        
        if ($redirect_url === null) {
            // Default redirect based on user role
            if ($current_role === ROLE_GUEST) {
                $redirect_url = BASE_URL . 'user/login.php';
            } else {
                $redirect_url = BASE_URL . 'index.php';
            }
        }
        
        $_SESSION['flash_message'] = 'Anda tidak memiliki akses ke halaman tersebut.';
        $_SESSION['flash_type'] = 'danger';
        
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Require specific role to access page
 * @param string|array $required_roles Required role(s)
 * @param string|null $redirect_url URL to redirect if not authorized
 */
function require_role($required_roles, $redirect_url = null) {
    $current_role = get_current_user_role();
    $required_roles = is_array($required_roles) ? $required_roles : [$required_roles];
    
    if (!in_array($current_role, $required_roles)) {
        // Log unauthorized role access attempt
        log_security_event('unauthorized_role_access', [
            'required_roles' => implode(', ', $required_roles),
            'user_role' => $current_role,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
        
        if ($redirect_url === null) {
            if ($current_role === ROLE_GUEST) {
                $redirect_url = BASE_URL . 'user/login.php';
            } else {
                $redirect_url = BASE_URL . 'index.php';
            }
        }
        
        $_SESSION['flash_message'] = 'Anda tidak memiliki hak akses yang cukup.';
        $_SESSION['flash_type'] = 'danger';
        
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Check if user can access specific resource
 * @param string $resource_type Type of resource (rental, product, user, etc.)
 * @param int $resource_id Resource ID
 * @param string $action Action to perform (view, edit, delete)
 * @return bool True if user can access resource
 */
function can_access_resource($resource_type, $resource_id, $action) {
    $current_role = get_current_user_role();
    $user_id = $_SESSION['user_id'] ?? 0;
    
    // Admin can access everything
    if ($current_role === ROLE_ADMIN) {
        return true;
    }
    
    // Users can only access their own rentals
    if ($resource_type === 'rental' && $current_role === ROLE_USER) {
        if ($action === 'view') {
            // Check if rental belongs to current user
            global $conn;
            $stmt = $conn->prepare("SELECT id_penyewa FROM sewa WHERE id = ?");
            $stmt->bind_param("i", $resource_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $rental = $result->fetch_assoc();
            $stmt->close();
            
            return $rental && $rental['id_penyewa'] == $user_id;
        }
    }
    
    return false;
}

/**
 * Get user permissions as array
 * @param string|null $user_role User role (optional)
 * @return array Array of permissions
 */
function get_user_permissions($user_role = null) {
    global $ROLE_PERMISSIONS;
    
    if ($user_role === null) {
        $user_role = get_current_user_role();
    }
    
    return $ROLE_PERMISSIONS[$user_role] ?? [];
}

/**
 * Check if current user is admin
 * @return bool True if user is admin
 */
function is_admin_user() {
    return get_current_user_role() === ROLE_ADMIN;
}

/**
 * Check if current user is super admin (not used in this system)
 * @return bool False - super admin not implemented
 */
function is_super_admin() {
    return false; // Super admin not implemented in current system
}

/**
 * Middleware function to protect admin routes
 */
function admin_auth_middleware() {
    require_role([ROLE_ADMIN]);
}

/**
 * Middleware function to protect user routes
 */
function user_auth_middleware() {
    require_role([ROLE_USER, ROLE_ADMIN]);
}

/**
 * Get role display name in Indonesian
 * @param string $role Role code
 * @return string Display name
 */
function get_role_display_name($role) {
    $role_names = [
        ROLE_GUEST => 'Tamu',
        ROLE_USER => 'Pengguna',
        ROLE_ADMIN => 'Administrator',
        ROLE_SUPER_ADMIN => 'Super Administrator'
    ];
    
    return $role_names[$role] ?? 'Tidak Diketahui';
}

/**
 * Update user role in database (not implemented for current system)
 * @param int $user_id User ID
 * @param string $new_role New role
 * @param mysqli $conn Database connection
 * @return bool False - role management not implemented
 */
function update_user_role($user_id, $new_role, $conn) {
    // Role management not implemented in current system
    // Users are either admin or regular user based on table
    return false;
}
?>
