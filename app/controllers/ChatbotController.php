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
        $reservationTime = sanitize($_POST['reservation_time'] ?? '');
        
        if (!$hotelId || !$resourceType || !$checkIn) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // Check hotel settings for rooms and tables
        require_once APP_PATH . '/controllers/SettingsController.php';
        $allowRoomOverlap = SettingsController::getSetting($hotelId, 'allow_room_overlap', false);
        $allowTableOverlap = SettingsController::getSetting($hotelId, 'allow_table_overlap', true);
        
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
                if ($allowRoomOverlap) {
                    // Allow all rooms if overlap is enabled
                    $available[] = $room;
                } else {
                    // Check if room is available
                    // Rooms are blocked for 21 hours: from 15:00 to 12:00 next day
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) as conflicts
                        FROM room_reservations
                        WHERE room_id = ?
                          AND status IN ('confirmed', 'checked_in', 'pending')
                          AND (
                              (check_in <= ? AND DATE_ADD(DATE_ADD(check_out, INTERVAL -12 HOUR), INTERVAL 1 DAY) > ?)
                              OR (check_in < ? AND DATE_ADD(DATE_ADD(check_out, INTERVAL -12 HOUR), INTERVAL 1 DAY) >= ?)
                              OR (check_in >= ? AND DATE_ADD(DATE_ADD(check_out, INTERVAL -12 HOUR), INTERVAL 1 DAY) <= DATE_ADD(DATE_ADD(?, INTERVAL -12 HOUR), INTERVAL 1 DAY))
                          )
                    ");
                    $stmt->execute([$room['id'], $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut]);
                    $result = $stmt->fetch();
                    
                    if ($result['conflicts'] == 0) {
                        $available[] = $room;
                    }
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
            $tables = $stmt->fetchAll();
            
            foreach ($tables as $table) {
                if ($allowTableOverlap) {
                    // Allow all tables if overlap is enabled
                    $available[] = $table;
                } else {
                    // Check if table is available
                    // Tables are blocked for 2 hours from reservation time
                    if (!empty($reservationTime)) {
                        $stmt = $this->db->prepare("
                            SELECT COUNT(*) as conflicts
                            FROM table_reservations
                            WHERE table_id = ?
                              AND status IN ('confirmed', 'seated', 'pending')
                              AND reservation_date = ?
                              AND (
                                  (reservation_time <= ? AND ADDTIME(reservation_time, '02:00:00') > ?)
                                  OR (reservation_time >= ? AND reservation_time < ADDTIME(?, '02:00:00'))
                              )
                        ");
                        $stmt->execute([$table['id'], $checkIn, $reservationTime, $reservationTime, $reservationTime, $reservationTime]);
                        $result = $stmt->fetch();
                        
                        if ($result['conflicts'] == 0) {
                            $available[] = $table;
                        }
                    } else {
                        // If no time specified, return all available tables
                        $available[] = $table;
                    }
                }
            }
        } elseif ($resourceType === 'amenity') {
            // Get all amenities for hotel
            $stmt = $this->db->prepare("
                SELECT a.*,
                       (SELECT image_path FROM resource_images WHERE resource_type = 'amenity' AND resource_id = a.id AND is_primary = 1 LIMIT 1) as image
                FROM amenities a
                WHERE a.hotel_id = ? AND a.is_available = 1
            ");
            $stmt->execute([$hotelId]);
            $amenities = $stmt->fetchAll();
            
            foreach ($amenities as $amenity) {
                // Amenities have individual allow_overlap settings
                $allowAmenityOverlap = $amenity['allow_overlap'] ?? 1;
                
                if ($allowAmenityOverlap) {
                    // Allow all amenities if overlap is enabled for this amenity
                    $available[] = $amenity;
                } else {
                    // Check if amenity is available based on its configuration
                    $blockDuration = $amenity['block_duration_hours'] ?? 2.00;
                    $maxReservations = $amenity['max_reservations'] ?? 1;
                    
                    if (!empty($reservationTime)) {
                        // Check existing reservations in the time block
                        $stmt = $this->db->prepare("
                            SELECT COUNT(*) as conflicts
                            FROM amenity_reservations
                            WHERE amenity_id = ?
                              AND status IN ('confirmed', 'in_use', 'pending')
                              AND reservation_date = ?
                              AND (
                                  (reservation_time <= ? AND ADDTIME(reservation_time, ?) > ?)
                                  OR (reservation_time >= ? AND reservation_time < ADDTIME(?, ?))
                              )
                        ");
                        $blockDurationTime = sprintf('%02d:%02d:00', floor($blockDuration), ($blockDuration - floor($blockDuration)) * 60);
                        $stmt->execute([
                            $amenity['id'], 
                            $checkIn, 
                            $reservationTime, $blockDurationTime, $reservationTime,
                            $reservationTime, $reservationTime, $blockDurationTime
                        ]);
                        $result = $stmt->fetch();
                        
                        // Check if we're under the max reservation limit
                        if ($result['conflicts'] < $maxReservations) {
                            $available[] = $amenity;
                        }
                    } else {
                        // If no time specified, return all available amenities
                        $available[] = $amenity;
                    }
                }
            }
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
            'reservation_time' => sanitize($_POST['reservation_time'] ?? ''),
            'room_number' => sanitize($_POST['room_number'] ?? ''),
            'is_visitor' => isset($_POST['is_visitor']) ? 1 : 0,
            'party_size' => intval($_POST['party_size'] ?? 2),
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
        
        // Validate time for tables and amenities
        if (($data['resource_type'] === 'table' || $data['resource_type'] === 'amenity') && empty($data['reservation_time'])) {
            $errors[] = 'La hora de reservación es requerida';
        }
        
        // Validate room number for guests reserving tables/amenities (not for room reservations)
        // Room number is only needed when guests reserve tables or amenities to identify them
        if ($data['resource_type'] !== 'room' && !$data['is_visitor'] && empty($data['room_number'])) {
            $errors[] = 'El número de habitación es requerido para huéspedes';
        }
        
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit;
        }
        
        // Create reservation based on resource type
        try {
            $this->db->beginTransaction();
            
            // Check hotel settings for rooms and tables
            require_once APP_PATH . '/controllers/SettingsController.php';
            $allowRoomOverlap = SettingsController::getSetting($data['hotel_id'], 'allow_room_overlap', false);
            $allowTableOverlap = SettingsController::getSetting($data['hotel_id'], 'allow_table_overlap', true);
            
            // Validate availability based on resource type
            if ($data['resource_type'] === 'room' && !$allowRoomOverlap) {
                // Check room availability (blocked for 21 hours: from 15:00 to 12:00 next day)
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as conflicts
                    FROM room_reservations
                    WHERE room_id = ?
                      AND status IN ('confirmed', 'checked_in', 'pending')
                      AND (
                          (check_in <= ? AND DATE_ADD(DATE_ADD(check_out, INTERVAL -12 HOUR), INTERVAL 1 DAY) > ?)
                          OR (check_in < ? AND DATE_ADD(DATE_ADD(check_out, INTERVAL -12 HOUR), INTERVAL 1 DAY) >= ?)
                          OR (check_in >= ? AND DATE_ADD(DATE_ADD(check_out, INTERVAL -12 HOUR), INTERVAL 1 DAY) <= DATE_ADD(DATE_ADD(?, INTERVAL -12 HOUR), INTERVAL 1 DAY))
                      )
                ");
                $stmt->execute([$data['resource_id'], $data['check_in_date'], $data['check_in_date'], 
                               $data['check_out_date'], $data['check_out_date'], 
                               $data['check_in_date'], $data['check_out_date']]);
                $result = $stmt->fetch();
                
                if ($result['conflicts'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'La habitación no está disponible para las fechas seleccionadas.']);
                    exit;
                }
            } elseif ($data['resource_type'] === 'table' && !$allowTableOverlap && !empty($data['reservation_time'])) {
                // Check table availability (blocked for 2 hours)
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as conflicts
                    FROM table_reservations
                    WHERE table_id = ?
                      AND status IN ('confirmed', 'seated', 'pending')
                      AND reservation_date = ?
                      AND (
                          (CAST(reservation_time AS CHAR) <= ? AND CAST(ADDTIME(reservation_time, '02:00:00') AS CHAR) > ?)
                          OR (CAST(reservation_time AS CHAR) >= ? AND CAST(reservation_time AS CHAR) < CAST(ADDTIME(?, '02:00:00') AS CHAR))
                      )
                ");
                $stmt->execute([$data['resource_id'], $data['check_in_date'], 
                               $data['reservation_time'], $data['reservation_time'],
                               $data['reservation_time'], $data['reservation_time']]);
                $result = $stmt->fetch();
                
                if ($result['conflicts'] > 0) {
                    echo json_encode(['success' => false, 'message' => 'La mesa no está disponible para el horario seleccionado.']);
                    exit;
                }
            } elseif ($data['resource_type'] === 'amenity' && !empty($data['reservation_time'])) {
                // Get amenity configuration
                $stmt = $this->db->prepare("SELECT allow_overlap, max_reservations, block_duration_hours FROM amenities WHERE id = ?");
                $stmt->execute([$data['resource_id']]);
                $amenity = $stmt->fetch();
                
                if ($amenity) {
                    $allowAmenityOverlap = $amenity['allow_overlap'] ?? 1;
                    
                    if (!$allowAmenityOverlap) {
                        // Check amenity availability based on its configuration
                        $blockDuration = $amenity['block_duration_hours'] ?? 2.00;
                        $maxReservations = $amenity['max_reservations'] ?? 1;
                        
                        $stmt = $this->db->prepare("
                            SELECT COUNT(*) as conflicts
                            FROM amenity_reservations
                            WHERE amenity_id = ?
                              AND status IN ('confirmed', 'in_use', 'pending')
                              AND reservation_date = ?
                              AND (
                                  (CAST(reservation_time AS CHAR) <= ? AND CAST(ADDTIME(reservation_time, ?) AS CHAR) > ?)
                                  OR (CAST(reservation_time AS CHAR) >= ? AND CAST(reservation_time AS CHAR) < CAST(ADDTIME(?, ?) AS CHAR))
                              )
                        ");
                        $blockDurationTime = sprintf('%02d:%02d:00', floor($blockDuration), ($blockDuration - floor($blockDuration)) * 60);
                        $stmt->execute([
                            $data['resource_id'], 
                            $data['check_in_date'],
                            $data['reservation_time'], $blockDurationTime, $data['reservation_time'],
                            $data['reservation_time'], $data['reservation_time'], $blockDurationTime
                        ]);
                        $result = $stmt->fetch();
                        
                        if ($result['conflicts'] >= $maxReservations) {
                            echo json_encode(['success' => false, 'message' => 'La amenidad ha alcanzado su capacidad máxima para el horario seleccionado.']);
                            exit;
                        }
                    }
                }
            }
            
            // Check if user exists by phone, if not create as guest
            $userModel = $this->model('User');
            $user = $userModel->findByPhone($data['guest_phone']);
            $guestId = null;
            
            if (!$user) {
                // Create new user as guest
                $randomPassword = bin2hex(random_bytes(8)); // Generate random 16-char password
                $userData = [
                    'email' => $data['guest_email'],
                    'password' => $randomPassword,
                    'first_name' => explode(' ', $data['guest_name'])[0],
                    'last_name' => implode(' ', array_slice(explode(' ', $data['guest_name']), 1)),
                    'phone' => $data['guest_phone'],
                    'role' => 'guest',
                    'hotel_id' => $data['hotel_id'],
                    'is_active' => 1
                ];
                
                // Check if email already exists
                if (!$userModel->emailExists($data['guest_email'])) {
                    if ($userModel->create($userData)) {
                        $guestId = $this->db->lastInsertId();
                    }
                } else {
                    // Email exists, use existing user
                    $user = $userModel->findByEmail($data['guest_email']);
                    $guestId = $user['id'];
                }
            } else {
                $guestId = $user['id'];
            }
            
            if ($data['resource_type'] === 'room') {
                // Create room reservation
                $stmt = $this->db->prepare("
                    INSERT INTO room_reservations 
                    (hotel_id, room_id, guest_id, guest_name, guest_email, guest_phone, check_in, check_out, total_price, status, special_requests)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'pending', ?)
                ");
                $specialRequests = $data['notes'];
                if (!$data['is_visitor'] && !empty($data['room_number'])) {
                    $specialRequests = 'Habitación: ' . $data['room_number'] . '. ' . $specialRequests;
                } elseif ($data['is_visitor']) {
                    $specialRequests = 'VISITA. ' . $specialRequests;
                }
                
                $stmt->execute([
                    $data['hotel_id'],
                    $data['resource_id'],
                    $guestId,
                    $data['guest_name'],
                    $data['guest_email'],
                    $data['guest_phone'],
                    $data['check_in_date'],
                    $data['check_out_date'],
                    $specialRequests
                ]);
            } elseif ($data['resource_type'] === 'table') {
                // Create table reservation
                $stmt = $this->db->prepare("
                    INSERT INTO table_reservations 
                    (hotel_id, table_id, guest_id, guest_name, guest_email, guest_phone, reservation_date, reservation_time, party_size, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $notes = $data['notes'];
                if (!$data['is_visitor'] && !empty($data['room_number'])) {
                    $notes = 'Habitación: ' . $data['room_number'] . '. ' . $notes;
                } elseif ($data['is_visitor']) {
                    $notes = 'VISITA. ' . $notes;
                }
                
                $stmt->execute([
                    $data['hotel_id'],
                    $data['resource_id'],
                    $guestId,
                    $data['guest_name'],
                    $data['guest_email'],
                    $data['guest_phone'],
                    $data['check_in_date'],
                    $data['reservation_time'],
                    $data['party_size'],
                    $notes
                ]);
            } elseif ($data['resource_type'] === 'amenity') {
                // Create amenity reservation
                $stmt = $this->db->prepare("
                    INSERT INTO amenity_reservations 
                    (hotel_id, amenity_id, user_id, guest_name, guest_email, guest_phone, reservation_date, reservation_time, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $notes = $data['notes'];
                if (!$data['is_visitor'] && !empty($data['room_number'])) {
                    $notes = 'Habitación: ' . $data['room_number'] . '. ' . $notes;
                } elseif ($data['is_visitor']) {
                    $notes = 'VISITA. ' . $notes;
                }
                
                $stmt->execute([
                    $data['hotel_id'],
                    $data['resource_id'],
                    $guestId,
                    $data['guest_name'],
                    $data['guest_email'],
                    $data['guest_phone'],
                    $data['check_in_date'],
                    $data['reservation_time'],
                    $notes
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
