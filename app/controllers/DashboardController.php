<?php
/**
 * Dashboard Controller
 */

require_once APP_PATH . '/controllers/BaseController.php';

class DashboardController extends BaseController {
    
    public function index() {
        $user = currentUser();
        $stats = [];
        
        // Get stats based on role
        switch ($user['role']) {
            case 'superadmin':
                $stats = $this->getSuperadminStats();
                break;
            case 'admin':
            case 'manager':
                $stats = $this->getAdminStats($user['hotel_id']);
                break;
            case 'hostess':
                $stats = $this->getHostessStats($user['hotel_id']);
                break;
            case 'collaborator':
                $stats = $this->getCollaboratorStats($user['id']);
                break;
            case 'guest':
                $stats = $this->getGuestStats($user['id']);
                break;
        }
        
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'user' => $user
        ]);
    }
    
    private function getSuperadminStats() {
        $stats = [];
        
        // Total hotels
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM hotels");
        $stats['total_hotels'] = $stmt->fetch()['count'];
        
        // Check which subscription tables exist and use accordingly
        $tables = $this->db->query("SHOW TABLES LIKE '%subscription%'")->fetchAll(\PDO::FETCH_COLUMN);
        $hasSubscriptionPlans = in_array('subscription_plans', array_map(function($t) { return basename($t); }, $tables));
        $hasHotelSubscriptions = in_array('hotel_subscriptions', array_map(function($t) { return basename($t); }, $tables));
        $hasUserSubscriptions = in_array('user_subscriptions', array_map(function($t) { return basename($t); }, $tables));
        
        // Active subscriptions - try different tables based on availability
        if ($hasHotelSubscriptions) {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM hotel_subscriptions WHERE status IN ('trial', 'active')");
        } elseif ($hasUserSubscriptions) {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active'");
        } else {
            $stmt = $this->db->query("SELECT 0 as count");
        }
        $stats['active_subscriptions'] = $stmt->fetch()['count'];
        
        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Monthly revenue - calculate based on available tables
        if ($hasSubscriptionPlans && $hasHotelSubscriptions) {
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(sp.price), 0) as revenue 
                FROM hotel_subscriptions hs
                JOIN subscription_plans sp ON hs.plan_id = sp.id
                WHERE hs.status IN ('trial', 'active')
                AND MONTH(hs.start_date) = MONTH(CURRENT_DATE())
                AND YEAR(hs.start_date) = YEAR(CURRENT_DATE())
            ");
        } elseif ($hasUserSubscriptions) {
            // Try to join with subscriptions table for price
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(s.price), 0) as revenue 
                FROM user_subscriptions us
                LEFT JOIN subscriptions s ON us.subscription_id = s.id
                WHERE us.status = 'active' 
                AND MONTH(us.start_date) = MONTH(CURRENT_DATE())
                AND YEAR(us.start_date) = YEAR(CURRENT_DATE())
            ");
        } else {
            $stmt = $this->db->query("SELECT 0 as revenue");
        }
        $stats['monthly_revenue'] = $stmt->fetch()['revenue'];
        
        // Recent hotels - check if owner_id column exists
        try {
            $stmt = $this->db->query("
                SELECT h.*, u.first_name, u.last_name, u.email 
                FROM hotels h
                LEFT JOIN users u ON h.owner_id = u.id
                ORDER BY h.created_at DESC
                LIMIT 5
            ");
            $stats['recent_hotels'] = $stmt->fetchAll();
        } catch (\PDOException $e) {
            // If owner_id doesn't exist, query without it
            $stmt = $this->db->query("
                SELECT * FROM hotels
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $stats['recent_hotels'] = $stmt->fetchAll();
        }
        
        // Subscription distribution - use available tables
        if ($hasSubscriptionPlans && $hasHotelSubscriptions) {
            $stmt = $this->db->query("
                SELECT sp.name, COUNT(hs.id) as count
                FROM subscription_plans sp
                LEFT JOIN hotel_subscriptions hs ON sp.id = hs.plan_id AND hs.status IN ('trial', 'active')
                GROUP BY sp.id, sp.name
                ORDER BY sp.sort_order, sp.id
            ");
        } elseif ($hasUserSubscriptions) {
            $stmt = $this->db->query("
                SELECT s.name, COUNT(us.id) as count
                FROM subscriptions s
                LEFT JOIN user_subscriptions us ON s.id = us.subscription_id AND us.status = 'active'
                GROUP BY s.id, s.name
                ORDER BY s.id
            ");
        } else {
            $stmt = $this->db->query("SELECT 'Sin datos' as name, 0 as count");
        }
        $stats['subscription_distribution'] = $stmt->fetchAll();
        
        // Monthly revenue trend (last 6 months)
        if ($hasSubscriptionPlans && $hasHotelSubscriptions) {
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(hs.start_date, '%Y-%m') as month,
                    SUM(sp.price) as revenue,
                    COUNT(hs.id) as subscriptions
                FROM hotel_subscriptions hs
                JOIN subscription_plans sp ON hs.plan_id = sp.id
                WHERE hs.start_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(hs.start_date, '%Y-%m')
                ORDER BY month
            ");
        } elseif ($hasUserSubscriptions) {
            $stmt = $this->db->query("
                SELECT 
                    DATE_FORMAT(us.start_date, '%Y-%m') as month,
                    SUM(s.price) as revenue,
                    COUNT(us.id) as subscriptions
                FROM user_subscriptions us
                LEFT JOIN subscriptions s ON us.subscription_id = s.id
                WHERE us.start_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(us.start_date, '%Y-%m')
                ORDER BY month
            ");
        } else {
            $stmt = $this->db->query("SELECT DATE_FORMAT(CURRENT_DATE(), '%Y-%m') as month, 0 as revenue, 0 as subscriptions");
        }
        $stats['revenue_trend'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    private function getAdminStats($hotelId) {
        $stats = [];
        
        // Date filters
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
        
        $stats['startDate'] = $startDate;
        $stats['endDate'] = $endDate;
        
        // Total rooms
        $stmt = $this->db->prepare("SELECT COUNT(*) as total, status FROM rooms WHERE hotel_id = ? GROUP BY status");
        $stmt->execute([$hotelId]);
        $rooms = $stmt->fetchAll();
        
        $stats['rooms'] = [
            'total' => array_sum(array_column($rooms, 'total')),
            'available' => 0,
            'occupied' => 0,
            'maintenance' => 0
        ];
        
        foreach ($rooms as $room) {
            if (isset($stats['rooms'][$room['status']])) {
                $stats['rooms'][$room['status']] = $room['total'];
            }
        }
        
        // Total tables
        $stmt = $this->db->prepare("SELECT COUNT(*) as total, status FROM restaurant_tables WHERE hotel_id = ? GROUP BY status");
        $stmt->execute([$hotelId]);
        $tables = $stmt->fetchAll();
        
        $stats['tables'] = [
            'total' => array_sum(array_column($tables, 'total')),
            'available' => 0,
            'occupied' => 0
        ];
        
        foreach ($tables as $table) {
            if (isset($stats['tables'][$table['status']])) {
                $stats['tables'][$table['status']] = $table['total'];
            }
        }
        
        // Service requests
        $stmt = $this->db->prepare("SELECT COUNT(*) as total, status FROM service_requests WHERE hotel_id = ? GROUP BY status");
        $stmt->execute([$hotelId]);
        $requests = $stmt->fetchAll();
        
        $stats['requests'] = [
            'total' => array_sum(array_column($requests, 'total')),
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0
        ];
        
        foreach ($requests as $req) {
            if (isset($stats['requests'][$req['status']])) {
                $stats['requests'][$req['status']] = $req['total'];
            }
        }
        
        // Recent reservations
        $stmt = $this->db->prepare("
            SELECT rr.*, r.room_number, u.first_name, u.last_name 
            FROM room_reservations rr
            JOIN rooms r ON rr.room_id = r.id
            JOIN users u ON rr.guest_id = u.id
            WHERE r.hotel_id = ?
            ORDER BY rr.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$hotelId]);
        $stats['recent_reservations'] = $stmt->fetchAll();
        
        // Today's revenue
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_amount), 0) as revenue 
            FROM orders 
            WHERE hotel_id = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$hotelId]);
        $stats['today_revenue'] = $stmt->fetch()['revenue'];
        
        // Get subscription info
        $currentUser = currentUser();
        $stmt = $this->db->prepare("
            SELECT us.*, sp.name as plan_name, sp.price,
                   DATEDIFF(us.end_date, CURDATE()) as days_remaining
            FROM user_subscriptions us
            JOIN subscription_plans sp ON us.subscription_id = sp.id
            WHERE us.user_id = ? AND us.status = 'active'
            ORDER BY us.end_date DESC
            LIMIT 1
        ");
        $stmt->execute([$currentUser['id']]);
        $stats['subscription'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Chart data for date range
        // Reservations by day
        $stmt = $this->db->prepare("
            SELECT DATE(rr.created_at) as date, COUNT(*) as count
            FROM room_reservations rr
            JOIN rooms r ON rr.room_id = r.id
            WHERE r.hotel_id = ? 
            AND DATE(rr.created_at) BETWEEN ? AND ?
            GROUP BY DATE(rr.created_at)
            ORDER BY date
        ");
        $stmt->execute([$hotelId, $startDate, $endDate]);
        $stats['chart_reservations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Service requests by day
        $stmt = $this->db->prepare("
            SELECT DATE(requested_at) as date, COUNT(*) as count
            FROM service_requests
            WHERE hotel_id = ?
            AND DATE(requested_at) BETWEEN ? AND ?
            GROUP BY DATE(requested_at)
            ORDER BY date
        ");
        $stmt->execute([$hotelId, $startDate, $endDate]);
        $stats['chart_requests'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Occupancy rate by day
        $stmt = $this->db->prepare("
            SELECT DATE(check_in) as date, COUNT(*) as occupied,
                   (SELECT COUNT(*) FROM rooms WHERE hotel_id = ?) as total_rooms
            FROM room_reservations rr
            JOIN rooms r ON rr.room_id = r.id
            WHERE r.hotel_id = ?
            AND rr.status IN ('confirmed', 'checked_in')
            AND DATE(check_in) BETWEEN ? AND ?
            GROUP BY DATE(check_in)
            ORDER BY date
        ");
        $stmt->execute([$hotelId, $hotelId, $startDate, $endDate]);
        $stats['chart_occupancy'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    private function getHostessStats($hotelId) {
        $stats = [];
        
        // Available tables
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM restaurant_tables WHERE hotel_id = ? AND status = 'available'");
        $stmt->execute([$hotelId]);
        $stats['available_tables'] = $stmt->fetch()['count'];
        
        // Today's reservations
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM table_reservations tr
            JOIN restaurant_tables rt ON tr.table_id = rt.id
            WHERE rt.hotel_id = ? AND DATE(tr.reservation_date) = CURDATE()
        ");
        $stmt->execute([$hotelId]);
        $stats['today_reservations'] = $stmt->fetch()['count'];
        
        // Active blocks
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM resource_blocks WHERE status = 'active'");
        $stmt->execute();
        $stats['active_blocks'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    private function getCollaboratorStats($userId) {
        $stats = [];
        
        // Assigned tasks
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total, status 
            FROM service_requests 
            WHERE assigned_to = ? 
            GROUP BY status
        ");
        $stmt->execute([$userId]);
        $tasks = $stmt->fetchAll();
        
        $stats['tasks'] = [
            'total' => array_sum(array_column($tasks, 'total')),
            'pending' => 0,
            'in_progress' => 0,
            'completed' => 0
        ];
        
        foreach ($tasks as $task) {
            if (isset($stats['tasks'][$task['status']])) {
                $stats['tasks'][$task['status']] = $task['total'];
            }
        }
        
        // Recent assignments
        $stmt = $this->db->prepare("
            SELECT * FROM service_requests 
            WHERE assigned_to = ? 
            ORDER BY requested_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $stats['recent_tasks'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    private function getGuestStats($userId) {
        $stats = [
            'active_reservations' => 0,
            'pending_requests' => 0
        ];
        
        // Active reservations
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM room_reservations 
            WHERE guest_id = ? AND status IN ('confirmed', 'checked_in')
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $stats['active_reservations'] = $result ? $result['count'] : 0;
        
        // Pending requests
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM service_requests 
            WHERE guest_id = ? AND status IN ('pending', 'in_progress')
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $stats['pending_requests'] = $result ? $result['count'] : 0;
        
        return $stats;
    }
}
