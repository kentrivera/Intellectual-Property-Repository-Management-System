<?php
/**
 * Activity Log Model
 * Handles activity logging for audit trail
 */

class ActivityLog {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Log an activity
     */
    public function log($data) {
        $sql = "INSERT INTO activity_logs 
                (user_id, action_type, entity_type, entity_id, description, ip_address, user_agent, metadata) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['user_id'] ?? null,
            $data['action_type'],
            $data['entity_type'] ?? null,
            $data['entity_id'] ?? null,
            $data['description'],
            $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
            $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null,
            isset($data['metadata']) ? json_encode($data['metadata']) : null
        ]);
    }
    
    /**
     * Get all logs with pagination
     */
    public function getAll($page = 1, $perPage = RECORDS_PER_PAGE, $filters = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT al.*, u.username, u.full_name 
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $sql .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['entity_type'])) {
            $sql .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (al.description LIKE ? OR al.action_type LIKE ? OR al.entity_type LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total count
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action_type'])) {
            $sql .= " AND action_type = ?";
            $params[] = $filters['action_type'];
        }
        
        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (al.description LIKE ? OR al.action_type LIKE ? OR al.entity_type LIKE ? OR u.username LIKE ? OR u.full_name LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get logs by user
     */
    public function getByUser($userId, $limit = 10) {
        $sql = "SELECT * FROM activity_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }
    
    /**
     * Get logs by entity
     */
    public function getByEntity($entityType, $entityId, $limit = 10) {
        $sql = "SELECT al.*, u.username, u.full_name 
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.entity_type = ? AND al.entity_id = ? 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$entityType, $entityId, $limit]);
    }
    
    /**
     * Get recent activity
     */
    public function getRecent($limit = 20) {
        $sql = "SELECT al.*, u.username, u.full_name 
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC 
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Delete old logs (cleanup)
     */
    public function deleteOlderThan($days) {
        $sql = "DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->execute($sql, [$days]);
    }
}
