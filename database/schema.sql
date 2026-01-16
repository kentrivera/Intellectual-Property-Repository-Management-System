-- ============================================
-- Intellectual Property Repository Management System
-- Database Schema
-- ============================================

-- Create Database
CREATE DATABASE IF NOT EXISTS ip_repository_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ip_repository_db;

-- ============================================
-- Table: users
-- Stores user accounts (Admin, Staff/Viewer)
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: ip_types
-- Stores intellectual property categories
-- ============================================
CREATE TABLE ip_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_name (type_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: ip_records
-- Stores intellectual property records
-- ============================================
CREATE TABLE ip_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_type_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    owner VARCHAR(100) NOT NULL,
    filing_date DATE,
    status ENUM('pending', 'approved', 'rejected', 'active', 'expired') NOT NULL DEFAULT 'pending',
    tags TEXT COMMENT 'Comma-separated keywords',
    reference_number VARCHAR(100) UNIQUE,
    created_by INT NOT NULL,
    is_archived TINYINT(1) DEFAULT 0,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ip_type_id) REFERENCES ip_types(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_title (title),
    INDEX idx_ip_type (ip_type_id),
    INDEX idx_owner (owner),
    INDEX idx_status (status),
    INDEX idx_reference (reference_number),
    INDEX idx_archived (is_archived),
    FULLTEXT idx_search (title, description, tags, owner)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: ip_documents
-- Stores documents linked to IP records
-- ============================================
CREATE TABLE ip_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_record_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL COMMENT 'Size in bytes',
    current_version INT DEFAULT 1,
    uploaded_by INT NOT NULL,
    is_deleted TINYINT(1) DEFAULT 0 COMMENT 'Soft delete flag',
    deleted_at TIMESTAMP NULL,
    deleted_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ip_record_id) REFERENCES ip_records(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ip_record (ip_record_id),
    INDEX idx_file_name (file_name),
    INDEX idx_deleted (is_deleted),
    FULLTEXT idx_filename_search (file_name, original_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: document_versions
-- Tracks document version history
-- ============================================
CREATE TABLE document_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    version_number INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    uploaded_by INT NOT NULL,
    version_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES ip_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_version (document_id, version_number),
    INDEX idx_document (document_id),
    INDEX idx_version (version_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: download_requests
-- Manages document download permission requests
-- ============================================
CREATE TABLE download_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    requested_by INT NOT NULL,
    request_reason TEXT,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by INT NULL,
    review_notes TEXT,
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    download_token VARCHAR(64) UNIQUE COMMENT 'Secure token for approved downloads',
    token_expires_at TIMESTAMP NULL,
    download_limit INT DEFAULT 3 COMMENT 'Number of allowed downloads',
    download_count INT DEFAULT 0 COMMENT 'Number of times downloaded',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES ip_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_document (document_id),
    INDEX idx_requester (requested_by),
    INDEX idx_status (status),
    INDEX idx_token (download_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: download_logs
-- Logs all document downloads
-- ============================================
CREATE TABLE download_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    document_id INT NOT NULL,
    user_id INT NOT NULL,
    download_token VARCHAR(64),
    ip_address VARCHAR(45),
    user_agent TEXT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES download_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES ip_documents(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_request (request_id),
    INDEX idx_document (document_id),
    INDEX idx_user (user_id),
    INDEX idx_downloaded_at (downloaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: activity_logs
-- Comprehensive audit trail for all system actions
-- ============================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action_type VARCHAR(50) NOT NULL COMMENT 'login, logout, upload, view, delete, approve, reject, etc.',
    entity_type VARCHAR(50) COMMENT 'user, ip_record, document, download_request',
    entity_id INT COMMENT 'ID of affected entity',
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON COMMENT 'Additional context data',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default IP Types
-- ============================================
INSERT INTO ip_types (type_name, description) VALUES
('Patent', 'Exclusive rights granted for an invention'),
('Trademark', 'Distinctive signs that identify goods or services'),
('Copyright', 'Legal right to creative works'),
('Industrial Design', 'Ornamental or aesthetic aspect of an article');

-- ============================================
-- Insert Default Admin Account
-- Username: admin
-- Password: Admin@123
-- ============================================
INSERT INTO users (username, email, password_hash, full_name, role, status) VALUES
('admin', 'admin@iprepo.com', '$2y$10$wYO9zt5Y3bXXhS/hM2f84uMUQKlfDJaic6FQCsCyiivHCPn6dPGgy', 'System Administrator', 'admin', 'active');

-- ============================================
-- Insert Sample Staff Account
-- Username: staff
-- Password: Staff@123
-- ============================================
INSERT INTO users (username, email, password_hash, full_name, role, status) VALUES
('staff', 'staff@iprepo.com', '$2y$10$Y6owXDaYKhG5Ls9LEy2P1.JAL5eO59/wjnhIX7r8L6S.XnlFy4kHu', 'Staff User', 'staff', 'active');

-- ============================================
-- Create Views for Reporting
-- ============================================

-- View: User Activity Summary
CREATE VIEW v_user_activity_summary AS
SELECT 
    u.id,
    u.username,
    u.full_name,
    u.role,
    COUNT(DISTINCT al.id) as total_actions,
    MAX(al.created_at) as last_activity,
    COUNT(DISTINCT CASE WHEN al.action_type = 'login' THEN al.id END) as login_count,
    COUNT(DISTINCT CASE WHEN al.action_type = 'upload' THEN al.id END) as upload_count,
    COUNT(DISTINCT CASE WHEN al.action_type = 'download' THEN al.id END) as download_count
FROM users u
LEFT JOIN activity_logs al ON u.id = al.user_id
GROUP BY u.id, u.username, u.full_name, u.role;

-- View: Document Request Summary
CREATE VIEW v_document_request_summary AS
SELECT 
    d.id as document_id,
    d.original_name,
    ir.title as ip_title,
    COUNT(dr.id) as total_requests,
    COUNT(CASE WHEN dr.status = 'pending' THEN 1 END) as pending_requests,
    COUNT(CASE WHEN dr.status = 'approved' THEN 1 END) as approved_requests,
    COUNT(CASE WHEN dr.status = 'rejected' THEN 1 END) as rejected_requests,
    COALESCE(SUM(dr.download_count), 0) as total_downloads
FROM ip_documents d
INNER JOIN ip_records ir ON d.ip_record_id = ir.id
LEFT JOIN download_requests dr ON d.id = dr.document_id
GROUP BY d.id, d.original_name, ir.title;

-- View: IP Records with Document Count
CREATE VIEW v_ip_records_summary AS
SELECT 
    ir.id,
    ir.title,
    it.type_name,
    ir.owner,
    ir.status,
    ir.filing_date,
    ir.reference_number,
    COUNT(d.id) as document_count,
    u.full_name as created_by_name,
    ir.created_at
FROM ip_records ir
INNER JOIN ip_types it ON ir.ip_type_id = it.id
INNER JOIN users u ON ir.created_by = u.id
LEFT JOIN ip_documents d ON ir.id = d.ip_record_id AND d.is_deleted = 0
WHERE ir.is_archived = 0
GROUP BY ir.id, ir.title, it.type_name, ir.owner, ir.status, ir.filing_date, 
         ir.reference_number, u.full_name, ir.created_at;

-- ============================================
-- End of Schema
-- ============================================
