<?php
/**
 * Resource Image Model
 * Handles images for rooms, tables, and amenities
 */

class ResourceImage {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new image record
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO resource_images (resource_type, resource_id, image_path, display_order, is_primary) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $data['resource_type'],
            $data['resource_id'],
            $data['image_path'],
            $data['display_order'] ?? 0,
            $data['is_primary'] ?? 0
        ]);
    }
    
    /**
     * Get all images for a specific resource
     */
    public function getByResource($resourceType, $resourceId) {
        $stmt = $this->db->prepare("
            SELECT * FROM resource_images 
            WHERE resource_type = ? AND resource_id = ? 
            ORDER BY is_primary DESC, display_order ASC
        ");
        $stmt->execute([$resourceType, $resourceId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get primary image for a resource
     */
    public function getPrimaryImage($resourceType, $resourceId) {
        $stmt = $this->db->prepare("
            SELECT * FROM resource_images 
            WHERE resource_type = ? AND resource_id = ? AND is_primary = 1
            LIMIT 1
        ");
        $stmt->execute([$resourceType, $resourceId]);
        return $stmt->fetch();
    }
    
    /**
     * Delete an image
     */
    public function delete($id) {
        // Get image path before deleting
        $stmt = $this->db->prepare("SELECT image_path FROM resource_images WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete file from disk
            $filePath = PUBLIC_PATH . '/' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete from database
            $stmt = $this->db->prepare("DELETE FROM resource_images WHERE id = ?");
            return $stmt->execute([$id]);
        }
        
        return false;
    }
    
    /**
     * Delete all images for a resource
     */
    public function deleteByResource($resourceType, $resourceId) {
        // Get all images first
        $images = $this->getByResource($resourceType, $resourceId);
        
        foreach ($images as $image) {
            $this->delete($image['id']);
        }
        
        return true;
    }
    
    /**
     * Set an image as primary
     */
    public function setPrimary($id, $resourceType, $resourceId) {
        // First, unset all primary flags for this resource
        $stmt = $this->db->prepare("
            UPDATE resource_images 
            SET is_primary = 0 
            WHERE resource_type = ? AND resource_id = ?
        ");
        $stmt->execute([$resourceType, $resourceId]);
        
        // Then set the new primary
        $stmt = $this->db->prepare("UPDATE resource_images SET is_primary = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
