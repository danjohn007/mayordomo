<?php
/**
 * Room Model
 */

class Room {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all rooms
     */
    public function getAll($filters = []) {
        $sql = "SELECT r.*, h.name as hotel_name,
                (SELECT image_path FROM resource_images WHERE resource_type = 'room' AND resource_id = r.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM rooms r LEFT JOIN hotels h ON r.hotel_id = h.id WHERE 1=1";
        $params = [];
        
        if (!empty($filters['hotel_id'])) {
            $sql .= " AND r.hotel_id = ?";
            $params[] = $filters['hotel_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND r.type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (r.room_number LIKE ? OR r.description LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY r.floor, r.room_number";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Find room by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new room
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO rooms (hotel_id, room_number, type, capacity, price, 
                price_monday, price_tuesday, price_wednesday, price_thursday, 
                price_friday, price_saturday, price_sunday,
                status, floor, description, amenities) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['hotel_id'],
            $data['room_number'],
            $data['type'],
            $data['capacity'],
            $data['price'],
            $data['price_monday'] ?? $data['price'],
            $data['price_tuesday'] ?? $data['price'],
            $data['price_wednesday'] ?? $data['price'],
            $data['price_thursday'] ?? $data['price'],
            $data['price_friday'] ?? $data['price'],
            $data['price_saturday'] ?? $data['price'],
            $data['price_sunday'] ?? $data['price'],
            $data['status'] ?? 'available',
            $data['floor'] ?? null,
            $data['description'] ?? null,
            $data['amenities'] ?? null
        ]);
    }
    
    /**
     * Update room
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE rooms 
            SET room_number = ?, type = ?, capacity = ?, price = ?,
                price_monday = ?, price_tuesday = ?, price_wednesday = ?, price_thursday = ?,
                price_friday = ?, price_saturday = ?, price_sunday = ?,
                status = ?, floor = ?, description = ?, amenities = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['room_number'],
            $data['type'],
            $data['capacity'],
            $data['price'],
            $data['price_monday'] ?? $data['price'],
            $data['price_tuesday'] ?? $data['price'],
            $data['price_wednesday'] ?? $data['price'],
            $data['price_thursday'] ?? $data['price'],
            $data['price_friday'] ?? $data['price'],
            $data['price_saturday'] ?? $data['price'],
            $data['price_sunday'] ?? $data['price'],
            $data['status'],
            $data['floor'] ?? null,
            $data['description'] ?? null,
            $data['amenities'] ?? null,
            $id
        ]);
    }
    
    /**
     * Delete room
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Check if room number exists
     */
    public function roomNumberExists($hotelId, $roomNumber, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM rooms WHERE hotel_id = ? AND room_number = ?";
        $params = [$hotelId, $roomNumber];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Update room status
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE rooms SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
