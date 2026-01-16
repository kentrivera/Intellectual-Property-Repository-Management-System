-- Fix Password Hashes for Admin and Staff Users
-- Run this SQL in phpMyAdmin to fix login issue

USE ip_repository_db;

-- Update admin password to 'Admin@123'
UPDATE users SET password_hash = '$2y$10$wYO9zt5Y3bXXhS/hM2f84uMUQKlfDJaic6FQCsCyiivHCPn6dPGgy' WHERE username = 'admin';

-- Update staff password to 'Staff@123'
UPDATE users SET password_hash = '$2y$10$Y6owXDaYKhG5Ls9LEy2P1.JAL5eO59/wjnhIX7r8L6S.XnlFy4kHu' WHERE username = 'staff';

-- Verify the changes
SELECT id, username, email, role, status, LEFT(password_hash, 30) as hash_preview FROM users WHERE username IN ('admin', 'staff');
