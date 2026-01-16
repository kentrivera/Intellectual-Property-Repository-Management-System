<?php 
ob_start();
$page_title = 'Trash Bin';

// Helper functions
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

function daysUntilAutoDelete($deletedDate) {
    $deleted = strtotime($deletedDate);
    $autoDelete = $deleted + (30 * 24 * 60 * 60); // 30 days
    $now = time();
    $daysLeft = ceil(($autoDelete - $now) / (24 * 60 * 60));
    
    if ($daysLeft <= 0) {
        return '<span class="text-red-600">Auto-delete scheduled</span>';
    } elseif ($daysLeft <= 7) {
        return '<span class="text-red-600">Auto-delete in ' . $daysLeft . ' days</span>';
    } else {
        return 'Auto-delete in ' . $daysLeft . ' days';
    }
}

$countDocs = isset($trashedDocuments) ? count($trashedDocuments) : 0;
$countRecords = isset($trashedRecords) ? count($trashedRecords) : 0;
$countFolders = isset($trashedFolders) ? count($trashedFolders) : 0;
$countFiles = isset($trashedFiles) ? count($trashedFiles) : 0;
$totalTrash = $countDocs + $countRecords + $countFolders + $countFiles;
?>

<!-- Page Header -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Trash Bin</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Restore or permanently delete items</p>
        </div>
        <?php
            $hasAnyTrash =
                (!empty($trashedDocuments) && count($trashedDocuments) > 0) ||
                (!empty($trashedRecords) && count($trashedRecords) > 0) ||
                (!empty($trashedFolders) && count($trashedFolders) > 0) ||
                (!empty($trashedFiles) && count($trashedFiles) > 0);
        ?>
        <?php if ($hasAnyTrash): ?>
            <button onclick="emptyTrash()" class="bg-red-500 hover:bg-red-600 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg transition flex items-center justify-center text-xs sm:text-sm shadow-md">
                <i class="fas fa-trash-alt mr-2"></i>
                Empty Trash (<?= (int)$totalTrash ?>)
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Summary Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
    <a href="#trash-documents" class="group bg-white rounded-lg sm:rounded-xl shadow-md hover:shadow-lg transition p-3 sm:p-3.5 border border-gray-100">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Documents</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1"><?= (int)$countDocs ?></p>
                <p class="text-[11px] text-gray-500 mt-1 group-hover:text-gray-700">View deleted documents</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-file text-red-600"></i>
            </div>
        </div>
    </a>

    <a href="#trash-records" class="group bg-white rounded-lg sm:rounded-xl shadow-md hover:shadow-lg transition p-3 sm:p-3.5 border border-gray-100">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">IP Records</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1"><?= (int)$countRecords ?></p>
                <p class="text-[11px] text-gray-500 mt-1 group-hover:text-gray-700">View deleted records</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-folder text-blue-600"></i>
            </div>
        </div>
    </a>

    <a href="#trash-folders" class="group bg-white rounded-lg sm:rounded-xl shadow-md hover:shadow-lg transition p-3 sm:p-3.5 border border-gray-100">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Repo Folders</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1"><?= (int)$countFolders ?></p>
                <p class="text-[11px] text-gray-500 mt-1 group-hover:text-gray-700">View deleted folders</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-folder-open text-amber-600"></i>
            </div>
        </div>
    </a>

    <a href="#trash-files" class="group bg-white rounded-lg sm:rounded-xl shadow-md hover:shadow-lg transition p-3 sm:p-3.5 border border-gray-100">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Repo Files</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1"><?= (int)$countFiles ?></p>
                <p class="text-[11px] text-gray-500 mt-1 group-hover:text-gray-700">View deleted files</p>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-file-alt text-emerald-600"></i>
            </div>
        </div>
    </a>
</div>

<!-- Info Banner -->
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 sm:p-4 mb-4 sm:mb-6 rounded-lg">
    <div class="flex items-start">
        <i class="fas fa-info-circle text-yellow-600 mt-0.5 sm:mt-1 mr-2 sm:mr-3 flex-shrink-0"></i>
        <div>
            <p class="text-xs sm:text-sm font-medium text-yellow-800">Items in trash will be kept for 30 days</p>
            <p class="text-xs text-yellow-700 mt-1">After 30 days, items will be automatically deleted permanently</p>
        </div>
    </div>
</div>

<!-- Trashed Documents -->
<section id="trash-documents" class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden mb-4 sm:mb-6 scroll-mt-24">
    <div class="px-4 sm:px-5 lg:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm sm:text-base font-semibold text-gray-800 flex items-center">
            <i class="fas fa-file mr-2"></i>
            Deleted Documents
            <?php if (isset($trashedDocuments)): ?>
                <span class="ml-2 px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-200 text-gray-700 rounded-full text-xs sm:text-sm">
                    <?= count($trashedDocuments) ?>
                </span>
            <?php endif; ?>
        </h2>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden">
        <?php if (isset($trashedDocuments) && count($trashedDocuments) > 0): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($trashedDocuments as $doc): ?>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-pdf text-red-600"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-800 text-xs truncate"><?= htmlspecialchars($doc['original_name'] ?? $doc['file_name'] ?? 'Document') ?></p>
                                <p class="text-[11px] text-gray-600 mt-0.5 truncate"><?= htmlspecialchars($doc['ip_title'] ?? 'Unknown') ?></p>
                                <div class="mt-2 text-[11px] text-gray-600 space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-500">Size</span>
                                        <span class="font-medium text-gray-700">
                                            <?= formatFileSize($doc['file_size'] ?? 0) ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-500">Deleted</span>
                                        <span class="font-medium text-gray-700 whitespace-nowrap">
                                            <?= date('M j, Y g:i A', strtotime($doc['deleted_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="text-gray-500">
                                        <?= daysUntilAutoDelete($doc['deleted_at']) ?>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-500">By</span>
                                        <span class="font-medium text-gray-700 truncate">
                                            <?= htmlspecialchars($doc['deleted_by_name'] ?? 'System') ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button onclick="restoreDocument(<?= $doc['id'] ?>)" class="flex-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-undo mr-1"></i> Restore
                                    </button>
                                    <button onclick="permanentDelete(<?= $doc['id'] ?>, 'document')" class="flex-1 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-times mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="px-6 py-10 text-center text-gray-500">
                <i class="fas fa-trash text-3xl mb-2 text-gray-300"></i>
                <p class="text-sm">Trash is empty</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Document</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">IP Record</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Deleted By</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Deleted Date</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (isset($trashedDocuments) && count($trashedDocuments) > 0): ?>
                    <?php foreach ($trashedDocuments as $doc): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0">
                                        <i class="fas fa-file-pdf text-red-600 text-xs sm:text-base"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-800 text-xs sm:text-sm truncate"><?= htmlspecialchars($doc['original_name'] ?? $doc['file_name'] ?? 'Document') ?></p>
                                        <p class="text-xs text-gray-500"><?= formatFileSize($doc['file_size'] ?? 0) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-700">
                                <span class="block truncate max-w-xs"><?= htmlspecialchars($doc['ip_title'] ?? 'Unknown') ?></span>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-700">
                                <?= htmlspecialchars($doc['deleted_by_name'] ?? 'System') ?>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-600">
                                <span class="block whitespace-nowrap"><?= date('M j, Y g:i A', strtotime($doc['deleted_at'])) ?></span>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?= daysUntilAutoDelete($doc['deleted_at']) ?>
                                </div>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-right">
                                <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                                    <button onclick="restoreDocument(<?= $doc['id'] ?>)" 
                                            class="px-2 sm:px-3 py-1 sm:py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i> <span class="hidden sm:inline">Restore</span>
                                    </button>
                                    <button onclick="permanentDelete(<?= $doc['id'] ?>, 'document')" 
                                            class="px-2 sm:px-3 py-1 sm:py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-times mr-1"></i> <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 sm:py-12 text-center text-gray-500">
                            <i class="fas fa-trash text-3xl sm:text-4xl mb-2 text-gray-300"></i>
                            <p class="text-sm sm:text-base">Trash is empty</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Trashed IP Records -->
<section id="trash-records" class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden scroll-mt-24">
    <div class="px-4 sm:px-5 lg:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm sm:text-base font-semibold text-gray-800 flex items-center">
            <i class="fas fa-folder mr-2"></i>
            Deleted IP Records
            <?php if (isset($trashedRecords)): ?>
                <span class="ml-2 px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-200 text-gray-700 rounded-full text-xs sm:text-sm">
                    <?= count($trashedRecords) ?>
                </span>
            <?php endif; ?>
        </h2>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden">
        <?php if (isset($trashedRecords) && count($trashedRecords) > 0): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($trashedRecords as $record): ?>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-folder text-blue-600"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-800 text-xs truncate"><?= htmlspecialchars($record['title']) ?></p>
                                <p class="text-[11px] text-gray-600 mt-0.5 truncate"><?= htmlspecialchars($record['ip_type']) ?> • <?= htmlspecialchars($record['owner']) ?></p>
                                <p class="text-[11px] text-gray-500 mt-1">
                                    <?= htmlspecialchars(substr($record['description'], 0, 120)) ?><?= strlen($record['description']) > 120 ? '...' : '' ?>
                                </p>
                                <div class="mt-2 text-[11px] text-gray-600">
                                    <span class="text-gray-500">Deleted: </span>
                                    <span class="font-medium text-gray-700 whitespace-nowrap"><?= date('M j, Y g:i A', strtotime($record['deleted_at'])) ?></span>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button onclick="restoreRecord(<?= $record['id'] ?>)" class="flex-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-undo mr-1"></i> Restore
                                    </button>
                                    <button onclick="permanentDelete(<?= $record['id'] ?>, 'record')" class="flex-1 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-times mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="px-6 py-10 text-center text-gray-500">
                <i class="fas fa-folder-open text-3xl mb-2 text-gray-300"></i>
                <p class="text-sm">No deleted IP records</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Title</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Type</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Owner</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Deleted Date</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (isset($trashedRecords) && count($trashedRecords) > 0): ?>
                    <?php foreach ($trashedRecords as $record): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                <p class="font-medium text-gray-800 text-xs sm:text-sm truncate"><?= htmlspecialchars($record['title']) ?></p>
                                <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars(substr($record['description'], 0, 50)) ?>...</p>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                <span class="px-2 py-0.5 sm:py-1 bg-blue-100 text-blue-700 rounded text-xs whitespace-nowrap">
                                    <?= htmlspecialchars($record['ip_type']) ?>
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-700">
                                <span class="block truncate max-w-xs"><?= htmlspecialchars($record['owner']) ?></span>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                                <?= date('M j, Y g:i A', strtotime($record['deleted_at'])) ?>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-right">
                                <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                                    <button onclick="restoreRecord(<?= $record['id'] ?>)" 
                                            class="px-2 sm:px-3 py-1 sm:py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i> <span class="hidden sm:inline">Restore</span>
                                    </button>
                                    <button onclick="permanentDelete(<?= $record['id'] ?>, 'record')" 
                                            class="px-2 sm:px-3 py-1 sm:py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-times mr-1"></i> <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-8 sm:py-12 text-center text-gray-500">
                            <i class="fas fa-folder-open text-3xl sm:text-4xl mb-2 text-gray-300"></i>
                            <p class="text-sm sm:text-base">No deleted IP records</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<!-- Trashed Repository Folders -->
<section id="trash-folders" class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden mt-4 sm:mt-6 scroll-mt-24">
    <div class="px-4 sm:px-5 lg:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm sm:text-base font-semibold text-gray-800 flex items-center">
            <i class="fas fa-folder mr-2"></i>
            Deleted Repository Folders
            <?php if (isset($trashedFolders)): ?>
                <span class="ml-2 px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-200 text-gray-700 rounded-full text-xs sm:text-sm">
                    <?= count($trashedFolders) ?>
                </span>
            <?php endif; ?>
        </h2>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden">
        <?php if (isset($trashedFolders) && count($trashedFolders) > 0): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($trashedFolders as $folder): ?>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-folder-open text-amber-600"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-800 text-xs truncate"><?= htmlspecialchars($folder['name'] ?? 'Folder') ?></p>
                                <p class="text-[11px] text-gray-600 mt-0.5 truncate"><?= htmlspecialchars($folder['path'] ?? '') ?></p>
                                <div class="mt-2 text-[11px] text-gray-600">
                                    <?php $dt = $folder['archived_at'] ?? $folder['updated_at'] ?? null; ?>
                                    <span class="text-gray-500">Deleted: </span>
                                    <span class="font-medium text-gray-700 whitespace-nowrap"><?= $dt ? date('M j, Y g:i A', strtotime($dt)) : '—' ?></span>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button onclick="restoreFolder(<?= (int)$folder['id'] ?>)" class="flex-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-undo mr-1"></i> Restore
                                    </button>
                                    <button onclick="permanentDeleteFolder(<?= (int)$folder['id'] ?>)" class="flex-1 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-times mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="px-6 py-10 text-center text-gray-500">
                <i class="fas fa-folder-open text-3xl mb-2 text-gray-300"></i>
                <p class="text-sm">No deleted repository folders</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Folder</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Path</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Deleted Date</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (isset($trashedFolders) && count($trashedFolders) > 0): ?>
                    <?php foreach ($trashedFolders as $folder): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-800">
                                <?= htmlspecialchars($folder['name'] ?? 'Folder') ?>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-600">
                                <?= htmlspecialchars($folder['path'] ?? '') ?>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                                <?php $dt = $folder['archived_at'] ?? $folder['updated_at'] ?? null; ?>
                                <?= $dt ? date('M j, Y g:i A', strtotime($dt)) : '—' ?>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-right">
                                <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                                    <button onclick="restoreFolder(<?= (int)$folder['id'] ?>)" class="px-2 sm:px-3 py-1 sm:py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i> <span class="hidden sm:inline">Restore</span>
                                    </button>
                                    <button onclick="permanentDeleteFolder(<?= (int)$folder['id'] ?>)" class="px-2 sm:px-3 py-1 sm:py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-times mr-1"></i> <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 sm:py-12 text-center text-gray-500">
                            <i class="fas fa-folder-open text-3xl sm:text-4xl mb-2 text-gray-300"></i>
                            <p class="text-sm sm:text-base">No deleted repository folders</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Trashed Repository Files -->
<section id="trash-files" class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden mt-4 sm:mt-6 scroll-mt-24">
    <div class="px-4 sm:px-5 lg:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-sm sm:text-base font-semibold text-gray-800 flex items-center">
            <i class="fas fa-file mr-2"></i>
            Deleted Repository Files
            <?php if (isset($trashedFiles)): ?>
                <span class="ml-2 px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-200 text-gray-700 rounded-full text-xs sm:text-sm">
                    <?= count($trashedFiles) ?>
                </span>
            <?php endif; ?>
        </h2>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden">
        <?php if (isset($trashedFiles) && count($trashedFiles) > 0): ?>
            <div class="divide-y divide-gray-100">
                <?php foreach ($trashedFiles as $file): ?>
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-alt text-emerald-600"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-800 text-xs truncate"><?= htmlspecialchars($file['original_name'] ?? $file['file_name'] ?? 'File') ?></p>
                                <p class="text-[11px] text-gray-600 mt-0.5 truncate"><?= htmlspecialchars($file['folder_path'] ?? $file['folder_name'] ?? 'Root') ?></p>
                                <div class="mt-2 text-[11px] text-gray-600 space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-500">Size</span>
                                        <span class="font-medium text-gray-700"><?= formatFileSize($file['file_size'] ?? 0) ?></span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-500">Deleted</span>
                                        <span class="font-medium text-gray-700 whitespace-nowrap"><?= !empty($file['deleted_at']) ? date('M j, Y g:i A', strtotime($file['deleted_at'])) : '—' ?></span>
                                    </div>
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <button onclick="restoreRepoFile(<?= (int)$file['id'] ?>)" class="flex-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-undo mr-1"></i> Restore
                                    </button>
                                    <button onclick="permanentDeleteRepoFile(<?= (int)$file['id'] ?>)" class="flex-1 px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] font-semibold">
                                        <i class="fas fa-times mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="px-6 py-10 text-center text-gray-500">
                <i class="fas fa-trash text-3xl mb-2 text-gray-300"></i>
                <p class="text-sm">No deleted repository files</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">File</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Folder</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Deleted Date</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-2 sm:py-3 text-right text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (isset($trashedFiles) && count($trashedFiles) > 0): ?>
                    <?php foreach ($trashedFiles as $file): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-800">
                                <?= htmlspecialchars($file['original_name'] ?? $file['file_name'] ?? 'File') ?>
                                <div class="text-xs text-gray-500"><?= formatFileSize($file['file_size'] ?? 0) ?></div>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-600">
                                <?= htmlspecialchars($file['folder_path'] ?? $file['folder_name'] ?? 'Root') ?>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                                <?= !empty($file['deleted_at']) ? date('M j, Y g:i A', strtotime($file['deleted_at'])) : '—' ?>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-right">
                                <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                                    <button onclick="restoreRepoFile(<?= (int)$file['id'] ?>)" class="px-2 sm:px-3 py-1 sm:py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i> <span class="hidden sm:inline">Restore</span>
                                    </button>
                                    <button onclick="permanentDeleteRepoFile(<?= (int)$file['id'] ?>)" class="px-2 sm:px-3 py-1 sm:py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-[11px] whitespace-nowrap">
                                        <i class="fas fa-times mr-1"></i> <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-6 py-8 sm:py-12 text-center text-gray-500">
                            <i class="fas fa-trash text-3xl sm:text-4xl mb-2 text-gray-300"></i>
                            <p class="text-sm sm:text-base">No deleted repository files</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</section>

<script>
    function restoreDocument(id) {
        Swal.fire({
            title: 'Restore Document?',
            text: 'This will restore the document to its original location',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, restore it',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                ajaxRequest('<?= BASE_URL ?>/document/restore', { document_id: id }, 'POST')
                    .then(() => {
                        showToast('Document restored successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            }
        });
    }

    function restoreRecord(id) {
        Swal.fire({
            title: 'Restore IP Record?',
            text: 'This will restore the IP record and all its documents',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, restore it',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                ajaxRequest('<?= BASE_URL ?>/ip-record/restore', { record_id: id }, 'POST')
                    .then(() => {
                        showToast('IP Record restored successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            }
        });
    }

    function permanentDelete(id, type) {
        Swal.fire({
            title: 'Delete Forever?',
            html: '<p class="text-red-600 font-semibold mb-2">⚠️ This action cannot be undone!</p><p>The ' + type + ' will be permanently deleted.</p>',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete forever',
            confirmButtonColor: '#ef4444',
            input: 'checkbox',
            inputValue: 0,
            inputPlaceholder: 'I understand this action is permanent'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const endpoint = type === 'document' ? '/document/permanent-delete' : '/ip-record/permanent-delete';
                const param = type === 'document' ? 'document_id' : 'record_id';

                ajaxRequest('<?= BASE_URL ?>' + endpoint, { [param]: id }, 'POST')
                    .then(() => {
                        showToast(type.charAt(0).toUpperCase() + type.slice(1) + ' deleted permanently', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            } else if (result.isConfirmed && !result.value) {
                Swal.fire('Checkbox Required', 'Please confirm by checking the box', 'warning');
            }
        });
    }

    function restoreFolder(id) {
        Swal.fire({
            title: 'Restore Folder?',
            text: 'This will restore the folder and make it visible again.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, restore it',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                ajaxRequest('<?= BASE_URL ?>/admin/folders/restore', { id: id }, 'POST')
                    .then(() => {
                        showToast('Folder restored successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            }
        });
    }

    function permanentDeleteFolder(id) {
        Swal.fire({
            title: 'Delete Folder Forever?',
            html: '<p class="text-red-600 font-semibold mb-2">⚠️ This action cannot be undone!</p><p>The folder and its contents will be permanently deleted.</p>',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete forever',
            confirmButtonColor: '#ef4444',
            input: 'checkbox',
            inputValue: 0,
            inputPlaceholder: 'I understand this action is permanent'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                ajaxRequest('<?= BASE_URL ?>/admin/folders/permanent-delete', { id: id }, 'POST')
                    .then(() => {
                        showToast('Folder deleted permanently', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            } else if (result.isConfirmed && !result.value) {
                Swal.fire('Checkbox Required', 'Please confirm by checking the box', 'warning');
            }
        });
    }

    function restoreRepoFile(id) {
        Swal.fire({
            title: 'Restore File?',
            text: 'This will restore the file in the repository.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, restore it',
            confirmButtonColor: '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                ajaxRequest('<?= BASE_URL ?>/document-file/restore', { file_id: id }, 'POST')
                    .then(() => {
                        showToast('File restored successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            }
        });
    }

    function permanentDeleteRepoFile(id) {
        Swal.fire({
            title: 'Delete File Forever?',
            html: '<p class="text-red-600 font-semibold mb-2">⚠️ This action cannot be undone!</p><p>The file will be permanently deleted.</p>',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete forever',
            confirmButtonColor: '#ef4444',
            input: 'checkbox',
            inputValue: 0,
            inputPlaceholder: 'I understand this action is permanent'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                ajaxRequest('<?= BASE_URL ?>/document-file/permanent-delete', { file_id: id }, 'POST')
                    .then(() => {
                        showToast('File deleted permanently', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            } else if (result.isConfirmed && !result.value) {
                Swal.fire('Checkbox Required', 'Please confirm by checking the box', 'warning');
            }
        });
    }

    function emptyTrash() {
        Swal.fire({
            title: 'Empty Entire Trash?',
            html: '<p class="text-red-600 font-semibold mb-2">⚠️ WARNING: This cannot be undone!</p><p>All items in trash will be permanently deleted.</p>',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Empty Trash',
            confirmButtonColor: '#ef4444',
            input: 'text',
            inputPlaceholder: 'Type "DELETE" to confirm',
            inputValidator: (value) => {
                if (value !== 'DELETE') {
                    return 'You must type DELETE to confirm';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                ajaxRequest('<?= BASE_URL ?>/admin/trash/empty', {}, 'POST')
                    .then(() => {
                        showToast('Trash emptied successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    });
            }
        });
    }
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
