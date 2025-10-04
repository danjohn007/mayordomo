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
