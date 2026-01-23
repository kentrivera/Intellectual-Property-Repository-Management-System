<?php
// Staff IP Records now uses the Drive-style Folder Repository UI.
// Render it in read-only mode for staff.
$recordFoldersReadOnly = true;
require APP_PATH . '/views/admin/ip-records.php';
return;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse IP Records - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php 
        $pageTitle = 'Browse IP Records';
        include APP_PATH . '/views/components/sidebar-staff.php'; 
        ?>

        <div class="flex-1 flex flex-col overflow-hidden lg:ml-72">
            <?php include APP_PATH . '/views/components/header.php'; ?>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Breadcrumb Navigation -->
                <nav class="flex items-center space-x-2 text-sm mb-6">
                    <button onclick="navigateToFolder('root')" class="flex items-center text-gray-600 hover:text-indigo-600 transition">
                        <i class="fas fa-home mr-2"></i>
                        <span>My Repository</span>
                    </button>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    <span id="currentFolder" class="text-gray-900 font-medium">All IP Records</span>
                </nav>

                <!-- Toolbar -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 gap-4">
                    <div class="flex-1 max-w-2xl">
                        <div class="relative">
                            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="searchInput" 
                                   placeholder="Search in IP Repository..." 
                                   class="w-full pl-12 pr-4 py-3 bg-gray-100 hover:bg-white hover:shadow-md border border-transparent focus:border-indigo-500 focus:bg-white rounded-lg transition"
                                   onkeyup="filterRecords()">
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <button id="newMenuBtn" onclick="toggleNewMenu(event)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition shadow-sm flex items-center">
                                <i class="fas fa-plus mr-2"></i>New
                                <i class="fas fa-chevron-down ml-2 text-sm"></i>
                            </button>
                            <!-- New Menu Dropdown -->
                            <div id="newMenu" class="hidden absolute top-full left-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <button onclick="showCreateFolderModal()" class="w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center transition">
                                    <i class="fas fa-folder text-gray-600 w-8"></i>
                                    <span class="font-medium">New folder</span>
                                </button>
                                <hr class="my-1">
                                <button onclick="showUploadModal()" class="w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center transition">
                                    <i class="fas fa-upload text-gray-600 w-8"></i>
                                    <span class="font-medium">File upload</span>
                                </button>
                                <button onclick="document.getElementById('folderUpload').click()" class="w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center transition">
                                    <i class="fas fa-folder-open text-gray-600 w-8"></i>
                                    <span class="font-medium">Folder upload</span>
                                </button>
                            </div>
                            <input type="file" id="folderUpload" webkitdirectory directory multiple style="display: none;" onchange="handleFolderUpload(event)">
                        </div>
                        <div class="h-6 w-px bg-gray-300 mx-1"></div>
                        <button onclick="toggleView('grid')" id="gridViewBtn" 
                                class="view-btn p-2 rounded-lg hover:bg-gray-200 transition" title="Grid view">
                            <i class="fas fa-th text-gray-700"></i>
                        </button>
                        <button onclick="toggleView('list')" id="listViewBtn" 
                                class="view-btn p-2 rounded-lg hover:bg-gray-200 transition" title="List view">
                            <i class="fas fa-list text-gray-700"></i>
                        </button>
                        <div class="h-6 w-px bg-gray-300 mx-1"></div>
                        <button onclick="showSortMenu()" class="p-2 rounded-lg hover:bg-gray-200 transition" title="Sort options">
                            <i class="fas fa-sort-amount-down text-gray-700"></i>
                        </button>
                        <button onclick="showFilterMenu()" class="p-2 rounded-lg hover:bg-gray-200 transition" title="Filter">
                            <i class="fas fa-filter text-gray-700"></i>
                        </button>
                    </div>
                </div>

                <!-- Folders Section -->
                <div id="foldersSection" class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Folders</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        <!-- Patent Folder -->
                        <div onclick="openFolder('patent')" 
                             class="folder-card group bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative">
                            <button onclick="event.stopPropagation(); showFolderMenu(event, 'patent')" 
                                    class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition">
                                <i class="fas fa-ellipsis-v text-gray-600"></i>
                            </button>
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder text-5xl text-blue-500 mb-3"></i>
                                <p class="font-medium text-gray-800 text-center text-sm">Patents</p>
                                <p class="text-xs text-gray-500 mt-1"><?= $stats['patent'] ?? 0 ?> items</p>
                            </div>
                        </div>

                        <!-- Trademark Folder -->
                        <div onclick="openFolder('trademark')" 
                             class="folder-card group bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative">
                            <button onclick="event.stopPropagation(); showFolderMenu(event, 'trademark')" 
                                    class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition">
                                <i class="fas fa-ellipsis-v text-gray-600"></i>
                            </button>
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder text-5xl text-green-500 mb-3"></i>
                                <p class="font-medium text-gray-800 text-center text-sm">Trademarks</p>
                                <p class="text-xs text-gray-500 mt-1"><?= $stats['trademark'] ?? 0 ?> items</p>
                            </div>
                        </div>

                        <!-- Copyright Folder -->
                        <div onclick="openFolder('copyright')" 
                             class="folder-card group bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative">
                            <button onclick="event.stopPropagation(); showFolderMenu(event, 'copyright')" 
                                    class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition">
                                <i class="fas fa-ellipsis-v text-gray-600"></i>
                            </button>
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder text-5xl text-purple-500 mb-3"></i>
                                <p class="font-medium text-gray-800 text-center text-sm">Copyrights</p>
                                <p class="text-xs text-gray-500 mt-1"><?= $stats['copyright'] ?? 0 ?> items</p>
                            </div>
                        </div>

                        <!-- Industrial Design Folder -->
                        <div onclick="openFolder('industrial-design')" 
                             class="folder-card group bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative">
                            <button onclick="event.stopPropagation(); showFolderMenu(event, 'design')" 
                                    class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition">
                                <i class="fas fa-ellipsis-v text-gray-600"></i>
                            </button>
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder text-5xl text-orange-500 mb-3"></i>
                                <p class="font-medium text-gray-800 text-center text-sm">Industrial Designs</p>
                                <p class="text-xs text-gray-500 mt-1"><?= $stats['industrial_design'] ?? 0 ?> items</p>
                            </div>
                        </div>

                        <!-- Shared with Me Folder -->
                        <div onclick="openFolder('shared')" 
                             class="folder-card group bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative">
                            <button onclick="event.stopPropagation(); showFolderMenu(event, 'shared')" 
                                    class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition">
                                <i class="fas fa-ellipsis-v text-gray-600"></i>
                            </button>
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder-open text-5xl text-indigo-500 mb-3"></i>
                                <p class="font-medium text-gray-800 text-center text-sm">Shared with Me</p>
                                <p class="text-xs text-gray-500 mt-1">12 items</p>
                            </div>
                        </div>

                        <!-- Recent Folder -->
                        <div onclick="openFolder('recent')" 
                             class="folder-card group bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative">
                            <button onclick="event.stopPropagation(); showFolderMenu(event, 'recent')" 
                                    class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition">
                                <i class="fas fa-ellipsis-v text-gray-600"></i>
                            </button>
                            <div class="flex flex-col items-center">
                                <i class="fas fa-clock text-5xl text-gray-500 mb-3"></i>
                                <p class="font-medium text-gray-800 text-center text-sm">Recent</p>
                                <p class="text-xs text-gray-500 mt-1">25 items</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Files Section -->
                <div id="filesSection">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Files</h3>
                    
                    <!-- Grid View -->
                    <div id="gridView" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                        <?php if (isset($records) && count($records) > 0): ?>
                            <?php foreach ($records as $record): ?>
                                <div class="file-card group bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:shadow-md cursor-pointer transition relative record-card"
                                     data-type="<?= $record['type'] ?>"
                                     data-status="<?= $record['status'] ?>"
                                     data-title="<?= strtolower($record['title']) ?>"
                                     onclick="viewRecord(<?= $record['id'] ?>)">
                                    <button onclick="event.stopPropagation(); showFileMenu(event, <?= $record['id'] ?>)" 
                                            class="absolute top-2 right-2 p-1 opacity-0 group-hover:opacity-100 hover:bg-gray-200 rounded transition z-10">
                                        <i class="fas fa-ellipsis-v text-gray-600"></i>
                                    </button>
                                    
                                    <div class="p-4">
                                        <!-- File Icon/Thumbnail -->
                                        <div class="flex items-center justify-center h-24 mb-3">
                                            <div class="relative">
                                                <i class="fas fa-file-alt text-6xl <?= getFileIconColor($record['type']) ?>"></i>
                                                <div class="absolute -bottom-1 -right-1 w-6 h-6 <?= getTypeBadgeColor($record['type']) ?> rounded-full flex items-center justify-center">
                                                    <i class="fas fa-<?= getTypeIcon($record['type']) ?> text-white text-xs"></i>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- File Info -->
                                        <div class="text-center">
                                            <p class="font-medium text-gray-800 text-sm line-clamp-2 mb-1 group-hover:text-indigo-600">
                                                <?= htmlspecialchars($record['title']) ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?= date('M j, Y', strtotime($record['filing_date'])) ?>
                                            </p>
                                            <div class="flex items-center justify-center mt-2 space-x-1">
                                                <span class="px-2 py-0.5 rounded text-xs <?= getStatusBadge($record['status']) ?>">
                                                    <?= htmlspecialchars($record['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full bg-white rounded-lg border-2 border-dashed border-gray-300 p-12 text-center">
                                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">This folder is empty</h3>
                                <p class="text-gray-600 text-sm">No IP records found in this location</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- List View -->
                <div id="listView" class="bg-white rounded-lg border border-gray-200" style="display: none;">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" class="rounded">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modified</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (isset($records) && count($records) > 0): ?>
                                <?php foreach ($records as $record): ?>
                                    <tr class="hover:bg-gray-50 cursor-pointer record-card group"
                                        data-type="<?= $record['type'] ?>"
                                        data-status="<?= $record['status'] ?>"
                                        data-title="<?= strtolower($record['title']) ?>"
                                        onclick="viewRecord(<?= $record['id'] ?>)">
                                        <td class="px-6 py-4 whitespace-nowrap" onclick="event.stopPropagation()">
                                            <input type="checkbox" class="rounded">
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 relative">
                                                    <i class="fas fa-file-alt text-3xl <?= getFileIconColor($record['type']) ?>"></i>
                                                    <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 <?= getTypeBadgeColor($record['type']) ?> rounded-full flex items-center justify-center">
                                                        <i class="fas fa-<?= getTypeIcon($record['type']) ?> text-white text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-600">
                                                        <?= htmlspecialchars($record['title']) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?= htmlspecialchars($record['registration_number']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900"><?= htmlspecialchars($record['owner_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500"><?= date('M j, Y', strtotime($record['filing_date'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium <?= getStatusBadge($record['status']) ?>">
                                                <?= htmlspecialchars($record['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium" onclick="event.stopPropagation()">
                                            <button onclick="requestDownload(<?= $record['id'] ?>)" 
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3" title="Download">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <button onclick="showFileMenu(event, <?= $record['id'] ?>)" 
                                                    class="text-gray-400 hover:text-gray-600" title="More">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                                        <h3 class="text-lg font-semibold text-gray-800 mb-2">This folder is empty</h3>
                                        <p class="text-gray-600 text-sm">No IP records found in this location</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/common.js?v=<?= filemtime(PUBLIC_PATH . '/js/common.js') ?>"></script>
    <script src="<?= BASE_URL ?>/js/utils.js"></script>
    <script>
        let currentView = localStorage.getItem('ipRecordsView') || 'grid';
        let currentFolder = 'root';
        
        function toggleView(view) {
            currentView = view;
            localStorage.setItem('ipRecordsView', view);
            
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.classList.remove('bg-gray-300');
            });
            
            if (view === 'grid') {
                document.getElementById('gridView').style.display = 'grid';
                document.getElementById('listView').style.display = 'none';
                document.getElementById('foldersSection').style.display = 'block';
                document.getElementById('gridViewBtn').classList.add('bg-gray-300');
            } else {
                document.getElementById('gridView').style.display = 'none';
                document.getElementById('listView').style.display = 'block';
                document.getElementById('foldersSection').style.display = 'none';
                document.getElementById('listViewBtn').classList.add('bg-gray-300');
            }
        }

        function navigateToFolder(folder) {
            if (folder === 'root') {
                document.getElementById('currentFolder').textContent = 'All IP Records';
                document.getElementById('foldersSection').style.display = 'block';
            }
            currentFolder = folder;
        }

        function openFolder(folderType) {
            const folderNames = {
                'patent': 'Patents',
                'trademark': 'Trademarks',
                'copyright': 'Copyrights',
                'industrial-design': 'Industrial Designs',
                'shared': 'Shared with Me',
                'recent': 'Recent'
            };
            
            document.getElementById('currentFolder').textContent = folderNames[folderType];
            document.getElementById('foldersSection').style.display = 'none';
            
            // Update breadcrumb
            const nav = document.querySelector('nav');
            const folderSpan = document.getElementById('currentFolder');
            
            // Filter records by type
            if (folderType !== 'shared' && folderType !== 'recent') {
                const typeMap = {
                    'patent': 'Patent',
                    'trademark': 'Trademark',
                    'copyright': 'Copyright',
                    'industrial-design': 'Industrial Design'
                };
                
                document.querySelectorAll('.record-card').forEach(card => {
                    if (card.dataset.type === typeMap[folderType]) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
            
            showToast(`Opened ${folderNames[folderType]} folder`, 'info');
        }

        function showFolderMenu(event, folderId) {
            event.stopPropagation();
            const menu = `
                <div class="text-left">
                    <button onclick="Swal.close()" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-folder-open w-6 text-gray-600"></i>
                        <span>Open</span>
                    </button>
                    <button onclick="Swal.close()" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-share-alt w-6 text-gray-600"></i>
                        <span>Share</span>
                    </button>
                    <button onclick="Swal.close()" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-info-circle w-6 text-gray-600"></i>
                        <span>Details</span>
                    </button>
                </div>
            `;
            
            Swal.fire({
                html: menu,
                showConfirmButton: false,
                width: 250,
                position: 'center',
                backdrop: true,
                customClass: {
                    popup: 'rounded-lg shadow-xl p-2'
                }
            });
        }

        function showFileMenu(event, fileId) {
            event.stopPropagation();
            const menu = `
                <div class="text-left">
                    <button onclick="viewRecord(${fileId}); Swal.close();" class="w-full text-left px-4 py-3 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-eye w-6 text-gray-600"></i>
                        <span>View Details</span>
                    </button>
                    <button onclick="requestDownload(${fileId}); Swal.close();" class="w-full text-left px-4 py-3 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-download w-6 text-gray-600"></i>
                        <span>Request Download</span>
                    </button>
                    <hr class="my-1">
                    <button onclick="shareFile(${fileId}); Swal.close();" class="w-full text-left px-4 py-3 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-share-alt w-6 text-gray-600"></i>
                        <span>Share</span>
                    </button>
                    <button onclick="copyLink(${fileId}); Swal.close();" class="w-full text-left px-4 py-3 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-link w-6 text-gray-600"></i>
                        <span>Copy Link</span>
                    </button>
                    <button onclick="showFileInfo(${fileId}); Swal.close();" class="w-full text-left px-4 py-3 hover:bg-gray-100 rounded flex items-center">
                        <i class="fas fa-info-circle w-6 text-gray-600"></i>
                        <span>File Information</span>
                    </button>
                </div>
            `;
            
            Swal.fire({
                html: menu,
                showConfirmButton: false,
                width: 280,
                position: 'center',
                backdrop: true,
                customClass: {
                    popup: 'rounded-lg shadow-xl p-2'
                }
            });
        }

        function showSortMenu() {
            const menu = `
                <div class="text-left">
                    <button onclick="sortBy('name'); Swal.close();" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded">Name</button>
                    <button onclick="sortBy('modified'); Swal.close();" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded">Last modified</button>
                    <button onclick="sortBy('owner'); Swal.close();" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded">Owner</button>
                    <button onclick="sortBy('type'); Swal.close();" class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded">Type</button>
                </div>
            `;
            
            Swal.fire({
                title: 'Sort by',
                html: menu,
                showConfirmButton: false,
                width: 250,
                customClass: {
                    popup: 'rounded-lg shadow-xl'
                }
            });
        }

        function showFilterMenu() {
            Swal.fire({
                title: 'Filter files',
                html: `
                    <div class="text-left space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Type</label>
                            <select id="filterType" class="w-full px-3 py-2 border rounded-lg">
                                <option value="">All Types</option>
                                <option value="Patent">Patent</option>
                                <option value="Trademark">Trademark</option>
                                <option value="Copyright">Copyright</option>
                                <option value="Industrial Design">Industrial Design</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select id="filterStatus" class="w-full px-3 py-2 border rounded-lg">
                                <option value="">All Status</option>
                                <option value="Active">Active</option>
                                <option value="Pending">Pending</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Apply',
                confirmButtonColor: '#6366f1',
                width: 400,
                preConfirm: () => {
                    return {
                        type: document.getElementById('filterType').value,
                        status: document.getElementById('filterStatus').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    applyFilters(result.value);
                }
            });
        }

        function sortBy(criteria) {
            showToast(`Sorted by ${criteria}`, 'info');
        }

        function applyFilters(filters) {
            document.querySelectorAll('.record-card').forEach(card => {
                const matchesType = !filters.type || card.dataset.type === filters.type;
                const matchesStatus = !filters.status || card.dataset.status === filters.status;
                
                if (matchesType && matchesStatus) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
            showToast('Filters applied', 'success');
        }

        function filterRecords() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            
            document.querySelectorAll('.record-card').forEach(card => {
                const cardTitle = card.dataset.title;
                
                if (cardTitle.includes(search)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function viewRecord(id) {
            window.location.href = `<?= BASE_URL ?>/staff/ip-records/view/${id}`;
        }

        function requestDownload(recordId) {
            Swal.fire({
                title: 'Request Document Download',
                html: `
                    <div class="text-left">
                        <label class="block text-sm font-medium mb-2">Reason for download request *</label>
                        <textarea id="downloadReason" class="swal2-textarea w-full" rows="4" 
                                  placeholder="Please provide a reason for this download request..." required></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Submit Request',
                confirmButtonColor: '#059669',
                width: 600,
                preConfirm: () => {
                    const reason = document.getElementById('downloadReason').value;
                    if (!reason) {
                        Swal.showValidationMessage('Please provide a reason');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Submitting request...');
                    ajaxRequest('<?= BASE_URL ?>/staff/request-download', {
                        ip_record_id: recordId,
                        reason: result.value
                    }, 'POST').then((res) => {
                        hideLoading();
                        if (res && res.success) {
                            showToast(res.message || 'Download request submitted successfully', 'success');
                        } else {
                            showToast(res?.message || 'Failed to submit request', 'error');
                        }
                    }).catch(() => {
                        hideLoading();
                        showToast('Failed to submit request', 'error');
                    });
                }
            });
        }

        function shareFile(id) {
            Swal.fire({
                title: 'Share File',
                html: `
                    <div class="text-left">
                        <label class="block text-sm font-medium mb-2">Share with</label>
                        <input type="email" class="swal2-input w-full" placeholder="Enter email address">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Share',
                confirmButtonColor: '#6366f1'
            }).then((result) => {
                if (result.isConfirmed) {
                    showToast('File shared successfully', 'success');
                }
            });
        }

        function copyLink(id) {
            const link = `<?= BASE_URL ?>/staff/ip-records/view/${id}`;
            navigator.clipboard.writeText(link).then(() => {
                showToast('Link copied to clipboard', 'success');
            });
        }

        function showFileInfo(id) {
            Swal.fire({
                title: 'File Information',
                html: `
                    <div class="text-left space-y-2 text-sm">
                        <p><strong>Type:</strong> Patent</p>
                        <p><strong>Size:</strong> 2.4 MB</p>
                        <p><strong>Created:</strong> Jan 15, 2024</p>
                        <p><strong>Modified:</strong> Jan 20, 2024</p>
                        <p><strong>Owner:</strong> Tech Innovations Inc.</p>
                    </div>
                `,
                confirmButtonColor: '#059669'
            });
        }

        // Toggle New Menu
        function toggleNewMenu(event) {
            if (event) {
                event.stopPropagation();
            }
            const menu = document.getElementById('newMenu');
            const btn = document.getElementById('newMenuBtn');
            if (!menu || !btn) return;

            if (menu.classList.contains('hidden')) {
                const rect = btn.getBoundingClientRect();
                menu.style.top = (rect.bottom + 8) + 'px';
                menu.style.left = rect.left + 'px';

                // Adjust if offscreen
                if (window.innerWidth < rect.left + 224) {
                    menu.style.left = 'auto';
                    menu.style.right = (window.innerWidth - rect.right) + 'px';
                } else {
                    menu.style.right = 'auto';
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
            const toggleFn = button ? (button.getAttribute('onclick') || '') : '';
            if (toggleFn && toggleFn.includes('toggleNewMenu')) return;
            if (menu && !menu.contains(event.target) && !menu.classList.contains('hidden')) {
                menu.classList.add('hidden');
            }
        });

        // Create Folder Modal
        function showCreateFolderModal() {
            document.getElementById('newMenu').classList.add('hidden');
            Swal.fire({
                title: 'New folder',
                html: `
                    <div class="text-left">
                        <input type="text" id="folderName" class="swal2-input w-full" 
                               placeholder="Untitled folder" autofocus>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Create',
                confirmButtonColor: '#059669',
                cancelButtonText: 'Cancel',
                width: 450,
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
            showLoading('Creating folder...');
            
            // Simulate API call
            setTimeout(() => {
                hideLoading();
                showToast(`Folder "${folderName}" created successfully`, 'success');
                
                // Here you would make an AJAX call to create the folder
                // ajaxRequest('<?= BASE_URL ?>/staff/folders/create', {
                //     name: folderName,
                //     parent: currentFolder
                // }, 'POST');
            }, 1000);
        }

        // Upload Modal
        function showUploadModal() {
            document.getElementById('newMenu').classList.add('hidden');
            Swal.fire({
                title: 'Upload files',
                html: `
                    <div class="text-left">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-emerald-400 transition cursor-pointer" onclick="document.getElementById('fileUpload').click()">
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
                confirmButtonColor: '#059669',
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
            const dropZone = document.querySelector('.border-dashed');
            if (dropZone) {
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.classList.add('border-emerald-500', 'bg-emerald-50');
                });
                
                dropZone.addEventListener('dragleave', () => {
                    dropZone.classList.remove('border-emerald-500', 'bg-emerald-50');
                });
                
                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('border-emerald-500', 'bg-emerald-50');
                    const files = e.dataTransfer.files;
                    document.getElementById('fileUpload').files = files;
                    handleFileSelect({ target: { files } });
                });
            }
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
            showLoading('Uploading files...');
            
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
                    showLoading(`Uploading... ${progress}%`);
                }
                if (progress >= 100) {
                    clearInterval(interval);
                    setTimeout(() => {
                        hideLoading();
                        showToast(`${files.length} file(s) uploaded successfully`, 'success');
                        // Refresh the view
                        // location.reload();
                    }, 500);
                }
            }, 200);
            
            // Here you would make an actual AJAX call
            // ajaxRequest('<?= BASE_URL ?>/staff/files/upload', formData, 'POST');
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
                confirmButtonColor: '#6366f1'
            }).then((result) => {
                if (result.isConfirmed) {
                    uploadFiles(files);
                }
            });
        }

        // Initialize view on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleView(currentView);
        });
    </script>
</body>
</html>

<?php
function getGradientColor($type) {
    $gradients = [
        'Patent' => 'bg-gradient-to-r from-blue-500 to-blue-600',
        'Trademark' => 'bg-gradient-to-r from-green-500 to-green-600',
        'Copyright' => 'bg-gradient-to-r from-purple-500 to-purple-600',
        'Industrial Design' => 'bg-gradient-to-r from-orange-500 to-orange-600'
    ];
    return $gradients[$type] ?? 'bg-gradient-to-r from-gray-500 to-gray-600';
}

function getTypeBadge($type) {
    $badges = [
        'Patent' => 'bg-blue-100 text-blue-700',
        'Trademark' => 'bg-green-100 text-green-700',
        'Copyright' => 'bg-purple-100 text-purple-700',
        'Industrial Design' => 'bg-orange-100 text-orange-700'
    ];
    return $badges[$type] ?? 'bg-gray-100 text-gray-700';
}

function getStatusBadge($status) {
    $badges = [
        'Active' => 'bg-green-100 text-green-700',
        'Pending' => 'bg-yellow-100 text-yellow-700',
        'Expired' => 'bg-red-100 text-red-700'
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-700';
}

function getFileIconColor($type) {
    $colors = [
        'Patent' => 'text-blue-500',
        'Trademark' => 'text-green-500',
        'Copyright' => 'text-purple-500',
        'Industrial Design' => 'text-orange-500'
    ];
    return $colors[$type] ?? 'text-gray-500';
}

function getTypeBadgeColor($type) {
    $colors = [
        'Patent' => 'bg-blue-500',
        'Trademark' => 'bg-green-500',
        'Copyright' => 'bg-purple-500',
        'Industrial Design' => 'bg-orange-500'
    ];
    return $colors[$type] ?? 'bg-gray-500';
}

function getTypeIcon($type) {
    $icons = [
        'Patent' => 'lightbulb',
        'Trademark' => 'trademark',
        'Copyright' => 'copyright',
        'Industrial Design' => 'palette'
    ];
    return $icons[$type] ?? 'file';
}
?>
