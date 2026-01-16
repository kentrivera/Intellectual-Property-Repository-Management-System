<?php
/**
 * Import legacy uploads from app/views/admin/record_folders/uploads into the main system.
 *
 * Usage (browser): http://localhost/<project>/import-record-folders.php
 * Usage (CLI): php import-record-folders.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

$isCli = (PHP_SAPI === 'cli');
$dryRun = false;
if ($isCli) {
    $dryRun = in_array('--dry-run', $argv ?? [], true);
} else {
    $dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] == '1';
    header('Content-Type: text/plain; charset=utf-8');
}

$sourceDir = __DIR__ . '/app/views/admin/record_folders/uploads';
$destDir = __DIR__ . '/uploads/documents/legacy-import';

function out($msg) {
    echo $msg . (PHP_SAPI === 'cli' ? "\n" : "\n");
}

try {
    if (!is_dir($sourceDir)) {
        out("ERROR: Source directory not found: $sourceDir");
        exit(1);
    }

    if (!is_dir($destDir)) {
        if ($dryRun) {
            out("DRY RUN: would create destination directory: $destDir");
        } else {
            if (!mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                throw new Exception("Failed to create destination directory: $destDir");
            }
        }
    }

    $db = Database::getInstance();

    // Verify required table exists
    $tables = $db->fetchAll("SHOW TABLES LIKE 'document_files'", []);
    if (empty($tables)) {
        out("ERROR: document_files table not found in DB '" . DB_NAME . "'.");
        out("Run the folder/file migrations first (see database/setup-enhanced-folders.php or database/migrations/*).");
        exit(1);
    }

    $files = array_values(array_filter(scandir($sourceDir), function ($name) use ($sourceDir) {
        if ($name === '.' || $name === '..') return false;
        return is_file($sourceDir . '/' . $name);
    }));

    if (empty($files)) {
        out('No legacy files found to import.');
        exit(0);
    }

    out('Found ' . count($files) . ' legacy file(s).');
    if ($dryRun) {
        out('DRY RUN enabled: no files or DB rows will be modified.');
    }

    // Pick an uploader user
    $uploaderId = 1;
    $uploader = $db->fetch("SELECT id FROM users WHERE id = 1 LIMIT 1", []);
    if (!$uploader) {
        $admin = $db->fetch("SELECT id FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1", []);
        if ($admin) $uploaderId = (int)$admin['id'];
    }

    $imported = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($files as $fileName) {
        $srcPath = $sourceDir . '/' . $fileName;

        $hash = hash_file('sha256', $srcPath);
        $exists = $db->fetch("SELECT id FROM document_files WHERE file_hash = ? LIMIT 1", [$hash]);
        if ($exists) {
            $skipped++;
            out("SKIP (duplicate hash): $fileName");
            continue;
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $srcPath) : 'application/octet-stream';

        $size = filesize($srcPath);
        if ($size === false) $size = 0;

        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'], true) ? 1 : 0;
        $isDocument = in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'], true) ? 1 : 0;
        $isVideo = in_array($ext, ['mp4', 'avi', 'mov'], true) ? 1 : 0;
        $isAudio = in_array($ext, ['mp3', 'wav'], true) ? 1 : 0;

        $base = pathinfo($fileName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base);
        $newFileName = $safeBase . '_' . uniqid() . ($ext ? '.' . $ext : '');
        $destPath = $destDir . '/' . $newFileName;

        if ($dryRun) {
            out("DRY RUN: would move $fileName -> $destPath");
            out("DRY RUN: would insert document_files row (original_name=$fileName, ext=$ext, size=$size)");
            $imported++;
            continue;
        }

        if (!rename($srcPath, $destPath)) {
            $failed++;
            out("FAIL: could not move file: $fileName");
            continue;
        }

        try {
            $db->insert(
                'INSERT INTO document_files (folder_id, file_name, original_name, file_path, file_size, file_type, mime_type, file_hash, thumbnail_path, is_image, is_document, is_video, is_audio, uploaded_by, upload_ip, tags, description, is_public) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    null,
                    $newFileName,
                    $fileName,
                    $destPath,
                    $size,
                    ($ext ?: 'bin'),
                    ($mime ?: 'application/octet-stream'),
                    $hash,
                    null,
                    $isImage,
                    $isDocument,
                    $isVideo,
                    $isAudio,
                    $uploaderId,
                    null,
                    null,
                    'Imported from legacy record_folders/uploads',
                    0
                ]
            );
            $imported++;
            out("IMPORTED: $fileName");
        } catch (Exception $e) {
            // Roll back physical move on DB failure
            @rename($destPath, $srcPath);
            $failed++;
            out("FAIL (DB): $fileName - " . $e->getMessage());
        }
    }

    out('');
    out('=== Import Summary ===');
    out('Imported: ' . $imported);
    out('Skipped:  ' . $skipped);
    out('Failed:   ' . $failed);

} catch (Exception $e) {
    out('ERROR: ' . $e->getMessage());
    exit(1);
}
