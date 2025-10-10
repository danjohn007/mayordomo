<?php
/**
 * Settings Controller
 * Manages hotel-level settings for admin users
 */

require_once APP_PATH . '/controllers/BaseController.php';

class SettingsController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin']);
    }
    
    /**
     * Display settings page
     */
    public function index() {
        $user = currentUser();
        $hotelId = $user['hotel_id'];
        
        // Get current settings
        $stmt = $this->db->prepare("
            SELECT setting_key, setting_value, setting_type
            FROM hotel_settings
            WHERE hotel_id = ?
        ");
        $stmt->execute([$hotelId]);
        $settingsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to key-value array
        $settings = [];
        foreach ($settingsRaw as $setting) {
            $value = $setting['setting_value'];
            
            // Convert based on type
            if ($setting['setting_type'] === 'boolean') {
                $value = ($value === '1' || $value === 'true');
            } elseif ($setting['setting_type'] === 'number') {
                $value = (int)$value;
            }
            
            $settings[$setting['setting_key']] = $value;
        }
        
        $this->view('settings/index', [
            'title' => 'Configuraciones del Hotel',
            'settings' => $settings
        ]);
    }
    
    /**
     * Save settings
     */
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/settings');
            return;
        }
        
        $user = currentUser();
        $hotelId = $user['hotel_id'];
        
        try {
            $this->db->beginTransaction();
            
            // Save allow_table_overlap setting
            $allowTableOverlap = isset($_POST['allow_table_overlap']) ? 1 : 0;
            $this->saveSetting($hotelId, 'allow_table_overlap', $allowTableOverlap, 'boolean', 'reservations');
            
            // Save allow_room_overlap setting
            $allowRoomOverlap = isset($_POST['allow_room_overlap']) ? 1 : 0;
            $this->saveSetting($hotelId, 'allow_room_overlap', $allowRoomOverlap, 'boolean', 'reservations');
            
            $this->db->commit();
            
            flash('success', 'Configuraciones guardadas exitosamente', 'success');
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error saving settings: ' . $e->getMessage());
            flash('error', 'Error al guardar configuraciones: ' . $e->getMessage(), 'danger');
        }
        
        redirect('/settings');
    }
    
    /**
     * Save or update a single setting
     */
    private function saveSetting($hotelId, $key, $value, $type = 'string', $category = 'general') {
        $stmt = $this->db->prepare("
            INSERT INTO hotel_settings (hotel_id, setting_key, setting_value, setting_type, category)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value),
                setting_type = VALUES(setting_type),
                category = VALUES(category)
        ");
        
        $stmt->execute([
            $hotelId,
            $key,
            (string)$value,
            $type,
            $category
        ]);
    }
    
    /**
     * Get a specific setting value
     */
    public static function getSetting($hotelId, $key, $default = null) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT setting_value, setting_type
            FROM hotel_settings
            WHERE hotel_id = ? AND setting_key = ?
        ");
        $stmt->execute([$hotelId, $key]);
        $setting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$setting) {
            return $default;
        }
        
        $value = $setting['setting_value'];
        
        // Convert based on type
        if ($setting['setting_type'] === 'boolean') {
            return ($value === '1' || $value === 'true');
        } elseif ($setting['setting_type'] === 'number') {
            return (int)$value;
        }
        
        return $value;
    }
}
