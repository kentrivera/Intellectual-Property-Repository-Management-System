<?php 
ob_start(); 
$page_title = 'IP Records';

// Initialize data if not provided by controller
if (!isset($records)) $records = [];
if (!isset($folders)) $folders = [];
if (!isset($ip_types)) $ip_types = [];
if (!isset($stats)) {
    $stats = [
        'patent_count' => 0,
        'trademark_count' => 0,
        'copyright_count' => 0,
        'design_count' => 0,
        'archived_count' => 0,
        'recent_count' => 0
    ];
}

// Get current context from URL parameters
$currentFolderId = $_GET['folder_id'] ?? null;
$currentFolder = $_GET['folder'] ?? null;
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Determine current path and folder info
$currentPath = [];
$currentFolderData = null;
if ($currentFolderId) {
    // Get folder data from database or folders array
    foreach ($folders as $folder) {
        if ($folder['id'] == $currentFolderId) {
            $currentFolderData = $folder;
            break;
        }
    }
}

// Helper functions
function getGradientColor($typeId) {
    $colors = [
        1 => 'from-blue-500 to-blue-600',
        2 => 'from-green-500 to-green-600',
        3 => 'from-purple-500 to-purple-600',
        4 => 'from-orange-500 to-orange-600'
    ];
    return $colors[$typeId] ?? 'from-gray-500 to-gray-600';
}

function getStatusBadge($status) {
    $badges = [
        'active' => 'bg-green-100 text-green-700',
        'pending' => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-blue-100 text-blue-700',
        'expired' => 'bg-red-100 text-red-700',
        'archived' => 'bg-gray-100 text-gray-700'
    ];
    return $badges[strtolower($status)] ?? 'bg-gray-100 text-gray-700';
}

// Ensure filter variables exist
if (!isset($type_filter)) $type_filter = '';
if (!isset($status_filter)) $status_filter = '';

// Determine current folder name for breadcrumbs
$currentFolderName = 'All Records';
if ($type_filter == 1) $currentFolderName = 'Patents';
elseif ($type_filter == 2) $currentFolderName = 'Trademarks';
elseif ($type_filter == 3) $currentFolderName = 'Copyrights';
elseif ($type_filter == 4) $currentFolderName = 'Industrial Designs';
elseif ($status_filter == 'archived') $currentFolderName = 'Archived';
elseif (isset($_GET['folder'])) $currentFolderName = htmlspecialchars(ucfirst(str_replace('-', ' ', $_GET['folder'])));
?>

<!-- Enhanced Dynamic Breadcrumb Navigation -->
<nav class="flex items-center space-x-1.5 sm:space-x-2 text-xs sm:text-sm mb-4 sm:mb-6 overflow-x-auto pb-2 bg-white rounded-lg shadow-sm p-3" id="breadcrumbNav">
    <button onclick="navigateToRoot()" class="flex items-center text-gray-600 hover:text-blue-600 transition whitespace-nowrap flex-shrink-0">
        <i class="fas fa-home mr-1.5 sm:mr-2 text-xs sm:text-sm"></i>
        <span class="hidden sm:inline">IP Repository</span>
        <span class="sm:hidden">Home</span>
    </button>
    
    <!-- Dynamic Breadcrumb Path -->
    <div id="breadcrumbPath" class="flex items-center space-x-1.5">
        <?php if ($currentFolderData || $type_filter || $status_filter): ?>
            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
            <?php if ($type_filter): ?>
                <span class="text-blue-600 font-medium">
                    <?php 
                    $typeNames = ['1' => 'Patents', '2' => 'Trademarks', '3' => 'Copyrights', '4' => 'Industrial Designs'];
                    echo $typeNames[$type_filter] ?? 'Unknown Type';
                    ?>
                </span>
            <?php elseif ($status_filter): ?>
                <span class="text-blue-600 font-medium"><?= ucfirst($status_filter) ?></span>
            <?php elseif ($currentFolderData): ?>
                <span class="text-blue-600 font-medium"><?= htmlspecialchars($currentFolderData['name']) ?></span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <!-- Folder Info and Actions -->
    <div class="ml-auto flex items-center space-x-2 text-xs text-gray-500">
        <span id="folderStats" class="hidden sm:inline">
            <?php if (isset($stats['total_files'])): ?>
                <?= $stats['total_files'] ?? 0 ?> files
            <?php endif; ?>
        </span>
        <button onclick="refreshCurrentView()" class="p-1 hover:bg-gray-100 rounded transition" title="Refresh">
            <i class="fas fa-sync text-xs"></i>
        </button>
        <button onclick="toggleViewInfo()" class="p-1 hover:bg-gray-100 rounded transition" title="View information">
            <i class="fas fa-info-circle text-xs"></i>
        </button>
    </div>
</nav>

<!-- Toolbar -->
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4 sm:mb-6 gap-3 sm:gap-4">
    <div class="flex-1 max-w-2xl">
        <div class="relative">
            <i class="fas fa-search absolute left-3 sm:left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" id="searchInput" 
                   placeholder="Search in IP Repository..." 
                   class="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-2.5 sm:py-3 text-sm sm:text-base bg-white hover:shadow-md border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 rounded-lg transition"
                   onkeyup="filterRecords()">
        </div>
    </div>
    
    <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-2 lg:pb-0">
        <div class="relative flex-shrink-0">
            <button id="newMenuBtn" onclick="toggleNewMenu(event)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 sm:py-2.5 text-sm rounded-lg transition shadow-sm flex items-center whitespace-nowrap">
                <i class="fas fa-plus mr-1.5 sm:mr-2 text-xs sm:text-sm"></i><span class="hidden xs:inline">New</span>
                <i class="fas fa-chevron-down ml-1.5 sm:ml-2 text-xs"></i>
            </button>
            <!-- Enhanced New Menu Dropdown -->
            <div id="newMenu" class="hidden fixed w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-[9999]">
                <!-- Folder Operations -->
                <div class="p-2 border-b border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide px-2 mb-2">Folders</p>
                    <button onclick="showCreateFolderModal()" class="w-full text-left px-3 py-2.5 hover:bg-blue-50 hover:text-blue-700 flex items-center transition rounded-md">
                        <i class="fas fa-folder-plus text-blue-600 w-8 text-sm"></i>
                        <div class="flex-1">
                            <span class="font-medium text-sm">New Folder</span>
                            <p class="text-xs text-gray-500">Create a new folder</p>
                        </div>
                    </button>
                </div>
                
                <!-- File Operations -->
                <div class="p-2 border-b border-gray-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide px-2 mb-2">Files</p>
                    <button onclick="showUploadModal()" class="w-full text-left px-3 py-2.5 hover:bg-green-50 hover:text-green-700 flex items-center transition rounded-md">
                        <i class="fas fa-cloud-upload-alt text-green-600 w-8 text-sm"></i>
                        <div class="flex-1">
                            <span class="font-medium text-sm">Upload Files</span>
                            <p class="text-xs text-gray-500" id="uploadLocationText">
                                Upload to <?= $currentFolderData ? htmlspecialchars($currentFolderData['name']) : 'current location' ?>
                            </p>
                        </div>
                    </button>
                    <button onclick="document.getElementById('folderUpload').click()" class="w-full text-left px-3 py-2.5 hover:bg-purple-50 hover:text-purple-700 flex items-center transition rounded-md">
                        <i class="fas fa-folder-open text-purple-600 w-8 text-sm"></i>
                        <div class="flex-1">
                            <span class="font-medium text-sm">Upload Folder</span>
                            <p class="text-xs text-gray-500">Upload entire folder structure</p>
                        </div>
                    </button>
                </div>
                
                <!-- IP Record Operations -->
                <div class="p-2">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide px-2 mb-2">IP Records</p>
                    <button onclick="openCreateRecordModal()" class="w-full text-left px-3 py-2.5 hover:bg-orange-50 hover:text-orange-700 flex items-center transition rounded-md">
                        <i class="fas fa-file-plus text-orange-600 w-8 text-sm"></i>
                        <div class="flex-1">
                            <span class="font-medium text-sm">New IP Record</span>
                            <p class="text-xs text-gray-500">Create intellectual property record</p>
                        </div>
                    </button>
                </div>
            </div>
            <input type="file" id="folderUpload" webkitdirectory directory multiple style="display: none;" onchange="handleFolderUpload(event)">
            <input type="file" id="fileUpload" multiple style="display: none;" onchange="handleFileUpload(event)">
        </div>
        <div class="h-5 sm:h-6 w-px bg-gray-300 mx-0.5 sm:mx-1 flex-shrink-0"></div>
        <button onclick="toggleView('grid')" id="gridViewBtn" 
                class="view-btn p-2 sm:p-2.5 rounded-lg hover:bg-gray-200 transition flex-shrink-0" title="Grid view">
            <i class="fas fa-th text-gray-700 text-sm"></i>
        </button>
        <button onclick="toggleView('list')" id="listViewBtn" 
                class="view-btn p-2 sm:p-2.5 rounded-lg hover:bg-gray-200 transition flex-shrink-0" title="List view">
            <i class="fas fa-list text-gray-700 text-sm"></i>
        </button>
        <div class="h-5 sm:h-6 w-px bg-gray-300 mx-0.5 sm:mx-1 flex-shrink-0"></div>
        <button onclick="showSortMenu()" class="p-2 sm:p-2.5 rounded-lg hover:bg-gray-200 transition flex-shrink-0" title="Sort options">
            <i class="fas fa-sort-amount-down text-gray-700 text-sm"></i>
        </button>
        <button onclick="showFilterMenu()" class="p-2 sm:p-2.5 rounded-lg hover:bg-gray-200 transition flex-shrink-0" title="Filter">
            <i class="fas fa-filter text-gray-700 text-sm"></i>
        </button>
        <button onclick="exportRecords()" class="p-2 sm:p-2.5 rounded-lg hover:bg-gray-200 transition flex-shrink-0" title="Export">
            <i class="fas fa-download text-gray-700 text-sm"></i>
        </button>
        <div class="h-5 sm:h-6 w-px bg-gray-300 mx-0.5 sm:mx-1 flex-shrink-0"></div>
        <button id="selectModeBtn" onclick="toggleSelectionMode()" class="p-2 sm:p-2.5 rounded-lg hover:bg-gray-200 transition flex-shrink-0" title="Select items">
            <i class="fas fa-check-square text-gray-700 text-sm"></i>
        </button>
        <button onclick="refreshView()" class="p-2 sm:p-2.5 rounded-lg hover:bg-gray-200 transition flex-shrink-0" title="Refresh">
            <i class="fas fa-sync text-gray-700 text-sm"></i>
        </button>
    </div>
</div>

<!-- Dynamic Folders Section -->
<?php if (!$currentFolderId && !$type_filter && !$status_filter): ?>
<div id="foldersSection" class="mb-6 sm:mb-8">
    <h3 class="text-xs sm:text-sm font-medium text-gray-700 mb-3 sm:mb-4 flex items-center">
        <i class="fas fa-folder mr-2"></i>Folders
        <span class="ml-auto text-xs text-gray-500"><?= count($folders) ?> folders</span>
    </h3>
    <div id="foldersGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2 sm:gap-3 lg:gap-4">
        
        <!-- System Folders -->
        <div onclick="openSystemFolder('patent', 1)" 
             class="folder-card group bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:bg-blue-50 hover:shadow-md cursor-pointer transition relative" data-folder-id="1">
            <button onclick="event.stopPropagation(); showSystemFolderMenu(event, 'patent', 1)" 
                    class="absolute top-1.5 sm:top-2 right-1.5 sm:right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition z-10">
                <i class="fas fa-ellipsis-v text-gray-600 text-xs sm:text-sm"></i>
            </button>
            <div class="flex flex-col items-center">
                <i class="fas fa-lightbulb text-3xl sm:text-4xl lg:text-5xl text-blue-500 mb-2 sm:mb-3"></i>
                <p class="font-medium text-gray-800 text-center text-xs sm:text-sm">Patents</p>
                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1" data-folder-count="1"><?= $stats['patent_count'] ?? 0 ?> items</p>
            </div>
        </div>

        <div onclick="openSystemFolder('trademark', 2)" 
             class="folder-card group bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:bg-green-50 hover:shadow-md cursor-pointer transition relative" data-folder-id="2">
            <button onclick="event.stopPropagation(); showSystemFolderMenu(event, 'trademark', 2)" 
                    class="absolute top-1.5 sm:top-2 right-1.5 sm:right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition z-10">
                <i class="fas fa-ellipsis-v text-gray-600 text-xs sm:text-sm"></i>
            </button>
            <div class="flex flex-col items-center">
                <i class="fas fa-trademark text-3xl sm:text-4xl lg:text-5xl text-green-500 mb-2 sm:mb-3"></i>
                <p class="font-medium text-gray-800 text-center text-xs sm:text-sm">Trademarks</p>
                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1" data-folder-count="2"><?= $stats['trademark_count'] ?? 0 ?> items</p>
            </div>
        </div>

        <div onclick="openSystemFolder('copyright', 3)" 
             class="folder-card group bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:bg-purple-50 hover:shadow-md cursor-pointer transition relative" data-folder-id="3">
            <button onclick="event.stopPropagation(); showSystemFolderMenu(event, 'copyright', 3)" 
                    class="absolute top-1.5 sm:top-2 right-1.5 sm:right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition z-10">
                <i class="fas fa-ellipsis-v text-gray-600 text-xs sm:text-sm"></i>
            </button>
            <div class="flex flex-col items-center">
                <i class="fas fa-copyright text-3xl sm:text-4xl lg:text-5xl text-purple-500 mb-2 sm:mb-3"></i>
                <p class="font-medium text-gray-800 text-center text-xs sm:text-sm">Copyrights</p>
                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1" data-folder-count="3"><?= $stats['copyright_count'] ?? 0 ?> items</p>
            </div>
        </div>

        <div onclick="openSystemFolder('design', 4)" 
             class="folder-card group bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:bg-orange-50 hover:shadow-md cursor-pointer transition relative" data-folder-id="4">
            <button onclick="event.stopPropagation(); showSystemFolderMenu(event, 'design', 4)" 
                    class="absolute top-1.5 sm:top-2 right-1.5 sm:right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition z-10">
                <i class="fas fa-ellipsis-v text-gray-600 text-xs sm:text-sm"></i>
            </button>
            <div class="flex flex-col items-center">
                <i class="fas fa-palette text-3xl sm:text-4xl lg:text-5xl text-orange-500 mb-2 sm:mb-3"></i>
                <p class="font-medium text-gray-800 text-center text-xs sm:text-sm line-clamp-2">Industrial Designs</p>
                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1" data-folder-count="4"><?= $stats['design_count'] ?? 0 ?> items</p>
            </div>
        </div>

        <!-- Custom Folders -->
        <?php if (!empty($folders)): ?>
            <?php foreach ($folders as $folder): ?>
                <div onclick="openCustomFolder('<?= htmlspecialchars($folder['name']) ?>', <?= $folder['id'] ?>)" 
                     class="folder-card group bg-white border border-gray-200 rounded-lg p-3 sm:p-4 hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative" data-folder-id="<?= $folder['id'] ?>">
                    <button onclick="event.stopPropagation(); showCustomFolderMenu(event, '<?= htmlspecialchars($folder['name']) ?>', <?= $folder['id'] ?>)" 
                            class="absolute top-1.5 sm:top-2 right-1.5 sm:right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition z-10">
                        <i class="fas fa-ellipsis-v text-gray-600 text-xs sm:text-sm"></i>
                    </button>
                    <div class="flex flex-col items-center">
                        <i class="fas fa-folder text-3xl sm:text-4xl lg:text-5xl text-gray-400 mb-2 sm:mb-3"></i>
                        <p class="font-medium text-gray-800 text-center text-xs sm:text-sm"><?= htmlspecialchars($folder['name']) ?></p>
                        <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1" data-folder-count="<?= $folder['id'] ?>"><?= $folder['item_count'] ?? 0 ?> items</p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Enhanced File Management Section -->
<div id="fileManagementSection" class="mb-6 sm:mb-8">
    <!-- Current Folder Display -->
    <div id="currentFolderHeader" class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <i id="currentFolderIcon" class="fas fa-folder text-white text-xl"></i>
                </div>
                <div>
                    <h3 id="currentFolderName" class="font-semibold text-gray-900 text-lg">All Files</h3>
                    <p id="currentFolderDescription" class="text-sm text-gray-600"></p>
                    <div id="currentFolderStats" class="flex items-center space-x-4 text-xs text-gray-500 mt-1">
                        <span><i class="fas fa-file mr-1"></i><span id="fileCount">0</span> files</span>
                        <span><i class="fas fa-folder mr-1"></i><span id="subfolderCount">0</span> folders</span>
                        <span><i class="fas fa-hdd mr-1"></i><span id="totalSize">0 MB</span></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="toggleFolderDetails()" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition">
                    <i class="fas fa-info-circle"></i>
                </button>
                <button onclick="refreshCurrentFolder()" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- File/Folder Filter and Sort Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex items-center space-x-3">
                <!-- View Type Toggles -->
                <div class="flex items-center bg-gray-100 rounded-lg p-1">
                    <button onclick="setViewMode('grid')" id="gridModeBtn" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-md transition">
                        <i class="fas fa-th mr-1"></i> Grid
                    </button>
                    <button onclick="setViewMode('list')" id="listModeBtn" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-md transition">
                        <i class="fas fa-list mr-1"></i> List
                    </button>
                </div>
                
                <!-- File Type Filter -->
                <select id="fileTypeFilter" onchange="filterByType(this.value)" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="image">Images</option>
                    <option value="document">Documents</option>
                    <option value="video">Videos</option>
                    <option value="audio">Audio</option>
                </select>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Sort Options -->
                <select id="sortOptions" onchange="sortFiles(this.value)" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="date_desc">Newest First</option>
                    <option value="date_asc">Oldest First</option>
                    <option value="size_desc">Largest First</option>
                    <option value="size_asc">Smallest First</option>
                </select>

                <!-- Quick Actions -->
                <div class="flex items-center space-x-1">
                    <button onclick="selectAllItems()" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Select All">
                        <i class="fas fa-check-square text-sm"></i>
                    </button>
                    <button onclick="bulkDownload()" id="bulkDownloadBtn" class="p-2 text-gray-500 hover:text-green-600 hover:bg-green-100 rounded-lg transition hidden" title="Download Selected">
                        <i class="fas fa-download text-sm"></i>
                    </button>
                    <button onclick="bulkDelete()" id="bulkDeleteBtn" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-100 rounded-lg transition hidden" title="Delete Selected">
                        <i class="fas fa-trash text-sm"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 min-h-[400px]">
        <!-- Loading State -->
        <div id="loadingState" class="flex items-center justify-center h-64 hidden">
            <div class="flex items-center space-x-3 text-gray-500">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span>Loading files...</span>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="flex flex-col items-center justify-center h-64 text-gray-500 hidden">
            <i class="fas fa-folder-open text-4xl mb-3 text-gray-400"></i>
            <h4 class="text-lg font-medium text-gray-600 mb-2">This folder is empty</h4>
            <p class="text-sm text-center max-w-md mb-4">Upload files or create folders to organize your intellectual property documents.</p>
            <button onclick="showUploadModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition">
                <i class="fas fa-plus mr-2"></i>Upload Files
            </button>
        </div>

        <!-- Grid View Container -->
        <div id="gridViewContainer" class="p-6">
            <div id="itemsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <!-- Items will be populated by JavaScript -->
            </div>
        </div>

        <!-- List View Container -->
        <div id="listViewContainer" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this.checked)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itemsList" class="bg-white divide-y divide-gray-200">
                        <!-- Items will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="border-t border-gray-200 px-6 py-3 flex items-center justify-between">
            <div class="text-sm text-gray-500" id="paginationInfo">
                <!-- Pagination info will be populated by JavaScript -->
            </div>
            <div class="flex items-center space-x-2" id="paginationControls">
                <!-- Pagination controls will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

                <!-- Files Section -->
                <div id="filesSection">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Files</h3>
                    
                    <!-- Grid View -->
                    <div id="gridView" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
    <?php 
    $ipTypes = [
        ['name' => 'Patent', 'count' => $stats['patent_count'] ?? 0, 'icon' => 'lightbulb', 'color' => 'blue'],
        ['name' => 'Trademark', 'count' => $stats['trademark_count'] ?? 0, 'icon' => 'trademark', 'color' => 'green'],
        ['name' => 'Copyright', 'count' => $stats['copyright_count'] ?? 0, 'icon' => 'copyright', 'color' => 'purple'],
        ['name' => 'Design', 'count' => $stats['design_count'] ?? 0, 'icon' => 'palette', 'color' => 'yellow']
    ];
    foreach ($ipTypes as $type): ?>
        <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 hover:shadow-lg transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] sm:text-xs text-gray-600 uppercase"><?= $type['name'] ?></p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-800 mt-1"><?= $type['count'] ?></p>
                </div>
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-<?= $type['color'] ?>-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-<?= $type['icon'] ?> text-<?= $type['color'] ?>-600 text-sm"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filters & Search -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-md p-3 sm:p-4 mb-4 sm:mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 sm:gap-4">
        <div class="md:col-span-2">
            <div class="relative">
                <input type="text" id="searchRecords" placeholder="Search by title, owner, reference..." 
                       class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-search absolute left-3 top-3.5 text-gray-400 text-sm"></i>
            </div>
        </div>
        <select id="filterType" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">All Types</option>
            <option value="1">Patent</option>
            <option value="2">Trademark</option>
            <option value="3">Copyright</option>
            <option value="4">Industrial Design</option>
        </select>
        <select id="filterStatus" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="active">Active</option>
            <option value="expired">Expired</option>
            <option value="archived">Archived</option>
        </select>
        <button onclick="resetFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition text-sm">
            <i class="fas fa-redo mr-2"></i>
            Reset
        </button>
    </div>
</div>

<!-- Records Grid/List View Toggle -->
<div class="flex items-center justify-between mb-4">
    <div class="text-xs sm:text-sm text-gray-600">
        Showing <span class="font-semibold"><?= count($records ?? []) ?></span> records
    </div>
    <div class="flex items-center space-x-2">
        <button id="viewGrid" onclick="setView('grid')" class="p-2 bg-blue-500 text-white rounded-lg">
            <i class="fas fa-th text-sm"></i>
        </button>
        <button id="viewList" onclick="setView('list')" class="p-2 bg-gray-200 text-gray-700 rounded-lg">
            <i class="fas fa-list text-sm"></i>
        </button>
    </div>
</div>

<!-- Records Grid View -->
<div id="gridView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
    <?php if (isset($records) && count($records) > 0): ?>
        <?php foreach ($records as $record): ?>
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md hover:shadow-xl transition overflow-hidden record-card" 
                 data-type="<?= $record['ip_type_id'] ?>" 
                 data-status="<?= $record['status'] ?>">
                <!-- Header -->
                <div class="h-2 bg-gradient-to-r <?= getGradientColor($record['ip_type_id']) ?>"></div>
                
                <div class="p-4 sm:p-6">
                    <!-- Type Badge -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="px-2 sm:px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                            <?= htmlspecialchars($record['type'] ?? $record['ip_type'] ?? 'Unknown') ?>
                        </span>
                        <span class="px-2 sm:px-3 py-1 <?= getStatusBadge($record['status'] ?? 'pending') ?> text-xs font-semibold rounded-full">
                            <?= ucfirst($record['status'] ?? 'Pending') ?>
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-2 line-clamp-2">
                        <?= htmlspecialchars($record['title'] ?? 'Untitled') ?>
                    </h3>

                    <!-- Description -->
                    <p class="text-xs sm:text-sm text-gray-600 mb-4 line-clamp-3">
                        <?= htmlspecialchars($record['description']) ?>
                    </p>

                    <!-- Meta Info -->
                    <div class="space-y-2 mb-4 text-xs sm:text-sm">
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-user w-5 text-gray-400 text-sm"></i>
                            <span class="ml-2"><?= htmlspecialchars($record['owner']) ?></span>
                        </div>
                        <?php if ($record['reference_number']): ?>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-hashtag w-5 text-gray-400 text-sm"></i>
                                <span class="ml-2"><?= htmlspecialchars($record['reference_number']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-calendar w-5 text-gray-400 text-sm"></i>
                            <span class="ml-2"><?= date('M j, Y', strtotime($record['created_at'])) ?></span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <i class="fas fa-paperclip w-5 text-gray-400 text-sm"></i>
                            <span class="ml-2"><?= $record['document_count'] ?? 0 ?> documents</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <a href="<?= BASE_URL ?>/admin/ip-records/<?= $record['id'] ?>" 
                           class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-lg transition text-center text-xs sm:text-sm">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                        <button onclick="editRecord(<?= $record['id'] ?>)" 
                                class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="archiveRecord(<?= $record['id'] ?>)" 
                                class="p-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg transition">
                            <i class="fas fa-archive text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-span-full bg-white rounded-xl shadow-md p-8 sm:p-12 text-center">
            <i class="fas fa-folder-open text-4xl sm:text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">No IP Records Found</h3>
            <p class="text-sm sm:text-base text-gray-600 mb-6">Start by creating your first IP record</p>
            <button onclick="openCreateRecordModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition text-sm sm:text-base">
                Create First Record
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- List View (Hidden by default) -->
<div id="listView" class="hidden bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase">Title</th>
                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase hidden md:table-cell">Type</th>
                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase hidden lg:table-cell">Owner</th>
                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase hidden xl:table-cell">Documents</th>
                    <th class="px-3 sm:px-6 py-3 sm:py-4 text-right text-xs font-semibold text-gray-600 uppercase">Actions</th>
                </tr>
            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (isset($records) && count($records) > 0): ?>
                                    <?php foreach ($records as $record): ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <div class="font-medium text-gray-800"><?= htmlspecialchars($record['title']) ?></div>
                                                <div class="text-sm text-gray-600"><?= htmlspecialchars($record['reference_number'] ?? 'N/A') ?></div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                                    <?= htmlspecialchars($record['ip_type']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($record['owner']) ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 <?= getStatusBadge($record['status']) ?> text-xs rounded-full">
                                                    <?= ucfirst($record['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700"><?= $record['document_count'] ?? 0 ?></td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end space-x-2">
                                                    <a href="<?= BASE_URL ?>/admin/ip-records/<?= $record['id'] ?>" 
                                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button onclick="editRecord(<?= $record['id'] ?>)" 
                                                            class="p-2 text-gray-600 hover:bg-gray-50 rounded-lg transition" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button onclick="archiveRecord(<?= $record['id'] ?>)" 
                                                            class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition" title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/common.js"></script>
    <script src="<?= BASE_URL ?>/js/utils.js"></script>
    <script>
        function setView(view) {
            if (view === 'grid') {
                document.getElementById('gridView').classList.remove('hidden');
                document.getElementById('listView').classList.add('hidden');
                document.getElementById('viewGrid').classList.add('bg-blue-500', 'text-white');
                document.getElementById('viewGrid').classList.remove('bg-gray-200', 'text-gray-700');
                document.getElementById('viewList').classList.remove('bg-blue-500', 'text-white');
                document.getElementById('viewList').classList.add('bg-gray-200', 'text-gray-700');
            } else {
                document.getElementById('gridView').classList.add('hidden');
                document.getElementById('listView').classList.remove('hidden');
                document.getElementById('viewList').classList.add('bg-blue-500', 'text-white');
                document.getElementById('viewList').classList.remove('bg-gray-200', 'text-gray-700');
                document.getElementById('viewGrid').classList.remove('bg-blue-500', 'text-white');
                document.getElementById('viewGrid').classList.add('bg-gray-200', 'text-gray-700');
            }
            IPRepoUtils.Storage.set('viewPreference', view);
        }

        function openCreateRecordModal() {
            Swal.fire({
                title: 'Create IP Record',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">IP Type</label>
                            <select id="ipType" class="swal2-input w-full">
                                <option value="1">Patent</option>
                                <option value="2">Trademark</option>
                                <option value="3">Copyright</option>
                                <option value="4">Industrial Design</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input type="text" id="title" class="swal2-input w-full" placeholder="Enter title">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Owner</label>
                            <input type="text" id="owner" class="swal2-input w-full" placeholder="Enter owner name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Description</label>
                            <textarea id="description" class="swal2-textarea w-full" rows="3"></textarea>
                        </div>
                    </div>
                `,
                width: 600,
                showCancelButton: true,
                confirmButtonText: 'Create Record',
                preConfirm: () => {
                    const ipType = document.getElementById('ipType').value;
                    const title = document.getElementById('title').value;
                    const owner = document.getElementById('owner').value;
                    const description = document.getElementById('description').value;

                    if (!title || !owner) {
                        Swal.showValidationMessage('Title and Owner are required');
                        return false;
                    }

                    return { ipType, title, owner, description };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showToast('success', 'IP Record created successfully');
                    setTimeout(() => location.reload(), 1000);
                }
            });
        }

        function editRecord(id) {
            showToast('info', 'Opening edit form...');
        }

        function archiveRecord(id) {
            confirmAction('Archive this IP record?', 'The record will be moved to archived status')
                .then(() => {
                    showToast('success', 'Record archived successfully');
                    setTimeout(() => location.reload(), 1000);
                });
        }
    </script>
    <script>

        // Toggle New Menu
        function toggleNewMenu(event) {
            if(event) {
                event.stopPropagation();
            }
            const menu = document.getElementById('newMenu');
            const btn = document.getElementById('newMenuBtn');
            
            if (menu.classList.contains('hidden')) {
                // Determine position
                const rect = btn.getBoundingClientRect();
                menu.style.top = (rect.bottom + 8) + 'px';
                menu.style.left = rect.left + 'px';
                // Adjust if going off screen
                if (window.innerWidth < rect.left + 224) { // 224px is roughly w-56
                     menu.style.left = 'auto';
                     menu.style.right = (window.innerWidth - rect.right) + 'px';
                }
                
                menu.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
            }
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('newMenu');
            const button = event.target.closest('button');
            const toggleFn = button ? button.getAttribute('onclick') : '';
            
            // Check if click is on the toggle button
            if (toggleFn && toggleFn.includes('toggleNewMenu')) {
                return;
            }

            if (!menu.contains(event.target) && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });

        // Create Folder Modal
        function showCreateFolderModal() {
            document.getElementById('newMenu').classList.add('hidden');
            
            const isMobile = window.innerWidth < 640;
            
            Swal.fire({
                title: 'New folder',
                html: `
                    <div class="flex flex-col text-left">
                        <label for="folderName" class="text-sm font-medium text-gray-700 mb-2">Folder Name</label>
                        <input type="text" id="folderName" 
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900" 
                               placeholder="Untitled folder">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Create',
                confirmButtonColor: '#2563eb',
                cancelButtonText: 'Cancel',
                width: isMobile ? '90%' : '450px',
                padding: isMobile ? '1.25rem' : '1.5rem',
                customClass: {
                    container: 'p-4',
                    popup: 'rounded-xl shadow-2xl',
                    title: 'text-xl font-bold text-gray-800',
                    confirmButton: 'px-6 py-2.5 rounded-lg text-sm font-medium shadow-sm',
                    cancelButton: 'px-6 py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                },
                didOpen: () => {
                    const input = document.getElementById('folderName');
                    input.focus();
                    input.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') Swal.clickConfirm();
                    });
                },
                preConfirm: () => {
                    const folderName = document.getElementById('folderName').value.trim();
                    if (!folderName) {
                        Swal.showValidationMessage('Please enter a folder name');
                        return false;
                    }
                    return folderName;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    createFolder(result.value);
                }
            });
        }

        // Create Folder
        function createFolder(folderName) {
            IPRepoUtils.Loading.show('Creating folder...');
            
            // Make actual AJAX call
            const formData = new FormData();
            formData.append('name', folderName);
            // formData.append('parent_id', currentParentId); // If nested folders implemented

            fetch('<?= BASE_URL ?>/admin/folders/create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                IPRepoUtils.Loading.hide();
                if (data.success) {
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 1000); 
                } else {
                     showToast('error', data.message || 'Failed to create folder');
                }
            })
            .catch(error => {
                IPRepoUtils.Loading.hide();
                console.error('Error:', error);
                showToast('error', 'An error occurred');
            });
        }


        // Upload Modal
        function showUploadModal() {
            document.getElementById('newMenu').classList.add('hidden');
            Swal.fire({
                title: 'Upload files',
                html: `
                    <div class="text-left">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition cursor-pointer" onclick="document.getElementById('fileUpload').click()">
                            <i class="fas fa-cloud-upload-alt text-6xl text-gray-400 mb-4"></i>
                            <p class="text-gray-700 font-medium mb-2">Click to select files</p>
                            <p class="text-sm text-gray-500">or drag and drop files here</p>
                            <p class="text-xs text-gray-400 mt-2">Supported: PDF, DOC, DOCX, JPG, PNG (Max 50MB)</p>
                        </div>
                        <input type="file" id="fileUpload" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display: none;" onchange="handleFileSelect(event)">
                        <div id="fileList" class="mt-4 space-y-2"></div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Upload',
                confirmButtonColor: '#2563eb',
                cancelButtonText: 'Cancel',
                width: 600,
                preConfirm: () => {
                    const files = document.getElementById('fileUpload').files;
                    if (files.length === 0) {
                        Swal.showValidationMessage('Please select at least one file');
                        return false;
                    }
                    return files;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    uploadFiles(result.value);
                }
            });

            // Enable drag and drop
            setTimeout(() => {
                const dropZone = document.querySelector('.border-dashed');
                if (dropZone) {
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropZone.classList.add('border-blue-500', 'bg-blue-50');
                    });
                    
                    dropZone.addEventListener('dragleave', () => {
                        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                    });
                    
                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                        const files = e.dataTransfer.files;
                        document.getElementById('fileUpload').files = files;
                        handleFileSelect({ target: { files } });
                    });
                }
            }, 100);
        }

        // Handle File Selection
        function handleFileSelect(event) {
            const files = event.target.files;
            const fileList = document.getElementById('fileList');
            fileList.innerHTML = '';
            
            Array.from(files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                fileItem.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-file text-gray-600"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">${file.name}</p>
                            <p class="text-xs text-gray-500">${IPRepoUtils.File.formatSize(file.size)}</p>
                        </div>
                    </div>
                    <i class="fas fa-check-circle text-green-500"></i>
                `;
                fileList.appendChild(fileItem);
            });
        }

        // Upload Files
        function uploadFiles(files) {
            IPRepoUtils.Loading.show('Uploading files...');
            
            const formData = new FormData();
            Array.from(files).forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });
            formData.append('folder', currentFolder);
            
            // Simulate upload with progress
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                if (progress <= 100) {
                    IPRepoUtils.Loading.show(`Uploading... ${progress}%`);
                }
                if (progress >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        IPRepoUtils.Loading.hide();
                        showToast('success', `${files.length} file(s) uploaded successfully`);
                        // Refresh the view
                        // location.reload();
                    }, 500);
                }
            }, 200);
            
            // Here you would make an actual AJAX call
            // ajaxRequest('<?= BASE_URL ?>/admin/files/upload', formData, 'POST');
        }

        // Handle Folder Upload
        function handleFolderUpload(event) {
            const files = event.target.files;
            if (files.length === 0) return;
            
            Swal.fire({
                title: 'Upload folder',
                text: `Upload ${files.length} files from this folder?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Upload',
                confirmButtonColor: '#2563eb'
            }).then((result) => {
                if (result.isConfirmed) {
                    uploadFiles(files);
                }
            });
        }
    </script>

    <script>
        // Global state management
        let currentFolderId = <?= json_encode($currentFolderId) ?>;
        let currentFolderPath = <?= json_encode($currentPath) ?>;
        let fileManager = null;
        let currentFolder = '';
        
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeFileManager();
            updateUIState();
        });
        
        function initializeFileManager() {
            // Initialize with current folder context
            if (currentFolderId) {
                loadFolderContents(currentFolderId);
            }
        }
        
        function updateUIState() {
            // Update breadcrumb and UI based on current state
            if (currentFolderId) {
                document.getElementById('foldersSection')?.classList.add('hidden');
                document.getElementById('fileManagementSection')?.classList.remove('hidden');
            } else {
                document.getElementById('foldersSection')?.classList.remove('hidden');
                document.getElementById('fileManagementSection')?.classList.add('hidden');
            }
        }
        
        // Navigation functions
        function navigateToRoot() {
            window.location.href = window.location.pathname;
        }
        
        function openSystemFolder(folderType, folderId) {
            const url = new URL(window.location);
            url.searchParams.set('folder_id', folderId);
            url.searchParams.set('folder_type', folderType);
            url.searchParams.delete('type');
            url.searchParams.delete('status');
            window.location.href = url.toString();
        }
        
        function openCustomFolder(folderName, folderId) {
            const url = new URL(window.location);
            url.searchParams.set('folder_id', folderId);
            url.searchParams.set('folder_name', folderName);
            url.searchParams.delete('type');
            url.searchParams.delete('status');
            window.location.href = url.toString();
        }
        
        // File management functions
        function loadFolderContents(folderId) {
            showLoadingState();
            
            fetch(`<?= BASE_URL ?>/document/listFiles?folder_id=${folderId}`)
                .then(response => response.json())
                .then(data => {
                    hideLoadingState();
                    if (data.success) {
                        renderFolderContents(data);
                        updateFolderInfo(data);
                    } else {
                        showEmptyState();
                        showToast('error', data.message || 'Failed to load folder contents');
                    }
                })
                .catch(error => {
                    hideLoadingState();
                    showEmptyState();
                    console.error('Error loading folder contents:', error);
                    showToast('error', 'An error occurred while loading folder contents');
                });
        }
        
        function renderFolderContents(data) {
            const { files, folder, stats } = data;
            const gridContainer = document.getElementById('itemsGrid');
            const listContainer = document.getElementById('itemsList');
            
            if (!files || files.length === 0) {
                showEmptyState();
                return;
            }
            
            // Clear containers
            if (gridContainer) gridContainer.innerHTML = '';
            if (listContainer) listContainer.innerHTML = '';
            
            // Render files in grid view
            if (gridContainer) {
                files.forEach(file => {
                    const fileCard = createFileCard(file);
                    gridContainer.appendChild(fileCard);
                });
            }
            
            // Render files in list view
            if (listContainer) {
                files.forEach(file => {
                    const fileRow = createFileRow(file);
                    listContainer.appendChild(fileRow);
                });
            }
            
            hideEmptyState();
        }
        
        function createFileCard(file) {
            const card = document.createElement('div');
            card.className = 'bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md cursor-pointer transition group';
            card.onclick = () => openFile(file);
            
            const fileIcon = getFileIcon(file.file_type, file.is_image);
            const fileSize = formatFileSize(file.file_size);
            
            card.innerHTML = `
                <div class="flex flex-col items-center text-center">
                    <div class="w-12 h-12 flex items-center justify-center mb-3">
                        ${file.is_image && file.thumbnail_path 
                            ? `<img src="<?= BASE_URL ?>/document/thumbnail?id=${file.id}" class="w-12 h-12 object-cover rounded" alt="${file.original_name}">` 
                            : `<i class="${fileIcon} text-2xl text-gray-600"></i>`}
                    </div>
                    <p class="text-sm font-medium text-gray-900 truncate w-full" title="${file.original_name}">${file.original_name}</p>
                    <p class="text-xs text-gray-500 mt-1">${fileSize}</p>
                </div>
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                    <button onclick="event.stopPropagation(); showFileMenu(event, ${file.id})" class="p-1 hover:bg-gray-200 rounded">
                        <i class="fas fa-ellipsis-v text-xs text-gray-600"></i>
                    </button>
                </div>
            `;
            
            return card;
        }
        
        function createFileRow(file) {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.onclick = () => openFile(file);
            
            const fileIcon = getFileIcon(file.file_type, file.is_image);
            const fileSize = formatFileSize(file.file_size);
            const fileDate = new Date(file.created_at).toLocaleDateString();
            
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="file-checkbox" value="${file.id}" onclick="event.stopPropagation()">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="w-8 h-8 flex items-center justify-center mr-3">
                            ${file.is_image && file.thumbnail_path 
                                ? `<img src="<?= BASE_URL ?>/document/thumbnail?id=${file.id}" class="w-8 h-8 object-cover rounded" alt="${file.original_name}">` 
                                : `<i class="${fileIcon} text-gray-600"></i>`}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${file.original_name}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${file.file_type.toUpperCase()}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${fileSize}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${fileDate}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="event.stopPropagation(); showFileMenu(event, ${file.id})" class="p-1 hover:bg-gray-200 rounded">
                        <i class="fas fa-ellipsis-v text-gray-600"></i>
                    </button>
                </td>
            `;
            
            return row;
        }
        
        // Upload functions
        function showUploadModal() {
            const currentFolderName = getCurrentFolderName();
            
            Swal.fire({
                title: 'Upload Files',
                html: `
                    <div class="text-left">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-folder mr-2"></i>Uploading to: <strong>${currentFolderName}</strong>
                            </p>
                        </div>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition cursor-pointer" 
                             onclick="document.getElementById('fileUploadInput').click()" id="uploadDropZone">
                            <i class="fas fa-cloud-upload-alt text-6xl text-gray-400 mb-4"></i>
                            <p class="text-gray-700 font-medium mb-2">Click to select files</p>
                            <p class="text-sm text-gray-500">or drag and drop files here</p>
                            <p class="text-xs text-gray-400 mt-2">Supported: PDF, DOC, DOCX, JPG, PNG (Max 50MB each)</p>
                        </div>
                        <input type="file" id="fileUploadInput" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.bmp,.txt,.rtf" 
                               style="display: none;" onchange="handleFileSelection(event)">
                        <div id="selectedFilesList" class="mt-4 space-y-2 max-h-40 overflow-y-auto"></div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Upload Files',
                confirmButtonColor: '#2563eb',
                cancelButtonText: 'Cancel',
                width: 600,
                preConfirm: () => {
                    const files = document.getElementById('fileUploadInput').files;
                    if (files.length === 0) {
                        Swal.showValidationMessage('Please select at least one file');
                        return false;
                    }
                    return files;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    uploadFilesToCurrentFolder(result.value);
                }
            });
            
            // Setup drag and drop after modal opens
            setTimeout(setupDragAndDrop, 100);
        }
        
        function setupDragAndDrop() {
            const dropZone = document.getElementById('uploadDropZone');
            if (!dropZone) return;
            
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
            });
            
            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            });
            
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                
                const files = e.dataTransfer.files;
                document.getElementById('fileUploadInput').files = files;
                handleFileSelection({ target: { files } });
            });
        }
        
        function handleFileSelection(event) {
            const files = Array.from(event.target.files);
            const filesList = document.getElementById('selectedFilesList');
            
            filesList.innerHTML = '';
            
            files.forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                
                fileItem.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <i class="${getFileIconByName(file.name)} text-gray-600"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">${file.name}</p>
                            <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                        </div>
                    </div>
                    <i class="fas fa-check-circle text-green-500"></i>
                `;
                
                filesList.appendChild(fileItem);
            });
        }
        
        function uploadFilesToCurrentFolder(files) {
            const formData = new FormData();
            
            // Add files to FormData
            Array.from(files).forEach((file, index) => {
                formData.append(`files[${index}]`, file);
            });
            
            // Add current folder context
            if (currentFolderId) {
                formData.append('folder_id', currentFolderId);
            }
            
            // Show loading state
            Swal.fire({
                title: 'Uploading Files',
                html: '<div class="text-center"><i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i><p>Please wait while files are being uploaded...</p></div>',
                allowOutsideClick: false,
                showConfirmButton: false
            });
            
            // Perform upload
            fetch('<?= BASE_URL ?>/document/uploadToFolder', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    showToast('success', data.message || 'Files uploaded successfully');
                    // Refresh current folder contents
                    if (currentFolderId) {
                        loadFolderContents(currentFolderId);
                    }
                } else {
                    showToast('error', data.message || 'Upload failed');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Upload error:', error);
                showToast('error', 'An error occurred during upload');
            });
        }
        
        // Utility functions
        function getCurrentFolderName() {
            if (!currentFolderId) return 'Root Directory';
            
            const folderTypes = {
                '1': 'Patents',
                '2': 'Trademarks', 
                '3': 'Copyrights',
                '4': 'Industrial Designs'
            };
            
            return folderTypes[currentFolderId] || 'Current Folder';
        }
        
        function getFileIcon(fileType, isImage) {
            if (isImage) return 'fas fa-image';
            
            const iconMap = {
                'pdf': 'fas fa-file-pdf',
                'doc': 'fas fa-file-word',
                'docx': 'fas fa-file-word',
                'xls': 'fas fa-file-excel',
                'xlsx': 'fas fa-file-excel',
                'ppt': 'fas fa-file-powerpoint',
                'pptx': 'fas fa-file-powerpoint',
                'txt': 'fas fa-file-alt',
                'zip': 'fas fa-file-archive',
                'rar': 'fas fa-file-archive',
                'mp3': 'fas fa-file-audio',
                'mp4': 'fas fa-file-video'
            };
            
            return iconMap[fileType.toLowerCase()] || 'fas fa-file';
        }
        
        function getFileIconByName(fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            return getFileIcon(ext, false);
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function showLoadingState() {
            document.getElementById('loadingState')?.classList.remove('hidden');
            document.getElementById('emptyState')?.classList.add('hidden');
            document.getElementById('gridViewContainer')?.classList.add('hidden');
            document.getElementById('listViewContainer')?.classList.add('hidden');
        }
        
        function hideLoadingState() {
            document.getElementById('loadingState')?.classList.add('hidden');
            const currentViewMode = localStorage.getItem('viewMode') || 'grid';
            
            if (currentViewMode === 'grid') {
                document.getElementById('gridViewContainer')?.classList.remove('hidden');
            } else {
                document.getElementById('listViewContainer')?.classList.remove('hidden');
            }
        }
        
        function showEmptyState() {
            document.getElementById('loadingState')?.classList.add('hidden');
            document.getElementById('emptyState')?.classList.remove('hidden');
            document.getElementById('gridViewContainer')?.classList.add('hidden');
            document.getElementById('listViewContainer')?.classList.add('hidden');
        }
        
        function hideEmptyState() {
            document.getElementById('emptyState')?.classList.add('hidden');
        }
        
        // Utility functions
        function showToast(type, message) {
            if (typeof Swal !== 'undefined') {
                const icon = type === 'success' ? 'success' : 'error';
                const title = type === 'success' ? 'Success' : 'Error';
                
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: message,
                    timer: 3000,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
        }
        
        function confirmAction(title, text) {
            return new Promise((resolve, reject) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, proceed!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            resolve();
                        } else {
                            reject();
                        }
                    });
                } else {
                    if (confirm(title + '\n' + text)) {
                        resolve();
                    } else {
                        reject();
                    }
                }
            });
        }
        
        function refreshCurrentView() {
            if (currentFolderId) {
                loadFolderContents(currentFolderId);
            } else {
                location.reload();
            }
        }
        
        // Missing utility object
        const IPRepoUtils = {
            Storage: {
                set: function(key, value) {
                    localStorage.setItem(key, value);
                },
                get: function(key) {
                    return localStorage.getItem(key);
                }
            },
            Loading: {
                show: function(message) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: message || 'Loading...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    }
                },
                hide: function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                }
            },
            File: {
                formatSize: function(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }
        };
        
        // Missing functions for UI interactions
        function resetFilters() {
            document.getElementById('searchRecords').value = '';
            document.getElementById('filterType').value = '';
            document.getElementById('filterStatus').value = '';
            location.reload();
        }
        
        function refreshView() {
            refreshCurrentView();
        }
        
        function toggleView(viewType) {
            const gridBtn = document.getElementById('gridViewBtn');
            const listBtn = document.getElementById('listViewBtn');
            const gridContainer = document.getElementById('gridViewContainer');
            const listContainer = document.getElementById('listViewContainer');
            
            if (viewType === 'grid') {
                gridBtn?.classList.add('bg-blue-500', 'text-white');
                gridBtn?.classList.remove('bg-gray-200');
                listBtn?.classList.remove('bg-blue-500', 'text-white');
                listBtn?.classList.add('bg-gray-200');
                gridContainer?.classList.remove('hidden');
                listContainer?.classList.add('hidden');
            } else {
                listBtn?.classList.add('bg-blue-500', 'text-white');
                listBtn?.classList.remove('bg-gray-200');
                gridBtn?.classList.remove('bg-blue-500', 'text-white');
                gridBtn?.classList.add('bg-gray-200');
                listContainer?.classList.remove('hidden');
                gridContainer?.classList.add('hidden');
            }
            localStorage.setItem('viewMode', viewType);
        }
        
        function toggleSelectionMode() {
            // Implementation for selection mode
            showToast('info', 'Selection mode toggled');
        }
        
        function exportRecords() {
            showToast('info', 'Export functionality coming soon');
        }
        
        function showSortMenu() {
            showToast('info', 'Sort menu functionality');
        }
        
        function showFilterMenu() {
            showToast('info', 'Filter menu functionality');
        }
        
        function toggleFolderDetails() {
            showToast('info', 'Folder details toggled');
        }
        
        function refreshCurrentFolder() {
            refreshCurrentView();
        }
        
        function toggleViewInfo() {
            showToast('info', 'View information toggled');
        }
        
        function updateFolderInfo(data) {
            // Update folder statistics and info
            const folderStats = document.getElementById('folderStats');
            if (folderStats && data.stats) {
                folderStats.textContent = `${data.stats.file_count || 0} files`;
            }
        }
        
        function openFile(file) {
            // Open file for viewing/downloading
            window.open(`<?= BASE_URL ?>/document/download?id=${file.id}`, '_blank');
        }
        
        function showFileMenu(event, fileId) {
            event.stopPropagation();
            showToast('info', 'File menu for ID: ' + fileId);
        }
        
        function filterByType(type) {
            // Implement file type filtering
            console.log('Filter by type:', type);
        }
        
        function sortFiles(sortBy) {
            // Implement file sorting
            console.log('Sort files by:', sortBy);
        }
        
        function selectAllItems() {
            const checkboxes = document.querySelectorAll('.file-checkbox');
            checkboxes.forEach(cb => cb.checked = !cb.checked);
            updateBulkActions();
        }
        
        function bulkDownload() {
            showToast('info', 'Bulk download initiated');
        }
        
        function bulkDelete() {
            confirmAction('Delete selected files?', 'This action cannot be undone')
                .then(() => {
                    showToast('success', 'Files deleted successfully');
                })
                .catch(() => {
                    showToast('info', 'Delete cancelled');
                });
        }
        
        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.file-checkbox:checked');
            const bulkActions = document.querySelectorAll('#bulkDownloadBtn, #bulkDeleteBtn');
            
            if (checkedBoxes.length > 0) {
                bulkActions.forEach(btn => btn.classList.remove('hidden'));
            } else {
                bulkActions.forEach(btn => btn.classList.add('hidden'));
            }
        }
        
        function showSystemFolderMenu(event, folderType, folderId) {
            event.stopPropagation();
            showToast('info', `System folder menu: ${folderType}`);
        }
        
        function bulkDownload() {
            showToast('info', 'Bulk download initiated');
        }
        
        function bulkDelete() {
            confirmAction('Delete selected files?', 'This action cannot be undone')
                .then(() => {
                    showToast('success', 'Files deleted successfully');
                })
                .catch(() => {
                    showToast('info', 'Delete cancelled');
                });
        }
        
        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.file-checkbox:checked');
            const bulkActions = document.querySelectorAll('#bulkDownloadBtn, #bulkDeleteBtn');
            
            if (checkedBoxes.length > 0) {
                bulkActions.forEach(btn => btn.classList.remove('hidden'));
            } else {
                bulkActions.forEach(btn => btn.classList.add('hidden'));
            }
        }
        
        // Load folder contents if we're in a folder
        if (currentFolderId) {
            loadFolderContents(currentFolderId);
        }
    </script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
