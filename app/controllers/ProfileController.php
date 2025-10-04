<?php
/**
 * Profile Controller
 * Manages user profile for all user types
 */

require_once APP_PATH . '/controllers/BaseController.php';

class ProfileController extends BaseController {
    
    /**
     * Show user profile
     */
    public function index() {
        $currentUser = currentUser();
        $userId = $currentUser['id'];
        
        // Get full user data
        $stmt = $this->db->prepare("
            SELECT u.*, h.name as hotel_name
            FROM users u
            LEFT JOIN hotels h ON u.hotel_id = h.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get active subscription if user is admin
        $subscription = null;
        $daysRemaining = 0;
        if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
            $stmt = $this->db->prepare("
                SELECT us.*, sp.name as plan_name, sp.price,
                       DATEDIFF(us.end_date, CURDATE()) as days_remaining
                FROM user_subscriptions us
                JOIN subscription_plans sp ON us.subscription_id = sp.id
                WHERE us.user_id = ? AND us.status = 'active'
                ORDER BY us.end_date DESC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            $daysRemaining = $subscription['days_remaining'] ?? 0;
        }
        
        // Get payment history if admin
        $payments = [];
        if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
            $stmt = $this->db->prepare("
                SELECT * FROM payment_transactions
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Get referral info if exists
        $referralInfo = null;
        $stmt = $this->db->prepare("SELECT * FROM loyalty_program WHERE user_id = ?");
        $stmt->execute([$userId]);
        $referralInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->view('profile/index', [
            'title' => 'Mi Perfil',
            'user' => $user,
            'subscription' => $subscription,
            'daysRemaining' => $daysRemaining,
            'payments' => $payments,
            'referralInfo' => $referralInfo
        ]);
    }
    
    /**
     * Update profile
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('profile');
        }
        
        $currentUser = currentUser();
        $userId = $currentUser['id'];
        
        $data = [
            'first_name' => sanitize($_POST['first_name'] ?? ''),
            'last_name' => sanitize($_POST['last_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'email' => sanitize($_POST['email'] ?? '')
        ];
        
        // Validation
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors[] = 'El nombre es requerido';
        }
        
        if (empty($data['last_name'])) {
            $errors[] = 'El apellido es requerido';
        }
        
        if (empty($data['email']) || !isValidEmail($data['email'])) {
            $errors[] = 'Email inválido';
        }
        
        // Check if email is already taken by another user
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$data['email'], $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'El email ya está en uso por otro usuario';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('profile');
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, email = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                $data['email'],
                $userId
            ]);
            
            // Update session
            $_SESSION['first_name'] = $data['first_name'];
            $_SESSION['last_name'] = $data['last_name'];
            $_SESSION['email'] = $data['email'];
            
            flash('success', 'Perfil actualizado exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al actualizar el perfil: ' . $e->getMessage(), 'danger');
        }
        
        redirect('profile');
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('profile');
        }
        
        $currentUser = currentUser();
        $userId = $currentUser['id'];
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'La contraseña actual es requerida';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'La nueva contraseña es requerida';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('profile');
        }
        
        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!password_verify($currentPassword, $user['password'])) {
                flash('error', 'La contraseña actual es incorrecta', 'danger');
                redirect('profile');
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
            
            $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            
            flash('success', 'Contraseña actualizada exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al cambiar la contraseña: ' . $e->getMessage(), 'danger');
        }
        
        redirect('profile');
    }
    
    /**
     * Generate or show referral link
     */
    public function referral() {
        $currentUser = currentUser();
        $userId = $currentUser['id'];
        
        // Check if user already has a referral program entry
        $stmt = $this->db->prepare("SELECT * FROM loyalty_program WHERE user_id = ?");
        $stmt->execute([$userId]);
        $loyalty = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$loyalty) {
            // Create loyalty program entry
            $referralCode = generateReferralCode($userId);
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO loyalty_program (user_id, referral_code, is_active, created_at)
                    VALUES (?, ?, 1, NOW())
                ");
                $stmt->execute([$userId, $referralCode]);
                
                flash('success', '¡Tu enlace de referidos ha sido generado!', 'success');
            } catch (Exception $e) {
                flash('error', 'Error al generar el código de referidos', 'danger');
            }
        }
        
        redirect('profile');
    }
}
