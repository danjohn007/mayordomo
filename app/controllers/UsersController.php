<?php
require_once APP_PATH . '/controllers/BaseController.php';

class UsersController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireRole(['admin', 'manager']);
    }
    
    public function index() {
        $user = currentUser();
        $filters = [
            'hotel_id' => $user['hotel_id'],
            'search' => $_GET['search'] ?? '',
            'role' => $_GET['role'] ?? '',
            'is_active' => $_GET['is_active'] ?? ''
        ];
        
        $model = $this->model('User');
        $users = $model->getAll($filters);
        
        $this->view('users/index', [
            'title' => 'Gestión de Usuarios',
            'users' => $users,
            'filters' => $filters
        ]);
    }
    
    public function create() {
        $this->view('users/create', ['title' => 'Nuevo Usuario']);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');
        
        $currentUser = currentUser();
        $data = [
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'role' => sanitize($_POST['role'] ?? 'guest'),
            'hotel_id' => $currentUser['hotel_id'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $errors = [];
        if (empty($data['email']) || !isValidEmail($data['email'])) {
            $errors[] = 'Email inválido';
        }
        if (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        $model = $this->model('User');
        if ($model->emailExists($data['email'])) {
            $errors[] = 'El email ya está registrado';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('users/create');
        }
        
        if ($model->create($data)) {
            flash('success', 'Usuario creado exitosamente', 'success');
        } else {
            flash('error', 'Error al crear el usuario', 'danger');
        }
        redirect('users');
    }
    
    public function edit($id) {
        $model = $this->model('User');
        $user = $model->findById($id);
        
        if (!$user) {
            flash('error', 'Usuario no encontrado', 'danger');
            redirect('users');
        }
        
        $this->view('users/edit', ['title' => 'Editar Usuario', 'editUser' => $user]);
    }
    
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');
        
        $data = [
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'role' => sanitize($_POST['role'] ?? 'guest'),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $model = $this->model('User');
        if ($model->update($id, $data)) {
            flash('success', 'Usuario actualizado exitosamente', 'success');
        } else {
            flash('error', 'Error al actualizar el usuario', 'danger');
        }
        redirect('users');
    }
    
    public function delete($id) {
        $this->requireRole(['admin']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('users');
        
        $model = $this->model('User');
        if ($model->delete($id)) {
            flash('success', 'Usuario eliminado exitosamente', 'success');
        } else {
            flash('error', 'Error al eliminar el usuario', 'danger');
        }
        redirect('users');
    }
}
