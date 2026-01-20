<?php
/**
 * Application Entry Point
 * Routes all requests through the router
 */

// Start session
session_name('IP_REPO_SESSION');
session_start();

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Load core classes
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Router.php';

// Initialize router
$router = new Router();

// =====================
// Public Routes
// =====================
$router->get('/', function() {
    header('Location: ' . BASE_URL . '/login');
});

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// =====================
// Admin Routes
// =====================
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/users', 'AdminController@users');
$router->post('/admin/users/create', 'AdminController@createUser');
$router->post('/admin/users/update-status', 'AdminController@updateUserStatus');
$router->post('/admin/users/delete', 'AdminController@deleteUser');

$router->get('/admin/ip-records', 'AdminController@ipRecords');
$router->get('/admin/ip-records/:id', 'AdminController@viewIPRecord');

// Legacy-compatible endpoints for the Record Folders UI (served via MVC)
$router->get('/admin/api_folders.php', 'RecordFoldersController@folders');
$router->post('/admin/api_folders.php', 'RecordFoldersController@folders');
$router->get('/admin/api_files.php', 'RecordFoldersController@files');
$router->post('/admin/api_files.php', 'RecordFoldersController@files');
$router->post('/admin/upload.php', 'RecordFoldersController@upload');

$router->post('/admin/folders/create', 'FolderController@create');
$router->post('/admin/folders/rename', 'FolderController@rename');
$router->post('/admin/folders/archive', 'FolderController@archive');
$router->post('/admin/folders/restore', 'FolderController@restore');
$router->post('/admin/folders/permanent-delete', 'FolderController@permanentDelete');

$router->get('/admin/download-requests', 'AdminController@downloadRequests');
$router->post('/admin/download-requests/approve', 'AdminController@approveRequest');
$router->post('/admin/download-requests/reject', 'AdminController@rejectRequest');

$router->get('/admin/trash', 'AdminController@trashBin');
$router->post('/admin/trash/empty', 'AdminController@emptyTrash');
$router->get('/admin/trash/serve', 'AdminController@serveTrashFile');
$router->get('/admin/activity-logs', 'AdminController@activityLogs');

// =====================
// Staff Routes
// =====================
$router->get('/staff/dashboard', 'StaffController@dashboard');
$router->get('/staff/ip-records', 'StaffController@ipRecords');
$router->get('/staff/ip-records/:id', 'StaffController@viewIPRecord');
$router->get('/staff/search', 'StaffController@search');
$router->get('/staff/my-requests', 'StaffController@myRequests');
$router->post('/staff/request-download', 'StaffController@requestDownload');

// =====================
// Common Routes (Both Admin & Staff)
// =====================
$router->get('/dashboard', function() {
    if (isset($_SESSION['role'])) {
        $prefix = $_SESSION['role'] === 'admin' ? 'admin' : 'staff';
        header('Location: ' . BASE_URL . '/' . $prefix . '/dashboard');
    } else {
        header('Location: ' . BASE_URL . '/login');
    }
});

// =====================
// Document Routes
// =====================
$router->post('/document/upload', 'DocumentController@upload');
$router->post('/document/upload-version', 'DocumentController@uploadVersion');
$router->post('/document/delete', 'DocumentController@delete');
$router->post('/document/restore', 'DocumentController@restore');
$router->post('/document/permanent-delete', 'DocumentController@permanentDelete');
$router->get('/document/download/:token', 'DocumentController@download');

// Trash actions for folder-based file manager (document_files)
$router->post('/document-file/restore', 'DocumentController@restoreFile');
$router->post('/document-file/permanent-delete', 'DocumentController@permanentDeleteFile');

// Backward-compatible (query-string) download endpoint used by some JS
$router->get('/document/download', 'DocumentController@downloadFile');

// Enhanced folder-based file manager routes
$router->get('/document/listFiles', 'DocumentController@listFiles');
$router->get('/document/searchFiles', 'DocumentController@searchFiles');
$router->get('/document/thumbnail', 'DocumentController@thumbnail');
$router->get('/document/downloadFile', 'DocumentController@downloadFile');
$router->post('/document/uploadToFolder', 'DocumentController@uploadToFolder');
$router->post('/document/moveFile', 'DocumentController@moveFile');
$router->post('/document/deleteFile', 'DocumentController@deleteFile');

// =====================
// IP Record Routes (Admin)
// =====================
$router->post('/ip-record/create', 'IPRecordController@create');
$router->post('/ip-record/update', 'IPRecordController@update');
$router->post('/ip-record/archive', 'IPRecordController@archive');
$router->post('/ip-record/delete', 'IPRecordController@delete');
$router->post('/ip-record/restore', 'IPRecordController@restore');
$router->post('/ip-record/permanent-delete', 'IPRecordController@permanentDelete');

// =====================
// 404 Handler
// =====================
$router->notFound(function() {
    http_response_code(404);
    echo '<h1>404 - Page Not Found</h1>';
});

// Dispatch the request
$router->dispatch();
