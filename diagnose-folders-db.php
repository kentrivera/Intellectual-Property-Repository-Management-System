<?php
/**
 * Diagnose Folder DB Issues
 *
 * Lists triggers on `folders` and stored routines that reference `folders`.
 * Useful when MySQL error 1442 persists.
 *
 * Run only on localhost, then delete this file.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

$addr = $_SERVER['REMOTE_ADDR'] ?? '';
if ($addr !== '127.0.0.1' && $addr !== '::1') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

echo '<h2>Diagnose Folders DB</h2>';

try {
    $db = Database::getInstance();
    $dbName = DB_NAME;
    echo '<p>Database: <code>' . htmlspecialchars($dbName) . '</code></p>';

    // Triggers on folders
    $triggers = $db->fetchAll(
        "SELECT TRIGGER_NAME, ACTION_TIMING, EVENT_MANIPULATION
         FROM information_schema.TRIGGERS
         WHERE TRIGGER_SCHEMA = ? AND EVENT_OBJECT_TABLE = 'folders'
         ORDER BY TRIGGER_NAME",
        [$dbName]
    );

    echo '<h3>Triggers on folders</h3>';
    echo '<p>Count: <strong>' . count($triggers) . '</strong></p>';
    if ($triggers) {
        echo '<ul>';
        foreach ($triggers as $t) {
            echo '<li><code>' . htmlspecialchars($t['TRIGGER_NAME']) . '</code> (' . htmlspecialchars($t['ACTION_TIMING']) . ' ' . htmlspecialchars($t['EVENT_MANIPULATION']) . ')</li>';
        }
        echo '</ul>';
    }

    // Routines referencing folders
    echo '<h3>Stored routines referencing folders</h3>';
    $routines = $db->fetchAll(
        "SELECT ROUTINE_NAME, ROUTINE_TYPE
         FROM information_schema.ROUTINES
         WHERE ROUTINE_SCHEMA = ?
           AND (ROUTINE_DEFINITION LIKE '%folders%' OR ROUTINE_DEFINITION LIKE '%`folders`%')
         ORDER BY ROUTINE_TYPE, ROUTINE_NAME",
        [$dbName]
    );

    echo '<p>Count: <strong>' . count($routines) . '</strong></p>';
    if ($routines) {
        echo '<details><summary>Show routine names</summary><ul>';
        foreach ($routines as $r) {
            echo '<li>' . htmlspecialchars($r['ROUTINE_TYPE']) . ': <code>' . htmlspecialchars($r['ROUTINE_NAME']) . '</code></li>';
        }
        echo '</ul></details>';
        echo '<p>If you still get error 1442 with 0 triggers, one of these routines may be called indirectly and tries to write to <code>folders</code>.</p>';
    }

    echo '<hr>';
    echo "<p style='color:red;'><strong>IMPORTANT:</strong> Delete <code>diagnose-folders-db.php</code> after use.</p>";
} catch (Exception $e) {
    echo '<p style="color:red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
