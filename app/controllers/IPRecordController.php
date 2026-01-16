<?php
/**
 * IP Record Controller
 * Handles IP Record CRUD operations
 */

require_once APP_PATH . '/models/IPRecord.php';
require_once APP_PATH . '/models/ActivityLog.php';

class IPRecordController extends Controller {
    private $ipRecordModel;
    private $activityLog;
    
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
        
        $this->ipRecordModel = new IPRecord();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Create IP Record
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'ip_type_id' => $_POST['ip_type_id'] ?? 0,
                'title' => $this->sanitize($_POST['title'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'owner' => $this->sanitize($_POST['owner'] ?? ''),
                'filing_date' => $_POST['filing_date'] ?? null,
                'status' => $_POST['status'] ?? 'pending',
                'tags' => $this->sanitize($_POST['tags'] ?? ''),
                'reference_number' => $this->sanitize($_POST['reference_number'] ?? ''),
                'created_by' => $this->getCurrentUserId()
            ];
            
            // Validate
            if (empty($data['title']) || empty($data['owner']) || empty($data['ip_type_id'])) {
                $this->json(['success' => false, 'message' => 'Title, Owner, and IP Type are required']);
            }
            
            try {
                $recordId = $this->ipRecordModel->create($data);
                
                // Log activity
                $this->activityLog->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action_type' => 'create',
                    'entity_type' => 'ip_record',
                    'entity_id' => $recordId,
                    'description' => "Created IP record: {$data['title']}"
                ]);
                
                $this->json(['success' => true, 'message' => 'IP Record created successfully', 'id' => $recordId]);
            } catch (Exception $e) {
                $this->json(['success' => false, 'message' => 'Failed to create IP record']);
            }
        }
    }
    
    /**
     * Update IP Record
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recordId = $_POST['record_id'] ?? 0;
            
            $record = $this->ipRecordModel->findById($recordId);
            if (!$record) {
                $this->json(['success' => false, 'message' => 'Record not found']);
            }
            
            $data = [
                'ip_type_id' => $_POST['ip_type_id'] ?? $record['ip_type_id'],
                'title' => $this->sanitize($_POST['title'] ?? $record['title']),
                'description' => $this->sanitize($_POST['description'] ?? $record['description']),
                'owner' => $this->sanitize($_POST['owner'] ?? $record['owner']),
                'filing_date' => $_POST['filing_date'] ?? $record['filing_date'],
                'status' => $_POST['status'] ?? $record['status'],
                'tags' => $this->sanitize($_POST['tags'] ?? $record['tags']),
                'reference_number' => $this->sanitize($_POST['reference_number'] ?? $record['reference_number'])
            ];
            
            try {
                $this->ipRecordModel->update($recordId, $data);
                
                // Log activity
                $this->activityLog->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action_type' => 'update',
                    'entity_type' => 'ip_record',
                    'entity_id' => $recordId,
                    'description' => "Updated IP record: {$data['title']}"
                ]);
                
                $this->json(['success' => true, 'message' => 'IP Record updated successfully']);
            } catch (Exception $e) {
                $this->json(['success' => false, 'message' => 'Failed to update IP record']);
            }
        }
    }
    
    /**
     * Archive IP Record
     */
    public function archive() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recordId = $_POST['record_id'] ?? 0;
            
            $record = $this->ipRecordModel->findById($recordId);
            if (!$record) {
                $this->json(['success' => false, 'message' => 'Record not found']);
            }
            
            $this->ipRecordModel->archive($recordId);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'archive',
                'entity_type' => 'ip_record',
                'entity_id' => $recordId,
                'description' => "Archived IP record: {$record['title']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'IP Record archived successfully']);
        }
    }
    
    /**
     * Delete IP Record
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recordId = $_POST['record_id'] ?? 0;
            
            $record = $this->ipRecordModel->findById($recordId);
            if (!$record) {
                $this->json(['success' => false, 'message' => 'Record not found']);
            }
            
            $this->ipRecordModel->delete($recordId);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'delete',
                'entity_type' => 'ip_record',
                'entity_id' => $recordId,
                'description' => "Deleted IP record: {$record['title']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'IP Record deleted successfully']);
        }
    }
}
