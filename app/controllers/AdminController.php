<?php
/**
 * Admin Controller
 * Handles all admin operations
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/IPRecord.php';
require_once APP_PATH . '/models/Document.php';
require_once APP_PATH . '/models/DownloadRequest.php';
require_once APP_PATH . '/models/ActivityLog.php';
require_once APP_PATH . '/models/Folder.php';

// Define constants if not already defined
if (!defined('RECORDS_PER_PAGE')) {
    define('RECORDS_PER_PAGE', 20);
}
if (!defined('DEFAULT_DOWNLOAD_LIMIT')) {
    define('DEFAULT_DOWNLOAD_LIMIT', 5);
}
if (!defined('TOKEN_EXPIRY_HOURS')) {
    define('TOKEN_EXPIRY_HOURS', 24);
}

class AdminController extends Controller {
    private $userModel;
    private $ipRecordModel;
    private $documentModel;
    private $downloadRequestModel;
    private $activityLog;
    private $folderModel;
    private $documentFileModel;
    
    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
        
        $this->userModel = new User();
        $this->ipRecordModel = new IPRecord();
        $this->documentModel = new Document();
        $this->downloadRequestModel = new DownloadRequest();
        $this->activityLog = new ActivityLog();
        $this->folderModel = new Folder();
        
        // Initialize DocumentFile model if it exists
        if (file_exists(APP_PATH . '/models/DocumentFile.php')) {
            require_once APP_PATH . '/models/DocumentFile.php';
            $this->documentFileModel = new DocumentFile();
        }
    }
    
    /**
     * Admin Dashboard
     */
    public function dashboard() {
        // Get statistics
        $stats = [
            'total_users' => $this->userModel->getActiveCount(),
            'total_records' => $this->ipRecordModel->getCount(),
            'total_documents' => $this->documentModel->getCount(),
            'pending_requests' => $this->downloadRequestModel->getPendingCount(),
            'ip_stats' => $this->ipRecordModel->getStatistics()
        ];
        
        // Get recent activity
        $recentActivity = $this->activityLog->getRecent(10);
        
        // Get pending download requests
        $pendingRequests = $this->downloadRequestModel->getAll(1, 5, ['status' => 'pending']);
        
        $this->view('admin/dashboard', [
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'pending_requests' => $pendingRequests,
            'page_title' => 'Admin Dashboard'
        ]);
    }
    
    /**
     * Users Management - List
     */
    public function users() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($role) $filters['role'] = $role;
        
        $users = $this->userModel->getAll($filters);
        
        $this->view('admin/users', [
            'users' => $users,
            'search' => $search,
            'role_filter' => $role,
            'page_title' => 'User Management',
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Create User
     */
    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $this->sanitize($_POST['username']),
                'email' => $this->sanitize($_POST['email']),
                'full_name' => $this->sanitize($_POST['full_name']),
                'password' => $_POST['password'],
                'role' => $this->sanitize($_POST['role']),
                'status' => 'active'
            ];
            
            // Validate
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                $this->json(['success' => false, 'message' => 'All fields are required']);
            }
            
            // Check if username exists
            if ($this->userModel->findByUsername($data['username'])) {
                $this->json(['success' => false, 'message' => 'Username already exists']);
            }
            
            // Check if email exists
            if ($this->userModel->findByEmail($data['email'])) {
                $this->json(['success' => false, 'message' => 'Email already exists']);
            }
            
            try {
                $userId = $this->userModel->create($data);
                
                // Log activity
                $username = $data['username'];
                $this->activityLog->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action_type' => 'user_create',
                    'entity_type' => 'user',
                    'entity_id' => $userId,
                    'description' => "Created new user: " . $username
                ]);
                
                $this->json(['success' => true, 'message' => 'User created successfully']);
            } catch (Exception $e) {
                $this->json(['success' => false, 'message' => 'Failed to create user']);
            }
        }
    }
    
    /**
     * Update User Status
     */
    public function updateUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            if (!in_array($status, ['active', 'inactive'])) {
                $this->json(['success' => false, 'message' => 'Invalid status']);
            }
            
            $user = $this->userModel->findById($userId);
            if (!$user) {
                $this->json(['success' => false, 'message' => 'User not found']);
            }
            
            $this->userModel->update($userId, ['status' => $status]);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'user_update',
                'entity_type' => 'user',
                'entity_id' => $userId,
                'description' => 'Changed user ' . $user['username'] . ' status to ' . $status
            ]);
            
            $this->json(['success' => true, 'message' => 'User status updated']);
        }
    }
    
    /**
     * Delete User
     */
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            
            $user = $this->userModel->findById($userId);
            if (!$user) {
                $this->json(['success' => false, 'message' => 'User not found']);
            }
            
            // Prevent deleting self
            if ($userId == $this->getCurrentUserId()) {
                $this->json(['success' => false, 'message' => 'You cannot delete your own account']);
            }
            
            $this->userModel->delete($userId);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'user_delete',
                'entity_type' => 'user',
                'entity_id' => $userId,
                'description' => "Deleted user: {$user['username']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'User deleted successfully']);
        }
    }
    
    /**
     * IP Records Management - Enhanced with folder support
     */
    public function ipRecords() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $status = $_GET['status'] ?? '';
        $folderId = $_GET['folder_id'] ?? null;
        $folderType = $_GET['folder_type'] ?? null;
        
        // Build filters
        $filters = [];
        if ($search) $filters['search'] = $search;
        if ($type) $filters['ip_type_id'] = $type;
        if ($status) $filters['status'] = $status;
        if ($folderId) $filters['folder_id'] = $folderId;
        
        // Get records based on current context
        $records = $this->ipRecordModel->getAll($page, RECORDS_PER_PAGE, $filters);
        $totalRecords = $this->ipRecordModel->getCount($filters);
        $totalPages = ceil($totalRecords / RECORDS_PER_PAGE);
        
        // Get IP types for filters
        $ipTypes = $this->ipRecordModel->getAllTypes();
        
        // Get folders (both system and custom folders)
        $folders = $this->folderModel->getSubFolders(null); // Get root folders
        
        // Get folder item counts and enhance folder data
        foreach ($folders as &$folder) {
            $folderFilters = ['folder_id' => $folder['id']];
            $folder['item_count'] = $this->ipRecordModel->getCount($folderFilters);
        }
        unset($folder);
        
        // Get comprehensive statistics
        $stats = [
            'patent_count' => $this->ipRecordModel->getCount(['ip_type_id' => 1]),
            'trademark_count' => $this->ipRecordModel->getCount(['ip_type_id' => 2]),
            'copyright_count' => $this->ipRecordModel->getCount(['ip_type_id' => 3]),
            'design_count' => $this->ipRecordModel->getCount(['ip_type_id' => 4]),
            'archived_count' => $this->ipRecordModel->getCount(['status' => 'archived']),
            'recent_count' => $this->ipRecordModel->getCount(['recent' => true]),
            'total_records' => $totalRecords,
            'total_files' => 0 // Default value, will be updated if DocumentFile model is available
        ];
        
        // Get current folder information if in folder context
        $currentFolderData = null;
        if ($folderId) {
            $currentFolderData = $this->folderModel->findById($folderId);
            if ($currentFolderData) {
                // Get additional folder statistics
                $folderStats = $this->getFolderStatistics($folderId);
                $stats = array_merge($stats, $folderStats);
            }
        }
        
        $this->view('admin/ip-records', [
            'records' => $records,
            'ip_types' => $ipTypes,
            'folders' => $folders,
            'current_folder' => $currentFolderData,
            'stats' => $stats,
            'search' => $search,
            'type_filter' => $type,
            'status_filter' => $status,
            'folder_id' => $folderId,
            'folder_type' => $folderType,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'page_title' => $currentFolderData ? $currentFolderData['name'] : 'IP Records Management',
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Get folder statistics
     */
    private function getFolderStatistics($folderId) {
        // Check if DocumentFile model is available and initialized
        if (!$this->documentFileModel) {
            return [
                'total_files' => 0,
                'total_size' => 0,
                'file_types' => []
            ];
        }
        
        try {
            return $this->documentFileModel->getStatistics($folderId);
        } catch (Exception $e) {
            error_log("Error getting folder statistics: " . $e->getMessage());
            return [
                'total_files' => 0,
                'total_size' => 0,
                'file_types' => []
            ];
        }
    }
    
    /**
     * View single IP Record
     */
    public function viewIPRecord($id) {
        $record = $this->ipRecordModel->findById($id);
        
        if (!$record) {
            $this->redirect('/admin/ip-records');
        }
        
        $documents = $this->documentModel->getByIPRecord($id);
        $downloadRequests = $this->downloadRequestModel->getAll(1, 10, ['document_id' => $id]);
        
        // Log view activity
        $this->activityLog->log([
            'user_id' => $this->getCurrentUserId(),
            'action_type' => 'view',
            'entity_type' => 'ip_record',
            'entity_id' => $id,
            'description' => "Viewed IP record: {$record['title']}"
        ]);
        
        $this->view('admin/view-ip-record', [
            'record' => $record,
            'documents' => $documents,
            'download_requests' => $downloadRequests,
            'page_title' => $record['title'],
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Download Requests Management
     */
    public function downloadRequests() {
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        
        $filters = [];
        if ($status) $filters['status'] = $status;
        
        $requests = $this->downloadRequestModel->getAll($page, RECORDS_PER_PAGE, $filters);
        $totalRequests = $this->downloadRequestModel->getCount($filters);
        $totalPages = ceil($totalRequests / RECORDS_PER_PAGE);
        
        $this->view('admin/download-requests', [
            'requests' => $requests,
            'status_filter' => $status,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'page_title' => 'Download Requests',
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Approve Download Request
     */
    public function approveRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestId = $_POST['request_id'] ?? 0;
            $downloadLimit = $_POST['download_limit'] ?? DEFAULT_DOWNLOAD_LIMIT;
            $expiryHours = $_POST['expiry_hours'] ?? TOKEN_EXPIRY_HOURS;
            
            $request = $this->downloadRequestModel->findById($requestId);
            if (!$request) {
                $this->json(['success' => false, 'message' => 'Request not found']);
            }
            
            $token = $this->downloadRequestModel->approve($requestId, $this->getCurrentUserId(), $downloadLimit, $expiryHours);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'approve_download',
                'entity_type' => 'download_request',
                'entity_id' => $requestId,
                'description' => "Approved download request for document: {$request['document_name']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'Request approved successfully']);
        }
    }
    
    /**
     * Reject Download Request
     */
    public function rejectRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $requestId = $_POST['request_id'] ?? 0;
            $reviewNotes = $this->sanitize($_POST['review_notes'] ?? '');
            
            $request = $this->downloadRequestModel->findById($requestId);
            if (!$request) {
                $this->json(['success' => false, 'message' => 'Request not found']);
            }
            
            $this->downloadRequestModel->reject($requestId, $this->getCurrentUserId(), $reviewNotes);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'reject_download',
                'entity_type' => 'download_request',
                'entity_id' => $requestId,
                'description' => "Rejected download request for document: {$request['document_name']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'Request rejected']);
        }
    }
    
    /**
     * Trash Bin
     */
    public function trashBin() {
        $trashedDocuments = $this->documentModel->getTrashed();
        $trashedRecords = method_exists($this->ipRecordModel, 'getTrashed')
            ? $this->ipRecordModel->getTrashed()
            : [];

        $trashedFolders = method_exists($this->folderModel, 'getTrashed')
            ? $this->folderModel->getTrashed()
            : [];

        $trashedFiles = ($this->documentFileModel && method_exists($this->documentFileModel, 'getTrashed'))
            ? $this->documentFileModel->getTrashed()
            : [];

        $this->view('admin/trash', [
            'trashedDocuments' => $trashedDocuments,
            'trashedRecords' => $trashedRecords,
            'trashedFolders' => $trashedFolders,
            'trashedFiles' => $trashedFiles,
            'page_title' => 'Trash Bin',
            'csrf_token' => $this->generateCSRF()
        ]);
    }

    /**
     * Empty Trash Bin (permanent delete everything in trash)
     */
    public function emptyTrash() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            // IP documents
            $docs = $this->documentModel->getTrashed();
            foreach ($docs as $doc) {
                // Delete all versions files
                if (method_exists($this->documentModel, 'getVersions')) {
                    $versions = $this->documentModel->getVersions((int)$doc['id']);
                    foreach ($versions as $version) {
                        if (!empty($version['file_path']) && file_exists($version['file_path'])) {
                            @unlink($version['file_path']);
                        }
                    }
                }

                // Delete file in trash (if moved there)
                if (defined('TRASH_PATH') && !empty($doc['file_path'])) {
                    $trashPath = TRASH_PATH . '/' . basename($doc['file_path']);
                    if (file_exists($trashPath)) {
                        @unlink($trashPath);
                    }
                }

                // Delete file in original path (fallback)
                if (!empty($doc['file_path']) && file_exists($doc['file_path'])) {
                    @unlink($doc['file_path']);
                }

                $this->documentModel->permanentDelete((int)$doc['id']);
            }

            // IP records (archived)
            if (method_exists($this->ipRecordModel, 'getTrashed') && method_exists($this->ipRecordModel, 'permanentDelete')) {
                $records = $this->ipRecordModel->getTrashed();
                foreach ($records as $record) {
                    $this->ipRecordModel->permanentDelete((int)$record['id']);
                }
            }

            // Folder-based files
            if ($this->documentFileModel && method_exists($this->documentFileModel, 'getTrashed') && method_exists($this->documentFileModel, 'permanentDelete')) {
                $files = $this->documentFileModel->getTrashed();
                foreach ($files as $file) {
                    $this->documentFileModel->permanentDelete((int)$file['id']);
                }
            }

            // Archived folders
            if (method_exists($this->folderModel, 'getTrashed') && method_exists($this->folderModel, 'permanentDeleteRecursive')) {
                $folders = $this->folderModel->getTrashed();
                foreach ($folders as $folder) {
                    $this->folderModel->permanentDeleteRecursive((int)$folder['id'], $this->documentFileModel);
                }
            }

            $this->json(['success' => true, 'message' => 'Trash emptied']);
        } catch (Exception $e) {
            error_log('Empty trash error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to empty trash']);
        }
    }
    
    /**
     * Activity Logs
     */
    public function activityLogs() {
        $page = $_GET['page'] ?? 1;
        $actionType = $_GET['action_type'] ?? '';
        $userId = $_GET['user_id'] ?? '';
        
        $filters = [];
        if ($actionType) $filters['action_type'] = $actionType;
        if ($userId) $filters['user_id'] = $userId;
        
        $logs = $this->activityLog->getAll($page, RECORDS_PER_PAGE, $filters);
        $totalLogs = $this->activityLog->getCount($filters);
        $totalPages = ceil($totalLogs / RECORDS_PER_PAGE);
        
        $this->view('admin/activity-logs', [
            'logs' => $logs,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'action_filter' => $actionType,
            'user_filter' => $userId,
            'page_title' => 'Activity Logs'
        ]);
    }
    
    /**
     * Create new folder
     */
    public function createFolder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $folderName = $this->sanitize($_POST['name'] ?? '');
            $parentId = $_POST['parent_id'] ?? null;
            
            if (empty($folderName)) {
                $this->json(['success' => false, 'message' => 'Folder name is required']);
                return;
            }
            
            try {
                $folderId = $this->folderModel->create([
                    'name' => $folderName,
                    'parent_id' => $parentId,
                    'created_by' => $this->getCurrentUserId(),
                    'type' => 'custom'
                ]);
                
                if ($folderId) {
                    // Log activity
                    $this->activityLog->log([
                        'user_id' => $this->getCurrentUserId(),
                        'action_type' => 'folder_create',
                        'entity_type' => 'folder',
                        'entity_id' => $folderId,
                        'description' => "Created folder: $folderName"
                    ]);
                    
                    $this->json(['success' => true, 'message' => 'Folder created successfully', 'folder_id' => $folderId]);
                } else {
                    $this->json(['success' => false, 'message' => 'Failed to create folder']);
                }
            } catch (Exception $e) {
                error_log("Folder creation error: " . $e->getMessage());
                $this->json(['success' => false, 'message' => 'An error occurred while creating the folder']);
            }
        }
    }
    
    /**
     * Rename folder
     */
    public function renameFolder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $folderId = $_POST['id'] ?? 0;
            $newName = $this->sanitize($_POST['name'] ?? '');
            
            if (empty($newName)) {
                $this->json(['success' => false, 'message' => 'Folder name is required']);
                return;
            }
            
            $folder = $this->folderModel->findById($folderId);
            if (!$folder) {
                $this->json(['success' => false, 'message' => 'Folder not found']);
                return;
            }
            
            // Check if user has permission to rename this folder
            if ($folder['created_by'] != $this->getCurrentUserId() && $_SESSION['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Permission denied']);
                return;
            }
            
            try {
                $success = $this->folderModel->update($folderId, ['name' => $newName]);
                
                if ($success) {
                    // Log activity
                    $this->activityLog->log([
                        'user_id' => $this->getCurrentUserId(),
                        'action_type' => 'folder_rename',
                        'entity_type' => 'folder',
                        'entity_id' => $folderId,
                        'description' => "Renamed folder from '{$folder['name']}' to '$newName'"
                    ]);
                    
                    $this->json(['success' => true, 'message' => 'Folder renamed successfully']);
                } else {
                    $this->json(['success' => false, 'message' => 'Failed to rename folder']);
                }
            } catch (Exception $e) {
                error_log("Folder rename error: " . $e->getMessage());
                $this->json(['success' => false, 'message' => 'An error occurred while renaming the folder']);
            }
        }
    }
    
    /**
     * Archive/Delete folder
     */
    public function archiveFolder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $folderId = $_POST['id'] ?? 0;
            
            $folder = $this->folderModel->findById($folderId);
            if (!$folder) {
                $this->json(['success' => false, 'message' => 'Folder not found']);
                return;
            }
            
            // Check permissions
            if ($folder['created_by'] != $this->getCurrentUserId() && $_SESSION['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Permission denied']);
                return;
            }
            
            try {
                $success = $this->folderModel->archive($folderId, $this->getCurrentUserId());
                
                if ($success) {
                    // Log activity
                    $this->activityLog->log([
                        'user_id' => $this->getCurrentUserId(),
                        'action_type' => 'folder_archive',
                        'entity_type' => 'folder',
                        'entity_id' => $folderId,
                        'description' => "Archived folder: {$folder['name']}"
                    ]);
                    
                    $this->json(['success' => true, 'message' => 'Folder archived successfully']);
                } else {
                    $this->json(['success' => false, 'message' => 'Failed to archive folder']);
                }
            } catch (Exception $e) {
                error_log("Folder archive error: " . $e->getMessage());
                $this->json(['success' => false, 'message' => 'An error occurred while archiving the folder']);
            }
        }
    }
}
