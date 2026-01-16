<?php
/**
 * Download Request Model
 * Handles document download permission requests
 */

class DownloadRequest {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create download request
     */
    public function create($data) {
        $sql = "INSERT INTO download_requests 
                (document_id, requested_by, request_reason, status) 
                VALUES (?, ?, ?, 'pending')";
        
        return $this->db->insert($sql, [
            $data['document_id'],
            $data['requested_by'],
            $data['request_reason'] ?? null
        ]);
    }
    
    /**
     * Get request by ID
     */
    public function findById($id) {
        $sql = "SELECT dr.*, 
                d.original_name as document_name, d.file_type,
                ir.title as ip_title,
                u1.full_name as requested_by_name, u1.email as requester_email,
                u2.full_name as reviewed_by_name
                FROM download_requests dr
                INNER JOIN ip_documents d ON dr.document_id = d.id
                INNER JOIN ip_records ir ON d.ip_record_id = ir.id
                INNER JOIN users u1 ON dr.requested_by = u1.id
                LEFT JOIN users u2 ON dr.reviewed_by = u2.id
                WHERE dr.id = ?
                LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Get all requests with filters
     */
    public function getAll($page = 1, $perPage = RECORDS_PER_PAGE, $filters = []) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT dr.*, 
                d.original_name as document_name,
                ir.title as ip_title,
                u1.full_name as requested_by_name,
                u2.full_name as reviewed_by_name
                FROM download_requests dr
                INNER JOIN ip_documents d ON dr.document_id = d.id
                INNER JOIN ip_records ir ON d.ip_record_id = ir.id
                INNER JOIN users u1 ON dr.requested_by = u1.id
                LEFT JOIN users u2 ON dr.reviewed_by = u2.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND dr.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['requested_by'])) {
            $sql .= " AND dr.requested_by = ?";
            $params[] = $filters['requested_by'];
        }
        
        if (!empty($filters['document_id'])) {
            $sql .= " AND dr.document_id = ?";
            $params[] = $filters['document_id'];
        }
        
        $sql .= " ORDER BY dr.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get total count
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM download_requests WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['requested_by'])) {
            $sql .= " AND requested_by = ?";
            $params[] = $filters['requested_by'];
        }
        
        if (!empty($filters['document_id'])) {
            $sql .= " AND document_id = ?";
            $params[] = $filters['document_id'];
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
    
    /**
     * Approve request
     */
    public function approve($id, $reviewedBy, $downloadLimit = DEFAULT_DOWNLOAD_LIMIT, $expiryHours = TOKEN_EXPIRY_HOURS) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
        
        $sql = "UPDATE download_requests 
                SET status = 'approved', 
                    reviewed_by = ?, 
                    approved_at = NOW(),
                    download_token = ?,
                    token_expires_at = ?,
                    download_limit = ?
                WHERE id = ?";
        
        $result = $this->db->execute($sql, [$reviewedBy, $token, $expiresAt, $downloadLimit, $id]);
        
        return $result ? $token : false;
    }
    
    /**
     * Reject request
     */
    public function reject($id, $reviewedBy, $reviewNotes = null) {
        $sql = "UPDATE download_requests 
                SET status = 'rejected', 
                    reviewed_by = ?, 
                    rejected_at = NOW(),
                    review_notes = ?
                WHERE id = ?";
        
        return $this->db->execute($sql, [$reviewedBy, $reviewNotes, $id]);
    }
    
    /**
     * Find request by token
     */
    public function findByToken($token) {
        $sql = "SELECT dr.*, d.file_path, d.original_name, d.file_type
                FROM download_requests dr
                INNER JOIN ip_documents d ON dr.document_id = d.id
                WHERE dr.download_token = ? 
                AND dr.status = 'approved'
                AND dr.token_expires_at > NOW()
                AND dr.download_count < dr.download_limit
                LIMIT 1";
        
        return $this->db->fetch($sql, [$token]);
    }
    
    /**
     * Increment download count
     */
    public function incrementDownloadCount($id) {
        $sql = "UPDATE download_requests SET download_count = download_count + 1 WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Check if user has pending request for document
     */
    public function hasPendingRequest($documentId, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM download_requests 
                WHERE document_id = ? AND requested_by = ? AND status = 'pending'";
        
        $result = $this->db->fetch($sql, [$documentId, $userId]);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Check if user has active approved request
     */
    public function hasActiveApprovedRequest($documentId, $userId) {
        $sql = "SELECT * FROM download_requests 
                WHERE document_id = ? 
                AND requested_by = ? 
                AND status = 'approved'
                AND token_expires_at > NOW()
                AND download_count < download_limit
                LIMIT 1";
        
        return $this->db->fetch($sql, [$documentId, $userId]);
    }
    
    /**
     * Get pending requests count
     */
    public function getPendingCount() {
        $sql = "SELECT COUNT(*) as count FROM download_requests WHERE status = 'pending'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Log download
     */
    public function logDownload($requestId, $documentId, $userId, $token) {
        $sql = "INSERT INTO download_logs 
                (request_id, document_id, user_id, download_token, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $requestId,
            $documentId,
            $userId,
            $token,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Get download logs
     */
    public function getDownloadLogs($filters = [], $limit = 50) {
        $sql = "SELECT dl.*, 
                d.original_name as document_name,
                u.full_name as user_name
                FROM download_logs dl
                INNER JOIN ip_documents d ON dl.document_id = d.id
                INNER JOIN users u ON dl.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND dl.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['document_id'])) {
            $sql .= " AND dl.document_id = ?";
            $params[] = $filters['document_id'];
        }
        
        $sql .= " ORDER BY dl.downloaded_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
}
