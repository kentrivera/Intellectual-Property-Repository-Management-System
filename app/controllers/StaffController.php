<?php
/**
 * Staff Controller
 * Handles staff/viewer operations
 */

require_once APP_PATH . '/models/IPRecord.php';
require_once APP_PATH . '/models/Document.php';
require_once APP_PATH . '/models/DownloadRequest.php';
require_once APP_PATH . '/models/ActivityLog.php';

class StaffController extends Controller {
    private $ipRecordModel;
    private $documentModel;
    private $downloadRequestModel;
    private $activityLog;
    
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        
        $this->ipRecordModel = new IPRecord();
        $this->documentModel = new Document();
        $this->downloadRequestModel = new DownloadRequest();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Staff Dashboard
     */
    public function dashboard() {
        // Get statistics
        $stats = [
            'total_records' => $this->ipRecordModel->getCount(),
            'total_documents' => $this->documentModel->getCount(),
            'my_requests' => $this->downloadRequestModel->getCount(['requested_by' => $this->getCurrentUserId()]),
            'pending_requests' => $this->downloadRequestModel->getCount([
                'requested_by' => $this->getCurrentUserId(),
                'status' => 'pending'
            ])
        ];
        
        // Get my recent requests
        $myRequests = $this->downloadRequestModel->getAll(1, 5, [
            'requested_by' => $this->getCurrentUserId()
        ]);
        
        $this->view('staff/dashboard', [
            'stats' => $stats,
            'my_requests' => $myRequests,
            'page_title' => 'Dashboard'
        ]);
    }
    
    /**
     * Browse IP Records
     */
    public function ipRecords() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($type) $filters['ip_type_id'] = $type;
        if ($status) $filters['status'] = $status;
        
        $records = $this->ipRecordModel->getAll($page, RECORDS_PER_PAGE, $filters);
        $totalRecords = $this->ipRecordModel->getCount($filters);
        $totalPages = ceil($totalRecords / RECORDS_PER_PAGE);
        
        $ipTypes = $this->ipRecordModel->getAllTypes();
        
        $this->view('staff/ip-records', [
            'records' => $records,
            'ip_types' => $ipTypes,
            'search' => $search,
            'type_filter' => $type,
            'status_filter' => $status,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'page_title' => 'IP Records'
        ]);
    }
    
    /**
     * View single IP Record
     */
    public function viewIPRecord($id) {
        $record = $this->ipRecordModel->findById($id);
        
        if (!$record) {
            $this->redirect('/staff/ip-records');
        }
        
        $documents = $this->documentModel->getByIPRecord($id);
        
        // Check existing requests for each document
        foreach ($documents as &$doc) {
            $doc['has_pending'] = $this->downloadRequestModel->hasPendingRequest($doc['id'], $this->getCurrentUserId());
            $doc['active_request'] = $this->downloadRequestModel->hasActiveApprovedRequest($doc['id'], $this->getCurrentUserId());
        }
        
        // Log view activity
        $this->activityLog->log([
            'user_id' => $this->getCurrentUserId(),
            'action_type' => 'view',
            'entity_type' => 'ip_record',
            'entity_id' => $id,
            'description' => "Viewed IP record: {$record['title']}"
        ]);
        
        $this->view('staff/view-ip-record', [
            'record' => $record,
            'documents' => $documents,
            'page_title' => $record['title'],
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Search Documents
     */
    public function search() {
        $keyword = $_GET['q'] ?? '';
        $page = $_GET['page'] ?? 1;
        
        $results = [];
        $totalPages = 0;
        
        if ($keyword) {
            // Search in both IP records and documents
            $ipResults = $this->ipRecordModel->search($keyword, $page, RECORDS_PER_PAGE);
            $docResults = $this->documentModel->search($keyword, $page, RECORDS_PER_PAGE);
            
            $results = [
                'ip_records' => $ipResults,
                'documents' => $docResults
            ];
        }
        
        $this->view('staff/search', [
            'keyword' => $keyword,
            'results' => $results,
            'current_page' => $page,
            'page_title' => 'Search Results'
        ]);
    }
    
    /**
     * My Download Requests
     */
    public function myRequests() {
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        
        $filters = ['requested_by' => $this->getCurrentUserId()];
        if ($status) $filters['status'] = $status;
        
        $requests = $this->downloadRequestModel->getAll($page, RECORDS_PER_PAGE, $filters);
        $totalRequests = $this->downloadRequestModel->getCount($filters);
        $totalPages = ceil($totalRequests / RECORDS_PER_PAGE);
        
        $this->view('staff/my-requests', [
            'requests' => $requests,
            'status_filter' => $status,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'page_title' => 'My Download Requests'
        ]);
    }
    
    /**
     * Request Download Permission
     */
    public function requestDownload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentId = $_POST['document_id'] ?? 0;
            $reason = $this->sanitize($_POST['reason'] ?? '');
            
            $document = $this->documentModel->findById($documentId);
            if (!$document) {
                $this->json(['success' => false, 'message' => 'Document not found']);
            }
            
            // Check if already has pending request
            if ($this->downloadRequestModel->hasPendingRequest($documentId, $this->getCurrentUserId())) {
                $this->json(['success' => false, 'message' => 'You already have a pending request for this document']);
            }
            
            // Check if has active approved request
            if ($this->downloadRequestModel->hasActiveApprovedRequest($documentId, $this->getCurrentUserId())) {
                $this->json(['success' => false, 'message' => 'You already have an active approved request for this document']);
            }
            
            $requestId = $this->downloadRequestModel->create([
                'document_id' => $documentId,
                'requested_by' => $this->getCurrentUserId(),
                'request_reason' => $reason
            ]);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'request_download',
                'entity_type' => 'download_request',
                'entity_id' => $requestId,
                'description' => "Requested download permission for document: {$document['original_name']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'Download request submitted successfully']);
        }
    }
}
