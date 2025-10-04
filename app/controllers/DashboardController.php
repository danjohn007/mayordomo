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
        
        // Active subscriptions
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM user_subscriptions WHERE status = 'active'");
        $stats['active_subscriptions'] = $stmt->fetch()['count'];
        
        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Monthly revenue
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(price), 0) as revenue 
            FROM user_subscriptions 
            WHERE status = 'active' 
            AND MONTH(start_date) = MONTH(CURRENT_DATE())
            AND YEAR(start_date) = YEAR(CURRENT_DATE())
        ");
        $stats['monthly_revenue'] = $stmt->fetch()['revenue'];
        
        // Recent hotels
        $stmt = $this->db->query("
            SELECT h.*, u.first_name, u.last_name, u.email 
            FROM hotels h
            LEFT JOIN users u ON h.owner_id = u.id
            ORDER BY h.created_at DESC
            LIMIT 5
        ");
        $stats['recent_hotels'] = $stmt->fetchAll();
        
        // Subscription distribution
        $stmt = $this->db->query("
            SELECT sp.name, COUNT(us.id) as count
            FROM subscription_plans sp
            LEFT JOIN user_subscriptions us ON sp.id = us.plan_id AND us.status = 'active'
            GROUP BY sp.id, sp.name
            ORDER BY sp.id
        ");
        $stats['subscription_distribution'] = $stmt->fetchAll();
        
        // Monthly revenue trend (last 6 months)
        $stmt = $this->db->query("
            SELECT 
                DATE_FORMAT(start_date, '%Y-%m') as month,
                SUM(price) as revenue,
                COUNT(*) as subscriptions
            FROM user_subscriptions
            WHERE start_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(start_date, '%Y-%m')
            ORDER BY month
        ");
        $stats['revenue_trend'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    private function getAdminStats($hotelId) {
        $stats = [];
        
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
