-- ============================================
-- Password Hash Generator Reference
-- ============================================

-- The default passwords are hashed using bcrypt
-- Cost factor: 10

-- To generate a new password hash in PHP:
-- password_hash('YourPasswordHere', PASSWORD_BCRYPT, ['cost' => 10]);

-- Default Password Hashes:
-- 'Admin@123' -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- 'Staff@123' -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ============================================
-- Sample Additional User Creation
-- ============================================

-- Create a new admin user
INSERT INTO users (username, email, password_hash, full_name, role, status) VALUES
('johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'admin', 'active');

-- Create a new staff user
INSERT INTO users (username, email, password_hash, full_name, role, status) VALUES
('janesmith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'staff', 'active');

-- ============================================
-- Sample IP Records Data (Optional)
-- ============================================

-- Insert sample IP record
INSERT INTO ip_records (ip_type_id, title, description, owner, filing_date, status, tags, reference_number, created_by) VALUES
(1, 'Innovative Widget Technology', 'A revolutionary widget design that improves efficiency by 50%', 'TechCorp Inc.', '2024-01-15', 'active', 'technology, innovation, widget', 'PAT-2024-001', 1);

-- Insert another sample
INSERT INTO ip_records (ip_type_id, title, description, owner, filing_date, status, tags, reference_number, created_by) VALUES
(2, 'BrandLogo™', 'Official trademark for company branding', 'Creative Agency Ltd.', '2023-12-10', 'approved', 'branding, logo, trademark', 'TM-2023-045', 1);

-- ============================================
-- Useful Queries
-- ============================================

-- View all users
SELECT id, username, email, full_name, role, status, created_at FROM users;

-- View all IP records with type
SELECT ir.*, it.type_name 
FROM ip_records ir
INNER JOIN ip_types it ON ir.ip_type_id = it.id;

-- View all pending download requests
SELECT dr.*, d.original_name, u.full_name as requester
FROM download_requests dr
INNER JOIN ip_documents d ON dr.document_id = d.id
INNER JOIN users u ON dr.requested_by = u.id
WHERE dr.status = 'pending';

-- View recent activity
SELECT al.*, u.full_name 
FROM activity_logs al
LEFT JOIN users u ON al.user_id = u.id
ORDER BY al.created_at DESC
LIMIT 20;

-- ============================================
-- Maintenance Queries
-- ============================================

-- Delete expired download tokens
DELETE FROM download_requests 
WHERE status = 'approved' 
AND token_expires_at < NOW();

-- Clean old activity logs (older than 90 days)
DELETE FROM activity_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Find large files
SELECT original_name, file_size, file_size/1024/1024 as size_mb
FROM ip_documents
WHERE is_deleted = 0
ORDER BY file_size DESC
LIMIT 10;

-- ============================================
-- Backup Commands
-- ============================================

-- Export database (run in command line):
-- mysqldump -u root -p ip_repository_db > backup.sql

-- Import database (run in command line):
-- mysql -u root -p ip_repository_db < backup.sql

-- ============================================
