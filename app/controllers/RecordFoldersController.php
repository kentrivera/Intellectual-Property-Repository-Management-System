<?php

require_once APP_PATH . '/models/Folder.php';
require_once APP_PATH . '/models/DocumentFile.php';

/**
 * RecordFoldersController
 *
 * Provides legacy-compatible endpoints for the old app/views/admin/record_folders module,
 * but backed by the main application's DB/models.
 *
 * Endpoints (via router):
 * - GET/POST /admin/api_folders.php?action=...
 * - GET/POST /admin/api_files.php?action=...
 * - POST     /admin/upload.php
 */
class RecordFoldersController extends Controller {
    private $folderModel;
    private $documentFileModel;

    public function __construct() {
        parent::__construct();
        // These endpoints are consumed by JS via fetch(); redirects break JSON parsing.
        if (!isset($_SESSION['user_id'])) {
            $this->jsonError('Not authenticated. Please log in again.', 401);
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->jsonError('Forbidden', 403);
        }
        $this->folderModel = new Folder();
        $this->documentFileModel = new DocumentFile();
    }

    private function normalizeFolderId($folderId) {
        // The embedded Record Folders UI uses 0 as a virtual root.
        if ($folderId === null) return null;
        $folderId = (int)$folderId;
        return $folderId <= 0 ? null : $folderId;
    }

    private function jsonError($message, $statusCode = 200) {
        // Legacy UI expects {error: "..."} with 200 in many cases.
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }

    private function isLocalRequest() {
        $addr = $_SERVER['REMOTE_ADDR'] ?? '';
        return $addr === '127.0.0.1' || $addr === '::1';
    }

    public function folders() {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? '';

        try {
            switch ($action) {
                case 'get_folders': {
                    $parentId = $this->normalizeFolderId($_GET['parent_id'] ?? null);
                    $folders = $this->folderModel->getSubFolders($parentId);
                    echo json_encode($folders);
                    return;
                }

                case 'get_folder': {
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id <= 0) {
                        // virtual root
                        echo json_encode(['id' => 0, 'name' => 'Home', 'parent_id' => null]);
                        return;
                    }
                    $folder = $this->folderModel->findById($id);
                    echo json_encode($folder ?: null);
                    return;
                }

                case 'get_breadcrumb': {
                    $folderId = $this->normalizeFolderId($_GET['folder_id'] ?? null);

                    $crumbs = [];
                    $crumbs[] = ['id' => 0, 'name' => 'Home', 'parent_id' => null];

                    if ($folderId !== null) {
                        $trail = $this->folderModel->getFolderBreadcrumbs($folderId);
                        foreach ($trail as $c) {
                            $crumbs[] = [
                                'id' => (int)$c['id'],
                                'name' => $c['name'],
                                'parent_id' => $c['parent_id'] !== null ? (int)$c['parent_id'] : null
                            ];
                        }
                    }

                    echo json_encode($crumbs);
                    return;
                }

                case 'create_folder': {
                    $raw = file_get_contents('php://input');
                    $data = json_decode($raw, true);
                    if (!is_array($data)) {
                        $this->jsonError('Invalid JSON body');
                    }

                    $name = trim((string)($data['name'] ?? ''));
                    $parentId = $this->normalizeFolderId($data['parent_id'] ?? null);

                    if ($name === '') {
                        $this->jsonError('Folder name is required');
                    }

                    $folderId = $this->folderModel->create([
                        'name' => $name,
                        'parent_id' => $parentId,
                        'created_by' => $this->getCurrentUserId(),
                        'color' => '#10B981'
                    ]);

                    if (!$folderId) {
                        $details = method_exists($this->folderModel, 'getLastError') ? $this->folderModel->getLastError() : null;
                        if ($details && $this->isLocalRequest()) {
                            $this->jsonError('Failed to create folder: ' . $details);
                        }
                        $this->jsonError('Failed to create folder (database error)');
                    }

                    echo json_encode([
                        'success' => true,
                        'id' => $folderId,
                        'message' => 'Folder created successfully'
                    ]);
                    return;
                }

                case 'update_folder': {
                    $raw = file_get_contents('php://input');
                    $data = json_decode($raw, true);
                    if (!is_array($data)) {
                        $this->jsonError('Invalid JSON body');
                    }

                    $id = (int)($data['id'] ?? 0);
                    $name = trim((string)($data['name'] ?? ''));

                    if ($id <= 0) {
                        $this->jsonError('Invalid folder id');
                    }
                    if ($name === '') {
                        $this->jsonError('Folder name is required');
                    }

                    $ok = $this->folderModel->update($id, ['name' => $name]);
                    echo json_encode([
                        'success' => (bool)$ok,
                        'message' => $ok ? 'Folder updated successfully' : 'Failed to update folder'
                    ]);
                    return;
                }

                case 'delete_folder': {
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id <= 0) {
                        $this->jsonError('Cannot delete root folder');
                    }

                    $ok = $this->archiveFolderRecursive($id);
                    echo json_encode([
                        'success' => (bool)$ok,
                        'message' => $ok ? 'Folder archived successfully' : 'Failed to archive folder'
                    ]);
                    return;
                }

                case 'get_files': {
                    $folderId = $this->normalizeFolderId($_GET['folder_id'] ?? null);
                    $files = $this->documentFileModel->getFilesByFolder($folderId, ['limit' => 1000]);
                    echo json_encode($files);
                    return;
                }

                case 'get_all_folders': {
                    $folders = $this->folderModel->getAllFolders(false);
                    echo json_encode($folders);
                    return;
                }

                default:
                    $this->jsonError('Invalid action');
            }
        } catch (Exception $e) {
            error_log('RecordFoldersController folders error: ' . $e->getMessage());
            $this->jsonError('Server error');
        }
    }

    public function files() {
        $action = $_GET['action'] ?? '';

        try {
            switch ($action) {
                case 'get_file': {
                    header('Content-Type: application/json');
                    $id = (int)($_GET['id'] ?? 0);
                    $file = $id > 0 ? $this->documentFileModel->findById($id) : null;
                    echo json_encode($file ?: null);
                    return;
                }

                case 'get_all_files': {
                    header('Content-Type: application/json');
                    $files = $this->documentFileModel->getAllFiles();
                    echo json_encode($files);
                    return;
                }

                case 'update_file': {
                    header('Content-Type: application/json');
                    $raw = file_get_contents('php://input');
                    $data = json_decode($raw, true);
                    if (!is_array($data)) {
                        $this->jsonError('Invalid JSON body');
                    }

                    $id = (int)($data['id'] ?? 0);
                    if ($id <= 0) {
                        $this->jsonError('Invalid file id');
                    }

                    $updates = [];
                    if (isset($data['name'])) {
                        $updates['original_name'] = trim((string)$data['name']);
                    }
                    if (array_key_exists('description', $data)) {
                        $updates['description'] = trim((string)$data['description']);
                    }

                    $ok = $this->documentFileModel->updateMetadata($id, $updates);

                    // Optional move
                    if (isset($data['folder_id'])) {
                        $newFolderId = $this->normalizeFolderId($data['folder_id']);
                        $this->documentFileModel->moveToFolder($id, $newFolderId, $this->getCurrentUserId());
                    }

                    echo json_encode([
                        'success' => (bool)$ok,
                        'message' => $ok ? 'File updated successfully' : 'Failed to update file'
                    ]);
                    return;
                }

                case 'delete_file': {
                    header('Content-Type: application/json');
                    $id = (int)($_GET['id'] ?? 0);
                    if ($id <= 0) {
                        $this->jsonError('Invalid file id');
                    }

                    $ok = $this->documentFileModel->delete($id, $this->getCurrentUserId());
                    echo json_encode([
                        'success' => (bool)$ok,
                        'message' => $ok ? 'File deleted successfully' : 'Failed to delete file'
                    ]);
                    return;
                }

                case 'download': {
                    $this->serveFile((int)($_GET['id'] ?? 0), false);
                    return;
                }

                case 'view': {
                    $this->serveFile((int)($_GET['id'] ?? 0), true);
                    return;
                }

                default:
                    $this->jsonError('Invalid action');
            }
        } catch (Exception $e) {
            error_log('RecordFoldersController files error: ' . $e->getMessage());
            $this->jsonError('Server error');
        }
    }

    public function upload() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Invalid request method');
        }

        if (!isset($_FILES['file'])) {
            $this->jsonError('No file uploaded');
        }

        $folderId = $this->normalizeFolderId($_POST['folder_id'] ?? null);
        $description = (string)($_POST['description'] ?? '');

        $result = $this->documentFileModel->uploadFile(
            $_FILES['file'],
            $this->getCurrentUserId(),
            $folderId,
            ['description' => $description]
        );

        echo json_encode($result);
    }

    private function serveFile($fileId, $inline) {
        $fileId = (int)$fileId;
        if ($fileId <= 0) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        $file = $this->documentFileModel->findById($fileId);
        if (!$file || empty($file['file_path']) || !file_exists($file['file_path'])) {
            http_response_code(404);
            echo 'File not found';
            exit;
        }

        $downloadName = $file['original_name'] ?? basename($file['file_path']);
        $mimeType = $file['mime_type'] ?? 'application/octet-stream';
        $fileSize = $file['file_size'] ?? filesize($file['file_path']);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . addslashes($downloadName) . '"');
        if ($fileSize) {
            header('Content-Length: ' . $fileSize);
        }

        readfile($file['file_path']);
        exit;
    }

    private function archiveFolderRecursive($folderId) {
        $folderId = (int)$folderId;
        if ($folderId <= 0) return false;

        $children = $this->folderModel->getSubFolders($folderId);
        foreach ($children as $child) {
            $this->archiveFolderRecursive((int)$child['id']);
        }

        return $this->folderModel->archive($folderId, $this->getCurrentUserId());
    }
}
