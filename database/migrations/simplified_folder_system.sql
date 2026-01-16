-- Simplified Enhanced Folder-based File Management System Migration
-- This migration enhances the existing schema with better folder relationships

-- First, let's improve the folders table
ALTER TABLE folders 
ADD COLUMN IF NOT EXISTS path VARCHAR(1000) NULL COMMENT 'Full folder path',
ADD COLUMN IF NOT EXISTS level INT DEFAULT 0 COMMENT 'Folder depth level',
ADD COLUMN IF NOT EXISTS is_system_folder TINYINT(1) DEFAULT 0 COMMENT 'System folders like Patents, etc',
ADD COLUMN IF NOT EXISTS color VARCHAR(20) DEFAULT '#6B7280' COMMENT 'Folder display color',
ADD COLUMN IF NOT EXISTS description TEXT NULL;

-- Add indexes
ALTER TABLE folders 
ADD INDEX IF NOT EXISTS idx_path (path),
ADD INDEX IF NOT EXISTS idx_level (level),
ADD INDEX IF NOT EXISTS idx_system_folder (is_system_folder);

-- Create a more comprehensive documents table for file management
CREATE TABLE IF NOT EXISTS document_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folder_id INT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(1000) NOT NULL,
    file_size BIGINT NOT NULL COMMENT 'Size in bytes',
    file_type VARCHAR(100) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_hash VARCHAR(64) NOT NULL COMMENT 'SHA-256 hash for duplicate detection',
    thumbnail_path VARCHAR(500) NULL,
    is_image TINYINT(1) DEFAULT 0,
    is_document TINYINT(1) DEFAULT 0,
    is_video TINYINT(1) DEFAULT 0,
    is_audio TINYINT(1) DEFAULT 0,
    uploaded_by INT NOT NULL,
    upload_ip VARCHAR(45) NULL,
    tags TEXT NULL COMMENT 'Comma-separated tags',
    description TEXT NULL,
    is_favorite TINYINT(1) DEFAULT 0,
    is_public TINYINT(1) DEFAULT 0 COMMENT 'Public access without login',
    download_count INT DEFAULT 0,
    last_accessed TIMESTAMP NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_at TIMESTAMP NULL,
    deleted_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_folder (folder_id),
    INDEX idx_file_type (file_type),
    INDEX idx_mime_type (mime_type),
    INDEX idx_hash (file_hash),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_deleted (is_deleted),
    INDEX idx_public (is_public),
    INDEX idx_favorite (is_favorite),
    INDEX idx_created_at (created_at),
    INDEX idx_file_size (file_size),
    INDEX idx_last_accessed (last_accessed),
    FULLTEXT idx_search (file_name, original_name, tags, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints
ALTER TABLE document_files 
ADD CONSTRAINT IF NOT EXISTS fk_document_files_folder 
FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT IF NOT EXISTS fk_document_files_uploaded_by 
FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT IF NOT EXISTS fk_document_files_deleted_by 
FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- Link IP documents to the new file management system
ALTER TABLE ip_documents 
ADD COLUMN IF NOT EXISTS document_file_id INT NULL AFTER ip_record_id,
ADD CONSTRAINT IF NOT EXISTS fk_ip_documents_file 
FOREIGN KEY (document_file_id) REFERENCES document_files(id) ON DELETE SET NULL;

-- Create folder permissions table for access control
CREATE TABLE IF NOT EXISTS folder_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folder_id INT NOT NULL,
    user_id INT NOT NULL,
    permission_type ENUM('read', 'write', 'admin') NOT NULL DEFAULT 'read',
    granted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_folder_user (folder_id, user_id),
    INDEX idx_folder (folder_id),
    INDEX idx_user (user_id),
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create file sharing table
CREATE TABLE IF NOT EXISTS file_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    share_token VARCHAR(64) UNIQUE NOT NULL,
    shared_by INT NOT NULL,
    shared_with_email VARCHAR(100) NULL,
    permission_type ENUM('view', 'download') NOT NULL DEFAULT 'view',
    password_hash VARCHAR(255) NULL,
    expires_at TIMESTAMP NULL,
    max_downloads INT DEFAULT 0 COMMENT '0 = unlimited',
    download_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_file (file_id),
    INDEX idx_token (share_token),
    INDEX idx_shared_by (shared_by),
    INDEX idx_expires (expires_at),
    INDEX idx_active (is_active),
    FOREIGN KEY (file_id) REFERENCES document_files(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert system folders (ignore if they already exist)
INSERT IGNORE INTO folders (name, parent_id, created_by, is_system_folder, color, description, path, level) VALUES
('Patents', NULL, 1, 1, '#3B82F6', 'Patent documents and related files', '/Patents', 0),
('Trademarks', NULL, 1, 1, '#10B981', 'Trademark registrations and applications', '/Trademarks', 0),
('Copyrights', NULL, 1, 1, '#8B5CF6', 'Copyright protected materials', '/Copyrights', 0),
('Industrial Designs', NULL, 1, 1, '#F59E0B', 'Industrial design registrations', '/Industrial Designs', 0),
('Archived', NULL, 1, 1, '#6B7280', 'Archived documents', '/Archived', 0),
('Recent', NULL, 1, 1, '#6366F1', 'Recently added documents', '/Recent', 0);

-- Create a view for folder statistics
CREATE OR REPLACE VIEW v_folder_stats AS
SELECT 
    f.id,
    f.name,
    f.path,
    f.parent_id,
    f.level,
    f.color,
    f.is_system_folder,
    COUNT(df.id) as file_count,
    COALESCE(SUM(df.file_size), 0) as total_size,
    COUNT(CASE WHEN df.is_image = 1 THEN 1 END) as image_count,
    COUNT(CASE WHEN df.is_document = 1 THEN 1 END) as document_count,
    COUNT(CASE WHEN df.is_video = 1 THEN 1 END) as video_count,
    COUNT(CASE WHEN df.is_audio = 1 THEN 1 END) as audio_count,
    MAX(df.created_at) as last_file_added,
    f.created_at,
    u.full_name as created_by_name
FROM folders f
LEFT JOIN document_files df ON f.id = df.folder_id AND df.is_deleted = 0
LEFT JOIN users u ON f.created_by = u.id
WHERE f.is_archived = 0
GROUP BY f.id, f.name, f.path, f.parent_id, f.level, f.color, f.is_system_folder, f.created_at, u.full_name;