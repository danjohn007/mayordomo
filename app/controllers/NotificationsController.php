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
                    id, 
                    notification_type,
                    title,
                    message,
                    priority,
                    requires_sound,
                    created_at
                FROM system_notifications
                WHERE user_id = ? 
                AND is_read = 0
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY created_at DESC
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
