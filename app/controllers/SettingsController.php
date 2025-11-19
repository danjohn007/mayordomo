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
        
        // Get service type catalog
        $catalogModel = $this->model('ServiceTypeCatalog');
        $serviceTypes = $catalogModel->getAll($hotelId);
        
        $this->view('settings/index', [
            'title' => 'Configuraciones del Hotel',
            'settings' => $settings,
            'serviceTypes' => $serviceTypes
        ]);
    }
    
    /**
     * Add new service type
     */
    public function addServiceType() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
            return;
        }
        
        $user = currentUser();
        $data = [
            'hotel_id' => $user['hotel_id'],
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'icon' => sanitize($_POST['icon'] ?? 'bi-wrench'),
            'is_active' => 1,
            'sort_order' => intval($_POST['sort_order'] ?? 0)
        ];
        
        $catalogModel = $this->model('ServiceTypeCatalog');
        if ($catalogModel->create($data)) {
            flash('success', 'Tipo de servicio agregado exitosamente', 'success');
        } else {
            flash('error', 'Error al agregar tipo de servicio', 'danger');
        }
        
        redirect('settings');
    }
    
    /**
     * Edit service type
     */
    public function editServiceType($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
            return;
        }
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'icon' => sanitize($_POST['icon'] ?? 'bi-wrench'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => intval($_POST['sort_order'] ?? 0)
        ];
        
        $catalogModel = $this->model('ServiceTypeCatalog');
        if ($catalogModel->update($id, $data)) {
            flash('success', 'Tipo de servicio actualizado exitosamente', 'success');
        } else {
            flash('error', 'Error al actualizar tipo de servicio', 'danger');
        }
        
        redirect('settings');
    }
    
    /**
     * Delete service type
     */
    public function deleteServiceType($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('settings');
            return;
        }
        
        $catalogModel = $this->model('ServiceTypeCatalog');
        if ($catalogModel->delete($id)) {
            flash('success', 'Tipo de servicio desactivado exitosamente', 'success');
        } else {
            flash('error', 'Error al desactivar tipo de servicio', 'danger');
        }
        
        redirect('settings');
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
            
            // Save contact phone setting
            $contactPhone = sanitize($_POST['contact_phone'] ?? '');
            $this->saveSetting($hotelId, 'contact_phone', $contactPhone, 'string', 'general');
            
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
