<?php
/**
 * System Configuration Checker
 * Run this file to verify your system setup
 * Access: http://localhost/Intellectual%20Property%20Repository%20Management%20System/check-system.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$checks = [];
$errors = 0;
$warnings = 0;

// Helper function
function checkItem($name, $status, $message, $type = 'info') {
    global $checks, $errors, $warnings;
    
    $checks[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'type' => $type
    ];
    
    if (!$status && $type === 'error') $errors++;
    if (!$status && $type === 'warning') $warnings++;
}

// Check PHP Version
$phpVersion = phpversion();
checkItem(
    'PHP Version',
    version_compare($phpVersion, '8.0.0', '>='),
    "PHP {$phpVersion} " . (version_compare($phpVersion, '8.0.0', '>=') ? '✓' : '✗ (Requires PHP 8.0+)'),
    version_compare($phpVersion, '8.0.0', '>=') ? 'success' : 'error'
);

// Check PDO MySQL
checkItem(
    'PDO MySQL Extension',
    extension_loaded('pdo_mysql'),
    extension_loaded('pdo_mysql') ? 'Installed ✓' : 'Not installed ✗',
    extension_loaded('pdo_mysql') ? 'success' : 'error'
);

// Check required extensions
$requiredExtensions = ['mbstring', 'json', 'session', 'fileinfo'];
foreach ($requiredExtensions as $ext) {
    checkItem(
        ucfirst($ext) . ' Extension',
        extension_loaded($ext),
        extension_loaded($ext) ? 'Installed ✓' : 'Not installed ✗',
        extension_loaded($ext) ? 'success' : 'warning'
    );
}

// Check directories
$baseDir = __DIR__;
$requiredDirs = [
    'uploads',
    'uploads/documents',
    'uploads/trash',
    'config',
    'database',
    'app',
    'public'
];

foreach ($requiredDirs as $dir) {
    $path = $baseDir . '/' . $dir;
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    
    if (strpos($dir, 'uploads') !== false) {
        checkItem(
            "Directory: {$dir}",
            $exists && $writable,
            $exists ? ($writable ? 'Writable ✓' : 'Not writable ✗') : 'Not found ✗',
            ($exists && $writable) ? 'success' : 'error'
        );
    } else {
        checkItem(
            "Directory: {$dir}",
            $exists,
            $exists ? 'Exists ✓' : 'Not found ✗',
            $exists ? 'success' : 'error'
        );
    }
}

// Check configuration file
$configFile = $baseDir . '/config/config.php';
checkItem(
    'Configuration File',
    file_exists($configFile),
    file_exists($configFile) ? 'Found ✓' : 'Not found ✗',
    file_exists($configFile) ? 'success' : 'error'
);

// Check database connection
if (file_exists($configFile)) {
    require_once $configFile;
    
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        checkItem(
            'Database Connection',
            true,
            'Connected successfully ✓',
            'success'
        );
        
        // Check tables
        $requiredTables = ['users', 'ip_types', 'ip_records', 'ip_documents', 'download_requests', 'activity_logs'];
        foreach ($requiredTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            $exists = $stmt->rowCount() > 0;
            
            checkItem(
                "Table: {$table}",
                $exists,
                $exists ? 'Exists ✓' : 'Not found ✗',
                $exists ? 'success' : 'error'
            );
        }
        
    } catch (PDOException $e) {
        checkItem(
            'Database Connection',
            false,
            'Failed: ' . $e->getMessage(),
            'error'
        );
    }
}

// Check Apache mod_rewrite
$modRewrite = false;
if (function_exists('apache_get_modules')) {
    $modRewrite = in_array('mod_rewrite', apache_get_modules());
}
checkItem(
    'Apache mod_rewrite',
    $modRewrite || !function_exists('apache_get_modules'),
    $modRewrite ? 'Enabled ✓' : (!function_exists('apache_get_modules') ? 'Cannot detect (may be enabled)' : 'Not enabled ✗'),
    $modRewrite ? 'success' : 'warning'
);

// Check .htaccess files
$htaccessFiles = [
    'public/.htaccess',
    'uploads/.htaccess'
];

foreach ($htaccessFiles as $file) {
    $path = $baseDir . '/' . $file;
    checkItem(
        "File: {$file}",
        file_exists($path),
        file_exists($path) ? 'Exists ✓' : 'Not found ✗',
        file_exists($path) ? 'success' : 'warning'
    );
}

// Check upload limits
$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');
checkItem(
    'Upload Max Filesize',
    true,
    "upload_max_filesize = {$uploadMax}",
    'info'
);
checkItem(
    'Post Max Size',
    true,
    "post_max_size = {$postMax}",
    'info'
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Configuration Checker</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">System Configuration Checker</h1>
            <p class="text-gray-600 mb-6">IP Repository Management System</p>
            
            <!-- Summary -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-2xl font-bold text-blue-600"><?= count($checks) ?></p>
                    <p class="text-sm text-blue-800">Total Checks</p>
                </div>
                <div class="bg-<?= $errors > 0 ? 'red' : 'green' ?>-50 border border-<?= $errors > 0 ? 'red' : 'green' ?>-200 rounded-lg p-4">
                    <p class="text-2xl font-bold text-<?= $errors > 0 ? 'red' : 'green' ?>-600"><?= $errors ?></p>
                    <p class="text-sm text-<?= $errors > 0 ? 'red' : 'green' ?>-800">Errors</p>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-2xl font-bold text-yellow-600"><?= $warnings ?></p>
                    <p class="text-sm text-yellow-800">Warnings</p>
                </div>
            </div>
            
            <?php if ($errors === 0 && $warnings === 0): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-green-700 font-medium">✓ All checks passed! Your system is ready.</p>
            </div>
            <?php elseif ($errors > 0): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-red-700 font-medium">✗ <?= $errors ?> critical error(s) found. Please fix before proceeding.</p>
            </div>
            <?php else: ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                <p class="text-yellow-700 font-medium">⚠ <?= $warnings ?> warning(s) found. System may work but some features might be limited.</p>
            </div>
            <?php endif; ?>
            
            <!-- Checks List -->
            <div class="space-y-2">
                <?php foreach ($checks as $check): ?>
                <?php
                $bgColor = 'gray';
                $icon = 'ℹ️';
                if ($check['type'] === 'success') {
                    $bgColor = 'green';
                    $icon = '✓';
                } elseif ($check['type'] === 'error') {
                    $bgColor = 'red';
                    $icon = '✗';
                } elseif ($check['type'] === 'warning') {
                    $bgColor = 'yellow';
                    $icon = '⚠';
                }
                ?>
                <div class="flex items-center justify-between p-3 bg-<?= $bgColor ?>-50 border border-<?= $bgColor ?>-200 rounded">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3"><?= $icon ?></span>
                        <div>
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($check['name']) ?></p>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($check['message']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Actions -->
            <div class="mt-8 pt-6 border-t">
                <?php if ($errors === 0): ?>
                <a href="public/index.php" class="inline-block px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Continue to Application →
                </a>
                <?php endif; ?>
                <button onclick="location.reload()" class="inline-block px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition ml-2">
                    Recheck
                </button>
            </div>
        </div>
        
        <div class="text-center mt-6 text-gray-600 text-sm">
            <p>Run this checker anytime to verify your system configuration</p>
        </div>
    </div>
</body>
</html>
