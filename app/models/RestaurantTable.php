<?php
/**
 * Restaurant Table Model
 */

class RestaurantTable {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT t.*,
                (SELECT image_path FROM resource_images WHERE resource_type = 'table' AND resource_id = t.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM restaurant_tables t WHERE 1=1";
        $params = [];
        
        if (!empty($filters['hotel_id'])) {
            $sql .= " AND t.hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (t.table_number LIKE ? OR t.description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['location'])) {
            $sql .= " AND t.location = ?";
            $params[] = $filters['location'];
        }
        
        $sql .= " ORDER BY t.table_number";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM restaurant_tables WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO restaurant_tables (hotel_id, table_number, capacity, location, status, description) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['hotel_id'],
            $data['table_number'],
            $data['capacity'],
            $data['location'] ?? null,
            $data['status'] ?? 'available',
            $data['description'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE restaurant_tables 
            SET table_number = ?, capacity = ?, location = ?, status = ?, description = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['table_number'],
            $data['capacity'],
            $data['location'] ?? null,
            $data['status'],
            $data['description'] ?? null,
            $id
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM restaurant_tables WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function tableNumberExists($hotelId, $tableNumber, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM restaurant_tables WHERE hotel_id = ? AND table_number = ?";
        $params = [$hotelId, $tableNumber];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
