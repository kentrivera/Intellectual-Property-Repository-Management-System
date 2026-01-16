<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Info - Dynamic Folder Repository</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">System Information</h1>
            
            <?php
            require_once 'config.php';
            require_once 'db.php';
            
            // Check database connection
            try {
                $db = Database::getInstance()->getConnection();
                $dbStatus = '<span class="text-green-600">✓ Connected</span>';
                
                // Get counts
                $stmt = $db->query("SELECT COUNT(*) as count FROM folders");
                $folderCount = $stmt->fetch()['count'];
                
                $stmt = $db->query("SELECT COUNT(*) as count FROM files");
                $fileCount = $stmt->fetch()['count'];
                
                $stmt = $db->query("SELECT SUM(file_size) as total FROM files");
                $totalSize = $stmt->fetch()['total'] ?? 0;
                
            } catch(Exception $e) {
                $dbStatus = '<span class="text-red-600">✗ ' . $e->getMessage() . '</span>';
                $folderCount = 'N/A';
                $fileCount = 'N/A';
                $totalSize = 'N/A';
            }
            
            // Check upload directory
            $uploadDirStatus = is_writable(UPLOAD_DIR) 
                ? '<span class="text-green-600">✓ Writable</span>' 
                : '<span class="text-red-600">✗ Not Writable</span>';
            
            // Format total size
            function formatBytes($bytes) {
                if ($bytes == 'N/A') return 'N/A';
                $units = ['B', 'KB', 'MB', 'GB'];
                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }
                return round($bytes, 2) . ' ' . $units[$i];
            }
            ?>
            
            <div class="space-y-6">
                <!-- PHP Info -->
                <div class="border-b pb-4">
                    <h2 class="text-xl font-semibold text-gray-700 mb-3">PHP Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600">PHP Version:</span>
                            <span class="font-semibold ml-2"><?php echo phpversion(); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Max Upload Size:</span>
                            <span class="font-semibold ml-2"><?php echo ini_get('upload_max_filesize'); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Max Post Size:</span>
                            <span class="font-semibold ml-2"><?php echo ini_get('post_max_size'); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Memory Limit:</span>
                            <span class="font-semibold ml-2"><?php echo ini_get('memory_limit'); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Database Info -->
                <div class="border-b pb-4">
                    <h2 class="text-xl font-semibold text-gray-700 mb-3">Database Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600">Connection Status:</span>
                            <span class="font-semibold ml-2"><?php echo $dbStatus; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Database Name:</span>
                            <span class="font-semibold ml-2"><?php echo DB_NAME; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Folders:</span>
                            <span class="font-semibold ml-2"><?php echo $folderCount; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Files:</span>
                            <span class="font-semibold ml-2"><?php echo $fileCount; ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Storage Info -->
                <div class="border-b pb-4">
                    <h2 class="text-xl font-semibold text-gray-700 mb-3">Storage Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-600">Upload Directory:</span>
                            <span class="font-semibold ml-2"><?php echo UPLOAD_DIR; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Directory Status:</span>
                            <span class="font-semibold ml-2"><?php echo $uploadDirStatus; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Storage Used:</span>
                            <span class="font-semibold ml-2"><?php echo formatBytes($totalSize); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Max File Size:</span>
                            <span class="font-semibold ml-2"><?php echo formatBytes(MAX_FILE_SIZE); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Allowed Extensions -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-3">Allowed File Types</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach(ALLOWED_EXTENSIONS as $ext): ?>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                                .<?php echo $ext; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex space-x-4">
                <a href="index.html" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    ← Back to Repository
                </a>
                <a href="backup.php" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    Create Backup
                </a>
            </div>
        </div>
    </div>
</body>
</html>
