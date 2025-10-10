<?php
/**
 * Service Request Model
 */

class ServiceRequest {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($filters = []) {
        $sql = "
            SELECT sr.*, 
                   guest.first_name as guest_first_name, guest.last_name as guest_last_name,
                   collab.first_name as collab_first_name, collab.last_name as collab_last_name,
                   stc.name as service_type_name, stc.icon as service_type_icon
            FROM service_requests sr
            JOIN users guest ON sr.guest_id = guest.id
            LEFT JOIN users collab ON sr.assigned_to = collab.id
            LEFT JOIN service_type_catalog stc ON sr.service_type_id = stc.id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filters['hotel_id'])) {
            $sql .= " AND sr.hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND sr.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $sql .= " AND sr.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (!empty($filters['guest_id'])) {
            $sql .= " AND sr.guest_id = ?";
            $params[] = $filters['guest_id'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (sr.title LIKE ? OR sr.description LIKE ? OR sr.room_number LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND sr.priority = ?";
            $params[] = $filters['priority'];
        }
        
        $sql .= " ORDER BY sr.requested_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT sr.*, 
                   guest.first_name as guest_first_name, guest.last_name as guest_last_name,
                   collab.first_name as collab_first_name, collab.last_name as collab_last_name,
                   stc.name as service_type_name, stc.icon as service_type_icon
            FROM service_requests sr
            JOIN users guest ON sr.guest_id = guest.id
            LEFT JOIN users collab ON sr.assigned_to = collab.id
            LEFT JOIN service_type_catalog stc ON sr.service_type_id = stc.id
            WHERE sr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO service_requests (hotel_id, guest_id, assigned_to, service_type_id, title, description, priority, status, room_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['hotel_id'],
            $data['guest_id'],
            $data['assigned_to'] ?? null,
            $data['service_type_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['priority'] ?? 'normal',
            $data['status'] ?? 'pending',
            $data['room_number'] ?? null
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE service_requests 
            SET assigned_to = ?, service_type_id = ?, title = ?, description = ?, priority = ?, status = ?, room_number = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['assigned_to'] ?? null,
            $data['service_type_id'] ?? null,
            $data['title'],
            $data['description'] ?? null,
            $data['priority'],
            $data['status'],
            $data['room_number'] ?? null,
            $id
        ]);
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE service_requests SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function assignTo($id, $userId) {
        $stmt = $this->db->prepare("UPDATE service_requests SET assigned_to = ?, status = 'assigned' WHERE id = ?");
        return $stmt->execute([$userId, $id]);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM service_requests WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
