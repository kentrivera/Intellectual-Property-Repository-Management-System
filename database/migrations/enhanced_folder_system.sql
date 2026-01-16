-- Enhanced Folder-based File Management System Migration
-- This migration enhances the existing schema with better folder relationships

-- First, let's improve the folders table
ALTER TABLE folders 
ADD COLUMN path VARCHAR(1000) NULL COMMENT 'Full folder path',
ADD COLUMN level INT DEFAULT 0 COMMENT 'Folder depth level',
ADD COLUMN is_system_folder TINYINT(1) DEFAULT 0 COMMENT 'System folders like Patents, etc',
ADD COLUMN color VARCHAR(20) DEFAULT '#6B7280' COMMENT 'Folder display color',
ADD COLUMN description TEXT NULL,
ADD INDEX idx_path (path),
ADD INDEX idx_level (level),
ADD INDEX idx_system_folder (is_system_folder);

-- Update existing foreign key constraint for better cascade handling
ALTER TABLE folders DROP FOREIGN KEY folders_ibfk_1;
ALTER TABLE folders 
ADD CONSTRAINT fk_folders_parent 
FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE ON UPDATE CASCADE;

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
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_folder (folder_id),
    INDEX idx_file_type (file_type),
    INDEX idx_mime_type (mime_type),
    INDEX idx_hash (file_hash),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_deleted (is_deleted),
    INDEX idx_public (is_public),
    INDEX idx_favorite (is_favorite),
    FULLTEXT idx_search (file_name, original_name, tags, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link IP documents to the new file management system
ALTER TABLE ip_documents 
ADD COLUMN document_file_id INT NULL AFTER ip_record_id,
ADD CONSTRAINT fk_ip_documents_file 
FOREIGN KEY (document_file_id) REFERENCES document_files(id) ON DELETE SET NULL;

-- Create folder permissions table for access control
CREATE TABLE folder_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folder_id INT NOT NULL,
    user_id INT NOT NULL,
    permission_type ENUM('read', 'write', 'admin') NOT NULL DEFAULT 'read',
    granted_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_folder_user (folder_id, user_id),
    INDEX idx_folder (folder_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create file sharing table
CREATE TABLE file_shares (
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
    FOREIGN KEY (file_id) REFERENCES document_files(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_file (file_id),
    INDEX idx_token (share_token),
    INDEX idx_shared_by (shared_by),
    INDEX idx_expires (expires_at),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert system folders
INSERT INTO folders (name, parent_id, created_by, is_system_folder, color, description, path, level) VALUES
('Patents', NULL, 1, 1, '#3B82F6', 'Patent documents and related files', '/Patents', 0),
('Trademarks', NULL, 1, 1, '#10B981', 'Trademark registrations and applications', '/Trademarks', 0),
('Copyrights', NULL, 1, 1, '#8B5CF6', 'Copyright protected materials', '/Copyrights', 0),
('Industrial Designs', NULL, 1, 1, '#F59E0B', 'Industrial design registrations', '/Industrial Designs', 0),
('Archived', NULL, 1, 1, '#6B7280', 'Archived documents', '/Archived', 0),
('Recent', NULL, 1, 1, '#6366F1', 'Recently added documents', '/Recent', 0);

-- Create a view for folder statistics
CREATE VIEW v_folder_stats AS
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

-- Create triggers to maintain folder paths
DELIMITER //

CREATE TRIGGER folders_after_insert
AFTER INSERT ON folders
FOR EACH ROW
BEGIN
    DECLARE parent_path VARCHAR(1000) DEFAULT '';
    DECLARE folder_level INT DEFAULT 0;
    
    IF NEW.parent_id IS NOT NULL THEN
        SELECT IFNULL(path, ''), level + 1 INTO parent_path, folder_level
        FROM folders WHERE id = NEW.parent_id;
    END IF;
    
    UPDATE folders 
    SET path = CONCAT(parent_path, '/', NEW.name),
        level = folder_level
    WHERE id = NEW.id;
END//

CREATE TRIGGER folders_after_update
AFTER UPDATE ON folders
FOR EACH ROW
BEGIN
    DECLARE parent_path VARCHAR(1000) DEFAULT '';
    DECLARE folder_level INT DEFAULT 0;
    
    IF NEW.parent_id IS NOT NULL THEN
        SELECT IFNULL(path, ''), level + 1 INTO parent_path, folder_level
        FROM folders WHERE id = NEW.parent_id;
    ELSE
        SET parent_path = '';
        SET folder_level = 0;
    END IF;
    
    UPDATE folders 
    SET path = CONCAT(parent_path, '/', NEW.name),
        level = folder_level
    WHERE id = NEW.id;
    
    -- Update children paths recursively (simplified version)
    UPDATE folders 
    SET path = REPLACE(path, OLD.path, NEW.path)
    WHERE path LIKE CONCAT(OLD.path, '%') AND id != NEW.id;
END//

DELIMITER ;

-- Add indexes for better performance
ALTER TABLE document_files 
ADD INDEX idx_created_at (created_at),
ADD INDEX idx_file_size (file_size),
ADD INDEX idx_last_accessed (last_accessed);

-- Create stored procedure for folder tree retrieval
DELIMITER //

CREATE PROCEDURE GetFolderTree(IN parent_folder_id INT)
BEGIN
    WITH RECURSIVE folder_tree AS (
        -- Base case: start with the specified parent (or root if NULL)
        SELECT id, name, parent_id, path, level, color, is_system_folder, 0 as depth
        FROM folders 
        WHERE (parent_id = parent_folder_id OR (parent_folder_id IS NULL AND parent_id IS NULL))
        AND is_archived = 0
        
        UNION ALL
        
        -- Recursive case: find children
        SELECT f.id, f.name, f.parent_id, f.path, f.level, f.color, f.is_system_folder, ft.depth + 1
        FROM folders f
        INNER JOIN folder_tree ft ON f.parent_id = ft.id
        WHERE f.is_archived = 0 AND ft.depth < 10 -- Prevent infinite recursion
    )
    SELECT * FROM folder_tree ORDER BY depth, name;
END//

DELIMITER ;