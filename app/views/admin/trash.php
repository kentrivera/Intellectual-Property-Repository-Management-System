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
?>

<!-- Page Header -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Trash Bin</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Restore or permanently delete items</p>
        </div>
        <?php if (isset($trashedDocuments) && count($trashedDocuments) > 0): ?>
            <button onclick="emptyTrash()" class="bg-red-500 hover:bg-red-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition flex items-center justify-center text-sm sm:text-base shadow-md">
                <i class="fas fa-trash-alt mr-2"></i>
                Empty Trash
            </button>
        <?php endif; ?>
    </div>
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
<div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden mb-4 sm:mb-6">
    <div class="px-4 sm:px-5 lg:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-file mr-2"></i>
            Deleted Documents
            <?php if (isset($trashedDocuments)): ?>
                <span class="ml-2 px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-200 text-gray-700 rounded-full text-xs sm:text-sm">
                    <?= count($trashedDocuments) ?>
                </span>
            <?php endif; ?>
        </h2>
    </div>
    <div class="overflow-x-auto">
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
                                        <p class="font-medium text-gray-800 text-xs sm:text-sm truncate"><?= htmlspecialchars($doc['name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= formatFileSize($doc['file_size'] ?? 0) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-700">
                                <span class="block truncate max-w-xs"><?= htmlspecialchars($doc['ip_record_title'] ?? 'Unknown') ?></span>
                            </td>
                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-700">
                                <?= htmlspecialchars($doc['deleted_by_user'] ?? 'System') ?>
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
                                            class="px-2 sm:px-3 py-1.5 sm:py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-xs whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i> <span class="hidden sm:inline">Restore</span>
                                    </button>
                                    <button onclick="permanentDelete(<?= $doc['id'] ?>, 'document')" 
                                            class="px-2 sm:px-3 py-1.5 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-xs whitespace-nowrap">
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
</div>

<!-- Trashed IP Records -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
    <div class="px-4 sm:px-5 lg:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-folder mr-2"></i>
            Deleted IP Records
            <?php if (isset($trashedRecords)): ?>
                <span class="ml-2 px-2 sm:px-3 py-0.5 sm:py-1 bg-gray-200 text-gray-700 rounded-full text-xs sm:text-sm">
                    <?= count($trashedRecords) ?>
                </span>
            <?php endif; ?>
        </h2>
    </div>
    <div class="overflow-x-auto">
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
                                            class="px-2 sm:px-3 py-1.5 sm:py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition text-xs whitespace-nowrap">
                                        <i class="fas fa-undo mr-1"></i> <span class="hidden sm:inline">Restore</span>
                                    </button>
                                    <button onclick="permanentDelete(<?= $record['id'] ?>, 'record')" 
                                            class="px-2 sm:px-3 py-1.5 sm:py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition text-xs whitespace-nowrap">
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
</div>

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
                            showToast('success', 'Document restored successfully');
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
                            showToast('success', 'IP Record restored successfully');
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
                            showToast('success', type.charAt(0).toUpperCase() + type.slice(1) + ' deleted permanently');
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
                            showToast('success', 'Trash emptied successfully');
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
