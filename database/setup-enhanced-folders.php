<?php
/**
 * Database Setup Script for Enhanced Folder System
 * Run this script to apply the enhanced folder-based file management system
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

try {
    $db = Database::getInstance();
    echo "Connected to database successfully.\n";

    // Read and execute the migration SQL
    $migrationFile = __DIR__ . '/migrations/simplified_folder_system.sql';
    $sql = file_get_contents($migrationFile);
    
    if ($sql === false) {
        throw new Exception("Could not read migration file: $migrationFile");
    }

    // Split SQL statements and filter
    $statements = preg_split('/;\s*$/m', $sql, -1, PREG_SPLIT_NO_EMPTY);
    $statements = array_filter(array_map('trim', $statements), function($stmt) {
        return !empty($stmt) && 
               !preg_match('/^--/', $stmt) && 
               !preg_match('/^\/\*/', $stmt);
    });

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        try {
            // Skip empty statements and comments
            if (empty(trim($statement))) continue;
            
            // Handle multi-line statements
            $statement = trim($statement);
            if (empty($statement)) continue;
            
            // Execute the statement
            $result = $db->execute($statement, []);
            $successCount++;
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            
        } catch (Exception $e) {
            $errorCount++;
            echo "✗ Error in statement: " . substr($statement, 0, 50) . "...\n";
            echo "  Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== Migration Summary ===\n";
    echo "Successful statements: $successCount\n";
    echo "Failed statements: $errorCount\n";

    // Verify system folders were created
    try {
        $folders = $db->fetchAll("SELECT id, name, IFNULL(path, name) as path FROM folders WHERE is_system_folder = 1", []);
        echo "\nSystem folders created:\n";
        foreach ($folders as $folder) {
            echo "  - {$folder['name']} (ID: {$folder['id']}, Path: {$folder['path']})\n";
        }
    } catch (Exception $e) {
        echo "\nError checking system folders: " . $e->getMessage() . "\n";
    }

    // Check if document_files table was created
    $tables = $db->fetchAll("SHOW TABLES LIKE 'document_files'", []);
    if (!empty($tables)) {
        echo "\n✓ document_files table created successfully\n";
    } else {
        echo "\n✗ document_files table was not created\n";
    }

    // Check if triggers were created
    $triggers = $db->fetchAll("SHOW TRIGGERS LIKE 'folders%'", []);
    echo "\nTriggers created: " . count($triggers) . "\n";

    echo "\n=== Setup Complete ===\n";
    echo "The enhanced folder-based file management system has been set up.\n";
    echo "You can now upload files to specific folders and organize your IP documents.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}