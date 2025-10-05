<?php
/**
 * Helper Functions
 */

/**
 * Redirect to a URL
 */
function redirect($path) {
    header('Location: ' . BASE_URL . '/' . $path);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'first_name' => $_SESSION['first_name'] ?? null,
        'last_name' => $_SESSION['last_name'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'hotel_id' => $_SESSION['hotel_id'] ?? null
    ];
}

/**
 * Check if user has specific role(s)
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['role'] ?? '', $roles);
}

/**
 * Escape HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) {
        return '';
    }
    return date($format, strtotime($datetime));
}

/**
 * Get flash message
 */
function flash($name = '', $message = '', $type = 'info') {
    if (!empty($name)) {
        if (!empty($message)) {
            $_SESSION['flash_' . $name] = [
                'message' => $message,
                'type' => $type
            ];
        } else {
            if (isset($_SESSION['flash_' . $name])) {
                $flash = $_SESSION['flash_' . $name];
                unset($_SESSION['flash_' . $name]);
                return $flash;
            }
        }
    }
    return null;
}

/**
 * Get role label in Spanish
 */
function getRoleLabel($role) {
    $roles = [
        'superadmin' => 'Super Administrador',
        'admin' => 'Administrador',
        'manager' => 'Gerente',
        'hostess' => 'Hostess',
        'collaborator' => 'Colaborador',
        'guest' => 'Huésped'
    ];
    
    return $roles[$role] ?? $role;
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'available' => '<span class="badge bg-success">Disponible</span>',
        'occupied' => '<span class="badge bg-warning">Ocupado</span>',
        'reserved' => '<span class="badge bg-info">Reservado</span>',
        'blocked' => '<span class="badge bg-danger">Bloqueado</span>',
        'maintenance' => '<span class="badge bg-secondary">Mantenimiento</span>',
        'pending' => '<span class="badge bg-warning">Pendiente</span>',
        'assigned' => '<span class="badge bg-info">Asignado</span>',
        'in_progress' => '<span class="badge bg-primary">En Progreso</span>',
        'completed' => '<span class="badge bg-success">Completado</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelado</span>',
        'active' => '<span class="badge bg-success">Activo</span>',
        'expired' => '<span class="badge bg-secondary">Expirado</span>',
        'confirmed' => '<span class="badge bg-success">Confirmado</span>',
        'checked_in' => '<span class="badge bg-primary">Check-in</span>',
        'checked_out' => '<span class="badge bg-secondary">Check-out</span>',
        'seated' => '<span class="badge bg-primary">Sentado</span>',
        'preparing' => '<span class="badge bg-info">Preparando</span>',
        'ready' => '<span class="badge bg-warning">Listo</span>',
        'delivered' => '<span class="badge bg-success">Entregado</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . e($status) . '</span>';
}

/**
 * Get priority badge HTML
 */
function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge bg-secondary">Baja</span>',
        'normal' => '<span class="badge bg-info">Normal</span>',
        'high' => '<span class="badge bg-warning">Alta</span>',
        'urgent' => '<span class="badge bg-danger">Urgente</span>'
    ];
    
    return $badges[$priority] ?? '<span class="badge bg-secondary">' . e($priority) . '</span>';
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Send email using configured SMTP settings
 */
function sendEmail($to, $subject, $body, $isHtml = true) {
    require_once CONFIG_PATH . '/email.php';
    
    // Check if SMTP is enabled
    if (!SMTP_ENABLED) {
        error_log("Email not sent - SMTP is disabled");
        return false;
    }
    
    // Basic email validation
    if (!isValidEmail($to)) {
        error_log("Invalid email address: $to");
        return false;
    }
    
    // Prepare headers
    $headers = [];
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">";
    $headers[] = "Reply-To: " . SMTP_FROM_EMAIL;
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    if ($isHtml) {
        $headers[] = "Content-Type: text/html; charset=UTF-8";
    } else {
        $headers[] = "Content-Type: text/plain; charset=UTF-8";
    }
    
    // Try to send email
    try {
        $success = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($success) {
            error_log("Email sent successfully to: $to");
        } else {
            error_log("Failed to send email to: $to");
        }
        
        return $success;
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Get setting from global_settings table
 */
function getSetting($key, $default = null) {
    static $cache = [];
    
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    
    try {
        require_once CONFIG_PATH . '/database.php';
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT setting_value, setting_type FROM global_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $value = $result['setting_value'];
            
            // Convert based on type
            switch ($result['setting_type']) {
                case 'boolean':
                    $value = (bool)$value;
                    break;
                case 'number':
                    $value = is_numeric($value) ? (float)$value : $value;
                    break;
                case 'json':
                    // Fix: Ensure value is not null before decoding
                    $value = json_decode($value ?? '[]', true);
                    break;
            }
            
            $cache[$key] = $value;
            return $value;
        }
    } catch (Exception $e) {
        error_log("Error getting setting '$key': " . $e->getMessage());
    }
    
    return $default;
}

/**
 * Update setting in global_settings table
 */
function updateSetting($key, $value, $userId = null) {
    try {
        require_once CONFIG_PATH . '/database.php';
        $db = Database::getInstance()->getConnection();
        
        // Get setting type
        $stmt = $db->prepare("SELECT setting_type FROM global_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        // Convert value based on type
        $settingType = $result['setting_type'];
        if ($settingType === 'json' && is_array($value)) {
            $value = json_encode($value);
        } elseif ($settingType === 'boolean') {
            $value = $value ? '1' : '0';
        }
        
        // Update setting
        $stmt = $db->prepare("
            UPDATE global_settings 
            SET setting_value = ?, updated_by = ?, updated_at = NOW() 
            WHERE setting_key = ?
        ");
        
        return $stmt->execute([$value, $userId, $key]);
    } catch (Exception $e) {
        error_log("Error updating setting '$key': " . $e->getMessage());
        return false;
    }
}

/**
 * Generate unique referral code
 */
function generateReferralCode($userId) {
    return strtoupper(substr(md5($userId . time()), 0, 8));
}
