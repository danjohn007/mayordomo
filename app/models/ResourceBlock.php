<?php
/**
 * Resource Block Model
 */

class ResourceBlock {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT rb.*, u.first_name, u.last_name FROM resource_blocks rb JOIN users u ON rb.blocked_by = u.id WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND rb.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY rb.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO resource_blocks (resource_type, resource_id, blocked_by, reason, start_date, end_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        
        return $stmt->execute([
            $data['resource_type'],
            $data['resource_id'],
            $data['blocked_by'],
            $data['reason'],
            $data['start_date'],
            $data['end_date']
        ]);
    }
    
    public function release($id) {
        $stmt = $this->db->prepare("UPDATE resource_blocks SET status = 'released' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
