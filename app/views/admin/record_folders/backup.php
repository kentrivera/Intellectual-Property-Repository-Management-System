<?php
/**
 * Simple Database Backup Script
 * Run this file to create a backup of your database
 */

require_once 'config.php';

// Backup directory
$backupDir = __DIR__ . '/backups/';
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Generate filename with timestamp
$backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// MySQL dump command
$command = sprintf(
    'mysqldump --user=%s --password=%s --host=%s %s > %s',
    DB_USER,
    DB_PASS,
    DB_HOST,
    DB_NAME,
    $backupFile
);

// Execute backup
system($command, $output);

if ($output === 0) {
    echo "✓ Backup created successfully!\n";
    echo "File: " . basename($backupFile) . "\n";
    echo "Location: " . $backupFile . "\n";
    echo "Size: " . round(filesize($backupFile) / 1024, 2) . " KB\n";
} else {
    echo "✗ Backup failed!\n";
    echo "Please check your MySQL credentials and path.\n";
}
?>
