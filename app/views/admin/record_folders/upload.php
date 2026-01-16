<?php
require_once 'config.php';
require_once 'db.php';

header('Content-Type: application/json');

class FileUploader {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function upload($file, $folder_id = null, $description = '') {
        try {
            // Validate file
            if (!isset($file['error']) || is_array($file['error'])) {
                return ['error' => 'Invalid file parameters'];
            }

            // Check for upload errors
            switch ($file['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    return ['error' => 'File exceeds maximum size'];
                case UPLOAD_ERR_NO_FILE:
                    return ['error' => 'No file was uploaded'];
                default:
                    return ['error' => 'Unknown upload error'];
            }

            // Check file size
            if ($file['size'] > MAX_FILE_SIZE) {
                return ['error' => 'File exceeds maximum allowed size'];
            }

            // Check file extension
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
                return ['error' => 'File type not allowed'];
            }

            // Generate unique filename
            $unique_name = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = UPLOAD_DIR . $unique_name;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                return ['error' => 'Failed to move uploaded file'];
            }

            // Get file info
            $file_size = filesize($file_path);
            $file_type = mime_content_type($file_path);

            // Save to database
            $stmt = $this->db->prepare(
                "INSERT INTO files (name, original_name, file_path, file_size, file_type, folder_id, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            
            $folder_id = ($folder_id && $folder_id != 1) ? $folder_id : null;
            
            $stmt->execute([
                $unique_name,
                $file['name'],
                $file_path,
                $file_size,
                $file_type,
                $folder_id,
                $description
            ]);

            return [
                'success' => true,
                'file_id' => $this->db->lastInsertId(),
                'message' => 'File uploaded successfully',
                'file_name' => $file['name'],
                'file_size' => $this->formatBytes($file_size)
            ];

        } catch(PDOException $e) {
            // Delete file if database insert fails
            if (isset($file_path) && file_exists($file_path)) {
                unlink($file_path);
            }
            return ['error' => $e->getMessage()];
        }
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

// Handle upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) {
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }

    $uploader = new FileUploader();
    $folder_id = $_POST['folder_id'] ?? null;
    $description = $_POST['description'] ?? '';

    $result = $uploader->upload($_FILES['file'], $folder_id, $description);
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>
