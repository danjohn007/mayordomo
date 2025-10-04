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
        
        // Get trial days from settings
        $trialDays = getSetting('trial_days', 30);
        
        $this->view('auth/register', [
            'title' => 'Registrarse',
            'subscriptions' => $subscriptions,
            'trialDays' => $trialDays
        ]);
    }
    
    /**
     * Process registration
     */
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/register');
        }
        
        $hotel_name = sanitize($_POST['hotel_name'] ?? '');
        $data = [
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'subscription_id' => intval($_POST['subscription_id'] ?? 0),
            'role' => 'admin' // Admin Local - propietario del hotel
        ];
        
        // Validation
        $errors = [];
        
        if (empty($hotel_name)) {
            $errors[] = 'El nombre del hotel es requerido';
        }
        
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
        
        // Begin transaction - create hotel and user
        try {
            $this->db->beginTransaction();
            
            // 1. Create hotel first
            $stmt = $this->db->prepare("
                INSERT INTO hotels (name, email, is_active, created_at) 
                VALUES (?, ?, 1, NOW())
            ");
            $stmt->execute([$hotel_name, $data['email']]);
            $hotel_id = $this->db->lastInsertId();
            
            // 2. Create user as owner/admin of the hotel
            $data['hotel_id'] = $hotel_id;
            unset($data['confirm_password']);
            
            if (!$userModel->create($data)) {
                throw new Exception('Error al crear el usuario');
            }
            
            $user_id = $this->db->lastInsertId();
            
            // 3. Set the user as owner of the hotel
            $stmt = $this->db->prepare("UPDATE hotels SET owner_id = ? WHERE id = ?");
            $stmt->execute([$user_id, $hotel_id]);
            
            // 4. Activate trial subscription if subscription_id is provided
            if ($data['subscription_id'] > 0) {
                // Get subscription details
                $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE id = ? AND is_active = 1");
                $stmt->execute([$data['subscription_id']]);
                $subscription = $stmt->fetch();
                
                if ($subscription) {
                    // Create user subscription
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d', strtotime('+' . $subscription['duration_days'] . ' days'));
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO user_subscriptions (user_id, subscription_id, start_date, end_date, status, created_at)
                        VALUES (?, ?, ?, ?, 'active', NOW())
                    ");
                    $stmt->execute([$user_id, $data['subscription_id'], $start_date, $end_date]);
                    
                    // Update hotel subscription status
                    $status = ($subscription['type'] === 'trial') ? 'trial' : 'active';
                    $stmt = $this->db->prepare("
                        UPDATE hotels 
                        SET subscription_status = ?,
                            subscription_start_date = ?,
                            subscription_end_date = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$status, $start_date, $end_date, $hotel_id]);
                }
            }
            
            $this->db->commit();
            
            flash('success', '¡Registro exitoso! Tu hotel ha sido creado y tu periodo de prueba está activo. Por favor inicia sesión.', 'success');
            redirect('auth/login');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            flash('error', 'Error al crear la cuenta: ' . $e->getMessage(), 'danger');
            redirect('auth/register');
        }
    }
    
    /**
     * Show forgot password form
     */
    public function forgotPassword() {
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        $this->view('auth/forgot_password', [
            'title' => 'Recuperar Contraseña'
        ]);
    }
    
    /**
     * Process forgot password request
     */
    public function processForgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/forgotPassword');
        }
        
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($email) || !isValidEmail($email)) {
            flash('error', 'Por favor ingresa un email válido', 'danger');
            redirect('auth/forgotPassword');
        }
        
        // Find user by email
        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);
        
        if (!$user) {
            // Don't reveal if email exists or not for security
            flash('success', 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación en breve.', 'success');
            redirect('auth/forgotPassword');
        }
        
        try {
            // Generate reset token
            $token = generateToken(32);
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save token to database
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (user_id, token, expires_at, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user['id'], $token, $expires_at]);
            
            // Send email with reset link
            $resetLink = BASE_URL . '/auth/resetPassword?token=' . $token;
            $subject = 'Recuperación de Contraseña - ' . APP_NAME;
            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .button { background-color: #0d6efd; color: white; padding: 12px 24px; 
                                 text-decoration: none; border-radius: 5px; display: inline-block; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2>Recuperación de Contraseña</h2>
                        <p>Hola {$user['first_name']},</p>
                        <p>Hemos recibido una solicitud para restablecer tu contraseña. 
                           Haz clic en el siguiente enlace para continuar:</p>
                        <p style='margin: 30px 0;'>
                            <a href='{$resetLink}' class='button'>Restablecer Contraseña</a>
                        </p>
                        <p>O copia y pega este enlace en tu navegador:</p>
                        <p style='word-break: break-all; color: #666;'>{$resetLink}</p>
                        <p>Este enlace expirará en 1 hora.</p>
                        <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                        <hr style='margin: 30px 0;'>
                        <p style='color: #666; font-size: 12px;'>
                            Este es un correo automático de " . APP_NAME . ". Por favor no respondas a este mensaje.
                        </p>
                    </div>
                </body>
                </html>
            ";
            
            sendEmail($email, $subject, $body, true);
            
            flash('success', 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación en breve.', 'success');
            
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            flash('error', 'Ocurrió un error al procesar tu solicitud. Por favor intenta más tarde.', 'danger');
        }
        
        redirect('auth/forgotPassword');
    }
    
    /**
     * Show reset password form
     */
    public function resetPassword() {
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            flash('error', 'Token de recuperación inválido', 'danger');
            redirect('auth/forgotPassword');
        }
        
        // Verify token
        $stmt = $this->db->prepare("
            SELECT pr.*, u.email, u.first_name 
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();
        
        $this->view('auth/reset_password', [
            'title' => 'Restablecer Contraseña',
            'token' => $token,
            'valid' => $resetRequest !== false
        ]);
    }
    
    /**
     * Process password reset
     */
    public function processResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/login');
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (empty($token)) {
            $errors[] = 'Token inválido';
        }
        
        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('auth/resetPassword?token=' . $token);
        }
        
        try {
            // Verify token again
            $stmt = $this->db->prepare("
                SELECT pr.*, u.id as user_id, u.email 
                FROM password_resets pr
                JOIN users u ON pr.user_id = u.id
                WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $resetRequest = $stmt->fetch();
            
            if (!$resetRequest) {
                flash('error', 'El enlace de recuperación es inválido o ha expirado', 'danger');
                redirect('auth/forgotPassword');
            }
            
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
            
            $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $resetRequest['user_id']]);
            
            // Mark token as used
            $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            flash('success', '¡Contraseña actualizada exitosamente! Ahora puedes iniciar sesión.', 'success');
            redirect('auth/login');
            
        } catch (Exception $e) {
            error_log("Password update error: " . $e->getMessage());
            flash('error', 'Ocurrió un error al actualizar tu contraseña. Por favor intenta más tarde.', 'danger');
            redirect('auth/resetPassword?token=' . $token);
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
