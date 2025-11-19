<?php
require_once APP_PATH . '/controllers/BaseController.php';

class TablesController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'manager', 'hostess']);
    }
    
    public function index() {
        $user = currentUser();
        $filters = [
            'hotel_id' => $user['hotel_id'],
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
            'location' => $_GET['location'] ?? ''
        ];
        
        $model = $this->model('RestaurantTable');
        $tables = $model->getAll($filters);
        
        $this->view('tables/index', [
            'title' => 'Gestión de Mesas',
            'tables' => $tables,
            'filters' => $filters
        ]);
    }
    
    public function create() {
        $this->requireRole(['admin', 'manager']);
        $this->view('tables/create', ['title' => 'Nueva Mesa']);
    }
    
    public function store() {
        $this->requireRole(['admin', 'manager']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('tables');
        
        $user = currentUser();
        $data = [
            'hotel_id' => $user['hotel_id'],
            'table_number' => sanitize($_POST['table_number'] ?? ''),
            'capacity' => intval($_POST['capacity'] ?? 2),
            'location' => sanitize($_POST['location'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'available'),
            'description' => sanitize($_POST['description'] ?? '')
        ];
        
        $model = $this->model('RestaurantTable');
        if ($model->tableNumberExists($data['hotel_id'], $data['table_number'])) {
            flash('error', 'El número de mesa ya existe', 'danger');
            redirect('tables/create');
        }
        
        if ($model->create($data)) {
            $tableId = $this->db->lastInsertId();
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = PUBLIC_PATH . '/uploads/tables/';
                $imageModel = $this->model('ResourceImage');
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExt, $allowedExts)) {
                            $newFileName = 'table_' . $tableId . '_' . uniqid() . '.' . $fileExt;
                            $uploadPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $uploadPath)) {
                                $imageModel->create([
                                    'resource_type' => 'table',
                                    'resource_id' => $tableId,
                                    'image_path' => 'uploads/tables/' . $newFileName,
                                    'display_order' => $key,
                                    'is_primary' => ($key === 0) ? 1 : 0
                                ]);
                            }
                        }
                    }
                }
            }
            
            flash('success', 'Mesa creada exitosamente', 'success');
        } else {
            flash('error', 'Error al crear la mesa', 'danger');
        }
        redirect('tables');
    }
    
    public function edit($id) {
        $this->requireRole(['admin', 'manager']);
        $model = $this->model('RestaurantTable');
        $table = $model->findById($id);
        
        if (!$table) {
            flash('error', 'Mesa no encontrada', 'danger');
            redirect('tables');
        }
        
        $this->view('tables/edit', ['title' => 'Editar Mesa', 'table' => $table]);
    }
    
    public function update($id) {
        $this->requireRole(['admin', 'manager']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('tables');
        
        $data = [
            'table_number' => sanitize($_POST['table_number'] ?? ''),
            'capacity' => intval($_POST['capacity'] ?? 2),
            'location' => sanitize($_POST['location'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'available'),
            'description' => sanitize($_POST['description'] ?? '')
        ];
        
        $model = $this->model('RestaurantTable');
        if ($model->update($id, $data)) {
            // Handle new image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = PUBLIC_PATH . '/uploads/tables/';
                $imageModel = $this->model('ResourceImage');
                
                // Get current image count
                $existingImages = $imageModel->getByResource('table', $id);
                $startOrder = count($existingImages);
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExt, $allowedExts)) {
                            $newFileName = 'table_' . $id . '_' . uniqid() . '.' . $fileExt;
                            $uploadPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $uploadPath)) {
                                $imageModel->create([
                                    'resource_type' => 'table',
                                    'resource_id' => $id,
                                    'image_path' => 'uploads/tables/' . $newFileName,
                                    'display_order' => $startOrder + $key,
                                    'is_primary' => (empty($existingImages) && $key === 0) ? 1 : 0
                                ]);
                            }
                        }
                    }
                }
            }
            
            flash('success', 'Mesa actualizada exitosamente', 'success');
        } else {
            flash('error', 'Error al actualizar la mesa', 'danger');
        }
        redirect('tables');
    }
    
    public function delete($id) {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('tables');
        
        $model = $this->model('RestaurantTable');
        if ($model->delete($id)) {
            flash('success', 'Mesa eliminada exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar la mesa', 'danger');
        }
        redirect('tables');
    }
    
    /**
     * Delete table image
     */
    public function deleteImage($imageId) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tables');
        }
        
        $imageModel = $this->model('ResourceImage');
        if ($imageModel->delete($imageId)) {
            flash('success', 'Imagen eliminada exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar la imagen', 'danger');
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/tables';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Set primary image for table
     */
    public function setPrimaryImage($imageId) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tables');
        }
        
        $imageModel = $this->model('ResourceImage');
        $stmt = $this->db->prepare("SELECT resource_type, resource_id FROM resource_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();
        
        if ($image && $image['resource_type'] === 'table') {
            if ($imageModel->setPrimary($imageId, 'table', $image['resource_id'])) {
                flash('success', 'Imagen principal actualizada exitosamente', 'success');
            } else {
                flash('error', 'Error al actualizar la imagen principal', 'danger');
            }
        } else {
            flash('error', 'Imagen no encontrada', 'danger');
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/tables';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Toggle table suspension (blocked status)
     */
    public function toggleSuspend($id) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tables');
        }
        
        $model = $this->model('RestaurantTable');
        $table = $model->findById($id);
        
        if (!$table) {
            flash('error', 'Mesa no encontrada', 'danger');
            redirect('tables');
        }
        
        // Toggle between blocked and available
        $newStatus = ($table['status'] === 'blocked') ? 'available' : 'blocked';
        
        $data = [
            'table_number' => $table['table_number'],
            'capacity' => $table['capacity'],
            'location' => $table['location'],
            'status' => $newStatus,
            'description' => $table['description']
        ];
        
        if ($model->update($id, $data)) {
            $message = ($newStatus === 'blocked') 
                ? 'Mesa suspendida exitosamente' 
                : 'Mesa reactivada exitosamente';
            flash('success', $message, 'success');
        } else {
            flash('error', 'Error al cambiar el estado de la mesa', 'danger');
        }
        
        redirect('tables');
    }
}
