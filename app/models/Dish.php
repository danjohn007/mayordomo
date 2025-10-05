<?php
/**
 * Dish Model
 */

class Dish {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT * FROM dishes WHERE 1=1";
        $params = [];
        
        if (!empty($filters['hotel_id'])) {
            $sql .= " AND hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['is_available']) && $filters['is_available'] !== '') {
            $sql .= " AND is_available = ?";
            $params[] = $filters['is_available'];
        }
        
        $sql .= " ORDER BY category, name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM dishes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO dishes (hotel_id, name, category, price, description, service_time, is_available) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['hotel_id'],
            $data['name'],
            $data['category'],
            $data['price'],
            $data['description'] ?? null,
            $data['service_time'] ?? 'all_day',
            $data['is_available'] ?? 1
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE dishes 
            SET name = ?, category = ?, price = ?, description = ?, service_time = ?, is_available = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['category'],
            $data['price'],
            $data['description'] ?? null,
            $data['service_time'],
            $data['is_available'],
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM dishes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
