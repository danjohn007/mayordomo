<?php
/**
 * Notifications Controller
 * Maneja las notificaciones del sistema y API para polling
 */

require_once APP_PATH . '/controllers/BaseController.php';

class NotificationsController extends BaseController {
    
    /**
     * API para obtener notificaciones no leídas (para polling)
     */
    public function check() {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $currentUser = currentUser();
        
        try {
            // Obtener notificaciones no leídas del usuario
            $stmt = $this->db->prepare("
                SELECT 
                    sn.id, 
                    sn.notification_type,
                    sn.title,
                    sn.message,
                    sn.priority,
                    sn.requires_sound,
                    sn.related_type,
                    sn.related_id,
                    sn.created_at,
                    CASE 
                        WHEN sn.related_type = 'room_reservation' THEN (
                            SELECT status FROM room_reservations WHERE id = sn.related_id
                        )
                        WHEN sn.related_type = 'table_reservation' THEN (
                            SELECT status FROM table_reservations WHERE id = sn.related_id
                        )
                        WHEN sn.related_type = 'amenity_reservation' THEN (
                            SELECT status FROM amenity_reservations WHERE id = sn.related_id
                        )
                        WHEN sn.related_type = 'service_request' THEN (
                            SELECT status FROM service_requests WHERE id = sn.related_id
                        )
                        ELSE NULL
                    END as status
                FROM system_notifications sn
                WHERE sn.user_id = ? 
                AND sn.is_read = 0
                AND sn.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY sn.created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$currentUser['id']]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => count($notifications),
                'notifications' => $notifications
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    /**
     * Marcar notificación como leída
     */
    public function markAsRead($id) {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $currentUser = currentUser();
        
        try {
            $stmt = $this->db->prepare("
                UPDATE system_notifications 
                SET is_read = 1, read_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $currentUser['id']]);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Marcar todas las notificaciones como leídas
     */
    public function markAllAsRead() {
        header('Content-Type: application/json');
        
        if (!isLoggedIn()) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $currentUser = currentUser();
        
        try {
            $stmt = $this->db->prepare("
                UPDATE system_notifications 
                SET is_read = 1, read_at = NOW()
                WHERE user_id = ? AND is_read = 0
            ");
            $stmt->execute([$currentUser['id']]);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Listar todas las notificaciones del usuario
     */
    public function index() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        
        $currentUser = currentUser();
        
        // Obtener todas las notificaciones (últimas 50)
        $stmt = $this->db->prepare("
            SELECT *
            FROM system_notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$currentUser['id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('notifications/index', [
            'title' => 'Notificaciones',
            'notifications' => $notifications
        ]);
    }
}
