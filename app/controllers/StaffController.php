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
        $userId = $this->getCurrentUserId();

        // Stats
        $stats = [
            'total_records' => (int)$this->ipRecordModel->getCount(),
            'total_documents' => (int)$this->documentModel->getCount(),
            'my_requests' => (int)$this->downloadRequestModel->getCount(['requested_by' => $userId]),
            'pending_requests' => (int)$this->downloadRequestModel->getCount([
                'requested_by' => $userId,
                'status' => 'pending'
            ]),
            'approved_requests' => (int)$this->downloadRequestModel->getCount([
                'requested_by' => $userId,
                'status' => 'approved'
            ]),
            'rejected_requests' => (int)$this->downloadRequestModel->getCount([
                'requested_by' => $userId,
                'status' => 'rejected'
            ]),
            'downloaded' => method_exists($this->downloadRequestModel, 'getDownloadedCountByUser')
                ? (int)$this->downloadRequestModel->getDownloadedCountByUser($userId)
                : 0,
            'active_approved' => 0
        ];

        // Active approvals = approved + not expired + remaining downloads
        try {
            $row = $this->db->fetch(
                "SELECT COUNT(*) as c
                 FROM download_requests
                 WHERE requested_by = ?
                   AND status = 'approved'
                   AND token_expires_at IS NOT NULL
                   AND token_expires_at > NOW()
                   AND COALESCE(download_count, 0) < COALESCE(download_limit, 0)",
                [$userId]
            );
            $stats['active_approved'] = (int)($row['c'] ?? 0);
        } catch (Exception $e) {
            $stats['active_approved'] = 0;
        }
        
        // Get my recent requests
        $myRequests = $this->downloadRequestModel->getAll(1, 6, [
            'requested_by' => $userId
        ]);

        // Trend: requests in the last 7 days
        $trend = [];
        $trendMap = [];
        try {
            $since = date('Y-m-d 00:00:00', strtotime('-6 days'));
            $rows = $this->db->fetchAll(
                "SELECT DATE(created_at) as d, COUNT(*) as c
                 FROM download_requests
                 WHERE requested_by = ? AND created_at >= ?
                 GROUP BY DATE(created_at)
                 ORDER BY d ASC",
                [$userId, $since]
            );
            foreach ($rows as $r) {
                $trendMap[(string)$r['d']] = (int)($r['c'] ?? 0);
            }
        } catch (Exception $e) {
            $trendMap = [];
        }

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $trend[] = [
                'date' => $date,
                'count' => (int)($trendMap[$date] ?? 0),
                'label' => date('D', strtotime($date))
            ];
        }

        // Recent activity (this user)
        $recentActivity = $this->activityLog->getByUser($userId, 8);
        
        $this->view('staff/dashboard', [
            'stats' => $stats,
            'my_requests' => $myRequests,
            'trend' => $trend,
            'recent_activity' => $recentActivity,
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
     * Help & Guide
     */
    public function help() {
        $this->view('staff/help', [
            'page_title' => 'Help & Guide'
        ]);
    }
    
    /**
     * My Download Requests
     */
    public function myRequests() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = trim((string)($_GET['status'] ?? ''));
        $q = trim((string)($_GET['q'] ?? ''));

        $filters = ['requested_by' => $this->getCurrentUserId()];
        if ($status !== '') $filters['status'] = $status;
        if ($q !== '') $filters['search'] = $q;

        $requests = $this->downloadRequestModel->getAll($page, RECORDS_PER_PAGE, $filters);
        $totalRequests = (int)$this->downloadRequestModel->getCount($filters);
        $totalPages = max(1, (int)ceil($totalRequests / RECORDS_PER_PAGE));
        if ($page > $totalPages) $page = $totalPages;

        $start = $totalRequests > 0 ? (($page - 1) * RECORDS_PER_PAGE + 1) : 0;
        $end = $totalRequests > 0 ? min($page * RECORDS_PER_PAGE, $totalRequests) : 0;

        // Compute per-request convenience fields used by the view
        $now = time();
        foreach ($requests as &$request) {
            $downloadLimit = (int)($request['download_limit'] ?? 0);
            $downloadCount = (int)($request['download_count'] ?? 0);
            $request['downloads_remaining'] = max(0, $downloadLimit - $downloadCount);
            $request['download_expires'] = $request['token_expires_at'] ?? null;

            $effectiveStatus = $request['status'] ?? 'pending';
            if ($effectiveStatus === 'approved' && !empty($request['token_expires_at'])) {
                $expiresAt = strtotime((string)$request['token_expires_at']);
                if ($expiresAt && $expiresAt < $now) {
                    $effectiveStatus = 'expired';
                }
            }
            $request['effective_status'] = $effectiveStatus;
        }
        unset($request);

        // Stats cards (ignore search filter; based on all user requests)
        $baseFilters = ['requested_by' => $this->getCurrentUserId()];
        $stats = [
            'pending' => (int)$this->downloadRequestModel->getCount($baseFilters + ['status' => 'pending']),
            'approved' => (int)$this->downloadRequestModel->getCount($baseFilters + ['status' => 'approved']),
            'rejected' => (int)$this->downloadRequestModel->getCount($baseFilters + ['status' => 'rejected']),
            'downloaded' => method_exists($this->downloadRequestModel, 'getDownloadedCountByUser')
                ? (int)$this->downloadRequestModel->getDownloadedCountByUser($this->getCurrentUserId())
                : 0
        ];

        $this->view('staff/my-requests', [
            'requests' => $requests,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'q' => $q
            ],
            'pagination' => [
                'page' => $page,
                'per_page' => RECORDS_PER_PAGE,
                'total_pages' => $totalPages,
                'total' => $totalRequests,
                'start' => $start,
                'end' => $end
            ],
            'page_title' => 'My Download Requests'
        ]);
    }

    /**
     * Notifications (Staff)
     * Returns JSON items for header dropdown.
     */
    public function notifications() {
        $this->requireLogin();

        $limit = max(1, min(20, (int)($_GET['limit'] ?? 10)));
        $since = trim((string)($_GET['since'] ?? ''));

        $sinceTs = null;
        if ($since !== '') {
            // Accept either unix ms, unix s, or datetime strings
            if (ctype_digit($since)) {
                $n = (int)$since;
                if ($n > 100000000000) {
                    $n = (int)floor($n / 1000);
                }
                if ($n > 0) {
                    $sinceTs = date('Y-m-d H:i:s', $n);
                }
            } else {
                $t = strtotime($since);
                if ($t) {
                    $sinceTs = date('Y-m-d H:i:s', $t);
                }
            }
        }

        $rows = method_exists($this->downloadRequestModel, 'getStaffNotificationItems')
            ? $this->downloadRequestModel->getStaffNotificationItems($this->getCurrentUserId(), $limit, $sinceTs)
            : [];

        $items = [];
        foreach ($rows as $row) {
            $status = (string)($row['status'] ?? '');
            $eventAt = (string)($row['event_at'] ?? '');
            $docName = (string)($row['document_name'] ?? 'Document');
            $token = (string)($row['download_token'] ?? '');

            $title = $status === 'approved' ? 'Download approved' : 'Download rejected';
            $body = ($status === 'approved')
                ? ("Approved: " . $docName)
                : ("Rejected: " . $docName);

            $url = ($status === 'approved' && $token !== '')
                ? (BASE_URL . '/document/download/' . $token)
                : (BASE_URL . '/staff/my-requests');

            $items[] = [
                'id' => (int)($row['id'] ?? 0),
                'type' => 'download_request',
                'status' => $status,
                'title' => $title,
                'body' => $body,
                'event_at' => $eventAt,
                'url' => $url,
                'download_token' => $token,
                'ip_record_id' => (int)($row['ip_record_id'] ?? 0)
            ];
        }

        $this->json([
            'success' => true,
            'items' => $items,
            'server_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Request Download Permission
     */
    public function requestDownload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentId = (int)($_POST['document_id'] ?? 0);
            $ipRecordId = (int)($_POST['ip_record_id'] ?? 0);
            $documentFileId = (int)($_POST['document_file_id'] ?? 0);
            $reason = $this->sanitize($_POST['reason'] ?? '');

            // Allow requesting by Drive file id (document_files.id) by mapping to ip_documents.id
            if ($documentId <= 0 && $documentFileId > 0) {
                try {
                    $row = $this->db->fetch(
                        'SELECT id FROM ip_documents WHERE document_file_id = ? AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1',
                        [$documentFileId]
                    );
                    $documentId = (int)($row['id'] ?? 0);
                } catch (Exception $e) {
                    // Older installs may not have ip_documents.document_file_id
                    $documentId = 0;
                }

                // Fallback for older data where ip_documents.document_file_id wasn't populated
                if ($documentId <= 0) {
                    $df = $this->db->fetch('SELECT file_path FROM document_files WHERE id = ? LIMIT 1', [$documentFileId]);
                    $filePath = (string)($df['file_path'] ?? '');
                    if ($filePath !== '') {
                        $row2 = $this->db->fetch(
                            'SELECT id FROM ip_documents WHERE file_path = ? AND is_deleted = 0 ORDER BY created_at DESC LIMIT 1',
                            [$filePath]
                        );
                        $documentId = (int)($row2['id'] ?? 0);

                        // Additional fallback: normalize slashes and match by suffix (absolute vs relative)
                        if ($documentId <= 0) {
                            $norm = str_replace('\\', '/', $filePath);
                            $suffix = $norm;
                            $pos = stripos($norm, '/uploads/');
                            if ($pos !== false) {
                                $suffix = substr($norm, $pos);
                            } else {
                                $base = basename($norm);
                                if ($base) $suffix = $base;
                            }

                            $row3 = $this->db->fetch(
                                "SELECT id FROM ip_documents WHERE is_deleted = 0 AND REPLACE(file_path, '\\\\', '/') LIKE ? ORDER BY created_at DESC LIMIT 1",
                                ['%' . $suffix]
                            );
                            $documentId = (int)($row3['id'] ?? 0);
                        }
                    }
                }

                // Final fallback: match by name+size+type (helps when file_path formats differ)
                if ($documentId <= 0) {
                    $df2 = $this->db->fetch('SELECT original_name, file_size, file_type FROM document_files WHERE id = ? LIMIT 1', [$documentFileId]);
                    $on = trim((string)($df2['original_name'] ?? ''));
                    $fs = (int)($df2['file_size'] ?? 0);
                    $ft = trim((string)($df2['file_type'] ?? ''));
                    if ($on !== '' && $fs > 0 && $ft !== '') {
                        $row4 = $this->db->fetch(
                            'SELECT id FROM ip_documents WHERE is_deleted = 0 AND original_name = ? AND file_size = ? AND file_type = ? ORDER BY created_at DESC LIMIT 1',
                            [$on, $fs, $ft]
                        );
                        $documentId = (int)($row4['id'] ?? 0);
                    }
                }
            }

            // Allow requesting by IP record (fallback) by selecting the newest document
            if ($documentId <= 0 && $ipRecordId > 0) {
                $docs = $this->documentModel->getByIPRecord($ipRecordId, true);
                if (empty($docs)) {
                    $this->json(['success' => false, 'message' => 'No documents found for this IP record']);
                }
                $documentId = (int)($docs[0]['id'] ?? 0);
            }

            // If still not linked but a Drive file id was provided, auto-link it to a hidden repository record.
            // This prevents staff requests failing for files uploaded into the Folder Repository without an IP record.
            if ($documentId <= 0 && $documentFileId > 0) {
                $documentId = (int)$this->autoLinkDriveFileToRepositoryDocument($documentFileId);
            }

            if ($documentId <= 0) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid document selected (file is not linked to a document record)'
                ]);
            }
            
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

            if (!$requestId) {
                $this->json(['success' => false, 'message' => 'Failed to submit download request']);
            }
            
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

    private function autoLinkDriveFileToRepositoryDocument($documentFileId) {
        $documentFileId = (int)$documentFileId;
        if ($documentFileId <= 0) return 0;

        // Ensure the file exists
        $file = $this->db->fetch(
            'SELECT id, folder_id, file_name, original_name, file_path, file_type, file_size, uploaded_by, is_deleted FROM document_files WHERE id = ? LIMIT 1',
            [$documentFileId]
        );

        if (!$file || (int)($file['is_deleted'] ?? 0) === 1) {
            return 0;
        }

        // If it already exists in ip_documents by file_path/meta, reuse it
        $existing = $this->db->fetch(
            'SELECT id FROM ip_documents WHERE is_deleted = 0 AND (file_path = ? OR (original_name = ? AND file_size = ? AND file_type = ?)) ORDER BY created_at DESC LIMIT 1',
            [
                (string)($file['file_path'] ?? ''),
                (string)($file['original_name'] ?? ''),
                (int)($file['file_size'] ?? 0),
                (string)($file['file_type'] ?? '')
            ]
        );
        $existingId = (int)($existing['id'] ?? 0);
        if ($existingId > 0) return $existingId;

        // Resolve IP type from the folder path (Patents/Trademarks/etc). Fallback to first type.
        $rootName = $this->resolveRootFolderNameForFile((int)($file['folder_id'] ?? 0));
        $ipTypeId = $this->resolveIpTypeIdFromRootName($rootName);

        $repoRecordId = $this->getOrCreateRepositoryIpRecordId($ipTypeId, (int)$this->getCurrentUserId(), $rootName);
        if ($repoRecordId <= 0) return 0;

        // Insert a matching ip_documents row from document_files
        try {
            $sql = 'INSERT INTO ip_documents (ip_record_id, document_file_id, file_name, original_name, file_path, file_type, file_size, uploaded_by) '
                 . 'SELECT ?, id, file_name, original_name, file_path, file_type, file_size, uploaded_by FROM document_files WHERE id = ?';
            return (int)$this->db->insert($sql, [$repoRecordId, $documentFileId]);
        } catch (Exception $e) {
            // Older installs may not have ip_documents.document_file_id
            try {
                $sql2 = 'INSERT INTO ip_documents (ip_record_id, file_name, original_name, file_path, file_type, file_size, uploaded_by) '
                      . 'SELECT ?, file_name, original_name, file_path, file_type, file_size, uploaded_by FROM document_files WHERE id = ?';
                return (int)$this->db->insert($sql2, [$repoRecordId, $documentFileId]);
            } catch (Exception $e2) {
                return 0;
            }
        }
    }

    private function resolveRootFolderNameForFile($folderId) {
        $folderId = (int)$folderId;
        if ($folderId <= 0) return '';

        $folder = $this->db->fetch('SELECT id, name, parent_id, path FROM folders WHERE id = ? LIMIT 1', [$folderId]);
        if (!$folder) return '';

        $path = (string)($folder['path'] ?? '');
        if ($path !== '') {
            $parts = explode('/', trim(str_replace('\\', '/', $path), '/'));
            return (string)($parts[0] ?? '');
        }

        // Fallback: walk to the top-most parent
        $current = $folder;
        while ($current && !empty($current['parent_id'])) {
            $current = $this->db->fetch('SELECT id, name, parent_id, path FROM folders WHERE id = ? LIMIT 1', [(int)$current['parent_id']]);
        }
        return (string)($current['name'] ?? '');
    }

    private function resolveIpTypeIdFromRootName($rootName) {
        $rootName = trim((string)$rootName);

        // Prefer exact match
        if ($rootName !== '') {
            $row = $this->db->fetch('SELECT id FROM ip_types WHERE type_name = ? LIMIT 1', [$rootName]);
            $id = (int)($row['id'] ?? 0);
            if ($id > 0) return $id;

            // Try singular/plural variations
            $singular = rtrim($rootName, 's');
            if ($singular !== $rootName) {
                $row = $this->db->fetch('SELECT id FROM ip_types WHERE type_name = ? LIMIT 1', [$singular]);
                $id = (int)($row['id'] ?? 0);
                if ($id > 0) return $id;
            }

            // Fuzzy match
            $row = $this->db->fetch('SELECT id FROM ip_types WHERE LOWER(type_name) LIKE LOWER(?) LIMIT 1', ['%' . $rootName . '%']);
            $id = (int)($row['id'] ?? 0);
            if ($id > 0) return $id;
        }

        // Fallback to first ip_type
        $row = $this->db->fetch('SELECT id FROM ip_types ORDER BY id ASC LIMIT 1');
        return (int)($row['id'] ?? 0);
    }

    private function getOrCreateRepositoryIpRecordId($ipTypeId, $createdByUserId, $rootName = '') {
        $ipTypeId = (int)$ipTypeId;
        $createdByUserId = (int)$createdByUserId;
        if ($ipTypeId <= 0 || $createdByUserId <= 0) return 0;

        $ref = 'REPO-UPLOADS-' . $ipTypeId;
        $existing = $this->db->fetch('SELECT id FROM ip_records WHERE reference_number = ? LIMIT 1', [$ref]);
        $existingId = (int)($existing['id'] ?? 0);
        if ($existingId > 0) return $existingId;

        $titleSuffix = trim((string)$rootName);
        $title = $titleSuffix !== '' ? ('Repository Uploads - ' . $titleSuffix) : 'Repository Uploads';
        $desc = 'System-generated record used to attach Folder Repository files for download requests.';
        $owner = 'System';
        $status = 'active';
        $tags = 'repository,system';
        $archivedAt = date('Y-m-d H:i:s');

        try {
            $sql = 'INSERT INTO ip_records (ip_type_id, title, description, owner, status, tags, reference_number, created_by, is_archived, archived_at) '
                 . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)';
            return (int)$this->db->insert($sql, [$ipTypeId, $title, $desc, $owner, $status, $tags, $ref, $createdByUserId, $archivedAt]);
        } catch (Exception $e) {
            // If archived columns differ in older schema, fallback without archiving
            try {
                $sql2 = 'INSERT INTO ip_records (ip_type_id, title, description, owner, status, tags, reference_number, created_by) '
                      . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
                return (int)$this->db->insert($sql2, [$ipTypeId, $title, $desc, $owner, $status, $tags, $ref, $createdByUserId]);
            } catch (Exception $e2) {
                return 0;
            }
        }
    }
}
