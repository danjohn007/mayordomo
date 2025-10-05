<?php
require_once APP_PATH . '/controllers/BaseController.php';

class AmenitiesController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'manager', 'hostess']);
    }
    
    public function index() {
        $user = currentUser();
        $filters = [
            'hotel_id' => $user['hotel_id'],
            'category' => $_GET['category'] ?? '',
            'search' => $_GET['search'] ?? '',
            'is_active' => $_GET['is_active'] ?? ''
        ];
        
        $model = $this->model('Amenity');
        $amenities = $model->getAll($filters);
        
        $this->view('amenities/index', [
            'title' => 'Gestión de Amenidades',
            'amenities' => $amenities,
            'filters' => $filters
        ]);
    }
    
    public function create() {
        $this->requireRole(['admin', 'manager']);
        $this->view('amenities/create', ['title' => 'Nueva Amenidad']);
    }
    
    public function store() {
        $this->requireRole(['admin', 'manager']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('amenities');
        
        $user = currentUser();
        $data = [
            'hotel_id' => $user['hotel_id'],
            'name' => sanitize($_POST['name'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'capacity' => intval($_POST['capacity'] ?? 0) ?: null,
            'opening_time' => sanitize($_POST['opening_time'] ?? '') ?: null,
            'closing_time' => sanitize($_POST['closing_time'] ?? '') ?: null,
            'description' => sanitize($_POST['description'] ?? ''),
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];
        
        $model = $this->model('Amenity');
        if ($model->create($data)) {
            $amenityId = $this->db->lastInsertId();
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = PUBLIC_PATH . '/uploads/amenities/';
                $imageModel = $this->model('ResourceImage');
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExt, $allowedExts)) {
                            $newFileName = 'amenity_' . $amenityId . '_' . uniqid() . '.' . $fileExt;
                            $uploadPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $uploadPath)) {
                                $imageModel->create([
                                    'resource_type' => 'amenity',
                                    'resource_id' => $amenityId,
                                    'image_path' => 'uploads/amenities/' . $newFileName,
                                    'display_order' => $key,
                                    'is_primary' => ($key === 0) ? 1 : 0
                                ]);
                            }
                        }
                    }
                }
            }
            
            flash('success', 'Amenidad creada exitosamente', 'success');
        } else {
            flash('error', 'Error al crear la amenidad', 'danger');
        }
        redirect('amenities');
    }
    
    public function edit($id) {
        $this->requireRole(['admin', 'manager']);
        $model = $this->model('Amenity');
        $amenity = $model->findById($id);
        
        if (!$amenity) {
            flash('error', 'Amenidad no encontrada', 'danger');
            redirect('amenities');
        }
        
        $this->view('amenities/edit', ['title' => 'Editar Amenidad', 'amenity' => $amenity]);
    }
    
    public function update($id) {
        $this->requireRole(['admin', 'manager']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('amenities');
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'capacity' => intval($_POST['capacity'] ?? 0) ?: null,
            'opening_time' => sanitize($_POST['opening_time'] ?? '') ?: null,
            'closing_time' => sanitize($_POST['closing_time'] ?? '') ?: null,
            'description' => sanitize($_POST['description'] ?? ''),
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];
        
        $model = $this->model('Amenity');
        if ($model->update($id, $data)) {
            // Handle new image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $uploadDir = PUBLIC_PATH . '/uploads/amenities/';
                $imageModel = $this->model('ResourceImage');
                
                // Get current image count
                $existingImages = $imageModel->getByResource('amenity', $id);
                $startOrder = count($existingImages);
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($fileExt, $allowedExts)) {
                            $newFileName = 'amenity_' . $id . '_' . uniqid() . '.' . $fileExt;
                            $uploadPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $uploadPath)) {
                                $imageModel->create([
                                    'resource_type' => 'amenity',
                                    'resource_id' => $id,
                                    'image_path' => 'uploads/amenities/' . $newFileName,
                                    'display_order' => $startOrder + $key,
                                    'is_primary' => (empty($existingImages) && $key === 0) ? 1 : 0
                                ]);
                            }
                        }
                    }
                }
            }
            
            flash('success', 'Amenidad actualizada exitosamente', 'success');
        } else {
            flash('error', 'Error al actualizar la amenidad', 'danger');
        }
        redirect('amenities');
    }
    
    public function delete($id) {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('amenities');
        
        $model = $this->model('Amenity');
        if ($model->delete($id)) {
            flash('success', 'Amenidad eliminada exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar la amenidad', 'danger');
        }
        redirect('amenities');
    }
    
    /**
     * Delete amenity image
     */
    public function deleteImage($imageId) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('amenities');
        }
        
        $imageModel = $this->model('ResourceImage');
        if ($imageModel->delete($imageId)) {
            flash('success', 'Imagen eliminada exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar la imagen', 'danger');
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/amenities';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Set primary image for amenity
     */
    public function setPrimaryImage($imageId) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('amenities');
        }
        
        $imageModel = $this->model('ResourceImage');
        $stmt = $this->db->prepare("SELECT resource_type, resource_id FROM resource_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();
        
        if ($image && $image['resource_type'] === 'amenity') {
            if ($imageModel->setPrimary($imageId, 'amenity', $image['resource_id'])) {
                flash('success', 'Imagen principal actualizada exitosamente', 'success');
            } else {
                flash('error', 'Error al actualizar la imagen principal', 'danger');
            }
        } else {
            flash('error', 'Imagen no encontrada', 'danger');
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/amenities';
        header('Location: ' . $referer);
        exit;
    }
}
