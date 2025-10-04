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
            'status' => $_GET['status'] ?? ''
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
}
