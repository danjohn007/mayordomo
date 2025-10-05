<?php
/**
 * Subscription Controller
 * Manages subscription upgrades and payments for admin users
 */

require_once APP_PATH . '/controllers/BaseController.php';

class SubscriptionController extends BaseController {
    
    /**
     * Show subscription upgrade page
     */
    public function index() {
        $currentUser = currentUser();
        
        // Only admins can access
        if ($currentUser['role'] !== 'admin') {
            flash('error', 'No tienes permiso para acceder a esta página', 'danger');
            redirect('dashboard');
        }
        
        // Get current subscription
        $stmt = $this->db->prepare("
            SELECT us.*, s.name as plan_name, s.price,
                   DATEDIFF(us.end_date, CURDATE()) as days_remaining
            FROM user_subscriptions us
            JOIN subscriptions s ON us.subscription_id = s.id
            WHERE us.user_id = ? AND us.status = 'active'
            ORDER BY us.end_date DESC
            LIMIT 1
        ");
        $stmt->execute([$currentUser['id']]);
        $currentSubscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get available plans
        $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE is_active = 1 ORDER BY price ASC");
        $stmt->execute();
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get PayPal settings
        $paypalEnabled = getSetting('paypal_enabled', '0') === '1';
        $paypalClientId = getSetting('paypal_client_id', '');
        $paypalMode = getSetting('paypal_mode', 'sandbox');
        
        // Get bank account info for transfer payments
        $bankAccounts = [];
        $stmt = $this->db->query("SELECT * FROM bank_accounts WHERE is_active = 1");
        $bankAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('subscription/upgrade', [
            'title' => 'Actualizar Plan',
            'currentSubscription' => $currentSubscription,
            'plans' => $plans,
            'paypalEnabled' => $paypalEnabled,
            'paypalClientId' => $paypalClientId,
            'paypalMode' => $paypalMode,
            'bankAccounts' => $bankAccounts
        ]);
    }
    
    /**
     * Process subscription upgrade with payment proof
     */
    public function uploadProof() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('subscription');
        }
        
        $currentUser = currentUser();
        
        if ($currentUser['role'] !== 'admin') {
            flash('error', 'No tienes permiso para realizar esta acción', 'danger');
            redirect('dashboard');
        }
        
        $planId = sanitize($_POST['plan_id'] ?? '');
        $paymentMethod = sanitize($_POST['payment_method'] ?? '');
        $transactionReference = sanitize($_POST['transaction_reference'] ?? '');
        
        // Validation
        $errors = [];
        
        if (empty($planId)) {
            $errors[] = 'Debes seleccionar un plan';
        }
        
        if (empty($paymentMethod)) {
            $errors[] = 'Debes seleccionar un método de pago';
        }
        
        if (empty($transactionReference)) {
            $errors[] = 'Debes proporcionar una referencia de pago';
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Debes subir el comprobante de pago';
        }
        
        if (!empty($errors)) {
            flash('error', implode('<br>', $errors), 'danger');
            redirect('subscription');
        }
        
        // Get plan details
        $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE id = ?");
        $stmt->execute([$planId]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$plan) {
            flash('error', 'Plan no encontrado', 'danger');
            redirect('subscription');
        }
        
        try {
            // Handle file upload
            $uploadDir = PUBLIC_PATH . '/uploads/payment_proofs/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $fileName = 'payment_' . $currentUser['id'] . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $filePath)) {
                throw new Exception('Error al subir el archivo');
            }
            
            // Create payment transaction record
            $transactionId = 'TXN_' . strtoupper(substr(md5(uniqid()), 0, 10));
            
            $stmt = $this->db->prepare("
                INSERT INTO payment_transactions 
                (user_id, subscription_id, amount, payment_method, transaction_id, 
                 payment_proof, transaction_reference, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $currentUser['id'],
                $planId,
                $plan['price'],
                $paymentMethod,
                $transactionId,
                $fileName,
                $transactionReference
            ]);
            
            flash('success', 'Comprobante de pago enviado. Tu solicitud será revisada por un administrador.', 'success');
            
        } catch (Exception $e) {
            flash('error', 'Error al procesar el pago: ' . $e->getMessage(), 'danger');
        }
        
        redirect('subscription');
    }
    
    /**
     * Process PayPal payment
     */
    public function paypalSuccess() {
        $currentUser = currentUser();
        
        if ($currentUser['role'] !== 'admin') {
            flash('error', 'No tienes permiso para acceder a esta página', 'danger');
            redirect('dashboard');
        }
        
        $planId = sanitize($_GET['plan_id'] ?? '');
        $paypalOrderId = sanitize($_GET['order_id'] ?? '');
        
        if (empty($planId) || empty($paypalOrderId)) {
            flash('error', 'Datos de pago incompletos', 'danger');
            redirect('subscription');
        }
        
        try {
            // Get plan details
            $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE id = ?");
            $stmt->execute([$planId]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plan) {
                throw new Exception('Plan no encontrado');
            }
            
            // Create payment transaction record
            $transactionId = 'PAYPAL_' . strtoupper(substr(md5($paypalOrderId), 0, 10));
            
            $stmt = $this->db->prepare("
                INSERT INTO payment_transactions 
                (user_id, subscription_id, amount, payment_method, transaction_id, 
                 transaction_reference, status, created_at)
                VALUES (?, ?, ?, 'paypal', ?, ?, 'approved', NOW())
            ");
            $stmt->execute([
                $currentUser['id'],
                $planId,
                $plan['price'],
                $transactionId,
                $paypalOrderId
            ]);
            
            // Activate or extend subscription
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+{$plan['duration_days']} days"));
            
            // Check if user has active subscription
            $stmt = $this->db->prepare("
                SELECT id FROM user_subscriptions 
                WHERE user_id = ? AND status = 'active'
            ");
            $stmt->execute([$currentUser['id']]);
            $existingSubscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingSubscription) {
                // Update existing subscription
                $stmt = $this->db->prepare("
                    UPDATE user_subscriptions 
                    SET subscription_id = ?, end_date = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$planId, $endDate, $existingSubscription['id']]);
            } else {
                // Create new subscription
                $stmt = $this->db->prepare("
                    INSERT INTO user_subscriptions 
                    (user_id, subscription_id, start_date, end_date, status, created_at)
                    VALUES (?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([$currentUser['id'], $planId, $startDate, $endDate]);
            }
            
            flash('success', '¡Pago procesado exitosamente! Tu plan ha sido actualizado.', 'success');
            
        } catch (Exception $e) {
            flash('error', 'Error al procesar el pago: ' . $e->getMessage(), 'danger');
        }
        
        redirect('subscription');
    }
    
    /**
     * Cancel PayPal payment
     */
    public function paypalCancel() {
        flash('warning', 'Pago cancelado. No se realizaron cargos.', 'warning');
        redirect('subscription');
    }
}
