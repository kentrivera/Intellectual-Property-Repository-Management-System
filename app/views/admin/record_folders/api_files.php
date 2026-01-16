<?php
require_once 'db.php';

header('Content-Type: application/json');

class FileAPI {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get file by ID
    public function getFile($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM files WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Update file
    public function updateFile($id, $data) {
        try {
            $updates = [];
            $params = [];

            if (isset($data['name'])) {
                $updates[] = "original_name = ?";
                $params[] = $data['name'];
            }

            if (isset($data['description'])) {
                $updates[] = "description = ?";
                $params[] = $data['description'];
            }

            if (isset($data['folder_id'])) {
                $updates[] = "folder_id = ?";
                $params[] = $data['folder_id'];
            }

            if (empty($updates)) {
                return ['error' => 'No data to update'];
            }

            $params[] = $id;
            $sql = "UPDATE files SET " . implode(", ", $updates) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return [
                'success' => true,
                'message' => 'File updated successfully'
            ];
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Delete file
    public function deleteFile($id) {
        try {
            // Get file info
            $file = $this->getFile($id);
            
            if (!$file || isset($file['error'])) {
                return ['error' => 'File not found'];
            }

            // Delete physical file (check if it exists first)
            if (isset($file['file_path']) && file_exists($file['file_path'])) {
                @unlink($file['file_path']); // @ suppresses warnings if file is locked or permissions issue
            }

            // Delete database record
            $stmt = $this->db->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$id]);
            
            return [
                'success' => true,
                'message' => 'File deleted successfully'
            ];
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        } catch(Exception $e) {
            return ['error' => 'Error deleting file: ' . $e->getMessage()];
        }
    }
    
    // Get all files for search
    public function getAllFiles() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    f.*,
                    fo.name as folder_name,
                    fo.path as folder_path
                FROM files f
                LEFT JOIN folders fo ON f.folder_id = fo.id
                ORDER BY f.original_name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

// Handle API requests
$api = new FileAPI();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'get_file':
            $id = $_GET['id'] ?? 0;
            echo json_encode($api->getFile($id));
            break;
        
        case 'get_all_files':
            echo json_encode($api->getAllFiles());
            break;

        case 'update_file':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            echo json_encode($api->updateFile($id, $data));
            break;

        case 'delete_file':
            $id = $_GET['id'] ?? 0;
            echo json_encode($api->deleteFile($id));
            break;

        case 'download':
            $id = $_GET['id'] ?? 0;
            $file = $api->getFile($id);
            
            if (!$file || isset($file['error'])) {
                echo json_encode(['error' => 'File not found']);
                exit;
            }

            if (!file_exists($file['file_path'])) {
                echo json_encode(['error' => 'Physical file not found']);
                exit;
            }

            header('Content-Type: ' . $file['file_type']);
            header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
            header('Content-Length: ' . $file['file_size']);
            readfile($file['file_path']);
            exit;

        case 'view':
            $id = $_GET['id'] ?? 0;
            $file = $api->getFile($id);
            
            if (!$file || isset($file['error'])) {
                echo json_encode(['error' => 'File not found']);
                exit;
            }

            if (!file_exists($file['file_path'])) {
                echo json_encode(['error' => 'Physical file not found']);
                exit;
            }

            // Set content type for inline display
            header('Content-Type: ' . $file['file_type']);
            header('Content-Disposition: inline; filename="' . $file['original_name'] . '"');
            header('Content-Length: ' . $file['file_size']);
            readfile($file['file_path']);
            exit;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
