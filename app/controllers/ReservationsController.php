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
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (guest_name LIKE ? OR guest_email LIKE ? OR resource_number LIKE ?)";
            $searchParam = '%' . $filters['search'] . '%';
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
            
            // Create reservation based on type
            if ($type === 'room') {
                $checkIn = sanitize($_POST['check_in'] ?? '');
                $checkOut = sanitize($_POST['check_out'] ?? '');
                
                if (empty($checkIn) || empty($checkOut)) {
                    throw new Exception('Las fechas de check-in y check-out son requeridas');
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO room_reservations 
                    (hotel_id, room_id, guest_id, guest_name, guest_email, guest_phone, check_in, check_out, total_price, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)
                ");
                $stmt->execute([
                    $currentUser['hotel_id'],
                    $resourceId,
                    $guestId,
                    $guestName,
                    $guestEmail,
                    $guestPhone,
                    $checkIn,
                    $checkOut,
                    $status,
                    $notes
                ]);
            } elseif ($type === 'table') {
                $reservationDate = sanitize($_POST['reservation_date'] ?? '');
                $reservationTime = sanitize($_POST['reservation_time'] ?? '');
                $partySize = intval($_POST['party_size'] ?? 1);
                
                if (empty($reservationDate) || empty($reservationTime)) {
                    throw new Exception('La fecha y hora de reservación son requeridas');
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO table_reservations 
                    (hotel_id, table_id, guest_id, guest_name, guest_email, guest_phone, reservation_date, reservation_time, party_size, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $currentUser['hotel_id'],
                    $resourceId,
                    $guestId,
                    $guestName,
                    $guestEmail,
                    $guestPhone,
                    $reservationDate,
                    $reservationTime,
                    $partySize,
                    $status,
                    $notes
                ]);
            } elseif ($type === 'amenity') {
                $reservationDate = sanitize($_POST['reservation_date'] ?? '');
                $reservationTime = sanitize($_POST['reservation_time'] ?? '');
                
                if (empty($reservationDate) || empty($reservationTime)) {
                    throw new Exception('La fecha y hora de reservación son requeridas');
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO amenity_reservations 
                    (hotel_id, amenity_id, user_id, guest_name, guest_email, guest_phone, reservation_date, reservation_time, status, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $currentUser['hotel_id'],
                    $resourceId,
                    $guestId,
                    $guestName,
                    $guestEmail,
                    $guestPhone,
                    $reservationDate,
                    $reservationTime,
                    $status,
                    $notes
                ]);
            } else {
                throw new Exception('Tipo de reservación inválido');
            }
            
            $this->db->commit();
            flash('success', 'Reservación creada exitosamente', 'success');
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
            $stmt = $this->db->prepare("UPDATE {$table} SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$id]);
            
            // For tables and amenities, the trigger will automatically create a 2-hour block
            flash('success', 'Reservación confirmada exitosamente', 'success');
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
            if ($type === 'room') {
                $stmt = $this->db->prepare("
                    UPDATE room_reservations 
                    SET status = ?, 
                        guest_name = ?,
                        guest_email = ?,
                        guest_phone = ?,
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
}
