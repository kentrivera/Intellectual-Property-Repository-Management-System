<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Folder Repository</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-size: 13px;
        }
        
        .folder-tree-item {
            position: relative;
        }
        
        .tree-chevron {
            transition: transform 0.2s;
        }
        
        .tree-children {
            transition: all 0.3s ease;
        }
        
        #folderTree::-webkit-scrollbar {
            width: 4px;
        }
        
        #folderTree::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #folderTree::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }
        
        #folderTree::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Responsive utilities */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Custom scrollbar for panels */
        #foldersList::-webkit-scrollbar,
        #filesList::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        
        #foldersList::-webkit-scrollbar-track,
        #filesList::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #foldersList::-webkit-scrollbar-thumb,
        #filesList::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 2px;
        }
        
        #foldersList::-webkit-scrollbar-thumb:hover,
        #filesList::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Google Drive-like hover effects */
        .drive-item:hover {
            background: #f0f7f4;
        }
        
        .drive-button {
            font-size: 13px;
            padding: 6px 16px;
            border-radius: 4px;
        }
        
        .drive-icon-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }
        
        .drive-icon-btn:hover {
            background: #e8f5e9;
        }
        
        /* Hide scrollbar on mobile for cleaner look */
        @media (max-width: 640px) {
            #breadcrumb::-webkit-scrollbar {
                display: none;
            }
            #breadcrumb {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        }
    </style>
</head>
<body class="bg-white min-h-screen">
    <!-- Header - Google Drive Style -->
    <header class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="px-3 sm:px-4 py-2">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                    <i class="fas fa-folder text-lg sm:text-xl text-green-600"></i>
                    <h1 class="text-sm sm:text-base font-medium text-gray-700 hidden sm:block">Folder Repository</h1>
                </div>
                
                <!-- Search Bar -->
                <div class="flex-1 max-w-2xl">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search files and folders..." 
                            class="w-full pl-9 pr-3 py-1.5 text-sm bg-gray-100 border-0 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-green-500 transition" 
                            onkeyup="handleSearch(event)">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                        <button onclick="clearSearch()" id="clearSearchBtn" class="hidden absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </div>
                
                <a href="info.php" class="drive-icon-btn text-gray-600 hover:text-green-600 transition flex-shrink-0" title="System Info">
                    <i class="fas fa-info-circle text-lg"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="px-3 sm:px-4 py-3 sm:py-4">

        <!-- Action Bar - Google Drive Style -->
        <div class="bg-white border-b border-gray-200 py-2 mb-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button onclick="goHome()" class="drive-icon-btn text-gray-600 hover:text-green-600" title="Go to Home">
                        <i class="fas fa-home text-base"></i>
                    </button>
                    <button onclick="refreshContent()" class="drive-icon-btn text-gray-600 hover:text-green-600" title="Refresh">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>
                </div>
                
                <span class="text-xs text-gray-500" id="itemCount">0 items</span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-0">
            <!-- Folders Panel - Google Drive Sidebar -->
            <div class="lg:col-span-1 border-r border-gray-200 bg-white">
                <div class="p-2 sm:p-3">
                    <div class="flex items-center space-x-2 px-2 py-1.5">
                        <i class="fas fa-folder text-sm text-gray-600"></i>
                        <h2 class="text-xs font-medium text-gray-700 uppercase tracking-wide">Folders</h2>
                    </div>
                    <div id="folderTree" class="mt-2 space-y-0.5 max-h-[calc(100vh-200px)] overflow-y-auto pr-1">
                        <!-- Folder tree will be loaded here -->
                    </div>
                </div>
            </div>

            <!-- Files Panel - Google Drive Main Area -->
            <div class="lg:col-span-3 bg-white">
                <div class="px-3 sm:px-4 py-2">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-3">
                        <div class="flex items-center gap-2">
                            <button onclick="goBack()" id="backButton" class="drive-icon-btn text-gray-600 hover:text-green-600" title="Go Back">
                                <i class="fas fa-arrow-left text-sm"></i>
                            </button>
                            <h2 class="text-base sm:text-lg font-medium text-gray-800 flex items-center">
                                <i class="fas fa-folder-open text-green-600 mr-1.5 text-sm"></i>
                                <span id="currentFolderName">Home</span>
                            </h2>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center gap-1">
                            <button onclick="showCreateFolderModal()" class="drive-button bg-green-600 text-white hover:bg-green-700 transition flex items-center gap-1.5" title="New Folder">
                                <i class="fas fa-folder-plus text-xs"></i>
                                <span class="hidden sm:inline">New Folder</span>
                            </button>
                            <button onclick="showUploadModal()" class="drive-button border border-green-600 text-green-600 hover:bg-green-50 transition flex items-center gap-1.5 ml-2" title="Upload">
                                <i class="fas fa-upload text-xs"></i>
                                <span class="hidden sm:inline">Upload</span>
                            </button>
                            <button onclick="toggleActionsMenu()" class="drive-icon-btn text-gray-600 hover:text-green-600 ml-1" title="More actions">
                                <i class="fas fa-ellipsis-v text-sm"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="actionsMenu" class="hidden absolute right-4 mt-32 w-44 bg-white rounded shadow-lg border border-gray-200 z-50 py-1 text-xs">
                                <button onclick="showCreateFolderModal(); toggleActionsMenu()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition flex items-center gap-2 text-gray-700">
                                    <i class="fas fa-folder-plus text-green-600 w-3"></i>
                                    <span>New Folder</span>
                                </button>
                                <button onclick="showUploadModal(); toggleActionsMenu()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition flex items-center gap-2 text-gray-700">
                                    <i class="fas fa-upload text-green-600 w-3"></i>
                                    <span>Upload File</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="pathDisplay" class="hidden flex items-center text-xs text-gray-500 bg-gray-50 px-2 py-1.5 rounded mb-3 overflow-x-auto">
                        <i class="fas fa-folder-open text-gray-400 mr-1.5 text-xs flex-shrink-0"></i>
                        <span class="font-mono" id="currentPath">/</span>
                    </div>
                    
                    <div id="filesList" class="space-y-1">
                        <!-- Folders and files will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal - Google Drive Style -->
    <div id="createFolderModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-3">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-800">New folder</h3>
            </div>
            <form onsubmit="createFolder(event)" class="p-5">
                <div class="mb-4">
                    <input type="text" id="folderName" required class="w-full px-3 py-2 text-sm border-b border-gray-300 focus:border-green-600 focus:outline-none" placeholder="Folder name">
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="hideCreateFolderModal()" class="drive-button text-gray-600 hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="drive-button bg-green-600 text-white hover:bg-green-700">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload File Modal - Google Drive Style -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-3">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-800">Upload file</h3>
            </div>
            <form onsubmit="uploadFile(event)" class="p-5">
                <div class="mb-4">
                    <label class="block text-xs text-gray-600 mb-2">Select file</label>
                    <input type="file" id="fileInput" required class="w-full text-sm px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-green-600">
                    <p class="text-xs text-gray-400 mt-1">Max file size: 50MB</p>
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-gray-600 mb-2">Description (Optional)</label>
                    <textarea id="fileDescription" rows="2" class="w-full text-sm px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-green-600" placeholder="Add description"></textarea>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="hideUploadModal()" class="drive-button text-gray-600 hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="drive-button bg-green-600 text-white hover:bg-green-700">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit File Modal - Google Drive Style -->
    <div id="editFileModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-3">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-800">Edit file</h3>
            </div>
            <form onsubmit="updateFile(event)" class="p-5">
                <input type="hidden" id="editFileId">
                <div class="mb-4">
                    <label class="block text-xs text-gray-600 mb-2">File name</label>
                    <input type="text" id="editFileName" required class="w-full text-sm px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-green-600">
                </div>
                <div class="mb-4">
                    <label class="block text-xs text-gray-600 mb-2">Description</label>
                    <textarea id="editFileDescription" rows="2" class="w-full text-sm px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-green-600"></textarea>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="hideEditFileModal()" class="drive-button text-gray-600 hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="drive-button bg-green-600 text-white hover:bg-green-700">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Folder Modal - Google Drive Style -->
    <div id="editFolderModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-3">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-base font-medium text-gray-800">Rename folder</h3>
            </div>
            <form onsubmit="updateFolder(event)" class="p-5">
                <input type="hidden" id="editFolderId">
                <div class="mb-4">
                    <input type="text" id="editFolderName" required class="w-full px-3 py-2 text-sm border-b border-gray-300 focus:border-green-600 focus:outline-none" placeholder="Folder name">
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" onclick="hideEditFolderModal()" class="drive-button text-gray-600 hover:bg-gray-100">
                        Cancel
                    </button>
                    <button type="submit" class="drive-button bg-green-600 text-white hover:bg-green-700">
                        Rename
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- File Preview Modal - Google Drive Style -->
    <div id="previewModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-3 sm:p-4">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200">
                <div>
                    <h3 class="text-base font-medium text-gray-800" id="previewFileName">File Preview</h3>
                    <p class="text-xs text-gray-500 mt-0.5" id="previewFileInfo"></p>
                </div>
                <button onclick="hidePreviewModal()" class="drive-icon-btn text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-4" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="flex justify-end gap-2 px-4 py-3 border-t border-gray-200 bg-gray-50">
                <button onclick="hidePreviewModal()" class="drive-button text-gray-600 hover:bg-gray-100">
                    Close
                </button>
                <button onclick="downloadFileFromPreview()" id="previewDownloadBtn" class="drive-button bg-green-600 text-white hover:bg-green-700 flex items-center gap-1.5">
                    <i class="fas fa-download text-xs"></i>
                    <span>Download</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay - Google Drive Style -->
    <div id="loadingOverlay" class="hidden fixed inset-0 bg-white bg-opacity-80 flex items-center justify-center z-50">
        <div class="flex items-center gap-3">
            <i class="fas fa-circle-notch fa-spin text-xl text-green-600"></i>
            <span class="text-sm text-gray-700">Loading...</span>
        </div>
    </div>

    <script src="app.js"></script>
</body>
</html>
