<?php
/**
 * Enhanced Document Controller
 * Handles file management operations including upload, organization, and access control
 */

require_once APP_PATH . '/models/DocumentFile.php';
require_once APP_PATH . '/models/Folder.php';
require_once APP_PATH . '/models/IPRecord.php';
require_once APP_PATH . '/models/Document.php';
require_once APP_PATH . '/models/DownloadRequest.php';
require_once APP_PATH . '/models/ActivityLog.php';

class DocumentController extends Controller {
    private $documentFileModel;
    private $folderModel;
    private $ipRecordModel;
    private $documentModel;
    private $downloadRequestModel;
    private $activityLog;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->documentFileModel = new DocumentFile();
        $this->folderModel = new Folder();
        $this->ipRecordModel = new IPRecord();
        $this->documentModel = new Document();
        $this->downloadRequestModel = new DownloadRequest();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Upload document
     */
    public function upload() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ipRecordId = $_POST['ip_record_id'] ?? 0;
            
            // Verify IP record exists
            $ipRecord = $this->ipRecordModel->findById($ipRecordId);
            if (!$ipRecord) {
                $this->json(['success' => false, 'message' => 'IP Record not found']);
            }
            
            // Check if file was uploaded
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $this->json(['success' => false, 'message' => 'No file uploaded or upload error']);
            }
            
            $file = $_FILES['document'];
            
            // Validate file size
            if ($file['size'] > MAX_FILE_SIZE) {
                $this->json(['success' => false, 'message' => 'File size exceeds maximum allowed size']);
            }
            
            // Validate file type
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
                $this->json(['success' => false, 'message' => 'File type not allowed']);
            }
            
            // Generate unique filename
            $uniqueName = uniqid() . '_' . time() . '.' . $fileExt;
            $uploadPath = DOCUMENT_PATH . '/' . $uniqueName;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $this->json(['success' => false, 'message' => 'Failed to save file']);
            }
            
            // Save to database
            try {
                $documentId = $this->documentModel->create([
                    'ip_record_id' => $ipRecordId,
                    'file_name' => $uniqueName,
                    'original_name' => $file['name'],
                    'file_path' => $uploadPath,
                    'file_type' => $fileExt,
                    'file_size' => $file['size'],
                    'current_version' => 1,
                    'uploaded_by' => $this->getCurrentUserId()
                ]);
                
                // Save first version
                $this->documentModel->addVersion($documentId, [
                    'version_number' => 1,
                    'file_name' => $uniqueName,
                    'file_path' => $uploadPath,
                    'file_size' => $file['size'],
                    'uploaded_by' => $this->getCurrentUserId(),
                    'version_notes' => 'Initial upload'
                ]);
                
                // Log activity
                $this->activityLog->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action_type' => 'upload',
                    'entity_type' => 'document',
                    'entity_id' => $documentId,
                    'description' => "Uploaded document: {$file['name']} to IP Record: {$ipRecord['title']}"
                ]);
                
                $this->json(['success' => true, 'message' => 'Document uploaded successfully']);
            } catch (Exception $e) {
                // Remove file if database insert fails
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
                $this->json(['success' => false, 'message' => 'Failed to save document information']);
            }
        }
    }
    
    /**
     * Upload new version
     */
    public function uploadVersion() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentId = $_POST['document_id'] ?? 0;
            $versionNotes = $this->sanitize($_POST['version_notes'] ?? '');
            
            // Get existing document
            $document = $this->documentModel->findById($documentId);
            if (!$document) {
                $this->json(['success' => false, 'message' => 'Document not found']);
            }
            
            // Check if file was uploaded
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $this->json(['success' => false, 'message' => 'No file uploaded or upload error']);
            }
            
            $file = $_FILES['document'];
            
            // Validate file
            if ($file['size'] > MAX_FILE_SIZE) {
                $this->json(['success' => false, 'message' => 'File size exceeds maximum']);
            }
            
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
                $this->json(['success' => false, 'message' => 'File type not allowed']);
            }
            
            // Generate unique filename
            $uniqueName = uniqid() . '_' . time() . '.' . $fileExt;
            $uploadPath = DOCUMENT_PATH . '/' . $uniqueName;
            
            // Move file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $this->json(['success' => false, 'message' => 'Failed to save file']);
            }
            
            try {
                $newVersion = $document['current_version'] + 1;
                
                // Add new version
                $this->documentModel->addVersion($documentId, [
                    'version_number' => $newVersion,
                    'file_name' => $uniqueName,
                    'file_path' => $uploadPath,
                    'file_size' => $file['size'],
                    'uploaded_by' => $this->getCurrentUserId(),
                    'version_notes' => $versionNotes
                ]);
                
                // Update document
                $this->documentModel->update($documentId, [
                    'current_version' => $newVersion,
                    'file_name' => $uniqueName,
                    'file_path' => $uploadPath,
                    'file_size' => $file['size']
                ]);
                
                // Log activity
                $this->activityLog->log([
                    'user_id' => $this->getCurrentUserId(),
                    'action_type' => 'upload_version',
                    'entity_type' => 'document',
                    'entity_id' => $documentId,
                    'description' => "Uploaded version {$newVersion} of document: {$document['original_name']}"
                ]);
                
                $this->json(['success' => true, 'message' => "Version {$newVersion} uploaded successfully"]);
            } catch (Exception $e) {
                if (file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
                $this->json(['success' => false, 'message' => 'Failed to save version']);
            }
        }
    }
    
    /**
     * Soft delete document
     */
    public function delete() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentId = $_POST['document_id'] ?? 0;
            
            $document = $this->documentModel->findById($documentId);
            if (!$document) {
                $this->json(['success' => false, 'message' => 'Document not found']);
            }
            
            $this->documentModel->softDelete($documentId, $this->getCurrentUserId());
            
            // Move file to trash
            $trashPath = TRASH_PATH . '/' . basename($document['file_path']);
            if (file_exists($document['file_path'])) {
                rename($document['file_path'], $trashPath);
            }
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'delete',
                'entity_type' => 'document',
                'entity_id' => $documentId,
                'description' => "Moved document to trash: {$document['original_name']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'Document moved to trash']);
        }
    }
    
    /**
     * Restore document
     */
    public function restore() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentId = $_POST['document_id'] ?? 0;
            
            $document = $this->documentModel->findById($documentId, true);
            if (!$document || !$document['is_deleted']) {
                $this->json(['success' => false, 'message' => 'Document not found in trash']);
            }
            
            $this->documentModel->restore($documentId);
            
            // Move file back from trash
            $trashPath = TRASH_PATH . '/' . basename($document['file_path']);
            if (file_exists($trashPath)) {
                rename($trashPath, $document['file_path']);
            }
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'restore',
                'entity_type' => 'document',
                'entity_id' => $documentId,
                'description' => "Restored document from trash: {$document['original_name']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'Document restored successfully']);
        }
    }
    
    /**
     * Permanently delete document
     */
    public function permanentDelete() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentId = $_POST['document_id'] ?? 0;
            
            $document = $this->documentModel->findById($documentId, true);
            if (!$document) {
                $this->json(['success' => false, 'message' => 'Document not found']);
            }
            
            // Delete all versions files
            $versions = $this->documentModel->getVersions($documentId);
            foreach ($versions as $version) {
                if (file_exists($version['file_path'])) {
                    unlink($version['file_path']);
                }
            }
            
            // Delete current file
            $trashPath = TRASH_PATH . '/' . basename($document['file_path']);
            if (file_exists($trashPath)) {
                unlink($trashPath);
            }
            if (file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            
            // Delete from database
            $this->documentModel->permanentDelete($documentId);
            
            // Log activity
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'permanent_delete',
                'entity_type' => 'document',
                'entity_id' => $documentId,
                'description' => "Permanently deleted document: {$document['original_name']}"
            ]);
            
            $this->json(['success' => true, 'message' => 'Document permanently deleted']);
        }
    }
    
    /**
     * Secure download with token
     */
    public function download($token) {
        // Find request by token
        $request = $this->downloadRequestModel->findByToken($token);
        
        if (!$request) {
            die("Invalid or expired download link");
        }
        
        // Check if file exists
        if (!file_exists($request['file_path'])) {
            die("File not found");
        }
        
        // Increment download count
        $this->downloadRequestModel->incrementDownloadCount($request['id']);
        
        // Log download
        $this->downloadRequestModel->logDownload(
            $request['id'],
            $request['document_id'],
            $this->getCurrentUserId(),
            $token
        );
        
        // Log activity
        $this->activityLog->log([
            'user_id' => $this->getCurrentUserId(),
            'action_type' => 'download',
            'entity_type' => 'document',
            'entity_id' => $request['document_id'],
            'description' => "Downloaded document: {$request['original_name']}"
        ]);
        
        // Serve file
        header('Content-Type: ' . mime_content_type($request['file_path']));
        header('Content-Disposition: attachment; filename="' . $request['original_name'] . '"');
        header('Content-Length: ' . filesize($request['file_path']));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        readfile($request['file_path']);
        exit;
    }

    /**
     * Enhanced file upload to folders
     */
    public function uploadToFolder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $folderId = !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
            $ipRecordId = !empty($_POST['ip_record_id']) ? (int)$_POST['ip_record_id'] : null;
            $userId = $_SESSION['user_id'];

            // Validate folder access
            if ($folderId && !$this->folderModel->canUserAccess($folderId, $userId, 'write')) {
                $this->json(['success' => false, 'message' => 'No permission to upload to this folder']);
                return;
            }

            $uploadOptions = [
                'tags' => $_POST['tags'] ?? '',
                'description' => $_POST['description'] ?? '',
                'is_public' => isset($_POST['is_public']) ? 1 : 0
            ];

            $uploadResults = [];
            $successCount = 0;
            $errorCount = 0;

            // Handle multiple file uploads
            if (isset($_FILES['files'])) {
                $fileCount = count($_FILES['files']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    $file = [
                        'name' => $_FILES['files']['name'][$i],
                        'type' => $_FILES['files']['type'][$i],
                        'tmp_name' => $_FILES['files']['tmp_name'][$i],
                        'error' => $_FILES['files']['error'][$i],
                        'size' => $_FILES['files']['size'][$i]
                    ];

                    $result = $this->documentFileModel->uploadFile($file, $folderId, $userId, $uploadOptions);
                    
                    if ($result['success']) {
                        $successCount++;
                        
                        // Link to IP record if provided
                        if ($ipRecordId && isset($result['file'])) {
                            $this->linkFileToIPRecord($result['file']['id'], $ipRecordId);
                        }
                        
                        $uploadResults[] = $result['file'];
                    } else {
                        $errorCount++;
                        $uploadResults[] = ['error' => $result['message'], 'file_name' => $file['name']];
                    }
                }
            } else {
                $this->json(['success' => false, 'message' => 'No files uploaded']);
                return;
            }

            // Log activity
            $this->activityLog->log([
                'user_id' => $userId,
                'action_type' => 'file_upload',
                'entity_type' => 'document_file',
                'entity_id' => null,
                'description' => "Uploaded $successCount files" . ($folderId ? " to folder ID: $folderId" : ""),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            $this->json([
                'success' => true,
                'message' => "Upload completed: $successCount successful, $errorCount failed",
                'results' => $uploadResults,
                'stats' => ['success' => $successCount, 'errors' => $errorCount]
            ]);

        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Get files in a folder
     */
    public function listFiles() {
        try {
            $folderId = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;
            $search = $_GET['search'] ?? '';
            $fileType = $_GET['type'] ?? '';
            $sort = $_GET['sort'] ?? 'created_at DESC';
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;

            // Check folder access
            if ($folderId && !$this->folderModel->canUserAccess($folderId, $_SESSION['user_id'], 'read')) {
                $this->json(['success' => false, 'message' => 'No permission to access this folder']);
                return;
            }

            $options = [
                'search' => $search,
                'file_type' => $fileType,
                'sort' => $sort,
                'limit' => $limit,
                'offset' => $offset
            ];

            $files = $this->documentFileModel->getFilesByFolder($folderId, $options);
            $stats = $this->documentFileModel->getStatistics($folderId);

            // Get folder information if specified
            $folderInfo = null;
            if ($folderId) {
                $folderInfo = $this->folderModel->findById($folderId);
                $folderInfo['breadcrumbs'] = $this->folderModel->getFolderBreadcrumbs($folderId);
            }

            $this->json([
                'success' => true,
                'files' => $files,
                'folder' => $folderInfo,
                'stats' => $stats,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'has_more' => count($files) === $limit
                ]
            ]);

        } catch (Exception $e) {
            error_log("List files error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to load files']);
        }
    }

    /**
     * Move file to different folder
     */
    public function moveFile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $fileId = (int)$_POST['file_id'];
            $newFolderId = !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null;
            $userId = $_SESSION['user_id'];

            // Validate permissions
            if ($newFolderId && !$this->folderModel->canUserAccess($newFolderId, $userId, 'write')) {
                $this->json(['success' => false, 'message' => 'No permission to move file to this folder']);
                return;
            }

            if ($this->documentFileModel->moveToFolder($fileId, $newFolderId, $userId)) {
                $this->activityLog->log([
                    'user_id' => $userId,
                    'action_type' => 'file_move',
                    'entity_type' => 'document_file',
                    'entity_id' => $fileId,
                    'description' => "Moved file to folder ID: " . ($newFolderId ?: 'root'),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $this->json(['success' => true, 'message' => 'File moved successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to move file']);
            }

        } catch (Exception $e) {
            error_log("File move error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Move failed']);
        }
    }

    /**
     * Delete file (soft delete)
     */
    public function deleteFile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $fileId = (int)$_POST['file_id'];
            $userId = $_SESSION['user_id'];

            // Check if user has permission
            $file = $this->documentFileModel->findById($fileId);
            if (!$file) {
                $this->json(['success' => false, 'message' => 'File not found']);
                return;
            }

            // Check folder write permission or file ownership
            $canDelete = false;
            if ($file['uploaded_by'] == $userId || $_SESSION['role'] === 'admin') {
                $canDelete = true;
            } elseif ($file['folder_id']) {
                $canDelete = $this->folderModel->canUserAccess($file['folder_id'], $userId, 'write');
            }

            if (!$canDelete) {
                $this->json(['success' => false, 'message' => 'No permission to delete this file']);
                return;
            }

            if ($this->documentFileModel->delete($fileId, $userId)) {
                $this->activityLog->log([
                    'user_id' => $userId,
                    'action_type' => 'file_delete',
                    'entity_type' => 'document_file',
                    'entity_id' => $fileId,
                    'description' => "Deleted file: {$file['original_name']}",
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
                ]);
                
                $this->json(['success' => true, 'message' => 'File deleted successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to delete file']);
            }

        } catch (Exception $e) {
            error_log("File delete error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Delete failed']);
        }
    }

    /**
     * Search files
     */
    public function searchFiles() {
        try {
            $query = $_GET['q'] ?? '';
            $folderId = !empty($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;
            $fileType = $_GET['type'] ?? '';
            $limit = (int)($_GET['limit'] ?? 20);

            if (strlen($query) < 2) {
                $this->json(['success' => false, 'message' => 'Query too short']);
                return;
            }

            $options = [
                'folder_id' => $folderId,
                'file_type' => $fileType,
                'limit' => $limit
            ];

            $files = $this->documentFileModel->searchFiles($query, $options);

            $this->json([
                'success' => true,
                'files' => $files,
                'query' => $query,
                'count' => count($files)
            ]);

        } catch (Exception $e) {
            error_log("File search error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Search failed']);
        }
    }

    /**
     * Get file thumbnail
     */
    public function thumbnail() {
        try {
            $fileId = (int)($_GET['id'] ?? 0);

            $file = $this->documentFileModel->findById($fileId);
            if (!$file || !$file['is_image'] || !$file['thumbnail_path'] || !file_exists($file['thumbnail_path'])) {
                // Return default file icon
                $this->serveDefaultIcon($file['file_type'] ?? 'unknown');
                return;
            }

            // Check access permission
            if (!$this->canDownloadFile($file)) {
                http_response_code(403);
                return;
            }

            // Serve thumbnail
            header('Content-Type: image/jpeg');
            header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
            readfile($file['thumbnail_path']);

        } catch (Exception $e) {
            error_log("Thumbnail error: " . $e->getMessage());
            $this->serveDefaultIcon('unknown');
        }
    }

    /**
     * Link uploaded file to IP record
     */
    private function linkFileToIPRecord($fileId, $ipRecordId) {
        try {
            // Insert into ip_documents table to maintain backward compatibility
            $sql = 'INSERT INTO ip_documents (ip_record_id, document_file_id, file_name, original_name, 
                    file_path, file_type, file_size, uploaded_by) 
                    SELECT ?, id, file_name, original_name, file_path, file_type, file_size, uploaded_by 
                    FROM document_files WHERE id = ?';
            
            // Use the database connection from the parent controller or create a new instance
            $db = Database::getInstance();
            $db->execute($sql, [$ipRecordId, $fileId]);

        } catch (Exception $e) {
            error_log("IP record link error: " . $e->getMessage());
        }
    }

    /**
     * Check if user can download file
     */
    private function canDownloadFile($file) {
        $userId = $_SESSION['user_id'] ?? null;

        // Public files can be downloaded by anyone
        if ($file['is_public']) {
            return true;
        }

        // File owner or admin can always download
        if ($userId && ($file['uploaded_by'] == $userId || $_SESSION['role'] === 'admin')) {
            return true;
        }

        // Check folder permissions
        if ($file['folder_id'] && $userId) {
            return $this->folderModel->canUserAccess($file['folder_id'], $userId, 'read');
        }

        return false;
    }

    /**
     * Serve default file type icon
     */
    private function serveDefaultIcon($fileType) {
        // Create simple colored rectangle as default icon
        $image = imagecreate(200, 200);
        $colors = [
            'pdf' => [255, 0, 0],
            'doc' => [0, 0, 255], 'docx' => [0, 0, 255],
            'xls' => [0, 255, 0], 'xlsx' => [0, 255, 0],
            'jpg' => [255, 255, 0], 'jpeg' => [255, 255, 0], 'png' => [255, 255, 0]
        ];
        
        $color = $colors[$fileType] ?? [128, 128, 128];
        $bgColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        
        imagefill($image, 0, 0, $bgColor);
        imagestring($image, 5, 75, 95, strtoupper($fileType), $textColor);
        
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400'); // Cache for 1 day
        imagepng($image);
        // imagedestroy is deprecated in PHP 8.0+, memory will be freed automatically
    }
}
