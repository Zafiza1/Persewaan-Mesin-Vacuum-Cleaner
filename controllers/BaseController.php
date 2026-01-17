<?php
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../includes/rbac.php';

/**
 * Base Controller class with RBAC support
 */
abstract class BaseController {
    protected $db;
    protected $current_user;
    protected $user_role;
    
    public function __construct($database) {
        $this->db = $database;
        $this->current_user = $this->getCurrentUser();
        $this->user_role = get_current_user_role();
    }
    
    /**
     * Get current user from session
     * @return array|null Current user data
     */
    protected function getCurrentUser() {
        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
            // Check if user is admin or regular user
            $user_id = $_SESSION['user_id'];
            $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
            
            if ($is_admin) {
                // Get admin data
                $stmt = $this->db->prepare("SELECT id, username, nama_lengkap, email, 'admin' as role FROM admin WHERE id = ?");
            } else {
                // Get penyewa data
                $stmt = $this->db->prepare("SELECT id, username, nama_lengkap, email, 'user' as role FROM penyewa WHERE id = ?");
            }
            
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
                return $user;
            }
        }
        return null;
    }
    
    /**
     * Require specific permission to access controller method
     * @param string $permission Required permission
     */
    protected function requirePermission($permission) {
        require_permission($permission);
    }
    
    /**
     * Require specific role to access controller method
     * @param string|array $roles Required role(s)
     */
    protected function requireRole($roles) {
        require_role($roles);
    }
    
    /**
     * Check if current user has permission
     * @param string $permission Permission to check
     * @return bool True if has permission
     */
    protected function hasPermission($permission) {
        return has_permission($permission);
    }
    
    /**
     * Load a view with data
     * @param string $view View file path
     * @param array $data Data to pass to view
     */
    protected function view($view, $data = []) {
        // Add user data to all views
        $data['current_user'] = $this->current_user;
        $data['user_role'] = $this->user_role;
        $data['user_permissions'] = get_user_permissions();
        
        extract($data);
        require_once __DIR__ . "/../views/{$view}.php";
    }
    
    /**
     * Load a model
     * @param string $model Model name
     * @return object Model instance
     */
    protected function model($model) {
        require_once __DIR__ . "/../models/{$model}.php";
        return new $model($this->db);
    }
    
    /**
     * Redirect to URL with flash message
     * @param string $url
     * @param string $message
     * @param string $type
     */
    protected function redirect($url, $message = '', $type = 'info') {
        if (!empty($message)) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header("Location: {$url}");
        exit();
    }
    
    /**
     * Send JSON response
     * @param mixed $data
     * @param int $status_code
     */
    protected function json($data, $status_code = 200) {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Validate required fields
     * @param array $data
     * @param array $required_fields
     * @return array Errors array
     */
    protected function validate($data, $required_fields) {
        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' harus diisi.';
            }
        }
        return $errors;
    }
    
    /**
     * Log user activity
     * @param string $action Action performed
     * @param array $details Additional details
     */
    protected function logActivity($action, $details = []) {
        log_user_activity($action, array_merge($details, [
            'user_id' => $this->current_user['id'] ?? null,
            'user_role' => $this->user_role,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]));
    }
    
    /**
     * Check if user can access specific resource
     * @param string $resource_type Type of resource
     * @param int $resource_id Resource ID
     * @param string $action Action to perform
     * @return bool True if can access
     */
    protected function canAccess($resource_type, $resource_id, $action) {
        return can_access_resource($resource_type, $resource_id, $action);
    }
}
