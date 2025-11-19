<?php
/**
 * Reservations Controller
 * Maneja las reservaciones de habitaciones y mesas de forma unificada
 */

require_once APP_PATH . '/controllers/BaseController.php';

class ReservationsController extends BaseController {
    
    /**
     * Mostrar listado de reservaciones
     */
    public function index() {
        // Solo admin, manager y hostess pueden ver reservaciones
        if (!hasRole(['admin', 'manager', 'hostess'])) {
            flash('error', 'No tienes permiso para acceder a esta página', 'danger');
            redirect('dashboard');
        }
        
        $currentUser = currentUser();
        $hotelId = $currentUser['hotel_id'];
        
        // Obtener filtros
        $filters = [
            'type' => sanitize($_GET['type'] ?? ''),
            'status' => sanitize($_GET['status'] ?? ''),
            'search' => sanitize($_GET['search'] ?? ''),
            'date_from' => sanitize($_GET['date_from'] ?? ''),
            'date_to' => sanitize($_GET['date_to'] ?? '')
        ];
        
        // Construir query base
        $sql = "SELECT * FROM v_all_reservations WHERE hotel_id = ?";
        $params = [$hotelId];
        
        // Aplicar filtros
        if (!empty($filters['type'])) {
            $sql .= " AND reservation_type = ?";
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        } else {
            // Si no se especifica un filtro de estado, excluir canceladas por defecto
            $sql .= " AND status != 'cancelled'";
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (guest_name LIKE ? OR guest_email LIKE ? OR resource_number LIKE ? OR confirmation_code LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND reservation_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND reservation_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        // Ordenar por fecha de creación descendente
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('reservations/index', [
            'title' => 'Reservaciones',
            'reservations' => $reservations,
            'filters' => $filters
        ]);
    }
    
    /**
     * Mostrar formulario de crear reservación
     */
    public function create() {
        if (!hasRole(['admin', 'manager', 'hostess'])) {
            flash('error', 'No tienes permiso para acceder a esta página', 'danger');
            redirect('dashboard');
        }
        
        $this->view('reservations/create', [
            'title' => 'Nueva Reservación'
        ]);
    }
    
    /**
     * Guardar nueva reservación
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('reservations');
        }
        
        if (!hasRole(['admin', 'manager', 'hostess'])) {
            flash('error', 'No tienes permiso para realizar esta acción', 'danger');
            redirect('reservations');
        }
        
        $currentUser = currentUser();
        $type = sanitize($_POST['reservation_type'] ?? '');
        $resourceId = intval($_POST['resource_id'] ?? 0);
        $roomIds = isset($_POST['room_ids']) && is_array($_POST['room_ids']) ? array_map('intval', $_POST['room_ids']) : [];
        $status = sanitize($_POST['status'] ?? 'pending');
        $notes = sanitize($_POST['notes'] ?? '');
        $guestType = sanitize($_POST['guest_type'] ?? 'existing');
        
        try {
            $this->db->beginTransaction();
            
            // Determine guest_id
            $guestId = null;
            if ($guestType === 'existing') {
                $guestId = intval($_POST['guest_id'] ?? 0);
                if (!$guestId) {
                    throw new Exception('Debe seleccionar un huésped');
                }
            } else {
                // Create new guest
                $guestName = sanitize($_POST['guest_name'] ?? '');
                $guestEmail = sanitize($_POST['guest_email'] ?? '');
                $guestPhone = sanitize($_POST['guest_phone'] ?? '');
                
                if (empty($guestName) || empty($guestEmail) || empty($guestPhone)) {
                    throw new Exception('Todos los campos del huésped son requeridos');
                }
                
                // Validate phone (10 digits)
                if (!preg_match('/^\d{10}$/', $guestPhone)) {
                    throw new Exception('El teléfono debe tener exactamente 10 dígitos');
                }
                
                // Check if email exists
                $userModel = $this->model('User');
                if ($userModel->emailExists($guestEmail)) {
                    $existingUser = $userModel->findByEmail($guestEmail);
                    $guestId = $existingUser['id'];
                } else {
                    // Create new user
                    $nameParts = explode(' ', $guestName);
                    $firstName = $nameParts[0];
                    $lastName = implode(' ', array_slice($nameParts, 1));
                    
                    $userData = [
                        'email' => $guestEmail,
                        'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $guestPhone,
                        'role' => 'guest',
                        'hotel_id' => $currentUser['hotel_id'],
                        'is_active' => 1
                    ];
                    
                    if ($userModel->create($userData)) {
                        $guestId = $this->db->lastInsertId();
                    } else {
                        throw new Exception('Error al crear el huésped');
                    }
                }
            }
            
            // Get guest info
            $userModel = $this->model('User');
            $guest = $userModel->findById($guestId);
            $guestName = $guest['first_name'] . ' ' . $guest['last_name'];
            $guestEmail = $guest['email'];
            $guestPhone = $guest['phone'] ?? '';
            
            // Get birthday field
            $guestBirthday = !empty($_POST['guest_birthday']) ? sanitize($_POST['guest_birthday']) : null;
            
            // Create reservation based on type
            if ($type === 'room') {
                $checkIn = sanitize($_POST['check_in'] ?? '');
                $checkOut = sanitize($_POST['check_out'] ?? '');
                
                if (empty($checkIn) || empty($checkOut)) {
                    throw new Exception('Las fechas de check-in y check-out son requeridas');
                }
                
                if (empty($roomIds)) {
                    throw new Exception('Debe seleccionar al menos una habitación');
                }
                
                $discountCodeId = intval($_POST['discount_code_id'] ?? 0);
                $discountAmount = floatval($_POST['discount_amount'] ?? 0);
                $originalPrice = floatval($_POST['original_price'] ?? 0);
                
                $totalRoomsCreated = 0;
                $createdReservationIds = []; // Para guardar los IDs creados
                
                // Create a reservation for each selected room
                foreach ($roomIds as $roomId) {
                    // Get room price
                    $roomStmt = $this->db->prepare("SELECT price FROM rooms WHERE id = ? AND hotel_id = ?");
                    $roomStmt->execute([$roomId, $currentUser['hotel_id']]);
                    $room = $roomStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$room) {
                        continue; // Skip invalid rooms
                    }
                    
                    $roomPrice = floatval($room['price']);
                    
                    // For multiple rooms, discount is applied proportionally or only to first room
                    // Here we'll apply discount proportionally based on room price
                    $roomDiscountAmount = 0;
                    $roomOriginalPrice = $roomPrice;
                    
                    if ($discountCodeId > 0 && $originalPrice > 0) {
                        // Calculate proportional discount based on this room's price
                        $discountPercentage = $discountAmount / $originalPrice;
                        $roomDiscountAmount = $roomPrice * $discountPercentage;
                        $roomDiscountAmount = round($roomDiscountAmount, 2);
                    }
                    
                    $finalPrice = $roomPrice - $roomDiscountAmount;
                    if ($finalPrice < 0) {
                        $finalPrice = 0;
                    }
                    
                    // Insert room reservation
                    if ($discountCodeId > 0 && $roomDiscountAmount > 0) {
                        $stmt = $this->db->prepare("
                            INSERT INTO room_reservations 
                            (hotel_id, room_id, guest_id, guest_name, guest_email, guest_phone, guest_birthday, check_in, check_out, 
                             total_price, discount_code_id, discount_amount, original_price, status, notes)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $currentUser['hotel_id'],
                            $roomId,
                            $guestId,
                            $guestName,
                            $guestEmail,
                            $guestPhone,
                            $guestBirthday,
                            $checkIn,
                            $checkOut,
                            $finalPrice,
                            $discountCodeId,
                            $roomDiscountAmount,
                            $roomOriginalPrice,
                            $status,
                            $notes
                        ]);
                        
                        $reservationId = $this->db->lastInsertId();
                        
                        // Generar y guardar PIN de confirmación
                        $confirmationPin = generateConfirmationPin($checkIn, $reservationId);
                        $updateStmt = $this->db->prepare("UPDATE room_reservations SET confirmation_code = ? WHERE id = ?");
                        $updateStmt->execute([$confirmationPin, $reservationId]);
                        
                        // Record discount code usage
                        $usageStmt = $this->db->prepare("
                            INSERT INTO discount_code_usages 
                            (discount_code_id, reservation_id, reservation_type, discount_amount, original_price, final_price)
                            VALUES (?, ?, 'room', ?, ?, ?)
                        ");
                        $usageStmt->execute([
                            $discountCodeId,
                            $reservationId,
                            $roomDiscountAmount,
                            $roomOriginalPrice,
                            $finalPrice
                        ]);
                    } else {
                        // No discount code applied
                        $stmt = $this->db->prepare("
                            INSERT INTO room_reservations 
                            (hotel_id, room_id, guest_id, guest_name, guest_email, guest_phone, guest_birthday, check_in, check_out, total_price, status, notes)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $currentUser['hotel_id'],
                            $roomId,
                            $guestId,
                            $guestName,
                            $guestEmail,
                            $guestPhone,
                            $guestBirthday,
                            $checkIn,
                            $checkOut,
                            $roomPrice,
                            $status,
                            $notes
                        ]);
                        
                        $reservationId = $this->db->lastInsertId();
                        
                        // Generar y guardar PIN de confirmación
                        $confirmationPin = generateConfirmationPin($checkIn, $reservationId);
                        $updateStmt = $this->db->prepare("UPDATE room_reservations SET confirmation_code = ? WHERE id = ?");
                        $updateStmt->execute([$confirmationPin, $reservationId]);
                    }
                    
                    // Guardar el ID de la reservación creada
                    $createdReservationIds[] = $reservationId;
                    $totalRoomsCreated++;
                }
                
                // Update discount code times_used counter only once for all rooms
                if ($discountCodeId > 0 && $totalRoomsCreated > 0) {
                    $updateStmt = $this->db->prepare("
                        UPDATE discount_codes 
                        SET times_used = times_used + 1 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$discountCodeId]);
                }
            } elseif ($type === 'table') {
                $reservationDate = sanitize($_POST['reservation_date'] ?? '');
                $reservationTime = sanitize($_POST['reservation_time'] ?? '');
                $partySize = intval($_POST['party_size'] ?? 1);
                
                if (empty($reservationDate) || empty($reservationTime)) {
                    throw new Exception('La fecha y hora de reservación son requeridas');
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO table_reservations 
                    (hotel_id, table_id, guest_id, guest_name, guest_email, guest_phone, guest_birthday, reservation_date, reservation_time, party_size, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $currentUser['hotel_id'],
                    $resourceId,
                    $guestId,
                    $guestName,
                    $guestEmail,
                    $guestPhone,
                    $guestBirthday,
                    $reservationDate,
                    $reservationTime,
                    $partySize,
                    $status,
                    $notes
                ]);
                
                // Generar y guardar PIN de confirmación para mesa
                $tableReservationId = $this->db->lastInsertId();
                $confirmationPin = generateConfirmationPin($reservationDate, $tableReservationId);
                $updateStmt = $this->db->prepare("UPDATE table_reservations SET confirmation_code = ? WHERE id = ?");
                $updateStmt->execute([$confirmationPin, $tableReservationId]);
            } elseif ($type === 'amenity') {
                $reservationDate = sanitize($_POST['reservation_date'] ?? '');
                $reservationTime = sanitize($_POST['reservation_time'] ?? '');
                $partySize = intval($_POST['party_size'] ?? 1);
                
                if (empty($reservationDate) || empty($reservationTime)) {
                    throw new Exception('La fecha y hora de reservación son requeridas');
                }
                
                // Check amenity capacity and allow_overlap setting
                $amenityStmt = $this->db->prepare("SELECT capacity, allow_overlap FROM amenities WHERE id = ?");
                $amenityStmt->execute([$resourceId]);
                $amenity = $amenityStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($amenity) {
                    // Check if party size exceeds capacity
                    if ($amenity['capacity'] && $partySize > $amenity['capacity']) {
                        throw new Exception('El número de personas (' . $partySize . ') excede la capacidad de la amenidad (' . $amenity['capacity'] . ')');
                    }
                    
                    // If overlap is not allowed, check for existing reservations
                    if (!$amenity['allow_overlap']) {
                        $overlapStmt = $this->db->prepare("
                            SELECT COUNT(*) as count 
                            FROM amenity_reservations 
                            WHERE amenity_id = ? 
                            AND reservation_date = ? 
                            AND reservation_time = ?
                            AND status NOT IN ('cancelled', 'no_show')
                        ");
                        $overlapStmt->execute([$resourceId, $reservationDate, $reservationTime]);
                        $overlapCount = $overlapStmt->fetch(PDO::FETCH_ASSOC)['count'];
                        
                        if ($overlapCount > 0) {
                            throw new Exception('La amenidad ya tiene una reservación para esta fecha y hora');
                        }
                    }
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO amenity_reservations 
                    (hotel_id, amenity_id, user_id, guest_name, guest_email, guest_phone, guest_birthday, reservation_date, reservation_time, party_size, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $currentUser['hotel_id'],
                    $resourceId,
                    $guestId,
                    $guestName,
                    $guestEmail,
                    $guestPhone,
                    $guestBirthday,
                    $reservationDate,
                    $reservationTime,
                    $partySize,
                    $status,
                    $notes
                ]);
                
                // Generar y guardar PIN de confirmación para amenidad
                $amenityReservationId = $this->db->lastInsertId();
                $confirmationPin = generateConfirmationPin($reservationDate, $amenityReservationId);
                $updateStmt = $this->db->prepare("UPDATE amenity_reservations SET confirmation_code = ? WHERE id = ?");
                $updateStmt->execute([$confirmationPin, $amenityReservationId]);
            } else {
                throw new Exception('Tipo de reservación inválido');
            }
            
            $this->db->commit();
            
            // Enviar correo de confirmación según el tipo de reservación
            if ($type === 'room' && isset($createdReservationIds) && !empty($createdReservationIds)) {
                // Para habitaciones múltiples, enviar un correo por cada una
                foreach ($createdReservationIds as $reservationId) {
                    $this->sendReservationEmail($type, $reservationId, $guestEmail, $guestName);
                }
            } elseif ($type === 'table' && isset($tableReservationId)) {
                // Para mesas
                $this->sendReservationEmail($type, $tableReservationId, $guestEmail, $guestName);
            } elseif ($type === 'amenity' && isset($amenityReservationId)) {
                // Para amenidades
                $this->sendReservationEmail($type, $amenityReservationId, $guestEmail, $guestName);
            }
            
            // Custom success message for multiple rooms
            if ($type === 'room' && isset($totalRoomsCreated) && $totalRoomsCreated > 1) {
                flash('success', "Se crearon exitosamente {$totalRoomsCreated} reservaciones de habitaciones", 'success');
            } else {
                flash('success', 'Reservación creada exitosamente', 'success');
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error al crear la reservación: ' . $e->getMessage(), 'danger');
        }
        
        redirect('reservations');
    }
    
    /**
     * Aceptar/Confirmar una reservación
     */
    public function accept($id) {
        if (!hasRole(['admin', 'manager', 'hostess'])) {
            flash('error', 'No tienes permiso para realizar esta acción', 'danger');
            redirect('reservations');
        }
        
        $type = sanitize($_GET['type'] ?? 'room');
        $table = $type === 'room' ? 'room_reservations' : ($type === 'table' ? 'table_reservations' : 'amenity_reservations');
        
        try {
            // Obtener datos de la reservación antes de confirmar
            $reservationData = $this->getReservationDetails($type, $id);
            
            if ($reservationData) {
                // Actualizar estado a confirmado
                $stmt = $this->db->prepare("UPDATE {$table} SET status = 'confirmed' WHERE id = ?");
                $stmt->execute([$id]);
                
                // Enviar correo de confirmación con PIN
                $this->sendConfirmationEmail($type, $id, $reservationData['guest_email'], $reservationData['guest_name']);
                
                flash('success', 'Reservación confirmada exitosamente y correo enviado al huésped', 'success');
            } else {
                flash('error', 'No se pudo obtener la información de la reservación', 'danger');
            }
        } catch (Exception $e) {
            flash('error', 'Error al confirmar la reservación: ' . $e->getMessage(), 'danger');
        }
        
        redirect('reservations');
    }
    
    /**
     * Mostrar formulario de edición
     */
    public function edit($id) {
        if (!hasRole(['admin', 'manager', 'hostess'])) {
            flash('error', 'No tienes permiso para acceder a esta página', 'danger');
            redirect('reservations');
        }
        
        $type = sanitize($_GET['type'] ?? 'room');
        
        if ($type === 'room') {
            $stmt = $this->db->prepare("
                SELECT rr.*, r.room_number, r.hotel_id, r.type as room_type
                FROM room_reservations rr
                JOIN rooms r ON rr.room_id = r.id
                WHERE rr.id = ?
            ");
        } else {
            $stmt = $this->db->prepare("
                SELECT tr.*, rt.table_number, rt.hotel_id
                FROM table_reservations tr
                JOIN restaurant_tables rt ON tr.table_id = rt.id
                WHERE tr.id = ?
            ");
        }
        
        $stmt->execute([$id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reservation) {
            flash('error', 'Reservación no encontrada', 'danger');
            redirect('reservations');
        }
        
        $currentUser = currentUser();
        if ($reservation['hotel_id'] != $currentUser['hotel_id']) {
            flash('error', 'No tienes permiso para editar esta reservación', 'danger');
            redirect('reservations');
        }
        
        $this->view('reservations/edit', [
            'title' => 'Editar Reservación',
            'reservation' => $reservation,
            'type' => $type
        ]);
    }
    
    /**
     * Actualizar reservación
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('reservations');
        }
        
        if (!hasRole(['admin', 'manager', 'hostess'])) {
            flash('error', 'No tienes permiso para realizar esta acción', 'danger');
            redirect('reservations');
        }
        
        $type = sanitize($_POST['type'] ?? 'room');
        $table = $type === 'room' ? 'room_reservations' : 'table_reservations';
        
        try {
            $guestBirthday = !empty($_POST['guest_birthday']) ? sanitize($_POST['guest_birthday']) : null;
            
            if ($type === 'room') {
                $stmt = $this->db->prepare("
                    UPDATE room_reservations 
                    SET status = ?, 
                        guest_name = ?,
                        guest_email = ?,
                        guest_phone = ?,
                        guest_birthday = ?,
                        notes = ?,
                        check_in = ?,
                        check_out = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    sanitize($_POST['status']),
                    sanitize($_POST['guest_name']),
                    sanitize($_POST['guest_email']),
                    sanitize($_POST['guest_phone']),
                    $guestBirthday,
                    sanitize($_POST['notes']),
                    sanitize($_POST['check_in']),
                    sanitize($_POST['check_out']),
                    $id
                ]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE table_reservations 
                    SET status = ?, 
                        guest_name = ?,
                        guest_email = ?,
                        guest_phone = ?,
                        guest_birthday = ?,
                        notes = ?,
                        reservation_date = ?,
                        reservation_time = ?,
                        party_size = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    sanitize($_POST['status']),
                    sanitize($_POST['guest_name']),
                    sanitize($_POST['guest_email']),
                    sanitize($_POST['guest_phone']),
                    $guestBirthday,
                    sanitize($_POST['notes']),
                    sanitize($_POST['reservation_date']),
                    sanitize($_POST['reservation_time']),
                    sanitize($_POST['party_size']),
                    $id
                ]);
            }
            
            flash('success', 'Reservación actualizada exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al actualizar la reservación: ' . $e->getMessage(), 'danger');
        }
        
        redirect('reservations');
    }
    
    /**
     * Eliminar reservación
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('reservations');
        }
        
        if (!hasRole(['admin', 'manager'])) {
            flash('error', 'No tienes permiso para realizar esta acción', 'danger');
            redirect('reservations');
        }
        
        $type = sanitize($_POST['type'] ?? 'room');
        $table = $type === 'room' ? 'room_reservations' : ($type === 'table' ? 'table_reservations' : 'amenity_reservations');
        
        try {
            // En lugar de eliminar, cambiar estado a cancelled
            $stmt = $this->db->prepare("UPDATE {$table} SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$id]);
            
            flash('success', 'Reservación cancelada exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al cancelar la reservación: ' . $e->getMessage(), 'danger');
        }
        
        redirect('reservations');
    }
    
    /**
     * Enviar correo de confirmación de reservación
     */
    private function sendReservationEmail($type, $reservationId, $guestEmail, $guestName, $includePin = false) {
        try {
            // Cargar helper de logging
            require_once APP_PATH . '/helpers/email_logger.php';
            
            // Log para debug
            logEmail("=== INICIO envío de correo ===");
            logEmail("Type: $type, ID: $reservationId, Email: $guestEmail, Name: $guestName");
            logEmail("Include PIN: " . ($includePin ? 'YES' : 'NO'));
            
            // Cargar vendor autoload para PHPMailer
            if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
                require_once ROOT_PATH . '/vendor/autoload.php';
                logEmail("PHPMailer autoload cargado correctamente");
            } else {
                logEmail("ERROR: PHPMailer no está instalado. No se puede enviar correo.");
                return false;
            }
            
            // Cargar el servicio de email
            require_once APP_PATH . '/services/EmailService.php';
            
            // Obtener los detalles de la reservación
            logEmail("Obteniendo detalles de la reservación...");
            $reservationData = $this->getReservationDetails($type, $reservationId);
            
            if (!$reservationData) {
                logEmail("ERROR: No se encontraron detalles de la reservación ID: $reservationId");
                return false;
            }
            
            logEmail("Detalles obtenidos: " . json_encode($reservationData));
            
            // Agregar información adicional
            $reservationData['type'] = $type;
            $reservationData['reservation_id'] = $reservationId;
            $reservationData['guest_email'] = $guestEmail;
            $reservationData['guest_name'] = $guestName;
            $reservationData['include_pin'] = $includePin;
            
            // Obtener hotel_id del usuario actual
            $currentUser = currentUser();
            $hotelId = $currentUser['hotel_id'] ?? null;
            
            // Enviar correo
            logEmail("Inicializando EmailService con hotel_id: " . ($hotelId ?? 'NULL'));
            $emailService = new EmailService($hotelId);
            
            logEmail("Enviando correo de confirmación...");
            $result = $emailService->sendReservationConfirmation($reservationData);
            
            if ($result) {
                logEmail("✅ Correo de confirmación enviado exitosamente para reservación #$reservationId");
            } else {
                logEmail("❌ No se pudo enviar el correo de confirmación para reservación #$reservationId");
            }
            
            logEmail("=== FIN envío de correo ===");
            return $result;
            
        } catch (Exception $e) {
            logEmail("❌ EXCEPCIÓN al enviar correo de confirmación: " . $e->getMessage());
            logEmail("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Enviar correo de confirmación CON PIN (cuando se confirma la reservación)
     */
    private function sendConfirmationEmail($type, $reservationId, $guestEmail, $guestName) {
        // Llamar al método sendReservationEmail con includePin = true
        return $this->sendReservationEmail($type, $reservationId, $guestEmail, $guestName, true);
    }
    
    /**
     * Obtener detalles de la reservación según el tipo
     */
    private function getReservationDetails($type, $reservationId) {
        try {
            if ($type === 'room') {
                $stmt = $this->db->prepare("
                    SELECT 
                        rr.*,
                        r.room_number,
                        rr.check_in as check_in,
                        rr.check_out as check_out,
                        rr.total_price
                    FROM room_reservations rr
                    JOIN rooms r ON rr.room_id = r.id
                    WHERE rr.id = ?
                ");
                $stmt->execute([$reservationId]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } elseif ($type === 'table') {
                $stmt = $this->db->prepare("
                    SELECT 
                        tr.*,
                        rt.table_number,
                        tr.reservation_date,
                        tr.reservation_time,
                        tr.party_size
                    FROM table_reservations tr
                    JOIN restaurant_tables rt ON tr.table_id = rt.id
                    WHERE tr.id = ?
                ");
                $stmt->execute([$reservationId]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } elseif ($type === 'amenity') {
                $stmt = $this->db->prepare("
                    SELECT 
                        ar.*,
                        a.name as amenity_name,
                        ar.reservation_date,
                        ar.reservation_time,
                        ar.party_size
                    FROM amenity_reservations ar
                    JOIN amenities a ON ar.amenity_id = a.id
                    WHERE ar.id = ?
                ");
                $stmt->execute([$reservationId]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } else {
                return null;
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Error al obtener detalles de reservación: " . $e->getMessage());
            return null;
        }
    }
}
