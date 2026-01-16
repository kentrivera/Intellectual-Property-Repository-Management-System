<?php
require_once APP_PATH . '/models/Folder.php';

class FolderController extends Controller {
    private $folderModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->folderModel = new Folder();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
                'created_by' => $_SESSION['user_id']
            ];

            if (empty($data['name'])) {
                $this->json(['success' => false, 'message' => 'Folder name is required']);
                return;
            }

            $folderId = $this->folderModel->create($data);
            if ($folderId) {
                $this->json([
                    'success' => true, 
                    'message' => 'Folder created successfully',
                    'folder' => [
                        'id' => $folderId,
                        'name' => $data['name'],
                        'slug' => strtolower(str_replace(' ', '-', $data['name'])) // Simple slug for frontend
                    ]
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to create folder']);
            }
        }
    }

    public function rename() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $name = trim($_POST['name']);

            if (empty($name)) {
                $this->json(['success' => false, 'message' => 'Folder name is required']);
                return;
            }

            if ($this->folderModel->update($id, ['name' => $name])) {
                $this->json(['success' => true, 'message' => 'Folder renamed successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to rename folder']);
            }
        }
    }

    public function archive() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            if ($this->folderModel->delete($id)) {
                $this->json(['success' => true, 'message' => 'Folder archived successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to archive folder']);
            }
        }
    }

    public function restore() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid folder id']);
            return;
        }

        $ok = $this->folderModel->restore($id);
        $this->json(['success' => (bool)$ok, 'message' => $ok ? 'Folder restored successfully' : 'Failed to restore folder']);
    }

    public function permanentDelete() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid folder id']);
            return;
        }

        // Best effort: if DocumentFile model exists, permanently delete files under the folder tree
        $documentFileModel = null;
        if (file_exists(APP_PATH . '/models/DocumentFile.php')) {
            require_once APP_PATH . '/models/DocumentFile.php';
            $documentFileModel = new DocumentFile();
        }

        $ok = $this->folderModel->permanentDeleteRecursive($id, $documentFileModel);
        $this->json(['success' => (bool)$ok, 'message' => $ok ? 'Folder permanently deleted' : 'Failed to permanently delete folder']);
    }
    
    // API endpoint to get folders usually called via AJAX
    public function list() {
        $parentId = $_GET['parent_id'] ?? null;
        $folders = $this->folderModel->getSubFolders($parentId);
        $this->json(['success' => true, 'folders' => $folders]);
    }
}
