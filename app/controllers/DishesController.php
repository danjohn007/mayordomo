<?php
require_once APP_PATH . '/controllers/BaseController.php';

class DishesController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'manager', 'hostess']);
    }
    
    public function index() {
        $user = currentUser();
        $filters = [
            'hotel_id' => $user['hotel_id'],
            'category' => $_GET['category'] ?? ''
        ];
        
        $model = $this->model('Dish');
        $dishes = $model->getAll($filters);
        
        $this->view('dishes/index', [
            'title' => 'Gestión de Menú',
            'dishes' => $dishes,
            'filters' => $filters
        ]);
    }
    
    public function create() {
        $this->requireRole(['admin', 'manager']);
        $this->view('dishes/create', ['title' => 'Nuevo Platillo']);
    }
    
    public function store() {
        $this->requireRole(['admin', 'manager']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dishes');
        
        $user = currentUser();
        $data = [
            'hotel_id' => $user['hotel_id'],
            'name' => sanitize($_POST['name'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'description' => sanitize($_POST['description'] ?? ''),
            'service_time' => sanitize($_POST['service_time'] ?? 'all_day'),
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];
        
        $model = $this->model('Dish');
        if ($model->create($data)) {
            flash('success', 'Platillo creado exitosamente', 'success');
        } else {
            flash('error', 'Error al crear el platillo', 'danger');
        }
        redirect('dishes');
    }
    
    public function edit($id) {
        $this->requireRole(['admin', 'manager']);
        $model = $this->model('Dish');
        $dish = $model->findById($id);
        
        if (!$dish) {
            flash('error', 'Platillo no encontrado', 'danger');
            redirect('dishes');
        }
        
        $this->view('dishes/edit', ['title' => 'Editar Platillo', 'dish' => $dish]);
    }
    
    public function update($id) {
        $this->requireRole(['admin', 'manager']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dishes');
        
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'category' => sanitize($_POST['category'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'description' => sanitize($_POST['description'] ?? ''),
            'service_time' => sanitize($_POST['service_time'] ?? 'all_day'),
            'is_available' => isset($_POST['is_available']) ? 1 : 0
        ];
        
        $model = $this->model('Dish');
        if ($model->update($id, $data)) {
            flash('success', 'Platillo actualizado exitosamente', 'success');
        } else {
            flash('error', 'Error al actualizar el platillo', 'danger');
        }
        redirect('dishes');
    }
    
    public function delete($id) {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dishes');
        
        $model = $this->model('Dish');
        if ($model->delete($id)) {
            flash('success', 'Platillo eliminado exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar el platillo', 'danger');
        }
        redirect('dishes');
    }
}
