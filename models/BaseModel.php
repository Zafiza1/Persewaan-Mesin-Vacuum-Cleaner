<?php
/**
 * Base Model class
 */
abstract class BaseModel {
    protected $db;
    protected $table;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all records
     * @return array
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get record by ID
     * @param int $id
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }
    
    /**
     * Create new record
     * @param array $data
     * @return int|bool Insert ID or false on failure
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        $types = str_repeat('s', count($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->bind_param($types, ...array_values($data));
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }
    
    /**
     * Update record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $set_parts = [];
        foreach (array_keys($data) as $key) {
            $set_parts[] = "{$key} = ?";
        }
        $set_clause = implode(', ', $set_parts);
        $types = str_repeat('s', count($data)) . 'i';
        $values = array_values($data);
        $values[] = $id;
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$set_clause} WHERE id = ?");
        $stmt->bind_param($types, ...$values);
        
        return $stmt->execute();
    }
    
    /**
     * Delete record
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    /**
     * Count records
     * @return int
     */
    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table}");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'];
    }
}
