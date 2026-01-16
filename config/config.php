<?php
/**
 * Configuration File
 * Contains all system configuration settings
 */

// Base Path Configuration
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('DOCUMENT_PATH', UPLOAD_PATH . '/documents');
define('TRASH_PATH', UPLOAD_PATH . '/trash');

// URL Configuration
define('BASE_URL', 'http://localhost/Intellectual%20Property%20Repository%20Management%20System/public');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ip_repository_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Session Configuration
define('SESSION_NAME', 'IP_REPO_SESSION');
define('SESSION_LIFETIME', 7200); // 2 hours

// File Upload Configuration
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

// Download Token Configuration
define('TOKEN_EXPIRY_HOURS', 24); // Token valid for 24 hours
define('DEFAULT_DOWNLOAD_LIMIT', 3); // Default number of allowed downloads

// Pagination
define('RECORDS_PER_PAGE', 10);

// Security
define('BCRYPT_COST', 10);

// Application Settings
define('APP_NAME', 'Intellectual Property Repository Management System');
define('APP_VERSION', '1.0.0');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
