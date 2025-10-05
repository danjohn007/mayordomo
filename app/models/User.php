<?php
/**
 * User Model
 */

class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT u.*, h.name as hotel_name 
            FROM users u 
            LEFT JOIN hotels h ON u.hotel_id = h.id 
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, h.name as hotel_name 
            FROM users u 
            LEFT JOIN hotels h ON u.hotel_id = h.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, first_name, last_name, phone, role, hotel_id, subscription_id, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $hashedPassword = password_hash($data['password'], PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
        
        return $stmt->execute([
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['role'] ?? 'guest',
            $data['hotel_id'] ?? null,
            $data['subscription_id'] ?? null,
            $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id' && $key !== 'password') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Update password
     */
    public function updatePassword($id, $newPassword) {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $hashedPassword = password_hash($newPassword, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
        return $stmt->execute([$hashedPassword, $id]);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get all users
     */
    public function getAll($filters = []) {
        $sql = "SELECT u.*, h.name as hotel_name FROM users u LEFT JOIN hotels h ON u.hotel_id = h.id WHERE 1=1";
        $params = [];
        
        if (!empty($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        
        if (!empty($filters['hotel_id'])) {
            $sql .= " AND u.hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND u.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
