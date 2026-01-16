<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Check</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Installation Verification</h1>
            
            <?php
            $checks = [];
            $allPassed = true;
            
            // Check 1: PHP Version
            $phpVersion = phpversion();
            $phpCheck = version_compare($phpVersion, '7.4', '>=');
            $checks[] = [
                'name' => 'PHP Version (>= 7.4)',
                'status' => $phpCheck,
                'message' => "Current: PHP $phpVersion"
            ];
            if (!$phpCheck) $allPassed = false;
            
            // Check 2: Config file
            $configCheck = file_exists('config.php');
            $checks[] = [
                'name' => 'Configuration File',
                'status' => $configCheck,
                'message' => $configCheck ? 'config.php found' : 'config.php not found'
            ];
            if (!$configCheck) $allPassed = false;
            
            // Check 3: Database connection
            $dbCheck = false;
            $dbMessage = '';
            try {
                require_once 'config.php';
                require_once 'db.php';
                $db = Database::getInstance()->getConnection();
                $dbCheck = true;
                $dbMessage = 'Connected successfully';
            } catch(Exception $e) {
                $dbMessage = 'Error: ' . $e->getMessage();
                $allPassed = false;
            }
            $checks[] = [
                'name' => 'Database Connection',
                'status' => $dbCheck,
                'message' => $dbMessage
            ];
            
            // Check 4: Tables exist
            $tablesCheck = false;
            $tablesMessage = '';
            if ($dbCheck) {
                try {
                    $stmt = $db->query("SHOW TABLES LIKE 'folders'");
                    $foldersTable = $stmt->fetch();
                    
                    $stmt = $db->query("SHOW TABLES LIKE 'files'");
                    $filesTable = $stmt->fetch();
                    
                    if ($foldersTable && $filesTable) {
                        $tablesCheck = true;
                        $tablesMessage = 'All tables created';
                    } else {
                        $tablesMessage = 'Tables missing. Run database.sql';
                        $allPassed = false;
                    }
                } catch(Exception $e) {
                    $tablesMessage = 'Error checking tables: ' . $e->getMessage();
                    $allPassed = false;
                }
            } else {
                $tablesMessage = 'Cannot check (database not connected)';
                $allPassed = false;
            }
            $checks[] = [
                'name' => 'Database Tables',
                'status' => $tablesCheck,
                'message' => $tablesMessage
            ];
            
            // Check 5: Upload directory
            $uploadDir = __DIR__ . '/uploads/';
            $uploadCheck = false;
            $uploadMessage = '';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (is_dir($uploadDir) && is_writable($uploadDir)) {
                $uploadCheck = true;
                $uploadMessage = 'Directory exists and is writable';
            } else {
                $uploadMessage = 'Directory not writable. Check permissions.';
                $allPassed = false;
            }
            $checks[] = [
                'name' => 'Upload Directory',
                'status' => $uploadCheck,
                'message' => $uploadMessage
            ];
            
            // Check 6: PDO MySQL extension
            $pdoCheck = extension_loaded('pdo_mysql');
            $checks[] = [
                'name' => 'PDO MySQL Extension',
                'status' => $pdoCheck,
                'message' => $pdoCheck ? 'Extension loaded' : 'Extension not found'
            ];
            if (!$pdoCheck) $allPassed = false;
            
            // Check 7: File upload enabled
            $fileUploadCheck = ini_get('file_uploads');
            $checks[] = [
                'name' => 'File Uploads',
                'status' => $fileUploadCheck,
                'message' => $fileUploadCheck ? 'Enabled' : 'Disabled in php.ini'
            ];
            if (!$fileUploadCheck) $allPassed = false;
            ?>
            
            <!-- Status Banner -->
            <div class="mb-6 p-4 rounded-lg <?php echo $allPassed ? 'bg-green-100 border border-green-400' : 'bg-red-100 border border-red-400'; ?>">
                <?php if ($allPassed): ?>
                    <div class="flex items-center text-green-800">
                        <i class="fas fa-check-circle text-2xl mr-3"></i>
                        <div>
                            <h2 class="text-xl font-bold">Installation Complete!</h2>
                            <p class="text-sm">All checks passed. Your system is ready to use.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-center text-red-800">
                        <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
                        <div>
                            <h2 class="text-xl font-bold">Installation Issues Detected</h2>
                            <p class="text-sm">Please fix the issues below before using the system.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Checks List -->
            <div class="space-y-3 mb-8">
                <?php foreach($checks as $check): ?>
                    <div class="flex items-start p-4 rounded-lg border <?php echo $check['status'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'; ?>">
                        <div class="flex-shrink-0 mt-1">
                            <?php if ($check['status']): ?>
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-red-600 text-xl"></i>
                            <?php endif; ?>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="font-semibold text-gray-800"><?php echo $check['name']; ?></h3>
                            <p class="text-sm text-gray-600"><?php echo $check['message']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-4">
                <?php if ($allPassed): ?>
                    <a href="index.html" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                        Launch Repository →
                    </a>
                    <a href="info.php" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition font-semibold">
                        View System Info
                    </a>
                <?php else: ?>
                    <button onclick="location.reload()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                        <i class="fas fa-sync-alt mr-2"></i>Re-check
                    </button>
                    <a href="SETUP.md" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition font-semibold">
                        View Setup Guide
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Help Section -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="font-semibold text-blue-900 mb-2">Need Help?</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Check SETUP.md for quick setup instructions</li>
                    <li>• Read README.md for detailed documentation</li>
                    <li>• Ensure XAMPP Apache and MySQL are running</li>
                    <li>• Import database.sql into MySQL</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
