<?php
require_once APP_PATH . '/controllers/BaseController.php';

class BlocksController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['hostess', 'admin', 'manager']);
    }
    
    public function index() {
        $filters = ['status' => $_GET['status'] ?? 'active'];
        
        $model = $this->model('ResourceBlock');
        $blocks = $model->getAll($filters);
        
        $this->view('blocks/index', [
            'title' => 'Sistema de Bloqueos',
            'blocks' => $blocks,
            'filters' => $filters
        ]);
    }
    
    public function create() {
        $user = currentUser();
        
        // Get resources for blocking
        $roomModel = $this->model('Room');
        $rooms = $roomModel->getAll(['hotel_id' => $user['hotel_id']]);
        
        $tableModel = $this->model('RestaurantTable');
        $tables = $tableModel->getAll(['hotel_id' => $user['hotel_id']]);
        
        $this->view('blocks/create', [
            'title' => 'Nuevo Bloqueo',
            'rooms' => $rooms,
            'tables' => $tables
        ]);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('blocks');
        
        $user = currentUser();
        $data = [
            'resource_type' => sanitize($_POST['resource_type'] ?? ''),
            'resource_id' => intval($_POST['resource_id'] ?? 0),
            'blocked_by' => $user['id'],
            'reason' => sanitize($_POST['reason'] ?? ''),
            'start_date' => sanitize($_POST['start_date'] ?? ''),
            'end_date' => sanitize($_POST['end_date'] ?? '')
        ];
        
        $model = $this->model('ResourceBlock');
        if ($model->create($data)) {
            flash('success', 'Bloqueo creado exitosamente', 'success');
        } else {
            flash('error', 'Error al crear el bloqueo', 'danger');
        }
        redirect('blocks');
    }
    
    public function release($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('blocks');
        
        $model = $this->model('ResourceBlock');
        if ($model->release($id)) {
            flash('success', 'Bloqueo liberado exitosamente', 'success');
        } else {
            flash('error', 'Error al liberar el bloqueo', 'danger');
        }
        redirect('blocks');
    }
}
