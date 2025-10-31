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
        
        // Get trial days from settings
        $trialDays = getSetting('trial_days', 30);
        
        // Check if there's a referral code in URL
        $referralCode = $_GET['ref'] ?? null;
        $referrerName = null;
        
        if ($referralCode) {
            // Get referrer user info from loyalty program
            $stmt = $this->db->prepare("
                SELECT u.first_name, u.last_name 
                FROM loyalty_program lp
                JOIN users u ON lp.user_id = u.id
                WHERE lp.referral_code = ? AND lp.is_active = 1
            ");
            $stmt->execute([$referralCode]);
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($referrer) {
                $referrerName = $referrer['first_name'] . ' ' . $referrer['last_name'];
            }
        }
        
        $this->view('auth/login', [
            'title' => 'Iniciar Sesión',
            'trialDays' => $trialDays,
            'referralCode' => $referralCode,
            'referrerName' => $referrerName
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
        
        // Get available subscriptions - Try new structure first, fallback to old
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, 
                    name, 
                    price, 
                    billing_cycle as type,
                    CASE 
                        WHEN price = 0 AND trial_days > 0 THEN trial_days
                        WHEN billing_cycle = 'monthly' THEN 30
                        WHEN billing_cycle = 'annual' THEN 365
                        WHEN billing_cycle = 'lifetime' THEN 36500
                        ELSE 30
                    END as duration_days
                FROM subscription_plans 
                WHERE is_active = 1 
                ORDER BY price ASC
            ");
            $stmt->execute();
            $subscriptions = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // Fallback to old subscriptions table structure
            $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE is_active = 1 ORDER BY price ASC");
            $stmt->execute();
            $subscriptions = $stmt->fetchAll();
        }
        
        // Get trial days from settings
        $trialDays = getSetting('trial_days', 30);
        
        // Get PayPal settings
        $paypalEnabled = getSetting('paypal_enabled', '0') === '1';
        
        // Get bank accounts from settings (stored as JSON)
        $bankAccountsJson = getSetting('bank_accounts', '[]');
        // Fix: Ensure $bankAccountsJson is not null before decoding
        $bankAccounts = json_decode($bankAccountsJson ?? '[]', true);
        if (!is_array($bankAccounts)) {
            $bankAccounts = [];
        }
        
        // Check if there's a referral code in URL
        $referralCode = $_GET['ref'] ?? null;
        $referrerName = null;
        
        if ($referralCode) {
            // Get referrer user info from loyalty program
            $stmt = $this->db->prepare("
                SELECT u.first_name, u.last_name 
                FROM loyalty_program lp
                JOIN users u ON lp.user_id = u.id
                WHERE lp.referral_code = ? AND lp.is_active = 1
            ");
            $stmt->execute([$referralCode]);
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($referrer) {
                $referrerName = $referrer['first_name'] . ' ' . $referrer['last_name'];
            }
        }
        
        $this->view('auth/register', [
            'title' => 'Registrarse',
            'subscriptions' => $subscriptions,
            'trialDays' => $trialDays,
            'paypalEnabled' => $paypalEnabled,
            'bankAccounts' => $bankAccounts,
            'referralCode' => $referralCode,
            'referrerName' => $referrerName
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
        $acceptTerms = isset($_POST['accept_terms']) && $_POST['accept_terms'];
        $paymentOption = sanitize($_POST['payment_option'] ?? 'later');
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
        
        if (empty($data['phone'])) {
            $errors[] = 'El teléfono es requerido';
        } elseif (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
            $errors[] = 'El teléfono debe contener exactamente 10 dígitos';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        if (!$acceptTerms) {
            $errors[] = 'Debes aceptar los términos y condiciones';
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
            
            // 5. Handle payment proof if provided
            if ($paymentOption === 'proof' && isset($_FILES['reg_payment_proof']) && $_FILES['reg_payment_proof']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Handle file upload
                    $uploadDir = PUBLIC_PATH . '/uploads/payment_proofs/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileExtension = pathinfo($_FILES['reg_payment_proof']['name'], PATHINFO_EXTENSION);
                    $fileName = 'payment_' . $user_id . '_' . time() . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['reg_payment_proof']['tmp_name'], $filePath)) {
                        // Get subscription to know the price
                        $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE id = ?");
                        $stmt->execute([$data['subscription_id']]);
                        $subscription = $stmt->fetch();
                        
                        // Create payment transaction record
                        $transactionId = 'TXN_' . strtoupper(substr(md5(uniqid()), 0, 10));
                        $paymentMethod = sanitize($_POST['reg_payment_method'] ?? 'transfer');
                        $transactionReference = sanitize($_POST['reg_transaction_reference'] ?? '');
                        
                        $stmt = $this->db->prepare("
                            INSERT INTO payment_transactions 
                            (user_id, subscription_id, amount, payment_method, transaction_id, 
                             payment_proof, transaction_reference, status, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
                        ");
                        $stmt->execute([
                            $user_id,
                            $data['subscription_id'],
                            $subscription['price'],
                            $paymentMethod,
                            $transactionId,
                            $fileName,
                            $transactionReference
                        ]);
                    }
                } catch (Exception $e) {
                    // Log error but don't stop registration
                    error_log('Payment proof upload failed: ' . $e->getMessage());
                }
            }
            
            $this->db->commit();
            
            $message = '¡Registro exitoso! Tu hotel ha sido creado y tu periodo de prueba está activo.';
            if ($paymentOption === 'proof') {
                $message .= ' Tu comprobante de pago será revisado por un administrador.';
            }
            $message .= ' Por favor inicia sesión.';
            
            flash('success', $message, 'success');
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
            $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales
            
            // Cargar helper de logging
            require_once APP_PATH . '/helpers/email_logger.php';
            logEmail("=== Generando token de reset ===");
            logEmail("User ID: {$user['id']}, Email: $email");
            logEmail("Token generado: $token");
            
            // Save token to database - Usar DATE_ADD de MySQL para evitar problemas de zona horaria
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (user_id, token, expires_at, created_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())
            ");
            $stmt->execute([$user['id'], $token]);
            logEmail("Token guardado en base de datos");
            
            // Verificar que se guardó correctamente
            $check = $this->db->prepare("SELECT expires_at, created_at FROM password_resets WHERE token = ?");
            $check->execute([$token]);
            $saved = $check->fetch();
            logEmail("Token verificado - Creado: {$saved['created_at']}, Expira: {$saved['expires_at']}");
            
            // Send email with reset link using new service
            $resetLink = BASE_URL . '/auth/resetPassword?token=' . $token;
            logEmail("Reset link: $resetLink");
            
            // Cargar vendor autoload para PHPMailer
            if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
                require_once ROOT_PATH . '/vendor/autoload.php';
                logEmail("PHPMailer autoload cargado");
            } else {
                logEmail("ERROR: PHPMailer no está instalado");
                throw new Exception("PHPMailer no está instalado");
            }
            
            // Cargar el servicio de email de reset
            require_once APP_PATH . '/services/PasswordResetEmailService.php';
            
            $resetEmailService = new PasswordResetEmailService();
            $emailSent = $resetEmailService->sendPasswordResetEmail($email, $user['first_name'], $resetLink);
            
            if ($emailSent) {
                logEmail("✅ Correo de recuperación enviado exitosamente");
            } else {
                logEmail("❌ Error al enviar correo de recuperación");
            }
            
            flash('success', 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación en breve.', 'success');
            
        } catch (Exception $e) {
            logEmail("❌ EXCEPCIÓN en processForgotPassword: " . $e->getMessage());
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
        
        // Cargar helper de logging
        require_once APP_PATH . '/helpers/email_logger.php';
        logEmail("=== Verificando token de reset ===");
        logEmail("Token recibido RAW: " . var_export($token, true));
        logEmail("Longitud del token: " . strlen($token));
        
        // Limpiar posibles espacios o caracteres especiales
        $token = trim($token);
        logEmail("Token después de trim: $token");
        
        if (empty($token)) {
            logEmail("ERROR: Token vacío");
            flash('error', 'Token de recuperación inválido', 'danger');
            redirect('auth/forgotPassword');
        }
        
        // Primero verificar si el token existe
        $stmt_check = $this->db->prepare("SELECT COUNT(*) as count FROM password_resets WHERE token = ?");
        $stmt_check->execute([$token]);
        $exists = $stmt_check->fetch();
        logEmail("Token existe en BD: " . ($exists['count'] > 0 ? 'SÍ' : 'NO'));
        
        // Verificar fecha actual del servidor
        $serverTime = $this->db->query("SELECT NOW() as now")->fetch();
        logEmail("Fecha/hora servidor BD: {$serverTime['now']}");
        
        // Verify token
        $stmt = $this->db->prepare("
            SELECT pr.*, u.email, u.first_name 
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();
        
        if ($resetRequest) {
            logEmail("✅ Token válido encontrado para user: {$resetRequest['email']}");
        } else {
            logEmail("❌ Token NO válido o expirado");
            
            // Verificar si el token existe pero está usado o expirado
            $stmt2 = $this->db->prepare("SELECT * FROM password_resets WHERE token = ?");
            $stmt2->execute([$token]);
            $tokenInfo = $stmt2->fetch();
            
            if ($tokenInfo) {
                logEmail("Token existe en BD - Used: {$tokenInfo['used']}, Expires: {$tokenInfo['expires_at']}");
            } else {
                logEmail("Token NO existe en la base de datos");
            }
        }
        
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
            // Cargar helper de logging
            require_once APP_PATH . '/helpers/email_logger.php';
            logEmail("=== Procesando reset de contraseña ===");
            logEmail("Token: $token");
            
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
                logEmail("❌ Token inválido o expirado en processResetPassword");
                flash('error', 'El enlace de recuperación es inválido o ha expirado', 'danger');
                redirect('auth/forgotPassword');
            }
            
            logEmail("✅ Token válido, actualizando contraseña para user: {$resetRequest['email']}");
            
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
            
            $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $resetRequest['user_id']]);
            logEmail("✅ Contraseña actualizada en BD");
            
            // Mark token as used
            $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            logEmail("✅ Token marcado como usado");
            
            flash('success', '¡Contraseña actualizada exitosamente! Ahora puedes iniciar sesión.', 'success');
            logEmail("=== Proceso completado exitosamente ===");
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
