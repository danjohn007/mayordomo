<?php
/**
 * Superadmin Controller
 * Manages all superadmin functionalities
 */

require_once APP_PATH . '/controllers/BaseController.php';

class SuperadminController extends BaseController {
    
    protected function checkAuth() {
        parent::checkAuth();
        
        // Only superadmin can access
        if (!hasRole('superadmin')) {
            flash('error', 'Acceso denegado. Solo superadministradores pueden acceder a esta sección.', 'danger');
            redirect('dashboard');
        }
    }
    
    /**
     * Superadmin Dashboard
     */
    public function index() {
        // Get date filters
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
        
        // Get statistics
        $stats = $this->getStatistics($startDate, $endDate);
        
        // Get chart data
        $chartData = $this->getChartData($startDate, $endDate);
        
        $this->view('superadmin/dashboard', [
            'title' => 'Dashboard Superadmin',
            'stats' => $stats,
            'chartData' => $chartData,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Get statistics for dashboard
     */
    private function getStatistics($startDate, $endDate) {
        $stats = [];
        
        // Total hotels
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM hotels WHERE is_active = 1");
        $stats['total_hotels'] = $stmt->fetch()['total'];
        
        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
        $stats['total_users'] = $stmt->fetch()['total'];
        
        // Active subscriptions
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM user_subscriptions 
            WHERE status = 'active' AND end_date >= CURDATE()
        ");
        $stats['active_subscriptions'] = $stmt->fetch()['total'];
        
        // Total revenue in date range
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total 
            FROM payment_transactions 
            WHERE status = 'completed' 
            AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['total_revenue'] = $stmt->fetch()['total'];
        
        // New hotels in date range
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM hotels 
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $stats['new_hotels'] = $stmt->fetch()['total'];
        
        // Active loyalty members
        $stmt = $this->db->query("
            SELECT COUNT(*) as total 
            FROM loyalty_program 
            WHERE is_active = 1
        ");
        $stats['loyalty_members'] = $stmt->fetch()['total'];
        
        // Recent hotels (last 5)
        $stmt = $this->db->query("
            SELECT h.name, h.email, h.created_at,
                   CONCAT(u.first_name, ' ', u.last_name) as owner_name
            FROM hotels h
            LEFT JOIN users u ON h.owner_id = u.id
            ORDER BY h.created_at DESC
            LIMIT 5
        ");
        $stats['recent_hotels'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Subscription distribution
        $stmt = $this->db->query("
            SELECT sp.name, COUNT(*) as count
            FROM user_subscriptions us
            JOIN subscription_plans sp ON us.subscription_id = sp.id
            WHERE us.status = 'active'
            GROUP BY sp.id, sp.name
        ");
        $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate percentages
        $totalSubs = array_sum(array_column($distribution, 'count'));
        foreach ($distribution as &$plan) {
            $plan['percentage'] = $totalSubs > 0 ? round(($plan['count'] / $totalSubs) * 100, 1) : 0;
        }
        $stats['subscription_distribution'] = $distribution;
        
        return $stats;
    }
    
    /**
     * Get chart data for dashboard
     */
    private function getChartData($startDate, $endDate) {
        $data = [];
        
        // Revenue by day
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, SUM(amount) as revenue
            FROM payment_transactions
            WHERE status = 'completed'
            AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        $data['revenue'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // New users by day
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM users
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        $data['new_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Subscriptions by plan
        $stmt = $this->db->query("
            SELECT s.name, COUNT(us.id) as count
            FROM subscription_plans s
            LEFT JOIN user_subscriptions us ON s.id = us.subscription_id 
            WHERE us.status = 'active'
            GROUP BY s.id, s.name
        ");
        $data['subscriptions_by_plan'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $data;
    }
    
    /**
     * Hotels Management
     */
    public function hotels() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filters
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        // Build query with filters
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(h.name LIKE ? OR h.email LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($startDate)) {
            $where[] = "DATE(h.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $where[] = "DATE(h.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get hotels with pagination
        $stmt = $this->db->prepare("
            SELECT h.*, 
                   u.email as owner_email,
                   CONCAT(u.first_name, ' ', u.last_name) as owner_name,
                   (SELECT COUNT(*) FROM users WHERE hotel_id = h.id) as user_count
            FROM hotels h
            LEFT JOIN users u ON h.owner_id = u.id
            {$whereClause}
            ORDER BY h.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countParams = array_slice($params, 0, -2); // Remove limit and offset
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM hotels h
            LEFT JOIN users u ON h.owner_id = u.id
            {$whereClause}
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/hotels', [
            'title' => 'Gestión de Hoteles',
            'hotels' => $hotels,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Subscriptions Management
     */
    public function subscriptions() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filters
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        // Build query with filters
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(sp.name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR h.name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($startDate)) {
            $where[] = "DATE(us.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $where[] = "DATE(us.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get subscriptions with pagination
        $stmt = $this->db->prepare("
            SELECT us.*, 
                   sp.name as plan_name,
                   sp.price as plan_price,
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email,
                   h.name as hotel_name,
                   DATEDIFF(us.end_date, CURDATE()) as days_remaining
            FROM user_subscriptions us
            JOIN subscription_plans sp ON us.subscription_id = sp.id
            JOIN users u ON us.user_id = u.id
            LEFT JOIN hotels h ON u.hotel_id = h.id
            {$whereClause}
            ORDER BY us.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countParams = array_slice($params, 0, -2);
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM user_subscriptions us
            JOIN subscription_plans sp ON us.subscription_id = sp.id
            JOIN users u ON us.user_id = u.id
            LEFT JOIN hotels h ON u.hotel_id = h.id
            {$whereClause}
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/subscriptions', [
            'title' => 'Gestión de Suscripciones',
            'subscriptions' => $subscriptions,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Users Management
     */
    public function users() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filters
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        // Build query with filters
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR h.name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($startDate)) {
            $where[] = "DATE(u.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $where[] = "DATE(u.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get users with pagination
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   h.name as hotel_name,
                   (SELECT COUNT(*) FROM user_subscriptions WHERE user_id = u.id AND status = 'active') as active_subscriptions
            FROM users u
            LEFT JOIN hotels h ON u.hotel_id = h.id
            {$whereClause}
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countParams = array_slice($params, 0, -2);
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM users u
            LEFT JOIN hotels h ON u.hotel_id = h.id
            {$whereClause}
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/users', [
            'title' => 'Gestión de Usuarios',
            'users' => $users,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Payment Transactions
     */
    public function payments() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filters
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        // Build query with filters
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR h.name LIKE ? OR pt.transaction_id LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($startDate)) {
            $where[] = "DATE(pt.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $where[] = "DATE(pt.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get payments with pagination
        $stmt = $this->db->prepare("
            SELECT pt.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email,
                   h.name as hotel_name
            FROM payment_transactions pt
            JOIN users u ON pt.user_id = u.id
            LEFT JOIN hotels h ON u.hotel_id = h.id
            {$whereClause}
            ORDER BY pt.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countParams = array_slice($params, 0, -2);
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM payment_transactions pt
            JOIN users u ON pt.user_id = u.id
            LEFT JOIN hotels h ON u.hotel_id = h.id
            {$whereClause}
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/payments', [
            'title' => 'Registro de Pagos',
            'payments' => $payments,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Loyalty Program Management
     */
    public function loyalty() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filters
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        
        // Build query with filters
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.email LIKE ? OR lp.referral_code LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($startDate)) {
            $where[] = "DATE(lp.created_at) >= ?";
            $params[] = $startDate;
        }
        
        if (!empty($endDate)) {
            $where[] = "DATE(lp.created_at) <= ?";
            $params[] = $endDate;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get loyalty members with pagination
        $stmt = $this->db->prepare("
            SELECT lp.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email,
                   u.role as user_role
            FROM loyalty_program lp
            JOIN users u ON lp.user_id = u.id
            {$whereClause}
            ORDER BY lp.total_earnings DESC
            LIMIT ? OFFSET ?
        ");
        $params[] = $perPage;
        $params[] = $offset;
        $stmt->execute($params);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countParams = array_slice($params, 0, -2);
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM loyalty_program lp
            JOIN users u ON lp.user_id = u.id
            {$whereClause}
        ");
        $stmt->execute($countParams);
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/loyalty', [
            'title' => 'Programa de Lealtad',
            'members' => $members,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
    
    /**
     * Global Settings
     */
    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings();
            return;
        }
        
        // Get all settings grouped by category
        $stmt = $this->db->query("
            SELECT * FROM global_settings 
            ORDER BY category, setting_key
        ");
        $allSettings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Group by category
        $settingsByCategory = [];
        foreach ($allSettings as $setting) {
            $settingsByCategory[$setting['category']][] = $setting;
        }
        
        $this->view('superadmin/settings', [
            'title' => 'Configuración Global',
            'settingsByCategory' => $settingsByCategory
        ]);
    }
    
    /**
     * Update global settings
     */
    private function updateSettings() {
        try {
            $currentUser = currentUser();
            $userId = $currentUser['id'];
            
            // Update each setting
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'setting_') === 0) {
                    $settingKey = str_replace('setting_', '', $key);
                    updateSetting($settingKey, $value, $userId);
                }
            }
            
            flash('success', 'Configuración actualizada exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al actualizar la configuración: ' . $e->getMessage(), 'danger');
        }
        
        redirect('superadmin/settings');
    }
    
    /**
     * Add Manual Payment
     */
    public function addPayment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('superadmin/payments');
        }
        
        try {
            $userId = (int)$_POST['user_id'];
            $amount = (float)$_POST['amount'];
            $paymentMethod = sanitize($_POST['payment_method']);
            $transactionId = sanitize($_POST['transaction_id'] ?? '');
            $notes = sanitize($_POST['notes'] ?? '');
            
            // Get user's hotel_id
            $stmt = $this->db->prepare("SELECT hotel_id FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                flash('error', 'Usuario no encontrado', 'danger');
                redirect('superadmin/payments');
            }
            
            // Generate transaction ID if not provided
            if (empty($transactionId)) {
                $transactionId = 'MANUAL-' . strtoupper(uniqid());
            }
            
            // Insert payment transaction
            $stmt = $this->db->prepare("
                INSERT INTO payment_transactions 
                (user_id, hotel_id, amount, payment_method, transaction_id, status, notes, created_at, completed_at)
                VALUES (?, ?, ?, ?, ?, 'completed', ?, NOW(), NOW())
            ");
            $stmt->execute([
                $userId,
                $user['hotel_id'],
                $amount,
                $paymentMethod,
                $transactionId,
                $notes
            ]);
            
            flash('success', 'Pago registrado exitosamente', 'success');
        } catch (Exception $e) {
            flash('error', 'Error al registrar el pago: ' . $e->getMessage(), 'danger');
        }
        
        redirect('superadmin/payments');
    }
    
    /**
     * Suspend User
     */
    public function suspendUser($userId) {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Usuario suspendido exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al suspender el usuario']);
        }
        exit;
    }
    
    /**
     * Activate User
     */
    public function activateUser($userId) {
        header('Content-Type: application/json');
        
        try {
            $stmt = $this->db->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            $stmt->execute([$userId]);
            
            echo json_encode(['success' => true, 'message' => 'Usuario activado exitosamente']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al activar el usuario']);
        }
        exit;
    }
}
