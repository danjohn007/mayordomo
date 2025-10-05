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
}
