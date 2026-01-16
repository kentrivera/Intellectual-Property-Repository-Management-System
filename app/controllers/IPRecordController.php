<?php
/**
 * IP Record Controller
 * Handles IP Record CRUD operations
 */

require_once APP_PATH . '/models/IPRecord.php';
require_once APP_PATH . '/models/Document.php';
require_once APP_PATH . '/models/ActivityLog.php';

class IPRecordController extends Controller {
    private $ipRecordModel;
    private $documentModel;
    private $activityLog;
    
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
        
        $this->ipRecordModel = new IPRecord();
        $this->documentModel = new Document();
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

    /**
     * Restore IP Record from trash (un-archive)
     */
    public function restore() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recordId = (int)($_POST['record_id'] ?? 0);
            if ($recordId <= 0) {
                $this->json(['success' => false, 'message' => 'Invalid record id']);
            }

            $ok = $this->ipRecordModel->restore($recordId);
            if ($ok) {
                $this->activityLog->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action_type' => 'restore',
                    'entity_type' => 'ip_record',
                    'entity_id' => $recordId,
                    'description' => "Restored IP record from trash: ID {$recordId}"
                ]);
            }

            $this->json(['success' => (bool)$ok, 'message' => $ok ? 'IP Record restored successfully' : 'Failed to restore IP Record']);
        }
    }

    /**
     * Permanently delete IP Record and its documents
     */
    public function permanentDelete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $recordId = (int)($_POST['record_id'] ?? 0);
            if ($recordId <= 0) {
                $this->json(['success' => false, 'message' => 'Invalid record id']);
            }

            try {
                // Best-effort delete physical files for all documents (including deleted)
                $docs = $this->documentModel->getByIPRecord($recordId, true);
                foreach ($docs as $doc) {
                    // versions
                    if (method_exists($this->documentModel, 'getVersions')) {
                        $versions = $this->documentModel->getVersions((int)$doc['id']);
                        foreach ($versions as $version) {
                            if (!empty($version['file_path']) && file_exists($version['file_path'])) {
                                @unlink($version['file_path']);
                            }
                        }
                    }

                    // trash-path variant
                    if (defined('TRASH_PATH') && !empty($doc['file_path'])) {
                        $trashPath = TRASH_PATH . '/' . basename($doc['file_path']);
                        if (file_exists($trashPath)) {
                            @unlink($trashPath);
                        }
                    }

                    // original-path fallback
                    if (!empty($doc['file_path']) && file_exists($doc['file_path'])) {
                        @unlink($doc['file_path']);
                    }
                }

                $ok = $this->ipRecordModel->permanentDelete($recordId);

                if ($ok) {
                    $this->activityLog->log([
                        'user_id' => $this->getCurrentUserId(),
                        'action_type' => 'permanent_delete',
                        'entity_type' => 'ip_record',
                        'entity_id' => $recordId,
                        'description' => "Permanently deleted IP record: ID {$recordId}"
                    ]);
                }

                $this->json(['success' => (bool)$ok, 'message' => $ok ? 'IP Record permanently deleted' : 'Failed to delete IP Record']);
            } catch (Exception $e) {
                error_log('IP record permanent delete error: ' . $e->getMessage());
                $this->json(['success' => false, 'message' => 'Failed to delete IP Record']);
            }
        }
    }
}
