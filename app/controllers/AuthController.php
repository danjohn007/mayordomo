<?php
/**
 * Authentication Controller
 */

require_once APP_PATH . '/controllers/BaseController.php';

class AuthController extends BaseController {
    
    protected function checkAuth() {
        // Override to allow public access to auth routes
    }
    
    /**
     * Show login form
     */
    public function login() {
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        $this->view('auth/login', [
            'title' => 'Iniciar Sesión'
        ]);
    }
    
    /**
     * Process login
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/login');
        }
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'El email es requerido';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Email inválido';
        }
        
        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('auth/login');
        }
        
        // Find user
        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);
        
        if (!$user || !$userModel->verifyPassword($password, $user['password'])) {
            flash('error', 'Credenciales inválidas', 'danger');
            redirect('auth/login');
        }
        
        // Check if user is active
        if (!$user['is_active']) {
            flash('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.', 'danger');
            redirect('auth/login');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['hotel_id'] = $user['hotel_id'];
        
        flash('success', '¡Bienvenido ' . $user['first_name'] . '!', 'success');
        redirect('dashboard');
    }
    
    /**
     * Show register form
     */
    public function register() {
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        // Get available subscriptions
        $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE is_active = 1 ORDER BY price ASC");
        $stmt->execute();
        $subscriptions = $stmt->fetchAll();
        
        $this->view('auth/register', [
            'title' => 'Registrarse',
            'subscriptions' => $subscriptions
        ]);
    }
    
    /**
     * Process registration
     */
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/register');
        }
        
        $data = [
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'subscription_id' => intval($_POST['subscription_id'] ?? 0),
            'role' => 'guest'
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'El nombre es requerido';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'El apellido es requerido';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'El email es requerido';
        } elseif (!isValidEmail($data['email'])) {
            $errors[] = 'Email inválido';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        // Check if email exists
        $userModel = $this->model('User');
        if ($userModel->emailExists($data['email'])) {
            $errors[] = 'El email ya está registrado';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('auth/register');
        }
        
        // Create user
        unset($data['confirm_password']);
        
        if ($userModel->create($data)) {
            flash('success', 'Registro exitoso. Por favor inicia sesión.', 'success');
            redirect('auth/login');
        } else {
            flash('error', 'Error al crear la cuenta', 'danger');
            redirect('auth/register');
        }
    }
    
    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        redirect('auth/login');
    }
}
