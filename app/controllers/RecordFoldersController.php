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
    private $isAdmin;

    public function __construct() {
        parent::__construct();
        // These endpoints are consumed by JS via fetch(); redirects break JSON parsing.
        if (!isset($_SESSION['user_id'])) {
            $this->jsonError('Not authenticated. Please log in again.', 401);
        }
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'], true)) {
            $this->jsonError('Forbidden', 403);
        }

        $this->isAdmin = ($_SESSION['role'] === 'admin');
        $this->folderModel = new Folder();
        $this->documentFileModel = new DocumentFile();
    }

    private function requireAdminWrite() {
        if (!$this->isAdmin) {
            $this->jsonError('Forbidden', 403);
        }
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
                    $this->requireAdminWrite();
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
                    $this->requireAdminWrite();
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
                    $this->requireAdminWrite();
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

                    // Enrich with linked IP document metadata when available (helps staff request-download)
                    $this->attachIpDocumentLinks($files);
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
                    $this->requireAdminWrite();
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
                    $this->requireAdminWrite();
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
                    // Staff should not be able to directly download.
                    $this->requireAdminWrite();
                    $this->serveFile((int)($_GET['id'] ?? 0), false);
                    return;
                }

                case 'view': {
                    // Allow preview for staff; enforce read access in serveFile().
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

        // Staff is read-only in the Drive UI.
        $this->requireAdminWrite();

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

        // Staff: allow inline preview (view-only). Download is blocked via requireAdminWrite().
        // This prevents the Drive UI from listing files staff can see but then forbidding previews.
        if (!$this->isAdmin && !$inline) {
            http_response_code(403);
            echo 'Forbidden';
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

    private function attachIpDocumentLinks(&$files) {
        if (!is_array($files) || empty($files)) return;

        $fileIds = [];
        $filePaths = [];
        foreach ($files as $f) {
            $id = (int)($f['id'] ?? 0);
            if ($id > 0) $fileIds[] = $id;
            $path = (string)($f['file_path'] ?? '');
            if ($path !== '') $filePaths[] = $path;
        }

        $fileIds = array_values(array_unique($fileIds));
        $filePaths = array_values(array_unique($filePaths));
        if (empty($fileIds) && empty($filePaths)) return;

        $rows = [];
        $queried = false;
        $params = [];

        // 1) Prefer exact mapping by document_file_id (if column exists)
        if (!empty($fileIds)) {
            $queried = true;
            try {
                $sql1 = 'SELECT id, ip_record_id, document_file_id, file_path FROM ip_documents WHERE is_deleted = 0 AND document_file_id IN (' . implode(',', array_fill(0, count($fileIds), '?')) . ')';
                $rows = array_merge($rows, $this->db->fetchAll($sql1, $fileIds));
            } catch (Exception $e) {
                // Column may not exist in older installs; ignore.
            }
        }

        // 2) Exact mapping by file_path
        if (!empty($filePaths)) {
            $queried = true;
            $sql2 = 'SELECT id, ip_record_id, NULL as document_file_id, file_path FROM ip_documents WHERE is_deleted = 0 AND file_path IN (' . implode(',', array_fill(0, count($filePaths), '?')) . ')';
            try {
                $rows = array_merge($rows, $this->db->fetchAll($sql2, $filePaths));
            } catch (Exception $e) {
                // ignore
            }
        }

        // 3) Fuzzy mapping: normalize slashes and match by suffix (helps absolute vs relative path mismatches)
        if (!empty($filePaths)) {
            $likeParams = [];
            foreach ($filePaths as $p) {
                $norm = str_replace('\\', '/', $p);
                $suffix = $norm;
                $pos = stripos($norm, '/uploads/');
                if ($pos !== false) {
                    $suffix = substr($norm, $pos);
                } else {
                    $base = basename($norm);
                    if ($base) $suffix = $base;
                }
                $likeParams[] = '%' . $suffix;
            }
            $likeParams = array_values(array_unique($likeParams));
            if (!empty($likeParams)) {
                $queried = true;
                $sql3 = 'SELECT id, ip_record_id, NULL as document_file_id, file_path FROM ip_documents WHERE is_deleted = 0 AND (' .
                        implode(' OR ', array_fill(0, count($likeParams), "REPLACE(file_path, '\\\\', '/') LIKE ?")) .
                        ')';
                try {
                    $rows = array_merge($rows, $this->db->fetchAll($sql3, $likeParams));
                } catch (Exception $e) {
                    // ignore
                }
            }
        }

        if (!$queried || empty($rows)) {
            // still set nulls below so frontend can handle gracefully
        }

        $byDocFileId = [];
        $byPath = [];
        $byMeta = [];
        foreach ($rows as $r) {
            $docId = (int)($r['id'] ?? 0);
            $ipRecordId = (int)($r['ip_record_id'] ?? 0);
            $dfid = (int)($r['document_file_id'] ?? 0);
            $p = (string)($r['file_path'] ?? '');
            if ($dfid > 0) $byDocFileId[$dfid] = ['ip_document_id' => $docId, 'ip_record_id' => $ipRecordId];
            if ($p !== '') {
                $byPath[$p] = ['ip_document_id' => $docId, 'ip_record_id' => $ipRecordId];
                $byPath[str_replace('\\', '/', $p)] = ['ip_document_id' => $docId, 'ip_record_id' => $ipRecordId];
            }
        }

        // Meta-based fallback for legacy data where file_path doesn't match (absolute vs relative)
        // Matches on original_name + file_size + file_type.
        $unlinkedMeta = [];
        foreach ($files as $f0) {
            $dfid0 = (int)($f0['id'] ?? 0);
            $p0 = (string)($f0['file_path'] ?? '');
            $pn0 = str_replace('\\', '/', $p0);
            if (($dfid0 > 0 && isset($byDocFileId[$dfid0])) || ($p0 !== '' && isset($byPath[$p0])) || ($pn0 !== '' && isset($byPath[$pn0]))) {
                continue;
            }
            $on = trim((string)($f0['original_name'] ?? ''));
            $fs = (int)($f0['file_size'] ?? 0);
            $ft = trim((string)($f0['file_type'] ?? ''));
            if ($on === '' || $fs <= 0 || $ft === '') continue;
            $key = mb_strtolower($on) . '|' . $fs . '|' . $ft;
            $unlinkedMeta[$key] = ['original_name' => $on, 'file_size' => $fs, 'file_type' => $ft];
            if (count($unlinkedMeta) >= 50) break;
        }

        if (!empty($unlinkedMeta)) {
            $metaClauses = [];
            $metaParams = [];
            foreach ($unlinkedMeta as $m) {
                $metaClauses[] = '(original_name = ? AND file_size = ? AND file_type = ?)';
                $metaParams[] = $m['original_name'];
                $metaParams[] = $m['file_size'];
                $metaParams[] = $m['file_type'];
            }
            $sqlMeta = 'SELECT id, ip_record_id, original_name, file_size, file_type FROM ip_documents WHERE is_deleted = 0 AND (' . implode(' OR ', $metaClauses) . ') ORDER BY created_at DESC';
            try {
                $metaRows = $this->db->fetchAll($sqlMeta, $metaParams);
                foreach ($metaRows as $mr) {
                    $k = mb_strtolower((string)($mr['original_name'] ?? '')) . '|' . (int)($mr['file_size'] ?? 0) . '|' . (string)($mr['file_type'] ?? '');
                    if (!isset($byMeta[$k])) {
                        $byMeta[$k] = ['ip_document_id' => (int)($mr['id'] ?? 0), 'ip_record_id' => (int)($mr['ip_record_id'] ?? 0)];
                    }
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        foreach ($files as &$f) {
            $f['ip_document_id'] = null;
            $f['ip_record_id'] = null;

            $dfid = (int)($f['id'] ?? 0);
            $p = (string)($f['file_path'] ?? '');
            $pn = str_replace('\\', '/', $p);

            $link = null;
            if ($dfid > 0 && isset($byDocFileId[$dfid])) {
                $link = $byDocFileId[$dfid];
            } elseif ($p !== '' && isset($byPath[$p])) {
                $link = $byPath[$p];
            } elseif ($pn !== '' && isset($byPath[$pn])) {
                $link = $byPath[$pn];
            } else {
                $on = trim((string)($f['original_name'] ?? ''));
                $fs = (int)($f['file_size'] ?? 0);
                $ft = trim((string)($f['file_type'] ?? ''));
                if ($on !== '' && $fs > 0 && $ft !== '') {
                    $k = mb_strtolower($on) . '|' . $fs . '|' . $ft;
                    if (isset($byMeta[$k])) {
                        $link = $byMeta[$k];
                    }
                }
            }

            if ($link) {
                $f['ip_document_id'] = $link['ip_document_id'];
                $f['ip_record_id'] = $link['ip_record_id'];
            }
        }
        unset($f);
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
