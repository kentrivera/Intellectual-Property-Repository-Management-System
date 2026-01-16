<?php
require_once 'db.php';

header('Content-Type: application/json');

class FolderAPI {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get folders by parent ID
    public function getFolders($parent_id = null) {
        try {
            if ($parent_id === null || $parent_id == 1) {
                $stmt = $this->db->prepare("
                    SELECT 
                        f.*,
                        (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                        (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
                    FROM folders f
                    WHERE f.parent_id IS NULL OR f.parent_id = 1 
                    ORDER BY f.name ASC
                ");
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare("
                    SELECT 
                        f.*,
                        (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                        (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
                    FROM folders f
                    WHERE f.parent_id = ? 
                    ORDER BY f.name ASC
                ");
                $stmt->execute([$parent_id]);
            }
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get folder by ID
    public function getFolder($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM folders WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get folder breadcrumb
    public function getBreadcrumb($folder_id) {
        try {
            $breadcrumb = [];
            $current_id = $folder_id;
            
            while ($current_id !== null) {
                $stmt = $this->db->prepare("SELECT id, name, parent_id FROM folders WHERE id = ?");
                $stmt->execute([$current_id]);
                $folder = $stmt->fetch();
                
                if ($folder) {
                    array_unshift($breadcrumb, $folder);
                    $current_id = $folder['parent_id'];
                } else {
                    break;
                }
            }
            
            return $breadcrumb;
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Create folder
    public function createFolder($name, $parent_id = null) {
        try {
            // Build path
            $path = '/';
            if ($parent_id && $parent_id != 1) {
                $parent = $this->getFolder($parent_id);
                if ($parent) {
                    $path = rtrim($parent['path'], '/') . '/' . $name . '/';
                }
            } else {
                $path = '/' . $name . '/';
                $parent_id = 1;
            }

            $stmt = $this->db->prepare("INSERT INTO folders (name, parent_id, path) VALUES (?, ?, ?)");
            $stmt->execute([$name, $parent_id, $path]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Folder created successfully'
            ];
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Update folder
    public function updateFolder($id, $name) {
        try {
            $stmt = $this->db->prepare("UPDATE folders SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            
            return [
                'success' => true,
                'message' => 'Folder updated successfully'
            ];
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Delete folder recursively
    public function deleteFolder($id) {
        try {
            // Prevent deletion of root folder
            if ($id == 1) {
                return ['error' => 'Cannot delete root folder'];
            }
            
            // Recursively delete all subfolders
            $stmt = $this->db->prepare("SELECT id FROM folders WHERE parent_id = ?");
            $stmt->execute([$id]);
            $subfolders = $stmt->fetchAll();
            
            foreach ($subfolders as $subfolder) {
                $result = $this->deleteFolder($subfolder['id']);
                if (isset($result['error'])) {
                    return $result; // Propagate error up
                }
            }

            // Delete all files in this folder
            $stmt = $this->db->prepare("SELECT id, file_path FROM files WHERE folder_id = ?");
            $stmt->execute([$id]);
            $files = $stmt->fetchAll();
            
            foreach ($files as $file) {
                // Delete physical file (with error suppression)
                if (isset($file['file_path']) && file_exists($file['file_path'])) {
                    @unlink($file['file_path']);
                }
                
                // Delete file record from database
                $deleteStmt = $this->db->prepare("DELETE FROM files WHERE id = ?");
                $deleteStmt->execute([$file['id']]);
            }

            // Finally, delete the folder itself
            $stmt = $this->db->prepare("DELETE FROM folders WHERE id = ?");
            $stmt->execute([$id]);
            
            return [
                'success' => true,
                'message' => 'Folder and all contents deleted successfully'
            ];
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        } catch(Exception $e) {
            return ['error' => 'Error deleting folder: ' . $e->getMessage()];
        }
    }

    // Get files in folder
    public function getFiles($folder_id = null) {
        try {
            if ($folder_id === null || $folder_id == 1) {
                $stmt = $this->db->prepare("SELECT * FROM files WHERE folder_id IS NULL OR folder_id = 1 ORDER BY created_at DESC");
                $stmt->execute();
            } else {
                $stmt = $this->db->prepare("SELECT * FROM files WHERE folder_id = ? ORDER BY created_at DESC");
                $stmt->execute([$folder_id]);
            }
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Get all folders for tree view with file/folder counts
    public function getAllFolders() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    f.*,
                    (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                    (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
                FROM folders f
                ORDER BY f.parent_id, f.name ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

// Handle API requests
$api = new FolderAPI();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'get_folders':
            $parent_id = $_GET['parent_id'] ?? null;
            echo json_encode($api->getFolders($parent_id));
            break;

        case 'get_folder':
            $id = $_GET['id'] ?? 0;
            echo json_encode($api->getFolder($id));
            break;

        case 'get_breadcrumb':
            $folder_id = $_GET['folder_id'] ?? 1;
            echo json_encode($api->getBreadcrumb($folder_id));
            break;

        case 'create_folder':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($api->createFolder($data['name'], $data['parent_id'] ?? null));
            break;

        case 'update_folder':
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($api->updateFolder($data['id'], $data['name']));
            break;

        case 'delete_folder':
            $id = $_GET['id'] ?? 0;
            echo json_encode($api->deleteFolder($id));
            break;

        case 'get_files':
            $folder_id = $_GET['folder_id'] ?? null;
            echo json_encode($api->getFiles($folder_id));
            break;

        case 'get_all_folders':
            echo json_encode($api->getAllFolders());
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
