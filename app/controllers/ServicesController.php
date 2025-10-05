<?php
require_once APP_PATH . '/controllers/BaseController.php';

class ServicesController extends BaseController {
    
    public function index() {
        $user = currentUser();
        $filters = [
            'hotel_id' => $user['hotel_id'],
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? ''
        ];
        
        // Filter based on role
        if ($user['role'] === 'collaborator') {
            $filters['assigned_to'] = $user['id'];
        } elseif ($user['role'] === 'guest') {
            $filters['guest_id'] = $user['id'];
        }
        
        $model = $this->model('ServiceRequest');
        $requests = $model->getAll($filters);
        
        $this->view('services/index', [
            'title' => 'Solicitudes de Servicio',
            'requests' => $requests,
            'filters' => $filters
        ]);
    }
    
    public function create() {
        $this->view('services/create', ['title' => 'Nueva Solicitud']);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('services');
        
        $user = currentUser();
        $data = [
            'hotel_id' => $user['hotel_id'],
            'guest_id' => $user['id'],
            'title' => sanitize($_POST['title'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'priority' => sanitize($_POST['priority'] ?? 'normal'),
            'room_number' => sanitize($_POST['room_number'] ?? '')
        ];
        
        $model = $this->model('ServiceRequest');
        if ($model->create($data)) {
            flash('success', 'Solicitud creada exitosamente', 'success');
        } else {
            flash('error', 'Error al crear la solicitud', 'danger');
        }
        redirect('services');
    }
    
    public function assign($id) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('services');
        
        $collaboratorId = intval($_POST['collaborator_id'] ?? 0);
        
        $model = $this->model('ServiceRequest');
        if ($model->assignTo($id, $collaboratorId)) {
            flash('success', 'Solicitud asignada exitosamente', 'success');
        } else {
            flash('error', 'Error al asignar la solicitud', 'danger');
        }
        redirect('services');
    }
    
    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('services');
        
        $status = sanitize($_POST['status'] ?? '');
        
        $model = $this->model('ServiceRequest');
        if ($model->updateStatus($id, $status)) {
            flash('success', 'Estado actualizado exitosamente', 'success');
        } else {
            flash('error', 'Error al actualizar el estado', 'danger');
        }
        redirect('services');
    }
    
    public function edit($id) {
        $this->requireRole(['admin', 'manager']);
        
        $model = $this->model('ServiceRequest');
        $service = $model->findById($id);
        
        if (!$service) {
            flash('error', 'Solicitud no encontrada', 'danger');
            redirect('services');
        }
        
        $this->view('services/edit', [
            'title' => 'Editar Solicitud',
            'service' => $service
        ]);
    }
    
    public function update($id) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('services');
        
        $data = [
            'title' => sanitize($_POST['title'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'priority' => sanitize($_POST['priority'] ?? 'normal'),
            'room_number' => sanitize($_POST['room_number'] ?? ''),
            'status' => sanitize($_POST['status'] ?? 'pending')
        ];
        
        $model = $this->model('ServiceRequest');
        if ($model->update($id, $data)) {
            flash('success', 'Solicitud actualizada exitosamente', 'success');
        } else {
            flash('error', 'Error al actualizar la solicitud', 'danger');
        }
        redirect('services');
    }
    
    public function cancel($id) {
        $this->requireRole(['admin', 'manager']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('services');
        
        $model = $this->model('ServiceRequest');
        if ($model->updateStatus($id, 'cancelled')) {
            flash('success', 'Solicitud cancelada exitosamente', 'success');
        } else {
            flash('error', 'Error al cancelar la solicitud', 'danger');
        }
        redirect('services');
    }
}
