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
        
        // Get hotels with pagination
        $stmt = $this->db->prepare("
            SELECT h.*, 
                   u.email as owner_email,
                   CONCAT(u.first_name, ' ', u.last_name) as owner_name,
                   (SELECT COUNT(*) FROM users WHERE hotel_id = h.id) as user_count
            FROM hotels h
            LEFT JOIN users u ON h.owner_id = u.id
            ORDER BY h.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM hotels");
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/hotels', [
            'title' => 'Gestión de Hoteles',
            'hotels' => $hotels,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    /**
     * Subscriptions Management
     */
    public function subscriptions() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
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
            ORDER BY us.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM user_subscriptions");
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/subscriptions', [
            'title' => 'Gestión de Suscripciones',
            'subscriptions' => $subscriptions,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    /**
     * Users Management
     */
    public function users() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get users with pagination
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   h.name as hotel_name,
                   (SELECT COUNT(*) FROM user_subscriptions WHERE user_id = u.id AND status = 'active') as active_subscriptions
            FROM users u
            LEFT JOIN hotels h ON u.hotel_id = h.id
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users");
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/users', [
            'title' => 'Gestión de Usuarios',
            'users' => $users,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    /**
     * Payment Transactions
     */
    public function payments() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get payments with pagination
        $stmt = $this->db->prepare("
            SELECT pt.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email,
                   h.name as hotel_name
            FROM payment_transactions pt
            JOIN users u ON pt.user_id = u.id
            LEFT JOIN hotels h ON u.hotel_id = h.id
            ORDER BY pt.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM payment_transactions");
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/payments', [
            'title' => 'Registro de Pagos',
            'payments' => $payments,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]);
    }
    
    /**
     * Loyalty Program Management
     */
    public function loyalty() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get loyalty members with pagination
        $stmt = $this->db->prepare("
            SELECT lp.*, 
                   CONCAT(u.first_name, ' ', u.last_name) as user_name,
                   u.email as user_email,
                   u.role as user_role
            FROM loyalty_program lp
            JOIN users u ON lp.user_id = u.id
            ORDER BY lp.total_earnings DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$perPage, $offset]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM loyalty_program");
        $total = $stmt->fetch()['total'];
        
        $this->view('superadmin/loyalty', [
            'title' => 'Programa de Lealtad',
            'members' => $members,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
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
}
