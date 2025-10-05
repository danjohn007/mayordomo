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
            $roomId = $this->db->lastInsertId();
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = PUBLIC_PATH . '/uploads/rooms/';
                $imageModel = $this->model('ResourceImage');
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExt, $allowedExts)) {
                            $newFileName = 'room_' . $roomId . '_' . uniqid() . '.' . $fileExt;
                            $uploadPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $uploadPath)) {
                                $imageModel->create([
                                    'resource_type' => 'room',
                                    'resource_id' => $roomId,
                                    'image_path' => 'uploads/rooms/' . $newFileName,
                                    'display_order' => $key,
                                    'is_primary' => ($key === 0) ? 1 : 0
                                ]);
                            }
                        }
                    }
                }
            }
            
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
            // Handle new image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = PUBLIC_PATH . '/uploads/rooms/';
                $imageModel = $this->model('ResourceImage');
                
                // Get current image count
                $existingImages = $imageModel->getByResource('room', $id);
                $startOrder = count($existingImages);
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExt, $allowedExts)) {
                            $newFileName = 'room_' . $id . '_' . uniqid() . '.' . $fileExt;
                            $uploadPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $uploadPath)) {
                                $imageModel->create([
                                    'resource_type' => 'room',
                                    'resource_id' => $id,
                                    'image_path' => 'uploads/rooms/' . $newFileName,
                                    'display_order' => $startOrder + $key,
                                    'is_primary' => (empty($existingImages) && $key === 0) ? 1 : 0
                                ]);
                            }
                        }
                    }
                }
            }
            
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
    
    /**
     * Delete room image
     */
    public function deleteImage($imageId) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('rooms');
        }
        
        $imageModel = $this->model('ResourceImage');
        if ($imageModel->delete($imageId)) {
            flash('success', 'Imagen eliminada exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar la imagen', 'danger');
        }
        
        // Redirect back to edit page
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/rooms';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Set primary image for room
     */
    public function setPrimaryImage($imageId) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('rooms');
        }
        
        // Get image to determine resource
        $imageModel = $this->model('ResourceImage');
        $stmt = $this->db->prepare("SELECT resource_type, resource_id FROM resource_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();
        
        if ($image && $image['resource_type'] === 'room') {
            if ($imageModel->setPrimary($imageId, 'room', $image['resource_id'])) {
                flash('success', 'Imagen principal actualizada exitosamente', 'success');
            } else {
                flash('error', 'Error al actualizar la imagen principal', 'danger');
            }
        } else {
            flash('error', 'Imagen no encontrada', 'danger');
        }
        
        // Redirect back to edit page
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/rooms';
        header('Location: ' . $referer);
        exit;
    }
}
