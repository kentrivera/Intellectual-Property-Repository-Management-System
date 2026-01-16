<?php
/**
 * Fix Folder Triggers Script
 *
 * Fixes MySQL error 1442 caused by the broken triggers from
 * database/migrations/enhanced_folder_system.sql:
 *  - folders_after_insert
 *  - folders_after_update
 *
 * Those triggers attempt to UPDATE the `folders` table inside a trigger on `folders`,
 * which MySQL forbids.
 *
 * Run once, then delete this file.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

echo "<h2>Fix Folder Triggers</h2>";

try {
    $db = Database::getInstance();

    $dbName = defined('DB_NAME') ? DB_NAME : null;
    if (!$dbName) {
        throw new Exception('DB_NAME is not defined');
    }

    echo "<p>Database: <code>" . htmlspecialchars($dbName) . "</code></p>";

    $folderTriggers = $db->fetchAll(
        "SELECT TRIGGER_NAME, ACTION_TIMING, EVENT_MANIPULATION, ACTION_STATEMENT
         FROM information_schema.TRIGGERS
         WHERE TRIGGER_SCHEMA = ? AND EVENT_OBJECT_TABLE = 'folders'
         ORDER BY TRIGGER_NAME",
        [$dbName]
    );

    echo "<p>Found <strong>" . count($folderTriggers) . "</strong> trigger(s) on table <code>folders</code>.</p>";

    if (!empty($folderTriggers)) {
        echo "<details style='margin: 12px 0;'><summary>Show trigger details</summary><pre>";
        foreach ($folderTriggers as $t) {
            $name = $t['TRIGGER_NAME'] ?? '(unknown)';
            $timing = $t['ACTION_TIMING'] ?? '';
            $event = $t['EVENT_MANIPULATION'] ?? '';
            $stmt = $t['ACTION_STATEMENT'] ?? '';
            echo htmlspecialchars("{$name} ({$timing} {$event})") . "\n";
            echo htmlspecialchars("Statement: " . substr($stmt, 0, 500)) . (strlen($stmt) > 500 ? "..." : "") . "\n\n";
        }
        echo "</pre></details>";
    }

    $dropped = 0;
    foreach ($folderTriggers as $t) {
        $triggerName = $t['TRIGGER_NAME'] ?? '';
        if (!$triggerName) continue;

        // Quote identifier safely
        $safe = str_replace('`', '``', $triggerName);
        $db->execute("DROP TRIGGER IF EXISTS `{$safe}`", []);
        echo "<div>✅ Dropped trigger <code>" . htmlspecialchars($triggerName) . "</code></div>";
        $dropped++;
    }

    $remaining = $db->fetchAll(
        "SELECT TRIGGER_NAME
         FROM information_schema.TRIGGERS
         WHERE TRIGGER_SCHEMA = ? AND EVENT_OBJECT_TABLE = 'folders'",
        [$dbName]
    );
    $remainingCount = count($remaining);
    if ($remainingCount === 0) {
        echo "<p style='color: green;'><strong>Remaining triggers on folders: 0</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>Remaining triggers on folders: {$remainingCount}</strong></p>";
        echo "<pre>";
        foreach ($remaining as $r) {
            echo htmlspecialchars($r['TRIGGER_NAME'] ?? '') . "\n";
        }
        echo "</pre>";
    }

    echo "<hr>";
    echo "<p><strong>Done.</strong> Dropped {$dropped} trigger(s).</p>";
    echo "<p>Now try creating a folder again in the admin UI (hard refresh the page: Ctrl+F5).</p>";
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Delete <code>fix-folder-triggers.php</code> after use.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
