<?php
/**
 * Discount Codes Controller
 * Manages discount codes for hotel reservations
 */

require_once APP_PATH . '/controllers/BaseController.php';

class DiscountCodesController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'manager']);
    }
    
    /**
     * Display discount codes list
     */
    public function index() {
        $user = currentUser();
        $hotelId = $user['hotel_id'];
        
        // Get all discount codes for this hotel
        $stmt = $this->db->prepare("
            SELECT 
                dc.*,
                (SELECT COUNT(*) FROM discount_code_usages WHERE discount_code_id = dc.id) as total_usages
            FROM discount_codes dc
            WHERE dc.hotel_id = ?
            ORDER BY dc.created_at DESC
        ");
        $stmt->execute([$hotelId]);
        $discountCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('discount_codes/index', [
            'title' => 'Códigos de Descuento',
            'discountCodes' => $discountCodes
        ]);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $this->view('discount_codes/create', [
            'title' => 'Crear Código de Descuento'
        ]);
    }
    
    /**
     * Store new discount code
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('discount-codes');
            return;
        }
        
        $user = currentUser();
        
        try {
            $this->db->beginTransaction();
            
            $code = strtoupper(sanitize($_POST['code'] ?? ''));
            $discountType = sanitize($_POST['discount_type'] ?? 'percentage');
            $amount = floatval($_POST['amount'] ?? 0);
            $validFrom = sanitize($_POST['valid_from'] ?? '');
            $validTo = sanitize($_POST['valid_to'] ?? '');
            $usageLimit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
            $description = sanitize($_POST['description'] ?? '');
            $active = isset($_POST['active']) ? 1 : 0;
            
            // Validate input
            if (empty($code)) {
                throw new Exception('El código es requerido');
            }
            
            if ($amount <= 0) {
                throw new Exception('El monto del descuento debe ser mayor a 0');
            }
            
            if (empty($validFrom) || empty($validTo)) {
                throw new Exception('Las fechas de validez son requeridas');
            }
            
            // Check if code already exists
            $checkStmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM discount_codes 
                WHERE code = ? AND hotel_id = ?
            ");
            $checkStmt->execute([$code, $user['hotel_id']]);
            $exists = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($exists > 0) {
                throw new Exception('Este código de descuento ya existe');
            }
            
            // Insert discount code
            $stmt = $this->db->prepare("
                INSERT INTO discount_codes 
                (code, discount_type, amount, hotel_id, active, valid_from, valid_to, usage_limit, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $code,
                $discountType,
                $amount,
                $user['hotel_id'],
                $active,
                $validFrom,
                $validTo,
                $usageLimit,
                $description
            ]);
            
            $this->db->commit();
            
            flash('success', 'Código de descuento creado exitosamente', 'success');
            redirect('discount-codes');
        } catch (Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error al crear código de descuento: ' . $e->getMessage(), 'danger');
            redirect('discount-codes/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $user = currentUser();
        
        $stmt = $this->db->prepare("
            SELECT * FROM discount_codes 
            WHERE id = ? AND hotel_id = ?
        ");
        $stmt->execute([$id, $user['hotel_id']]);
        $discountCode = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$discountCode) {
            flash('error', 'Código de descuento no encontrado', 'danger');
            redirect('discount-codes');
            return;
        }
        
        $this->view('discount_codes/edit', [
            'title' => 'Editar Código de Descuento',
            'discountCode' => $discountCode
        ]);
    }
    
    /**
     * Update discount code
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('discount-codes');
            return;
        }
        
        $user = currentUser();
        
        try {
            $this->db->beginTransaction();
            
            $code = strtoupper(sanitize($_POST['code'] ?? ''));
            $discountType = sanitize($_POST['discount_type'] ?? 'percentage');
            $amount = floatval($_POST['amount'] ?? 0);
            $validFrom = sanitize($_POST['valid_from'] ?? '');
            $validTo = sanitize($_POST['valid_to'] ?? '');
            $usageLimit = !empty($_POST['usage_limit']) ? intval($_POST['usage_limit']) : null;
            $description = sanitize($_POST['description'] ?? '');
            $active = isset($_POST['active']) ? 1 : 0;
            
            // Validate input
            if (empty($code)) {
                throw new Exception('El código es requerido');
            }
            
            if ($amount <= 0) {
                throw new Exception('El monto del descuento debe ser mayor a 0');
            }
            
            if (empty($validFrom) || empty($validTo)) {
                throw new Exception('Las fechas de validez son requeridas');
            }
            
            // Check if code already exists (excluding current)
            $checkStmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM discount_codes 
                WHERE code = ? AND hotel_id = ? AND id != ?
            ");
            $checkStmt->execute([$code, $user['hotel_id'], $id]);
            $exists = $checkStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($exists > 0) {
                throw new Exception('Este código de descuento ya existe');
            }
            
            // Update discount code
            $stmt = $this->db->prepare("
                UPDATE discount_codes 
                SET code = ?, 
                    discount_type = ?, 
                    amount = ?, 
                    active = ?, 
                    valid_from = ?, 
                    valid_to = ?, 
                    usage_limit = ?, 
                    description = ?
                WHERE id = ? AND hotel_id = ?
            ");
            $stmt->execute([
                $code,
                $discountType,
                $amount,
                $active,
                $validFrom,
                $validTo,
                $usageLimit,
                $description,
                $id,
                $user['hotel_id']
            ]);
            
            $this->db->commit();
            
            flash('success', 'Código de descuento actualizado exitosamente', 'success');
            redirect('discount-codes');
        } catch (Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error al actualizar código de descuento: ' . $e->getMessage(), 'danger');
            redirect('discount-codes/edit/' . $id);
        }
    }
    
    /**
     * Delete discount code
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('discount-codes');
            return;
        }
        
        $user = currentUser();
        
        try {
            $stmt = $this->db->prepare("
                DELETE FROM discount_codes 
                WHERE id = ? AND hotel_id = ?
            ");
            $stmt->execute([$id, $user['hotel_id']]);
            
            flash('success', 'Código de descuento eliminado exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al eliminar código de descuento: ' . $e->getMessage(), 'danger');
        }
        
        redirect('discount-codes');
    }
}
