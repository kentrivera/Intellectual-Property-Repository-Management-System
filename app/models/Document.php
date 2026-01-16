<?php
/**
 * Document Model
 * Handles IP document management
 */

class Document {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get documents by IP record
     */
    public function getByIPRecord($ipRecordId, $includeDeleted = false) {
        $sql = "SELECT d.*, u.full_name as uploaded_by_name
                FROM ip_documents d
                INNER JOIN users u ON d.uploaded_by = u.id
                WHERE d.ip_record_id = ?";
        
        if (!$includeDeleted) {
            $sql .= " AND d.is_deleted = 0";
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        
        return $this->db->fetchAll($sql, [$ipRecordId]);
    }
    
    /**
     * Find document by ID
     */
    public function findById($id, $includeDeleted = false) {
        $sql = "SELECT d.*, u.full_name as uploaded_by_name, ir.title as ip_title
                FROM ip_documents d
                INNER JOIN users u ON d.uploaded_by = u.id
                INNER JOIN ip_records ir ON d.ip_record_id = ir.id
                WHERE d.id = ?";
        
        if (!$includeDeleted) {
            $sql .= " AND d.is_deleted = 0";
        }
        
        $sql .= " LIMIT 1";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Upload document
     */
    public function create($data) {
        $sql = "INSERT INTO ip_documents 
                (ip_record_id, file_name, original_name, file_path, file_type, file_size, current_version, uploaded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $data['ip_record_id'],
            $data['file_name'],
            $data['original_name'],
            $data['file_path'],
            $data['file_type'],
            $data['file_size'],
            $data['current_version'] ?? 1,
            $data['uploaded_by']
        ]);
    }
    
    /**
     * Update document
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['current_version'])) {
            $fields[] = "current_version = ?";
            $params[] = $data['current_version'];
        }
        
        if (isset($data['file_name'])) {
            $fields[] = "file_name = ?";
            $params[] = $data['file_name'];
        }
        
        if (isset($data['file_path'])) {
            $fields[] = "file_path = ?";
            $params[] = $data['file_path'];
        }
        
        if (isset($data['file_size'])) {
            $fields[] = "file_size = ?";
            $params[] = $data['file_size'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE ip_documents SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Soft delete document
     */
    public function softDelete($id, $deletedBy) {
        $sql = "UPDATE ip_documents SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?";
        return $this->db->execute($sql, [$deletedBy, $id]);
    }
    
    /**
     * Restore deleted document
     */
    public function restore($id) {
        $sql = "UPDATE ip_documents SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Permanently delete document
     */
    public function permanentDelete($id) {
        $sql = "DELETE FROM ip_documents WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Get all deleted documents (trash)
     */
    public function getTrashed() {
        $sql = "SELECT d.*, u1.full_name as uploaded_by_name, u2.full_name as deleted_by_name, ir.title as ip_title
                FROM ip_documents d
                INNER JOIN users u1 ON d.uploaded_by = u1.id
                LEFT JOIN users u2 ON d.deleted_by = u2.id
                INNER JOIN ip_records ir ON d.ip_record_id = ir.id
                WHERE d.is_deleted = 1
                ORDER BY d.deleted_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Add document version
     */
    public function addVersion($documentId, $data) {
        $sql = "INSERT INTO document_versions 
                (document_id, version_number, file_name, file_path, file_size, uploaded_by, version_notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            $documentId,
            $data['version_number'],
            $data['file_name'],
            $data['file_path'],
            $data['file_size'],
            $data['uploaded_by'],
            $data['version_notes'] ?? null
        ]);
    }
    
    /**
     * Get document versions
     */
    public function getVersions($documentId) {
        $sql = "SELECT v.*, u.full_name as uploaded_by_name
                FROM document_versions v
                INNER JOIN users u ON v.uploaded_by = u.id
                WHERE v.document_id = ?
                ORDER BY v.version_number DESC";
        
        return $this->db->fetchAll($sql, [$documentId]);
    }
    
    /**
     * Get version by number
     */
    public function getVersion($documentId, $versionNumber) {
        $sql = "SELECT * FROM document_versions 
                WHERE document_id = ? AND version_number = ? 
                LIMIT 1";
        
        return $this->db->fetch($sql, [$documentId, $versionNumber]);
    }
    
    /**
     * Search documents by filename
     */
    public function search($keyword, $page = 1, $perPage = RECORDS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT d.*, u.full_name as uploaded_by_name, ir.title as ip_title, it.type_name
                FROM ip_documents d
                INNER JOIN users u ON d.uploaded_by = u.id
                INNER JOIN ip_records ir ON d.ip_record_id = ir.id
                INNER JOIN ip_types it ON ir.ip_type_id = it.id
                WHERE d.is_deleted = 0
                AND (d.file_name LIKE ? OR d.original_name LIKE ? OR ir.title LIKE ? OR ir.tags LIKE ?)
                ORDER BY d.created_at DESC
                LIMIT ? OFFSET ?";
        
        $searchTerm = '%' . $keyword . '%';
        
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
    }
    
    /**
     * Get document count
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM ip_documents WHERE is_deleted = 0";
        $params = [];
        
        if (!empty($filters['ip_record_id'])) {
            $sql .= " AND ip_record_id = ?";
            $params[] = $filters['ip_record_id'];
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get total storage used
     */
    public function getTotalStorageUsed() {
        $sql = "SELECT SUM(file_size) as total FROM ip_documents WHERE is_deleted = 0";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }
}
