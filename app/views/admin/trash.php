<?php 
ob_start();
$page_title = 'Recycle Bin';

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
        return '<span class="text-red-600 text-xs">Auto-delete scheduled</span>';
    } elseif ($daysLeft <= 7) {
        return '<span class="text-red-600 text-xs">Auto-delete in ' . $daysLeft . ' days</span>';
    } else {
        return '<span class="text-gray-500 text-xs">Auto-delete in ' . $daysLeft . ' days</span>';
    }
}

// Combine all trashed items into a single array
$allTrashedItems = [];

// Add documents
if (isset($trashedDocuments) && is_array($trashedDocuments)) {
    foreach ($trashedDocuments as $doc) {
        $allTrashedItems[] = [
            'id' => $doc['id'],
            'type' => 'document',
            'type_label' => 'Document',
            'icon' => 'fa-file-pdf',
            'icon_bg' => 'bg-red-50',
            'icon_color' => 'text-red-600',
            'badge_bg' => 'bg-red-100',
            'badge_color' => 'text-red-700',
            'name' => $doc['original_name'] ?? $doc['file_name'] ?? 'Document',
            'subtitle' => $doc['ip_title'] ?? 'Unknown',
            'size' => $doc['file_size'] ?? 0,
            'deleted_at' => $doc['deleted_at'] ?? '',
            'deleted_by' => $doc['deleted_by_name'] ?? 'System',
            'extra' => ''
        ];
    }
}

// Add IP records
if (isset($trashedRecords) && is_array($trashedRecords)) {
    foreach ($trashedRecords as $record) {
        $allTrashedItems[] = [
            'id' => $record['id'],
            'type' => 'record',
            'type_label' => 'IP Record',
            'icon' => 'fa-folder',
            'icon_bg' => 'bg-blue-50',
            'icon_color' => 'text-blue-600',
            'badge_bg' => 'bg-blue-100',
            'badge_color' => 'text-blue-700',
            'name' => $record['title'],
            'subtitle' => $record['ip_type'] . ' • ' . $record['owner'],
            'size' => 0,
            'deleted_at' => $record['deleted_at'] ?? '',
            'deleted_by' => 'System',
            'extra' => substr($record['description'], 0, 80) . (strlen($record['description']) > 80 ? '...' : '')
        ];
    }
}

// Add folders
if (isset($trashedFolders) && is_array($trashedFolders)) {
    foreach ($trashedFolders as $folder) {
        $dt = $folder['archived_at'] ?? $folder['updated_at'] ?? '';
        $allTrashedItems[] = [
            'id' => $folder['id'],
            'type' => 'folder',
            'type_label' => 'Repo Folder',
            'icon' => 'fa-folder-open',
            'icon_bg' => 'bg-amber-50',
            'icon_color' => 'text-amber-600',
            'badge_bg' => 'bg-amber-100',
            'badge_color' => 'text-amber-700',
            'name' => $folder['name'] ?? 'Folder',
            'subtitle' => $folder['path'] ?? '',
            'size' => 0,
            'deleted_at' => $dt,
            'deleted_by' => 'System',
            'extra' => ''
        ];
    }
}

// Add files
if (isset($trashedFiles) && is_array($trashedFiles)) {
    foreach ($trashedFiles as $file) {
        $allTrashedItems[] = [
            'id' => $file['id'],
            'type' => 'file',
            'type_label' => 'Repo File',
            'icon' => 'fa-file-alt',
            'icon_bg' => 'bg-emerald-50',
            'icon_color' => 'text-emerald-600',
            'badge_bg' => 'bg-emerald-100',
            'badge_color' => 'text-emerald-700',
            'name' => $file['original_name'] ?? $file['file_name'] ?? 'File',
            'subtitle' => $file['folder_path'] ?? $file['folder_name'] ?? 'Root',
            'size' => $file['file_size'] ?? 0,
            'deleted_at' => $file['deleted_at'] ?? '',
            'deleted_by' => 'System',
            'extra' => ''
        ];
    }
}

// Sort by deleted_at (newest first)
usort($allTrashedItems, function($a, $b) {
    return strtotime($b['deleted_at']) - strtotime($a['deleted_at']);
});

$totalTrash = count($allTrashedItems);
?>

<!-- Page Header -->
<div class="mb-4">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                <i class="fas fa-trash-alt text-gray-600 text-sm"></i>
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-800">Recycle Bin</h1>
                <p class="text-xs text-gray-500">Auto-delete after 30 days</p>
            </div>
        </div>
        <?php if ($totalTrash > 0): ?>
            <button onclick="emptyTrash()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-md transition text-xs font-medium">
                <i class="fas fa-trash-alt mr-1.5"></i>
                Empty (<?= $totalTrash ?>)
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Single Unified Recycle Bin Panel -->
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
    <!-- Header -->
    <div class="px-4 py-2.5 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <!-- Select All Checkbox -->
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="select-all" onchange="toggleSelectAll()" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-xs text-gray-600" id="selection-count"><?= $totalTrash ?> items</span>
                </label>
                
                <!-- Bulk Actions (hidden by default) -->
                <div id="bulk-actions" class="hidden flex items-center gap-2">
                    <button onclick="bulkRestore()" class="px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-[11px] font-medium transition">
                        <i class="fas fa-undo mr-1"></i> Restore
                    </button>
                    <button onclick="bulkDelete()" class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-[11px] font-medium transition">
                        <i class="fas fa-trash-alt mr-1"></i> Delete
                    </button>
                </div>
            </div>
            
            <!-- Filter & Search -->
            <div class="flex items-center gap-2">
                <!-- Search -->
                <div class="relative hidden sm:block">
                    <input type="text" 
                           id="search-input" 
                           placeholder="Search..." 
                           onkeyup="onSearchKeyup()"
                           class="w-32 lg:w-40 px-2 py-1 pr-6 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <i class="fas fa-search absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                </div>

                <button type="button" onclick="clearSearch()" class="hidden sm:inline-flex px-2 py-1 rounded text-[11px] bg-gray-100 text-gray-600 hover:bg-gray-200 transition" title="Clear search">
                    Clear
                </button>

                <select id="sort-select" onchange="setSort(this.value)" class="px-2 py-1 rounded text-[11px] bg-gray-100 text-gray-700 border border-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="deleted_desc">Newest</option>
                    <option value="deleted_asc">Oldest</option>
                    <option value="name_asc">Name A→Z</option>
                    <option value="name_desc">Name Z→A</option>
                    <option value="type_asc">Type</option>
                </select>
                
                <!-- Filter Buttons -->
                <div class="flex gap-1">
                    <button onclick="filterItems('all', this)" class="filter-btn active px-2 py-1 rounded text-[11px] font-medium transition bg-gray-700 text-white" data-filter="all">
                        All
                    </button>
                    <button onclick="filterItems('document', this)" class="filter-btn px-2 py-1 rounded text-[11px] font-medium transition bg-gray-100 text-gray-600 hover:bg-gray-200" data-filter="document">
                        Docs
                    </button>
                    <button onclick="filterItems('record', this)" class="filter-btn px-2 py-1 rounded text-[11px] font-medium transition bg-gray-100 text-gray-600 hover:bg-gray-200" data-filter="record">
                        Records
                    </button>
                    <button onclick="filterItems('folder', this)" class="filter-btn px-2 py-1 rounded text-[11px] font-medium transition bg-gray-100 text-gray-600 hover:bg-gray-200" data-filter="folder">
                        Folders
                    </button>
                    <button onclick="filterItems('file', this)" class="filter-btn px-2 py-1 rounded text-[11px] font-medium transition bg-gray-100 text-gray-600 hover:bg-gray-200" data-filter="file">
                        Files
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area - Unified List View -->
    <?php if ($totalTrash > 0): ?>
        <div class="divide-y divide-gray-100">
            <?php foreach ($allTrashedItems as $index => $item): ?>
                <div class="px-3 py-2.5 hover:bg-gray-50 transition item-row group" 
                     data-item-type="<?= $item['type'] ?>"
                     data-item-id="<?= $item['id'] ?>"
                     data-item-name="<?= htmlspecialchars(mb_strtolower((string)($item['name'] ?? ''), 'UTF-8'), ENT_QUOTES) ?>"
                     data-item-subtitle="<?= htmlspecialchars(mb_strtolower((string)($item['subtitle'] ?? ''), 'UTF-8'), ENT_QUOTES) ?>"
                     data-item-type-label="<?= htmlspecialchars((string)($item['type_label'] ?? ''), ENT_QUOTES) ?>"
                     data-item-deleted-by="<?= htmlspecialchars((string)($item['deleted_by'] ?? ''), ENT_QUOTES) ?>"
                     data-item-size="<?= (int)($item['size'] ?? 0) ?>"
                     data-item-extra="<?= htmlspecialchars((string)($item['extra'] ?? ''), ENT_QUOTES) ?>"
                     data-deleted-at="<?= (int)strtotime($item['deleted_at'] ?? '') ?>"
                     data-deleted-at-str="<?= htmlspecialchars((string)($item['deleted_at'] ?? ''), ENT_QUOTES) ?>">
                    <div class="flex items-center gap-3">
                        <!-- Checkbox -->
                        <input type="checkbox" 
                               class="item-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                               data-type="<?= $item['type'] ?>"
                               data-id="<?= $item['id'] ?>"
                               onchange="updateSelectionUI()">
                        
                        <!-- Icon -->
                        <div class="w-8 h-8 rounded-lg <?= $item['icon_bg'] ?> flex items-center justify-center flex-shrink-0">
                            <i class="fas <?= $item['icon'] ?> <?= $item['icon_color'] ?> text-sm"></i>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <!-- Name & Type Badge -->
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <span class="inline-block px-1.5 py-0.5 <?= $item['badge_bg'] ?> <?= $item['badge_color'] ?> rounded text-[9px] font-bold uppercase">
                                            <?= $item['type_label'] ?>
                                        </span>
                                        <?php if ($item['size'] > 0): ?>
                                            <span class="text-[10px] text-gray-500">• <?= formatFileSize($item['size']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h3 class="font-medium text-gray-900 text-xs truncate">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </h3>
                                    
                                    <p class="text-[11px] text-gray-500 truncate mt-0.5">
                                        <?= htmlspecialchars($item['subtitle']) ?>
                                        <?php if ($item['deleted_at']): ?>
                                            <span class="mx-1">•</span>
                                            <?= date('M j, Y', strtotime($item['deleted_at'])) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                
                                <!-- Actions Menu Button -->
                                <div class="action-menu-container relative flex-shrink-0">
                                    <button type="button" onclick="toggleActionMenu(<?= $index ?>, event)" 
                                            class="w-7 h-7 rounded hover:bg-gray-200 flex items-center justify-center transition text-gray-500 hover:text-gray-700"
                                            id="menu-button-<?= $index ?>"
                                            aria-haspopup="menu"
                                            aria-expanded="false"
                                            aria-controls="action-menu-<?= $index ?>">
                                        <i class="fas fa-ellipsis-v text-xs"></i>
                                    </button>
                                    
                                    <!-- Dropdown Menu -->
                                    <div id="action-menu-<?= $index ?>" 
                                         class="hidden absolute right-0 mt-1 w-36 bg-white rounded-md shadow-lg border border-gray-200 z-50 overflow-hidden"
                                         role="menu"
                                         aria-hidden="true">
                                        <button onclick="openPreview('<?= $item['type'] ?>', <?= (int)$item['id'] ?>); closeAllMenus();" 
                                                class="w-full px-3 py-1.5 text-left hover:bg-gray-50 transition flex items-center gap-2 text-xs">
                                            <i class="fas fa-eye text-gray-600 text-[10px] w-3"></i>
                                            <span class="text-gray-700">Preview</span>
                                        </button>
                                        <?php if ($item['type'] === 'document'): ?>
                                            <button onclick="restoreDocument(<?= $item['id'] ?>); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-green-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-undo text-green-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Restore</span>
                                            </button>
                                            <button onclick="permanentDelete(<?= $item['id'] ?>, 'document'); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-red-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-trash-alt text-red-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Delete Forever</span>
                                            </button>
                                        <?php elseif ($item['type'] === 'record'): ?>
                                            <button onclick="restoreRecord(<?= $item['id'] ?>); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-green-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-undo text-green-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Restore</span>
                                            </button>
                                            <button onclick="permanentDelete(<?= $item['id'] ?>, 'record'); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-red-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-trash-alt text-red-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Delete Forever</span>
                                            </button>
                                        <?php elseif ($item['type'] === 'folder'): ?>
                                            <button onclick="restoreFolder(<?= $item['id'] ?>); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-green-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-undo text-green-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Restore</span>
                                            </button>
                                            <button onclick="permanentDeleteFolder(<?= $item['id'] ?>); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-red-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-trash-alt text-red-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Delete Forever</span>
                                            </button>
                                        <?php elseif ($item['type'] === 'file'): ?>
                                            <button onclick="restoreRepoFile(<?= $item['id'] ?>); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-green-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-undo text-green-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Restore</span>
                                            </button>
                                            <button onclick="permanentDeleteRepoFile(<?= $item['id'] ?>); closeAllMenus();" 
                                                    class="w-full px-3 py-1.5 text-left hover:bg-red-50 transition flex items-center gap-2 text-xs border-t border-gray-100">
                                                <i class="fas fa-trash-alt text-red-600 text-[10px] w-3"></i>
                                                <span class="text-gray-700">Delete Forever</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="px-4 py-12 text-center">
            <i class="fas fa-trash-alt text-gray-300 text-3xl mb-2"></i>
            <p class="text-xs text-gray-500">Recycle bin is empty</p>
        </div>
    <?php endif; ?>
</div>

<!-- Preview Modal -->
<div id="preview-modal" class="fixed inset-0 z-[60] hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/40" onclick="closePreviewModal()"></div>
    <div class="relative w-full h-full flex items-stretch sm:items-center justify-center p-0 sm:p-6">
        <div class="bg-white w-full h-full sm:h-auto max-w-2xl sm:max-h-[85vh] rounded-none sm:rounded-lg shadow-xl sm:border border-gray-200 overflow-hidden flex flex-col" role="dialog" aria-modal="true">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-start justify-between gap-3 shrink-0">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span id="preview-badge" class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700">Item</span>
                        <span id="preview-size" class="text-[11px] text-gray-500"></span>
                    </div>
                    <h3 id="preview-title" class="mt-1 font-semibold text-gray-900 text-sm truncate">Preview</h3>
                    <p id="preview-subtitle" class="text-[11px] text-gray-500 truncate"></p>
                </div>

                <button type="button" onclick="closePreviewModal()" class="w-8 h-8 rounded hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="px-4 py-3 flex-1 overflow-y-auto">
                <div id="preview-media-wrap" class="mb-3 hidden">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <div class="text-[11px] text-gray-500">Preview</div>
                        <div class="flex items-center gap-2">
                            <a id="preview-open-link" href="#" target="_blank" rel="noopener" class="text-[11px] text-blue-600 hover:text-blue-700 hidden">Open</a>
                            <a id="preview-download-link" href="#" class="text-[11px] text-gray-600 hover:text-gray-800 hidden">Download</a>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded border border-gray-200 overflow-hidden">
                        <iframe id="preview-iframe" title="File preview" class="w-full h-64 sm:h-72 md:h-80 lg:h-[420px]" src="about:blank"></iframe>
                    </div>
                    <div id="preview-media-hint" class="mt-1 text-[11px] text-gray-500 hidden">If preview doesn’t load, use Open/Download.</div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[12px]">
                    <div class="flex items-center justify-between gap-2 bg-gray-50 rounded px-3 py-2">
                        <span class="text-gray-500">Deleted</span>
                        <span id="preview-deleted-at" class="text-gray-800 font-medium"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2 bg-gray-50 rounded px-3 py-2">
                        <span class="text-gray-500">Deleted by</span>
                        <span id="preview-deleted-by" class="text-gray-800 font-medium"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2 bg-gray-50 rounded px-3 py-2 sm:col-span-2">
                        <span class="text-gray-500">Auto-delete</span>
                        <span id="preview-auto-delete" class="text-gray-700"></span>
                    </div>
                </div>

                <div id="preview-extra-wrap" class="mt-3 hidden">
                    <div class="text-[11px] text-gray-500 mb-1">Details</div>
                    <div id="preview-extra" class="text-[12px] text-gray-700 bg-gray-50 rounded px-3 py-2"></div>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 bg-white flex items-center justify-end gap-2 shrink-0">
                <button type="button" onclick="previewRestore()" class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-xs font-medium transition">
                    <i class="fas fa-undo mr-1.5"></i> Restore
                </button>
                <button type="button" onclick="previewDeleteForever()" class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-medium transition">
                    <i class="fas fa-trash-alt mr-1.5"></i> Delete Forever
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-slideDown {
        animation: slideDown 0.15s ease-out;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    // Global state
    let currentFilter = 'all';
    let currentSort = 'deleted_desc';
    let searchDebounceTimer = null;
    let currentPreviewItem = null;

    function buildTrashServeUrl(type, id, download = false) {
        const base = '<?= BASE_URL ?>/admin/trash/serve';
        const params = new URLSearchParams({ type: String(type), id: String(id) });
        if (download) params.set('download', '1');
        return `${base}?${params.toString()}`;
    }

    function formatBytes(bytes) {
        const b = parseInt(bytes || '0', 10) || 0;
        if (b <= 0) return '';
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(b) / Math.log(1024));
        const value = (b / Math.pow(1024, i));
        const rounded = value >= 10 ? Math.round(value) : Math.round(value * 10) / 10;
        return `${rounded} ${sizes[i]}`;
    }

    function formatDateFromUnix(seconds) {
        const s = parseInt(seconds || '0', 10) || 0;
        if (!s) return '';
        const d = new Date(s * 1000);
        return d.toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function computeAutoDeleteText(deletedAtSeconds) {
        const s = parseInt(deletedAtSeconds || '0', 10) || 0;
        if (!s) return '';
        const autoDelete = (s + 30 * 24 * 60 * 60) * 1000;
        const now = Date.now();
        const diffDays = Math.ceil((autoDelete - now) / (24 * 60 * 60 * 1000));
        if (diffDays <= 0) return 'Auto-delete scheduled';
        if (diffDays <= 7) return `Auto-delete in ${diffDays} day${diffDays === 1 ? '' : 's'}`;
        return `Auto-delete in ${diffDays} days`;
    }

    function openPreview(type, id) {
        const row = document.querySelector(`.item-row[data-item-type="${CSS.escape(type)}"][data-item-id="${CSS.escape(String(id))}"]`);
        if (!row) return;

        currentPreviewItem = {
            id: String(id),
            type: String(type),
            typeLabel: row.dataset.itemTypeLabel || type,
            name: (row.dataset.itemName || '').toString(),
            subtitle: (row.dataset.itemSubtitle || '').toString(),
            deletedAt: row.dataset.deletedAt || '0',
            deletedAtStr: row.dataset.deletedAtStr || '',
            deletedBy: row.dataset.itemDeletedBy || 'System',
            size: row.dataset.itemSize || '0',
            extra: row.dataset.itemExtra || ''
        };

        // Populate modal
        const badge = document.getElementById('preview-badge');
        const sizeEl = document.getElementById('preview-size');
        const titleEl = document.getElementById('preview-title');
        const subtitleEl = document.getElementById('preview-subtitle');
        const deletedAtEl = document.getElementById('preview-deleted-at');
        const deletedByEl = document.getElementById('preview-deleted-by');
        const autoDeleteEl = document.getElementById('preview-auto-delete');
        const extraWrap = document.getElementById('preview-extra-wrap');
        const extraEl = document.getElementById('preview-extra');

        if (badge) badge.textContent = currentPreviewItem.typeLabel;
        if (sizeEl) sizeEl.textContent = formatBytes(currentPreviewItem.size);

        // dataset stores lowercase; use visible text from DOM for nicer title
        const visibleTitle = row.querySelector('h3')?.textContent?.trim() || '';
        const visibleSubtitle = row.querySelector('p')?.childNodes?.[0]?.textContent?.trim() || '';
        if (titleEl) titleEl.textContent = visibleTitle || 'Item';
        if (subtitleEl) subtitleEl.textContent = visibleSubtitle || '';

        const deletedLabel = formatDateFromUnix(currentPreviewItem.deletedAt) || (currentPreviewItem.deletedAtStr ? currentPreviewItem.deletedAtStr : '');
        if (deletedAtEl) deletedAtEl.textContent = deletedLabel || '—';
        if (deletedByEl) deletedByEl.textContent = currentPreviewItem.deletedBy || '—';
        if (autoDeleteEl) autoDeleteEl.textContent = computeAutoDeleteText(currentPreviewItem.deletedAt) || '—';

        if (extraWrap && extraEl) {
            const extra = (currentPreviewItem.extra || '').trim();
            if (extra) {
                extraEl.textContent = extra;
                extraWrap.classList.remove('hidden');
            } else {
                extraWrap.classList.add('hidden');
                extraEl.textContent = '';
            }
        }

        // Media preview for files/documents
        const mediaWrap = document.getElementById('preview-media-wrap');
        const iframe = document.getElementById('preview-iframe');
        const openLink = document.getElementById('preview-open-link');
        const downloadLink = document.getElementById('preview-download-link');
        const hint = document.getElementById('preview-media-hint');

        const isPreviewable = currentPreviewItem.type === 'file' || currentPreviewItem.type === 'document';
        if (mediaWrap) {
            if (isPreviewable) {
                mediaWrap.classList.remove('hidden');

                const serveUrl = buildTrashServeUrl(currentPreviewItem.type, currentPreviewItem.id, false);
                const downloadUrl = buildTrashServeUrl(currentPreviewItem.type, currentPreviewItem.id, true);

                if (iframe) {
                    iframe.src = serveUrl;
                }

                if (openLink) {
                    openLink.href = serveUrl;
                    openLink.classList.remove('hidden');
                }
                if (downloadLink) {
                    downloadLink.href = downloadUrl;
                    downloadLink.classList.remove('hidden');
                }
                if (hint) hint.classList.remove('hidden');
            } else {
                mediaWrap.classList.add('hidden');
                if (iframe) iframe.src = 'about:blank';
                if (openLink) openLink.classList.add('hidden');
                if (downloadLink) downloadLink.classList.add('hidden');
                if (hint) hint.classList.add('hidden');
            }
        }

        const modal = document.getElementById('preview-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        }
        document.body.style.overflow = 'hidden';
    }

    function closePreviewModal() {
        const modal = document.getElementById('preview-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }

        const iframe = document.getElementById('preview-iframe');
        if (iframe) iframe.src = 'about:blank';

        currentPreviewItem = null;
        document.body.style.overflow = '';
    }

    function previewRestore() {
        if (!currentPreviewItem) return;
        const id = parseInt(currentPreviewItem.id, 10);
        closePreviewModal();

        switch (currentPreviewItem.type) {
            case 'document':
                restoreDocument(id);
                break;
            case 'record':
                restoreRecord(id);
                break;
            case 'folder':
                restoreFolder(id);
                break;
            case 'file':
                restoreRepoFile(id);
                break;
        }
    }

    function previewDeleteForever() {
        if (!currentPreviewItem) return;
        const id = parseInt(currentPreviewItem.id, 10);
        const type = currentPreviewItem.type;
        closePreviewModal();

        switch (type) {
            case 'document':
                permanentDelete(id, 'document');
                break;
            case 'record':
                permanentDelete(id, 'record');
                break;
            case 'folder':
                permanentDeleteFolder(id);
                break;
            case 'file':
                permanentDeleteRepoFile(id);
                break;
        }
    }

    function getVisibleRows() {
        return Array.from(document.querySelectorAll('.item-row')).filter(row => row.style.display !== 'none');
    }

    function persistViewState() {
        try {
            localStorage.setItem('trash_view_filter', currentFilter);
            localStorage.setItem('trash_view_sort', currentSort);
            const searchInput = document.getElementById('search-input');
            localStorage.setItem('trash_view_search', (searchInput?.value || '').trim());
        } catch (e) {
            // ignore
        }
    }

    function restoreViewState() {
        try {
            const savedFilter = localStorage.getItem('trash_view_filter');
            const savedSort = localStorage.getItem('trash_view_sort');
            const savedSearch = localStorage.getItem('trash_view_search');

            if (savedFilter) currentFilter = savedFilter;
            if (savedSort) currentSort = savedSort;

            const sortSelect = document.getElementById('sort-select');
            if (sortSelect && currentSort) sortSelect.value = currentSort;

            const searchInput = document.getElementById('search-input');
            if (searchInput && savedSearch) searchInput.value = savedSearch;
        } catch (e) {
            // ignore
        }
    }

    function applyFilterSearch() {
        const searchInput = document.getElementById('search-input');
        const searchTerm = (searchInput?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.item-row');
        let visibleCount = 0;

        items.forEach(item => {
            const itemType = item.dataset.itemType;
            const itemName = item.dataset.itemName || '';
            const itemSubtitle = item.dataset.itemSubtitle || '';

            const matchesFilter = currentFilter === 'all' || itemType === currentFilter;
            const matchesSearch = !searchTerm || itemName.includes(searchTerm) || itemSubtitle.includes(searchTerm);
            const shouldShow = matchesFilter && matchesSearch;

            item.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleCount++;
        });

        updateItemCount(visibleCount);

        // Reset selection when the view changes
        const selectAll = document.getElementById('select-all');
        if (selectAll) selectAll.checked = false;
        document.querySelectorAll('.item-checkbox').forEach(cb => (cb.checked = false));
        updateSelectionUI();

        persistViewState();
    }

    function closeAllMenus(exceptId = null) {
        document.querySelectorAll('[id^="action-menu-"]').forEach(menu => {
            if (!exceptId || menu.id !== exceptId) {
                menu.classList.add('hidden');
                menu.setAttribute('aria-hidden', 'true');
            }
        });

        document.querySelectorAll('[id^="menu-button-"]').forEach(btn => {
            if (!exceptId || btn.getAttribute('aria-controls') !== exceptId) {
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    }
    
    // Close all menus when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.action-menu-container')) {
            closeAllMenus();
        }
    });
    
    // Toggle action menu
    function toggleActionMenu(index, evt) {
        if (evt) {
            evt.preventDefault();
            evt.stopPropagation();
        }

        const menu = document.getElementById(`action-menu-${index}`);

        if (!menu) return;

        const button = document.getElementById(`menu-button-${index}`);
        
        // Close all other menus
        closeAllMenus(`action-menu-${index}`);
        
        // Toggle current menu
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menu.classList.add('animate-slideDown');
            menu.setAttribute('aria-hidden', 'false');
            if (button) button.setAttribute('aria-expanded', 'true');
        } else {
            menu.classList.add('hidden');
            menu.setAttribute('aria-hidden', 'true');
            if (button) button.setAttribute('aria-expanded', 'false');
        }
    }
    
    // Filter functionality
    function filterItems(type, buttonEl = null) {
        currentFilter = type;
        const items = document.querySelectorAll('.item-row');
        const buttons = document.querySelectorAll('.filter-btn');
        
        // Update button states
        buttons.forEach(btn => {
            btn.classList.remove('active', 'bg-gray-700', 'text-white');
            btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
        });
        
        const activeBtn = buttonEl || document.querySelector(`.filter-btn[data-filter="${type}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            activeBtn.classList.add('active', 'bg-gray-700', 'text-white');
        }

        applyFilterSearch();
    }

    function onSearchKeyup() {
        if (searchDebounceTimer) clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            applyFilterSearch();
        }, 120);
    }

    function clearSearch() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = '';
        applyFilterSearch();
    }

    function setSort(sortMode) {
        currentSort = sortMode || 'deleted_desc';
        sortItems(currentSort);
        persistViewState();
    }

    function sortItems(mode) {
        const container = document.querySelector('.divide-y.divide-gray-100');
        if (!container) return;

        const rows = Array.from(container.querySelectorAll('.item-row'));

        const getName = (row) => (row.dataset.itemName || '').toString();
        const getType = (row) => (row.dataset.itemType || '').toString();
        const getDeleted = (row) => parseInt(row.dataset.deletedAt || '0', 10) || 0;

        rows.sort((a, b) => {
            switch (mode) {
                case 'deleted_asc':
                    return getDeleted(a) - getDeleted(b);
                case 'deleted_desc':
                    return getDeleted(b) - getDeleted(a);
                case 'name_desc':
                    return getName(b).localeCompare(getName(a));
                case 'type_asc':
                    return getType(a).localeCompare(getType(b)) || getName(a).localeCompare(getName(b));
                case 'name_asc':
                default:
                    return getName(a).localeCompare(getName(b));
            }
        });

        rows.forEach(row => container.appendChild(row));
    }
    
    // Search functionality
    function searchItems() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const items = document.querySelectorAll('.item-row');
        let visibleCount = 0;
        
        items.forEach(item => {
            const itemType = item.dataset.itemType;
            const itemName = item.dataset.itemName || '';
            const itemSubtitle = item.dataset.itemSubtitle || '';
            
            const matchesFilter = currentFilter === 'all' || itemType === currentFilter;
            const matchesSearch = !searchTerm || 
                                  itemName.includes(searchTerm) || 
                                  itemSubtitle.includes(searchTerm);
            
            const shouldShow = matchesFilter && matchesSearch;
            item.style.display = shouldShow ? '' : 'none';
            if (shouldShow) visibleCount++;
        });
        
        updateItemCount(visibleCount);
    }

    // Backwards compatibility: older inline handler may call this
    // (kept to avoid breakage if a cached template is used)
    // Prefer: onSearchKeyup() / applyFilterSearch()
    
    // Toggle select all
    function toggleSelectAll() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.item-row:not([style*="display: none"]) .item-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateSelectionUI();
    }
    
    // Update selection UI
    function updateSelectionUI() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        const bulkActions = document.getElementById('bulk-actions');
        const selectionCount = document.getElementById('selection-count');
        const selectAll = document.getElementById('select-all');
        
        // Show/hide bulk actions
        if (checked.length > 0) {
            bulkActions.classList.remove('hidden');
            selectionCount.textContent = `${checked.length} selected`;
            selectionCount.classList.add('font-semibold', 'text-blue-600');
        } else {
            bulkActions.classList.add('hidden');
            const visibleItems = getVisibleRows().length;
            selectionCount.textContent = `${visibleItems} items`;
            selectionCount.classList.remove('font-semibold', 'text-blue-600');
        }
        
        // Update select all checkbox state
        const visibleCheckboxes = document.querySelectorAll('.item-row:not([style*="display: none"]) .item-checkbox');
        const visibleChecked = Array.from(visibleCheckboxes).filter(cb => cb.checked);
        selectAll.checked = visibleCheckboxes.length > 0 && visibleChecked.length === visibleCheckboxes.length;
        selectAll.indeterminate = visibleChecked.length > 0 && visibleChecked.length < visibleCheckboxes.length;
    }
    
    // Update item count
    function updateItemCount(count) {
        const selectionCount = document.getElementById('selection-count');
        if (!selectionCount.classList.contains('font-semibold')) {
            selectionCount.textContent = `${count} items`;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        restoreViewState();
        // apply saved filter highlighting
        const activeBtn = document.querySelector(`.filter-btn[data-filter="${currentFilter}"]`);
        if (activeBtn) {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-gray-700', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            });
            activeBtn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
            activeBtn.classList.add('active', 'bg-gray-700', 'text-white');
        }
        sortItems(currentSort);
        applyFilterSearch();
    });

    // Close menu + preview on Esc
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllMenus();
            closePreviewModal();
        }
    });
    
    // Bulk restore
    function bulkRestore() {
        const checked = document.querySelectorAll('.item-checkbox:checked');
        
        if (checked.length === 0) {
            showToast('No items selected', 'warning');
            return;
        }
        
        // Group by type
        const items = {
            document: [],
            record: [],
            folder: [],
            file: []
        };
        
        checked.forEach(checkbox => {
            items[checkbox.dataset.type].push(checkbox.dataset.id);
        });
        
        Swal.fire({
            title: `Restore ${checked.length} Item${checked.length > 1 ? 's' : ''}?`,
            text: 'Selected items will be restored to their original locations',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, restore them',
            confirmButtonColor: '#10b981'
        }).then(async (result) => {
            if (result.isConfirmed) {
                let successCount = 0;
                let errorCount = 0;
                
                // Restore documents
                for (const id of items.document) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/document/restore', { document_id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                // Restore records
                for (const id of items.record) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/ip-record/restore', { record_id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                // Restore folders
                for (const id of items.folder) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/admin/folders/restore', { id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                // Restore files
                for (const id of items.file) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/document-file/restore', { file_id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                if (successCount > 0) {
                    showToast(`${successCount} item${successCount > 1 ? 's' : ''} restored successfully`, 'success');
                }
                if (errorCount > 0) {
                    showToast(`${errorCount} item${errorCount > 1 ? 's' : ''} failed to restore`, 'error');
                }
                
                setTimeout(() => location.reload(), 1500);
            }
        });
    }
    
    // Bulk delete
    function bulkDelete() {
        const checked = document.querySelectorAll('.item-checkbox:checked');
        
        if (checked.length === 0) {
            showToast('No items selected', 'warning');
            return;
        }
        
        // Group by type
        const items = {
            document: [],
            record: [],
            folder: [],
            file: []
        };
        
        checked.forEach(checkbox => {
            items[checkbox.dataset.type].push(checkbox.dataset.id);
        });
        
        Swal.fire({
            title: `Delete ${checked.length} Item${checked.length > 1 ? 's' : ''} Forever?`,
            html: '<p class="text-red-600 font-semibold mb-2">⚠️ This action cannot be undone!</p><p>All selected items will be permanently deleted.</p>',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete forever',
            confirmButtonColor: '#ef4444',
            input: 'checkbox',
            inputValue: 0,
            inputPlaceholder: 'I understand this action is permanent'
        }).then(async (result) => {
            if (result.isConfirmed && result.value) {
                let successCount = 0;
                let errorCount = 0;
                
                // Delete documents
                for (const id of items.document) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/document/permanent-delete', { document_id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                // Delete records
                for (const id of items.record) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/ip-record/permanent-delete', { record_id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                // Delete folders
                for (const id of items.folder) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/admin/folders/permanent-delete', { id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                // Delete files
                for (const id of items.file) {
                    try {
                        await ajaxRequest('<?= BASE_URL ?>/document-file/permanent-delete', { file_id: id }, 'POST');
                        successCount++;
                    } catch (e) {
                        errorCount++;
                    }
                }
                
                if (successCount > 0) {
                    showToast(`${successCount} item${successCount > 1 ? 's' : ''} deleted permanently`, 'success');
                }
                if (errorCount > 0) {
                    showToast(`${errorCount} item${errorCount > 1 ? 's' : ''} failed to delete`, 'error');
                }
                
                setTimeout(() => location.reload(), 1500);
            } else if (result.isConfirmed && !result.value) {
                Swal.fire('Checkbox Required', 'Please confirm by checking the box', 'warning');
            }
        });
    }

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
            title: 'Empty Entire Recycle Bin?',
            html: '<p class="text-red-600 font-semibold mb-2">⚠️ WARNING: This cannot be undone!</p><p>All items in the recycle bin will be permanently deleted.</p>',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Empty Recycle Bin',
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
                        showToast('Recycle Bin emptied successfully', 'success');
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
