<?php
/**
 * IP Record Model
 * Handles intellectual property records
 */

class IPRecord {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all IP records
     */
    public function getAll($page = 1, $perPage = RECORDS_PER_PAGE, $filters = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT ir.*, it.type_name, u.full_name as created_by_name,
                (SELECT COUNT(*) FROM ip_documents WHERE ip_record_id = ir.id AND is_deleted = 0) as document_count
                FROM ip_records ir
                INNER JOIN ip_types it ON ir.ip_type_id = it.id
                INNER JOIN users u ON ir.created_by = u.id
                WHERE ir.is_archived = 0";
        
        $params = [];
        
        if (!empty($filters['ip_type_id'])) {
            $sql .= " AND ir.ip_type_id = ?";
            $params[] = $filters['ip_type_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND ir.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['folder_id'])) {
            if ($filters['folder_id'] === null) {
                $sql .= " AND ir.folder_id IS NULL";
            } else {
                $sql .= " AND ir.folder_id = ?";
                $params[] = $filters['folder_id'];
            }
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (ir.title LIKE ? OR ir.description LIKE ? OR ir.owner LIKE ? OR ir.tags LIKE ? OR ir.reference_number LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $sql .= " ORDER BY ir.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total count
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM ip_records WHERE is_archived = 0";
        $params = [];
        
        if (!empty($filters['ip_type_id'])) {
            $sql .= " AND ip_type_id = ?";
            $params[] = $filters['ip_type_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['folder_id'])) {
            if ($filters['folder_id'] === null) {
                $sql .= " AND folder_id IS NULL";
            } else {
                $sql .= " AND folder_id = ?";
                $params[] = $filters['folder_id'];
            }
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR description LIKE ? OR owner LIKE ? OR tags LIKE ? OR reference_number LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
    
    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "SELECT ir.*, it.type_name, u.full_name as created_by_name
                FROM ip_records ir
                INNER JOIN ip_types it ON ir.ip_type_id = it.id
                INNER JOIN users u ON ir.created_by = u.id
                WHERE ir.id = ? AND ir.is_archived = 0
                LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Create new IP record
     */
    public function create($data) {
        $sql = "INSERT INTO ip_records 
                (ip_type_id, title, description, owner, filing_date, status, tags, reference_number, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['ip_type_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['owner'],
            $data['filing_date'] ?? null,
            $data['status'] ?? 'pending',
            $data['tags'] ?? null,
            $data['reference_number'] ?? null,
            $data['created_by']
        ]);
    }
    
    /**
     * Update IP record
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['ip_type_id'])) {
            $fields[] = "ip_type_id = ?";
            $params[] = $data['ip_type_id'];
        }
        
        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $params[] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }
        
        if (isset($data['owner'])) {
            $fields[] = "owner = ?";
            $params[] = $data['owner'];
        }
        
        if (isset($data['filing_date'])) {
            $fields[] = "filing_date = ?";
            $params[] = $data['filing_date'];
        }
        
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        
        if (isset($data['tags'])) {
            $fields[] = "tags = ?";
            $params[] = $data['tags'];
        }
        
        if (isset($data['reference_number'])) {
            $fields[] = "reference_number = ?";
            $params[] = $data['reference_number'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE ip_records SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Archive IP record
     */
    public function archive($id) {
        $sql = "UPDATE ip_records SET is_archived = 1, archived_at = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Delete IP record permanently
     */
    public function delete($id) {
        $sql = "DELETE FROM ip_records WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Search IP records using fulltext
     */
    public function search($keyword, $page = 1, $perPage = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT ir.*, it.type_name, u.full_name as created_by_name,
                (SELECT COUNT(*) FROM ip_documents WHERE ip_record_id = ir.id AND is_deleted = 0) as document_count
                FROM ip_records ir
                INNER JOIN ip_types it ON ir.ip_type_id = it.id
                INNER JOIN users u ON ir.created_by = u.id
                WHERE ir.is_archived = 0
                AND (ir.title LIKE ? OR ir.description LIKE ? OR ir.tags LIKE ? OR ir.owner LIKE ? OR ir.reference_number LIKE ?)
                ORDER BY ir.created_at DESC
                LIMIT ? OFFSET ?";
        
        $searchTerm = '%' . $keyword . '%';
        
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
    }
    
    /**
     * Get all IP types
     */
    public function getAllTypes() {
        $sql = "SELECT * FROM ip_types ORDER BY type_name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        $sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                COUNT(CASE WHEN status = 'expired' THEN 1 END) as expired
                FROM ip_records 
                WHERE is_archived = 0";
        
        return $this->db->fetch($sql);
    }
}
