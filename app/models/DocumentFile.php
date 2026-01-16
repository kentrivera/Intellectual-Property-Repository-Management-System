<?php
/**
 * DocumentFile Model
 * Enhanced file management with folder support, metadata, and advanced features
 */

require_once dirname(APP_PATH) . '/core/Database.php';

class DocumentFile {
    private $db;
    private $uploadPath;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->uploadPath = dirname(APP_PATH) . '/uploads/documents/';
        $this->maxFileSize = 50 * 1024 * 1024; // 50MB default
        $this->allowedTypes = [
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            // Audio/Video
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime'
        ];
    }

    /**
     * Upload a file to a specific folder
     */
    public function uploadFile($file, $userId, $folderId = null, $options = []) {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return ['success' => false, 'message' => $validation['message']];
            }

            $fileInfo = $validation['file_info'];
            $fileName = $this->generateFileName($fileInfo['original_name']);
            $filePath = $this->getFileStoragePath($folderId) . $fileName;

            // Ensure directory exists
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'message' => 'Failed to move uploaded file'];
            }

            // Generate thumbnail for images
            $thumbnailPath = null;
            if ($fileInfo['is_image']) {
                $thumbnailPath = $this->generateThumbnail($filePath, $fileInfo['extension']);
            }

            // Save to database
            $data = [
                'folder_id' => $folderId,
                'file_name' => $fileName,
                'original_name' => $fileInfo['original_name'],
                'file_path' => $filePath,
                'file_size' => $fileInfo['size'],
                'file_type' => $fileInfo['extension'],
                'mime_type' => $fileInfo['mime_type'],
                'file_hash' => $fileInfo['hash'],
                'thumbnail_path' => $thumbnailPath,
                'is_image' => $fileInfo['is_image'],
                'is_document' => $fileInfo['is_document'],
                'is_video' => $fileInfo['is_video'],
                'is_audio' => $fileInfo['is_audio'],
                'uploaded_by' => $userId,
                'upload_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'tags' => $options['tags'] ?? null,
                'description' => $options['description'] ?? null,
                'is_public' => $options['is_public'] ?? 0
            ];

            $fileId = $this->create($data);
            if ($fileId) {
                return [
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'file' => $this->findById($fileId)
                ];
            } else {
                // Clean up file if database insert failed
                unlink($filePath);
                if ($thumbnailPath && file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }
                return ['success' => false, 'message' => 'Failed to save file information'];
            }
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            return ['success' => false, 'message' => 'File upload failed'];
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'File upload error: ' . $file['error']];
        }

        if ($file['size'] > $this->maxFileSize) {
            return ['valid' => false, 'message' => 'File size exceeds maximum limit'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($this->allowedTypes[$extension])) {
            return ['valid' => false, 'message' => 'File type not allowed'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        // finfo_close($finfo); // Optional in modern PHP, resource freed automatically

        if ($mimeType !== $this->allowedTypes[$extension]) {
            return ['valid' => false, 'message' => 'File type mismatch'];
        }

        // Generate file hash for duplicate detection
        $hash = hash_file('sha256', $file['tmp_name']);

        return [
            'valid' => true,
            'file_info' => [
                'original_name' => $file['name'],
                'size' => $file['size'],
                'extension' => $extension,
                'mime_type' => $mimeType,
                'hash' => $hash,
                'is_image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp']),
                'is_document' => in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf']),
                'is_video' => in_array($extension, ['mp4', 'avi', 'mov']),
                'is_audio' => in_array($extension, ['mp3', 'wav'])
            ]
        ];
    }

    /**
     * Generate unique filename
     */
    private function generateFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        return $safeName . '_' . uniqid() . '.' . $extension;
    }

    /**
     * Get storage path for folder
     */
    private function getFileStoragePath($folderId) {
        if ($folderId) {
            $folder = $this->getFolderPath($folderId);
            return $this->uploadPath . $folder . '/';
        }
        return $this->uploadPath . 'root/';
    }

    /**
     * Get folder path by ID
     */
    private function getFolderPath($folderId) {
        $sql = 'SELECT path FROM folders WHERE id = ?';
        $folder = $this->db->fetch($sql, [$folderId]);
        return $folder ? ltrim($folder['path'], '/') : 'uncategorized';
    }

    /**
     * Generate thumbnail for images
     */
    private function generateThumbnail($imagePath, $extension) {
        try {
            $thumbnailDir = dirname($imagePath) . '/thumbnails/';
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            $thumbnailPath = $thumbnailDir . basename($imagePath);
            $thumbnailSize = 200; // 200x200 pixels

            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $source = imagecreatefromjpeg($imagePath);
                    break;
                case 'png':
                    $source = imagecreatefrompng($imagePath);
                    break;
                case 'gif':
                    $source = imagecreatefromgif($imagePath);
                    break;
                default:
                    return null;
            }

            if (!$source) return null;

            $width = imagesx($source);
            $height = imagesy($source);
            
            // Calculate thumbnail dimensions
            if ($width > $height) {
                $thumbWidth = $thumbnailSize;
                $thumbHeight = ($height / $width) * $thumbnailSize;
            } else {
                $thumbHeight = $thumbnailSize;
                $thumbWidth = ($width / $height) * $thumbnailSize;
            }

            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

            // Save thumbnail
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($thumbnail, $thumbnailPath, 80);
                    break;
                case 'png':
                    imagepng($thumbnail, $thumbnailPath);
                    break;
                case 'gif':
                    imagegif($thumbnail, $thumbnailPath);
                    break;
            }

            // imagedestroy is deprecated in PHP 8.0+, memory will be freed automatically
            // imagedestroy($source);
            // imagedestroy($thumbnail);

            return $thumbnailPath;
        } catch (Exception $e) {
            error_log("Thumbnail generation error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create file record
     */
    public function create($data) {
        $sql = 'INSERT INTO document_files (folder_id, file_name, original_name, file_path, file_size, 
                file_type, mime_type, file_hash, thumbnail_path, is_image, is_document, is_video, 
                is_audio, uploaded_by, upload_ip, tags, description, is_public) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        
        $params = [
            $data['folder_id'], $data['file_name'], $data['original_name'], $data['file_path'],
            $data['file_size'], $data['file_type'], $data['mime_type'], $data['file_hash'],
            $data['thumbnail_path'], $data['is_image'], $data['is_document'], $data['is_video'],
            $data['is_audio'], $data['uploaded_by'], $data['upload_ip'], $data['tags'],
            $data['description'], $data['is_public']
        ];

        try {
            return $this->db->insert($sql, $params);
        } catch (Exception $e) {
            error_log("File record creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get files in folder
     */
    public function getFilesByFolder($folderId = null, $options = []) {
        $whereClause = 'WHERE is_deleted = 0';
        $params = [];

        if ($folderId !== null) {
            $whereClause .= ' AND folder_id = ?';
            $params[] = $folderId;
        } else {
            $whereClause .= ' AND folder_id IS NULL';
        }

        // Add search filter
        if (!empty($options['search'])) {
            $whereClause .= ' AND (original_name LIKE ? OR tags LIKE ? OR description LIKE ?)';
            $searchTerm = '%' . $options['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Add file type filter
        if (!empty($options['file_type'])) {
            switch ($options['file_type']) {
                case 'image':
                    $whereClause .= ' AND is_image = 1';
                    break;
                case 'document':
                    $whereClause .= ' AND is_document = 1';
                    break;
                case 'video':
                    $whereClause .= ' AND is_video = 1';
                    break;
                case 'audio':
                    $whereClause .= ' AND is_audio = 1';
                    break;
            }
        }

        $orderBy = $options['sort'] ?? 'created_at DESC';
        $limit = $options['limit'] ?? 50;
        $offset = $options['offset'] ?? 0;

        $sql = "SELECT df.*, u.full_name as uploaded_by_name 
                FROM document_files df 
                LEFT JOIN users u ON df.uploaded_by = u.id 
                $whereClause 
                ORDER BY $orderBy 
                LIMIT $limit OFFSET $offset";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find file by ID
     */
    public function findById($id, $includeDeleted = false) {
        $where = $includeDeleted ? 'WHERE df.id = ?' : 'WHERE df.id = ? AND df.is_deleted = 0';
        $sql = 'SELECT df.*, u.full_name as uploaded_by_name 
                FROM document_files df 
                LEFT JOIN users u ON df.uploaded_by = u.id 
                ' . $where;
        return $this->db->fetch($sql, [(int)$id]);
    }

    /**
     * Get deleted files (trash)
     */
    public function getTrashed($limit = 500) {
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 500;
        if ($limit > 5000) $limit = 5000;

        $sql = "SELECT df.*, 
                       u.full_name as uploaded_by_name,
                       du.full_name as deleted_by_name,
                       f.name as folder_name,
                       f.path as folder_path
                FROM document_files df
                LEFT JOIN users u ON df.uploaded_by = u.id
                LEFT JOIN users du ON df.deleted_by = du.id
                LEFT JOIN folders f ON df.folder_id = f.id
                WHERE df.is_deleted = 1
                ORDER BY df.deleted_at DESC
                LIMIT {$limit}";

        return $this->db->fetchAll($sql, []);
    }

    /**
     * Restore deleted file
     */
    public function restore($fileId) {
        $sql = 'UPDATE document_files SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?';
        return $this->db->execute($sql, [(int)$fileId]) > 0;
    }

    /**
     * Permanently delete file (DB row + physical file)
     */
    public function permanentDelete($fileId) {
        $fileId = (int)$fileId;
        if ($fileId <= 0) return false;

        $file = $this->findById($fileId, true);
        if (!$file) return false;

        if (!empty($file['thumbnail_path']) && file_exists($file['thumbnail_path'])) {
            @unlink($file['thumbnail_path']);
        }
        if (!empty($file['file_path']) && file_exists($file['file_path'])) {
            @unlink($file['file_path']);
        }

        return $this->db->execute('DELETE FROM document_files WHERE id = ?', [$fileId]) > 0;
    }

    /**
     * Get files by multiple folder ids (used for permanent folder delete)
     */
    public function getFilesByFolderIds($folderIds) {
        if (!is_array($folderIds) || empty($folderIds)) return [];
        $ids = array_values(array_filter(array_map('intval', $folderIds), fn($v) => $v > 0));
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM document_files WHERE folder_id IN ({$placeholders})";
        return $this->db->fetchAll($sql, $ids);
    }

    /**
     * Update file metadata (does not rename physical file)
     */
    public function updateMetadata($fileId, $data) {
        $fileId = (int)$fileId;
        if ($fileId <= 0 || !is_array($data) || empty($data)) {
            return false;
        }

        $allowed = ['original_name', 'description', 'tags', 'is_public'];
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) continue;
            $fields[] = "$key = ?";
            $params[] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $fileId;
        $sql = 'UPDATE document_files SET ' . implode(', ', $fields) . ' WHERE id = ?';

        try {
            return $this->db->execute($sql, $params) > 0;
        } catch (Exception $e) {
            error_log('File metadata update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all files (for global search UI)
     */
    public function getAllFiles() {
        $sql = "SELECT df.*, f.name as folder_name, f.path as folder_path
                FROM document_files df
                LEFT JOIN folders f ON df.folder_id = f.id
                WHERE df.is_deleted = 0
                ORDER BY df.original_name ASC";
        return $this->db->fetchAll($sql, []);
    }

    /**
     * Move file to different folder
     */
    public function moveToFolder($fileId, $newFolderId, $userId) {
        try {
            $file = $this->findById($fileId);
            if (!$file) return false;

            // Get new folder path
            $newPath = $this->getFileStoragePath($newFolderId) . $file['file_name'];
            $newDir = dirname($newPath);

            // Create directory if not exists
            if (!is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }

            // Move physical file
            if (file_exists($file['file_path'])) {
                if (!rename($file['file_path'], $newPath)) {
                    return false;
                }

                // Move thumbnail if exists
                if ($file['thumbnail_path'] && file_exists($file['thumbnail_path'])) {
                    $newThumbnailPath = dirname($newPath) . '/thumbnails/' . basename($file['thumbnail_path']);
                    $thumbnailDir = dirname($newThumbnailPath);
                    if (!is_dir($thumbnailDir)) {
                        mkdir($thumbnailDir, 0755, true);
                    }
                    rename($file['thumbnail_path'], $newThumbnailPath);
                }
            }

            // Update database
            $sql = 'UPDATE document_files SET folder_id = ?, file_path = ? WHERE id = ?';
            return $this->db->execute($sql, [$newFolderId, $newPath, $fileId]) > 0;
        } catch (Exception $e) {
            error_log("File move error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete file (soft delete)
     */
    public function delete($fileId, $userId) {
        $sql = 'UPDATE document_files SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?';
        try {
            return $this->db->execute($sql, [$userId, $fileId]) > 0;
        } catch (Exception $e) {
            error_log("File delete error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file statistics
     */
    public function getStatistics($folderId = null) {
        $whereClause = 'WHERE is_deleted = 0';
        $params = [];

        if ($folderId !== null) {
            $whereClause .= ' AND folder_id = ?';
            $params[] = $folderId;
        }

        $sql = "SELECT 
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size,
                    COUNT(CASE WHEN is_image = 1 THEN 1 END) as image_count,
                    COUNT(CASE WHEN is_document = 1 THEN 1 END) as document_count,
                    COUNT(CASE WHEN is_video = 1 THEN 1 END) as video_count,
                    COUNT(CASE WHEN is_audio = 1 THEN 1 END) as audio_count,
                    AVG(file_size) as avg_file_size,
                    MAX(created_at) as latest_upload
                FROM document_files 
                $whereClause";

        return $this->db->fetch($sql, $params);
    }

    /**
     * Search files across all folders
     */
    public function searchFiles($query, $options = []) {
        $searchTerm = '%' . $query . '%';
        $whereClause = 'WHERE is_deleted = 0 AND (original_name LIKE ? OR tags LIKE ? OR description LIKE ?)';
        $params = [$searchTerm, $searchTerm, $searchTerm];

        if (!empty($options['folder_id'])) {
            $whereClause .= ' AND folder_id = ?';
            $params[] = $options['folder_id'];
        }

        if (!empty($options['file_type'])) {
            $whereClause .= ' AND file_type = ?';
            $params[] = $options['file_type'];
        }

        $orderBy = $options['sort'] ?? 'created_at DESC';
        $limit = $options['limit'] ?? 20;

        $sql = "SELECT df.*, u.full_name as uploaded_by_name, f.name as folder_name 
                FROM document_files df 
                LEFT JOIN users u ON df.uploaded_by = u.id 
                LEFT JOIN folders f ON df.folder_id = f.id 
                $whereClause 
                ORDER BY $orderBy 
                LIMIT $limit";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Update file access time
     */
    public function updateLastAccessed($fileId) {
        $sql = 'UPDATE document_files SET last_accessed = NOW(), download_count = download_count + 1 WHERE id = ?';
        $this->db->execute($sql, [$fileId]);
    }

    /**
     * Get recent files
     */
    public function getRecentFiles($limit = 10, $userId = null) {
        $whereClause = 'WHERE df.is_deleted = 0';
        $params = [];

        if ($userId) {
            $whereClause .= ' AND df.uploaded_by = ?';
            $params[] = $userId;
        }

        $sql = "SELECT df.*, u.full_name as uploaded_by_name, f.name as folder_name 
                FROM document_files df 
                LEFT JOIN users u ON df.uploaded_by = u.id 
                LEFT JOIN folders f ON df.folder_id = f.id 
                $whereClause 
                ORDER BY df.created_at DESC 
                LIMIT $limit";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Format file size
     */
    public static function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}