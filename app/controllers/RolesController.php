<?php
/**
 * Roles Controller
 * Gestiona los permisos por rol y asignación de áreas
 */

require_once APP_PATH . '/controllers/BaseController.php';

class RolesController extends BaseController {
    
    /**
     * Mostrar listado de roles/permisos
     */
    public function index() {
        // Solo admin puede gestionar roles
        if (!hasRole(['admin'])) {
            flash('error', 'No tienes permiso para acceder a esta página', 'danger');
            redirect('dashboard');
        }
        
        $currentUser = currentUser();
        $hotelId = $currentUser['hotel_id'];
        
        // Obtener usuarios colaboradores del hotel con sus permisos
        $stmt = $this->db->prepare("
            SELECT 
                u.id, u.first_name, u.last_name, u.email, u.role,
                rp.can_manage_rooms, rp.can_manage_tables, rp.can_manage_menu,
                rp.amenity_ids, rp.service_types, rp.id as permission_id
            FROM users u
            LEFT JOIN role_permissions rp ON u.id = rp.user_id AND rp.hotel_id = ?
            WHERE u.hotel_id = ? 
            AND u.role IN ('manager', 'hostess', 'collaborator')
            AND u.is_active = 1
            ORDER BY u.role, u.first_name
        ");
        $stmt->execute([$hotelId, $hotelId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener amenidades disponibles
        $stmt = $this->db->prepare("SELECT id, name, category FROM amenities WHERE hotel_id = ? AND is_available = 1 ORDER BY name");
        $stmt->execute([$hotelId]);
        $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('roles/index', [
            'title' => 'Gestión de Roles y Permisos',
            'users' => $users,
            'amenities' => $amenities
        ]);
    }
    
    /**
     * Actualizar permisos de un usuario
     */
    public function update($userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('roles');
        }
        
        if (!hasRole(['admin'])) {
            flash('error', 'No tienes permiso para realizar esta acción', 'danger');
            redirect('roles');
        }
        
        $currentUser = currentUser();
        $hotelId = $currentUser['hotel_id'];
        
        // Verificar que el usuario pertenece al hotel
        $stmt = $this->db->prepare("SELECT id, role FROM users WHERE id = ? AND hotel_id = ?");
        $stmt->execute([$userId, $hotelId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            flash('error', 'Usuario no encontrado', 'danger');
            redirect('roles');
        }
        
        // Obtener permisos del formulario
        $canManageRooms = isset($_POST['can_manage_rooms']) ? 1 : 0;
        $canManageTables = isset($_POST['can_manage_tables']) ? 1 : 0;
        $canManageMenu = isset($_POST['can_manage_menu']) ? 1 : 0;
        
        // Amenidades seleccionadas (array de IDs)
        $amenityIds = isset($_POST['amenity_ids']) ? $_POST['amenity_ids'] : [];
        $amenityIdsJson = !empty($amenityIds) ? json_encode(array_map('intval', $amenityIds)) : null;
        
        // Tipos de servicios seleccionados
        $serviceTypes = isset($_POST['service_types']) ? $_POST['service_types'] : [];
        $serviceTypesJson = !empty($serviceTypes) ? json_encode($serviceTypes) : null;
        
        try {
            // Verificar si ya existe un registro de permisos
            $stmt = $this->db->prepare("SELECT id FROM role_permissions WHERE user_id = ? AND hotel_id = ?");
            $stmt->execute([$userId, $hotelId]);
            $existingPermission = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingPermission) {
                // Actualizar permisos existentes
                $stmt = $this->db->prepare("
                    UPDATE role_permissions 
                    SET can_manage_rooms = ?,
                        can_manage_tables = ?,
                        can_manage_menu = ?,
                        amenity_ids = ?,
                        service_types = ?,
                        updated_at = NOW()
                    WHERE user_id = ? AND hotel_id = ?
                ");
                $stmt->execute([
                    $canManageRooms,
                    $canManageTables,
                    $canManageMenu,
                    $amenityIdsJson,
                    $serviceTypesJson,
                    $userId,
                    $hotelId
                ]);
            } else {
                // Insertar nuevos permisos
                $stmt = $this->db->prepare("
                    INSERT INTO role_permissions 
                    (hotel_id, user_id, role_name, can_manage_rooms, can_manage_tables, can_manage_menu, amenity_ids, service_types, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $hotelId,
                    $userId,
                    $user['role'],
                    $canManageRooms,
                    $canManageTables,
                    $canManageMenu,
                    $amenityIdsJson,
                    $serviceTypesJson,
                    $currentUser['id']
                ]);
            }
            
            flash('success', 'Permisos actualizados exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al actualizar permisos: ' . $e->getMessage(), 'danger');
        }
        
        redirect('roles');
    }
}
