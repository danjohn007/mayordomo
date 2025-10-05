<?php
/**
 * Chatbot Controller - Public Reservation Interface
 */

require_once APP_PATH . '/controllers/BaseController.php';

class ChatbotController extends BaseController {
    
    /**
     * Show chatbot interface
     */
    public function index($hotelId = null) {
        if (!$hotelId) {
            echo "Hotel ID requerido";
            exit;
        }
        
        // Get hotel info
        $stmt = $this->db->prepare("SELECT * FROM hotels WHERE id = ? AND is_active = 1");
        $stmt->execute([$hotelId]);
        $hotel = $stmt->fetch();
        
        if (!$hotel) {
            echo "Hotel no encontrado";
            exit;
        }
        
        $this->view('chatbot/index', [
            'title' => 'Reservaciones - ' . $hotel['name'],
            'hotel' => $hotel,
            'hotelId' => $hotelId
        ], false); // false = don't require login
    }
    
    /**
     * Get available resources for a date range
     */
    public function checkAvailability() {
        header('Content-Type: application/json');
        
        $hotelId = intval($_POST['hotel_id'] ?? 0);
        $resourceType = sanitize($_POST['resource_type'] ?? '');
        $checkIn = sanitize($_POST['check_in'] ?? '');
        $checkOut = sanitize($_POST['check_out'] ?? '');
        
        if (!$hotelId || !$resourceType || !$checkIn) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Get available resources
        $available = [];
        
        if ($resourceType === 'room') {
            // Get all rooms for hotel
            $stmt = $this->db->prepare("
                SELECT r.*, 
                       (SELECT image_path FROM resource_images WHERE resource_type = 'room' AND resource_id = r.id AND is_primary = 1 LIMIT 1) as image
                FROM rooms r
                WHERE r.hotel_id = ? AND r.status = 'available'
            ");
            $stmt->execute([$hotelId]);
            $rooms = $stmt->fetchAll();
            
            foreach ($rooms as $room) {
                // Check if room is available
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as conflicts
                    FROM room_reservations
                    WHERE room_id = ?
                      AND status IN ('confirmed', 'checked_in')
                      AND (
                          (check_in <= ? AND check_out > ?)
                          OR (check_in < ? AND check_out >= ?)
                          OR (check_in >= ? AND check_out <= ?)
                      )
                ");
                $stmt->execute([$room['id'], $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut]);
                $result = $stmt->fetch();
                
                if ($result['conflicts'] == 0) {
                    $available[] = $room;
                }
            }
        } elseif ($resourceType === 'table') {
            // Get all tables for hotel
            $stmt = $this->db->prepare("
                SELECT t.*,
                       (SELECT image_path FROM resource_images WHERE resource_type = 'table' AND resource_id = t.id AND is_primary = 1 LIMIT 1) as image
                FROM restaurant_tables t
                WHERE t.hotel_id = ? AND t.status = 'available'
            ");
            $stmt->execute([$hotelId]);
            $available = $stmt->fetchAll();
        } elseif ($resourceType === 'amenity') {
            // Get all amenities for hotel
            $stmt = $this->db->prepare("
                SELECT a.*,
                       (SELECT image_path FROM resource_images WHERE resource_type = 'amenity' AND resource_id = a.id AND is_primary = 1 LIMIT 1) as image
                FROM amenities a
                WHERE a.hotel_id = ? AND a.is_available = 1
            ");
            $stmt->execute([$hotelId]);
            $available = $stmt->fetchAll();
        }
        
        echo json_encode(['success' => true, 'resources' => $available]);
    }
    
    /**
     * Create a chatbot reservation
     */
    public function createReservation() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        $data = [
            'hotel_id' => intval($_POST['hotel_id'] ?? 0),
            'resource_type' => sanitize($_POST['resource_type'] ?? ''),
            'resource_id' => intval($_POST['resource_id'] ?? 0),
            'guest_name' => sanitize($_POST['guest_name'] ?? ''),
            'guest_email' => sanitize($_POST['guest_email'] ?? ''),
            'guest_phone' => sanitize($_POST['guest_phone'] ?? ''),
            'check_in_date' => sanitize($_POST['check_in_date'] ?? ''),
            'check_out_date' => sanitize($_POST['check_out_date'] ?? ''),
            'notes' => sanitize($_POST['notes'] ?? '')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['guest_name'])) {
            $errors[] = 'El nombre es requerido';
        }
        
        if (empty($data['guest_email']) || !filter_var($data['guest_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }
        
        if (empty($data['guest_phone']) || !preg_match('/^[0-9]{10}$/', $data['guest_phone'])) {
            $errors[] = 'El teléfono debe contener exactamente 10 dígitos';
        }
        
        if (empty($data['check_in_date'])) {
            $errors[] = 'La fecha de inicio es requerida';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }
        
        // Create reservation based on resource type
        try {
            $this->db->beginTransaction();
            
            if ($data['resource_type'] === 'room') {
                // Create room reservation
                // Note: guest_id is set to NULL for anonymous chatbot reservations
                $stmt = $this->db->prepare("
                    INSERT INTO room_reservations 
                    (hotel_id, room_id, guest_id, guest_name, guest_email, guest_phone, check_in, check_out, total_price, status, special_requests)
                    VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 0, 'pending', ?)
                ");
                $stmt->execute([
                    $data['hotel_id'],
                    $data['resource_id'],
                    $data['guest_name'],
                    $data['guest_email'],
                    $data['guest_phone'],
                    $data['check_in_date'],
                    $data['check_out_date'],
                    $data['notes']
                ]);
            } elseif ($data['resource_type'] === 'table') {
                // Create table reservation
                // Note: guest_id is set to NULL for anonymous chatbot reservations
                $stmt = $this->db->prepare("
                    INSERT INTO table_reservations 
                    (hotel_id, table_id, guest_id, guest_name, guest_email, guest_phone, reservation_date, reservation_time, party_size, status, notes)
                    VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $stmt->execute([
                    $data['hotel_id'],
                    $data['resource_id'],
                    $data['guest_name'],
                    $data['guest_email'],
                    $data['guest_phone'],
                    $data['check_in_date'],
                    '19:00:00', // Default time
                    2, // Default party size
                    $data['notes']
                ]);
            } elseif ($data['resource_type'] === 'amenity') {
                // Create amenity reservation
                $stmt = $this->db->prepare("
                    INSERT INTO amenity_reservations 
                    (hotel_id, amenity_id, guest_name, guest_email, guest_phone, reservation_date, reservation_time, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $stmt->execute([
                    $data['hotel_id'],
                    $data['resource_id'],
                    $data['guest_name'],
                    $data['guest_email'],
                    $data['guest_phone'],
                    $data['check_in_date'],
                    '10:00:00', // Default time
                    $data['notes']
                ]);
            }
            
            $this->db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Reservación creada exitosamente. Te contactaremos pronto para confirmar.'
            ]);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Chatbot reservation error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al crear la reservación: ' . $e->getMessage()]);
        }
    }
}
