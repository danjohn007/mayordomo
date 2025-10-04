<?php
/**
 * Rooms Controller
 */

require_once APP_PATH . '/controllers/BaseController.php';

class RoomsController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'manager', 'hostess']);
    }
    
    /**
     * List all rooms
     */
    public function index() {
        $user = currentUser();
        
        // Get filters
        $filters = [
            'hotel_id' => $user['hotel_id'],
            'status' => $_GET['status'] ?? '',
            'type' => $_GET['type'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $roomModel = $this->model('Room');
        $rooms = $roomModel->getAll($filters);
        
        $this->view('rooms/index', [
            'title' => 'Gestión de Habitaciones',
            'rooms' => $rooms,
            'filters' => $filters
        ]);
    }
    
    /**
     * Show create form
     */
    public function create() {
        $this->requireRole(['admin', 'manager']);
        
        $this->view('rooms/create', [
            'title' => 'Nueva Habitación'
        ]);
    }
    
    /**
     * Store new room
     */
    public function store() {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('rooms');
        }
        
        $user = currentUser();
        
        $data = [
            'hotel_id' => $user['hotel_id'],
            'room_number' => sanitize($_POST['room_number'] ?? ''),
            'type' => sanitize($_POST['type'] ?? ''),
            'capacity' => intval($_POST['capacity'] ?? 1),
            'price' => floatval($_POST['price'] ?? 0),
            'status' => sanitize($_POST['status'] ?? 'available'),
            'floor' => intval($_POST['floor'] ?? 0) ?: null,
            'description' => sanitize($_POST['description'] ?? ''),
            'amenities' => sanitize($_POST['amenities'] ?? '')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['room_number'])) {
            $errors[] = 'El número de habitación es requerido';
        }
        
        if (empty($data['type'])) {
            $errors[] = 'El tipo de habitación es requerido';
        }
        
        if ($data['capacity'] < 1) {
            $errors[] = 'La capacidad debe ser al menos 1';
        }
        
        if ($data['price'] < 0) {
            $errors[] = 'El precio no puede ser negativo';
        }
        
        // Check if room number exists
        $roomModel = $this->model('Room');
        if ($roomModel->roomNumberExists($data['hotel_id'], $data['room_number'])) {
            $errors[] = 'El número de habitación ya existe';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('rooms/create');
        }
        
        // Create room
        if ($roomModel->create($data)) {
            flash('success', 'Habitación creada exitosamente', 'success');
            redirect('rooms');
        } else {
            flash('error', 'Error al crear la habitación', 'danger');
            redirect('rooms/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id) {
        $this->requireRole(['admin', 'manager']);
        
        $roomModel = $this->model('Room');
        $room = $roomModel->findById($id);
        
        if (!$room) {
            flash('error', 'Habitación no encontrada', 'danger');
            redirect('rooms');
        }
        
        $this->view('rooms/edit', [
            'title' => 'Editar Habitación',
            'room' => $room
        ]);
    }
    
    /**
     * Update room
     */
    public function update($id) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('rooms');
        }
        
        $roomModel = $this->model('Room');
        $room = $roomModel->findById($id);
        
        if (!$room) {
            flash('error', 'Habitación no encontrada', 'danger');
            redirect('rooms');
        }
        
        $user = currentUser();
        
        $data = [
            'room_number' => sanitize($_POST['room_number'] ?? ''),
            'type' => sanitize($_POST['type'] ?? ''),
            'capacity' => intval($_POST['capacity'] ?? 1),
            'price' => floatval($_POST['price'] ?? 0),
            'status' => sanitize($_POST['status'] ?? 'available'),
            'floor' => intval($_POST['floor'] ?? 0) ?: null,
            'description' => sanitize($_POST['description'] ?? ''),
            'amenities' => sanitize($_POST['amenities'] ?? '')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['room_number'])) {
            $errors[] = 'El número de habitación es requerido';
        }
        
        if (empty($data['type'])) {
            $errors[] = 'El tipo de habitación es requerido';
        }
        
        if ($data['capacity'] < 1) {
            $errors[] = 'La capacidad debe ser al menos 1';
        }
        
        if ($data['price'] < 0) {
            $errors[] = 'El precio no puede ser negativo';
        }
        
        // Check if room number exists (excluding current room)
        if ($roomModel->roomNumberExists($user['hotel_id'], $data['room_number'], $id)) {
            $errors[] = 'El número de habitación ya existe';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('rooms/edit/' . $id);
        }
        
        // Update room
        if ($roomModel->update($id, $data)) {
            flash('success', 'Habitación actualizada exitosamente', 'success');
            redirect('rooms');
        } else {
            flash('error', 'Error al actualizar la habitación', 'danger');
            redirect('rooms/edit/' . $id);
        }
    }
    
    /**
     * Delete room
     */
    public function delete($id) {
        $this->requireRole(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('rooms');
        }
        
        $roomModel = $this->model('Room');
        $room = $roomModel->findById($id);
        
        if (!$room) {
            flash('error', 'Habitación no encontrada', 'danger');
            redirect('rooms');
        }
        
        if ($roomModel->delete($id)) {
            flash('success', 'Habitación eliminada exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar la habitación', 'danger');
        }
        
        redirect('rooms');
    }
}
