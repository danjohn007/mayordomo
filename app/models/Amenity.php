<?php
/**
 * Amenity Model
 */

class Amenity {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT * FROM amenities WHERE 1=1";
        $params = [];
        
        if (!empty($filters['hotel_id'])) {
            $sql .= " AND hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        $sql .= " ORDER BY category, name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM amenities WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO amenities (hotel_id, name, category, price, capacity, opening_time, closing_time, description, is_available) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['hotel_id'],
            $data['name'],
            $data['category'],
            $data['price'] ?? 0,
            $data['capacity'] ?? null,
            $data['opening_time'] ?? null,
            $data['closing_time'] ?? null,
            $data['description'] ?? null,
            $data['is_available'] ?? 1
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE amenities 
            SET name = ?, category = ?, price = ?, capacity = ?, opening_time = ?, closing_time = ?, description = ?, is_available = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['category'],
            $data['price'],
            $data['capacity'] ?? null,
            $data['opening_time'] ?? null,
            $data['closing_time'] ?? null,
            $data['description'] ?? null,
            $data['is_available'],
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM amenities WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
