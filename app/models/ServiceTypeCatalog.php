<?php
/**
 * Service Type Catalog Model
 */

class ServiceTypeCatalog {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($hotelId) {
        $stmt = $this->db->prepare("
            SELECT * FROM service_type_catalog 
            WHERE hotel_id = ? 
            ORDER BY sort_order, name
        ");
        $stmt->execute([$hotelId]);
        return $stmt->fetchAll();
    }
    
    public function getAllActive($hotelId) {
        $stmt = $this->db->prepare("
            SELECT * FROM service_type_catalog 
            WHERE hotel_id = ? AND is_active = 1 
            ORDER BY sort_order, name
        ");
        $stmt->execute([$hotelId]);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM service_type_catalog WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO service_type_catalog (hotel_id, name, description, icon, is_active, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['hotel_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['icon'] ?? 'bi-wrench',
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE service_type_catalog 
            SET name = ?, description = ?, icon = ?, is_active = ?, sort_order = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['icon'] ?? 'bi-wrench',
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0,
            $id
        ]);
    }
    
    public function delete($id) {
        // Soft delete - just deactivate
        $stmt = $this->db->prepare("UPDATE service_type_catalog SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function hardDelete($id) {
        // Hard delete - use with caution
        $stmt = $this->db->prepare("DELETE FROM service_type_catalog WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
