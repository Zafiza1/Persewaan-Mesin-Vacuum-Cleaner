<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * User Model
 */
class UserModel extends BaseModel {
    protected $table = 'penyewa';
    
    /**
     * Register new user
     * @param array $data
     * @return int|bool
     */
    public function register($data) {
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Check if username exists
        if ($this->getByUsername($data['username'])) {
            return false;
        }
        
        // Check if email exists
        if ($this->getByEmail($data['email'])) {
            return false;
        }
        
        return $this->create($data);
    }
    
    /**
     * Get user by username
     * @param string $username
     * @return array|null
     */
    public function getByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    
    /**
     * Get user by email
     * @param string $email
     * @return array|null
     */
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    
    /**
     * Authenticate user
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }
    
    /**
     * Update user profile
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProfile($id, $data) {
        // Don't allow password update through this method
        unset($data['password']);
        
        return $this->update($id, $data);
    }
    
    /**
     * Update user password
     * @param int $id
     * @param string $new_password
     * @return bool
     */
    public function updatePassword($id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $id);
        return $stmt->execute();
    }
}
