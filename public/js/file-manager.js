/**
 * Enhanced File Management System JavaScript
 * Handles folder navigation, file uploads, and modern UI interactions
 */

// Global state management
const FileManager = {
    currentFolderId: null,
    currentView: 'grid',
    selectedItems: new Set(),
    isSelectMode: false,
    currentFiles: [],
    currentFolders: [],
    
    // Configuration
    config: {
        itemsPerPage: 20,
        maxUploadSize: 50 * 1024 * 1024, // 50MB
        allowedFileTypes: ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'mp3', 'wav', 'mp4', 'avi', 'mov', 'zip', 'rar', '7z'],
        thumbnailSize: 150,
        apiBaseUrl: '/document/' // Adjust based on your routing
    },

    // Initialize the file manager
    init() {
        this.setupEventListeners();
        this.loadCurrentFolder();
        this.initializeDropZone();
        this.setupKeyboardShortcuts();
    },

    // Setup event listeners
    setupEventListeners() {
        // View mode toggles
        document.addEventListener('click', (e) => {
            if (e.target.id === 'gridModeBtn') this.setViewMode('grid');
            if (e.target.id === 'listModeBtn') this.setViewMode('list');
        });

        // File input handlers
        const fileUpload = document.getElementById('fileUpload');
        if (fileUpload) {
            fileUpload.addEventListener('change', (e) => this.handleFileUpload(e));
        }

        const folderUpload = document.getElementById('folderUpload');
        if (folderUpload) {
            folderUpload.addEventListener('change', (e) => this.handleFolderUpload(e));
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.searchFiles(e.target.value);
            }, 300));
        }
    },

    // Setup keyboard shortcuts
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+A - Select all
            if (e.ctrlKey && e.key === 'a' && !e.target.matches('input, textarea')) {
                e.preventDefault();
                this.selectAllItems();
            }
            // Delete key - Delete selected items
            if (e.key === 'Delete' && this.selectedItems.size > 0) {
                e.preventDefault();
                this.bulkDelete();
            }
            // Escape - Clear selection
            if (e.key === 'Escape') {
                this.clearSelection();
            }
        });
    },

    // Initialize drag and drop functionality
    initializeDropZone() {
        const fileManagementSection = document.getElementById('fileManagementSection');
        if (!fileManagementSection) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileManagementSection.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            fileManagementSection.addEventListener(eventName, () => {
                fileManagementSection.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileManagementSection.addEventListener(eventName, () => {
                fileManagementSection.classList.remove('drag-over');
            }, false);
        });

        fileManagementSection.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFileDrop(files);
        }, false);
    },

    // Prevent default drag behaviors
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    },

    // Handle drag and drop file upload
    handleFileDrop(files) {
        if (files.length > 0) {
            this.uploadFiles(files);
        }
    },

    // Load current folder contents
    async loadCurrentFolder(folderId = null) {
        this.currentFolderId = folderId;
        this.showLoading(true);
        
        try {
            const response = await fetch(`${this.config.apiBaseUrl}listFiles?folder_id=${folderId || ''}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentFiles = data.files || [];
                this.currentFolders = data.folders || [];
                
                this.updateBreadcrumbs(data.folder);
                this.updateFolderHeader(data.folder, data.stats);
                this.renderItems();
                this.updatePagination(data.pagination);
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Error loading folder:', error);
            this.showError('Failed to load folder contents');
        } finally {
            this.showLoading(false);
        }
    },

    // Update breadcrumb navigation
    updateBreadcrumbs(folder) {
        const breadcrumbPath = document.getElementById('breadcrumbPath');
        if (!breadcrumbPath) return;

        breadcrumbPath.innerHTML = '';
        
        if (folder && folder.breadcrumbs) {
            folder.breadcrumbs.forEach((crumb, index) => {
                if (index > 0) {
                    breadcrumbPath.innerHTML += '<i class="fas fa-chevron-right text-gray-400 text-xs"></i>';
                }
                breadcrumbPath.innerHTML += `
                    <button onclick="FileManager.navigateToFolder(${crumb.id})" 
                            class="text-gray-600 hover:text-blue-600 transition whitespace-nowrap">
                        ${this.escapeHtml(crumb.name)}
                    </button>
                `;
            });
        }
    },

    // Update folder header information
    updateFolderHeader(folder, stats) {
        const elements = {
            currentFolderName: document.getElementById('currentFolderName'),
            currentFolderDescription: document.getElementById('currentFolderDescription'),
            fileCount: document.getElementById('fileCount'),
            subfolderCount: document.getElementById('subfolderCount'),
            totalSize: document.getElementById('totalSize')
        };

        if (elements.currentFolderName) {
            elements.currentFolderName.textContent = folder ? folder.name : 'All Files';
        }
        
        if (elements.currentFolderDescription && folder) {
            elements.currentFolderDescription.textContent = folder.description || '';
        }

        if (stats) {
            if (elements.fileCount) elements.fileCount.textContent = stats.total_files || 0;
            if (elements.subfolderCount) elements.subfolderCount.textContent = stats.subfolder_count || 0;
            if (elements.totalSize) elements.totalSize.textContent = this.formatFileSize(stats.total_size || 0);
        }
    },

    // Render folders and files
    renderItems() {
        if (this.currentView === 'grid') {
            this.renderGridView();
        } else {
            this.renderListView();
        }

        // Show/hide empty state
        const hasItems = this.currentFolders.length > 0 || this.currentFiles.length > 0;
        document.getElementById('emptyState').classList.toggle('hidden', hasItems);
        document.getElementById('gridViewContainer').classList.toggle('hidden', !hasItems || this.currentView !== 'grid');
        document.getElementById('listViewContainer').classList.toggle('hidden', !hasItems || this.currentView !== 'list');
    },

    // Render grid view
    renderGridView() {
        const grid = document.getElementById('itemsGrid');
        if (!grid) return;

        let html = '';
        
        // Render folders first
        this.currentFolders.forEach(folder => {
            const isSelected = this.selectedItems.has(`folder-${folder.id}`);
            html += `
                <div class="folder-item group relative bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md cursor-pointer transition ${isSelected ? 'ring-2 ring-blue-500 bg-blue-50' : ''}"
                     data-type="folder" data-id="${folder.id}"
                     onclick="FileManager.handleItemClick(event, 'folder', ${folder.id})"
                     ondblclick="FileManager.navigateToFolder(${folder.id})">
                    
                    ${this.isSelectMode ? `
                        <div class="absolute top-2 left-2 z-10">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   ${isSelected ? 'checked' : ''}
                                   onchange="FileManager.toggleItemSelection('folder-${folder.id}', this.checked)"
                                   onclick="event.stopPropagation()">
                        </div>
                    ` : ''}
                    
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                        <button onclick="FileManager.showItemMenu(event, 'folder', ${folder.id})" 
                                class="p-1 hover:bg-gray-100 rounded">
                            <i class="fas fa-ellipsis-v text-gray-400"></i>
                        </button>
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 flex items-center justify-center mb-3" style="color: ${folder.color || '#6B7280'}">
                            <i class="fas fa-folder text-4xl"></i>
                        </div>
                        <h4 class="font-medium text-sm text-gray-900 text-center truncate w-full" title="${this.escapeHtml(folder.name)}">
                            ${this.escapeHtml(folder.name)}
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">${folder.file_count || 0} items</p>
                    </div>
                </div>
            `;
        });

        // Render files
        this.currentFiles.forEach(file => {
            const isSelected = this.selectedItems.has(`file-${file.id}`);
            html += `
                <div class="file-item group relative bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md cursor-pointer transition ${isSelected ? 'ring-2 ring-blue-500 bg-blue-50' : ''}"
                     data-type="file" data-id="${file.id}"
                     onclick="FileManager.handleItemClick(event, 'file', ${file.id})"
                     ondblclick="FileManager.openFile(${file.id})">
                    
                    ${this.isSelectMode ? `
                        <div class="absolute top-2 left-2 z-10">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   ${isSelected ? 'checked' : ''}
                                   onchange="FileManager.toggleItemSelection('file-${file.id}', this.checked)"
                                   onclick="event.stopPropagation()">
                        </div>
                    ` : ''}
                    
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                        <button onclick="FileManager.showItemMenu(event, 'file', ${file.id})" 
                                class="p-1 hover:bg-gray-100 rounded">
                            <i class="fas fa-ellipsis-v text-gray-400"></i>
                        </button>
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <div class="w-16 h-16 flex items-center justify-center mb-3">
                            ${this.renderFileIcon(file)}
                        </div>
                        <h4 class="font-medium text-sm text-gray-900 text-center truncate w-full" title="${this.escapeHtml(file.original_name)}">
                            ${this.escapeHtml(file.original_name)}
                        </h4>
                        <p class="text-xs text-gray-500 mt-1">${this.formatFileSize(file.file_size)}</p>
                    </div>
                </div>
            `;
        });

        grid.innerHTML = html;
    },

    // Render list view
    renderListView() {
        const list = document.getElementById('itemsList');
        if (!list) return;

        let html = '';

        // Render folders first
        this.currentFolders.forEach(folder => {
            const isSelected = this.selectedItems.has(`folder-${folder.id}`);
            html += `
                <tr class="hover:bg-gray-50 cursor-pointer ${isSelected ? 'bg-blue-50' : ''}"
                    data-type="folder" data-id="${folder.id}"
                    onclick="FileManager.handleItemClick(event, 'folder', ${folder.id})"
                    ondblclick="FileManager.navigateToFolder(${folder.id})">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               ${isSelected ? 'checked' : ''}
                               onchange="FileManager.toggleItemSelection('folder-${folder.id}', this.checked)"
                               onclick="event.stopPropagation()">
                    </td>
                    <td class="px-6 py-4 flex items-center">
                        <i class="fas fa-folder text-xl mr-3" style="color: ${folder.color || '#6B7280'}"></i>
                        <span class="font-medium text-gray-900">${this.escapeHtml(folder.name)}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">Folder</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${folder.file_count || 0} items</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${this.formatDate(folder.created_at)}</td>
                    <td class="px-6 py-4">
                        <button onclick="FileManager.showItemMenu(event, 'folder', ${folder.id})" 
                                class="p-1 hover:bg-gray-100 rounded">
                            <i class="fas fa-ellipsis-v text-gray-400"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        // Render files
        this.currentFiles.forEach(file => {
            const isSelected = this.selectedItems.has(`file-${file.id}`);
            html += `
                <tr class="hover:bg-gray-50 cursor-pointer ${isSelected ? 'bg-blue-50' : ''}"
                    data-type="file" data-id="${file.id}"
                    onclick="FileManager.handleItemClick(event, 'file', ${file.id})"
                    ondblclick="FileManager.openFile(${file.id})">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                               ${isSelected ? 'checked' : ''}
                               onchange="FileManager.toggleItemSelection('file-${file.id}', this.checked)"
                               onclick="event.stopPropagation()">
                    </td>
                    <td class="px-6 py-4 flex items-center">
                        ${this.renderFileIcon(file, 'small')}
                        <span class="font-medium text-gray-900 ml-3">${this.escapeHtml(file.original_name)}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">${file.file_type.toUpperCase()}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${this.formatFileSize(file.file_size)}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${this.formatDate(file.created_at)}</td>
                    <td class="px-6 py-4">
                        <button onclick="FileManager.showItemMenu(event, 'file', ${file.id})" 
                                class="p-1 hover:bg-gray-100 rounded">
                            <i class="fas fa-ellipsis-v text-gray-400"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        list.innerHTML = html;
    },

    // Render file icon based on file type
    renderFileIcon(file, size = 'normal') {
        const iconClass = size === 'small' ? 'text-xl' : 'text-4xl';
        
        if (file.is_image && file.thumbnail_path) {
            return `<img src="${this.config.apiBaseUrl}thumbnail?id=${file.id}" 
                         alt="${this.escapeHtml(file.original_name)}" 
                         class="w-full h-full object-cover rounded-lg">`;
        }

        const iconMap = {
            pdf: 'fa-file-pdf text-red-500',
            doc: 'fa-file-word text-blue-500',
            docx: 'fa-file-word text-blue-500',
            xls: 'fa-file-excel text-green-500',
            xlsx: 'fa-file-excel text-green-500',
            ppt: 'fa-file-powerpoint text-orange-500',
            pptx: 'fa-file-powerpoint text-orange-500',
            txt: 'fa-file-alt text-gray-500',
            jpg: 'fa-file-image text-purple-500',
            jpeg: 'fa-file-image text-purple-500',
            png: 'fa-file-image text-purple-500',
            gif: 'fa-file-image text-purple-500',
            mp3: 'fa-file-audio text-yellow-500',
            wav: 'fa-file-audio text-yellow-500',
            mp4: 'fa-file-video text-red-500',
            avi: 'fa-file-video text-red-500',
            zip: 'fa-file-archive text-gray-600',
            rar: 'fa-file-archive text-gray-600'
        };

        const iconClass2 = iconMap[file.file_type] || 'fa-file text-gray-500';
        return `<i class="fas ${iconClass2} ${iconClass}"></i>`;
    },

    // Handle item click (for selection)
    handleItemClick(event, type, id) {
        if (this.isSelectMode) {
            const itemKey = `${type}-${id}`;
            const isSelected = this.selectedItems.has(itemKey);
            this.toggleItemSelection(itemKey, !isSelected);
        }
    },

    // Set view mode (grid/list)
    setViewMode(mode) {
        this.currentView = mode;
        
        // Update button states
        document.getElementById('gridModeBtn').classList.toggle('bg-blue-500', mode === 'grid');
        document.getElementById('gridModeBtn').classList.toggle('text-white', mode === 'grid');
        document.getElementById('listModeBtn').classList.toggle('bg-blue-500', mode === 'list');
        document.getElementById('listModeBtn').classList.toggle('text-white', mode === 'list');
        
        this.renderItems();
    },

    // Upload files
    async uploadFiles(files) {
        const formData = new FormData();
        
        // Add files to form data
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
        
        if (this.currentFolderId) {
            formData.append('folder_id', this.currentFolderId);
        }

        try {
            this.showUploadProgress(true);
            
            const response = await fetch(`${this.config.apiBaseUrl}uploadToFolder`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`Successfully uploaded ${data.stats.success} files`);
                this.loadCurrentFolder(this.currentFolderId); // Refresh
            } else {
                this.showError(data.message);
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showError('Upload failed');
        } finally {
            this.showUploadProgress(false);
        }
    },

    // Navigation methods
    navigateToFolder(folderId) {
        this.clearSelection();
        this.loadCurrentFolder(folderId);
    },

    openFile(fileId) {
        window.open(`${this.config.apiBaseUrl}download?id=${fileId}`, '_blank');
    },

    // Selection methods
    toggleItemSelection(itemKey, isSelected) {
        if (isSelected) {
            this.selectedItems.add(itemKey);
        } else {
            this.selectedItems.delete(itemKey);
        }
        this.updateSelectionUI();
    },

    selectAllItems() {
        this.currentFolders.forEach(folder => {
            this.selectedItems.add(`folder-${folder.id}`);
        });
        this.currentFiles.forEach(file => {
            this.selectedItems.add(`file-${file.id}`);
        });
        this.updateSelectionUI();
        this.renderItems();
    },

    clearSelection() {
        this.selectedItems.clear();
        this.updateSelectionUI();
        this.renderItems();
    },

    toggleSelectionMode() {
        this.isSelectMode = !this.isSelectMode;
        document.getElementById('selectModeBtn').classList.toggle('bg-blue-500', this.isSelectMode);
        document.getElementById('selectModeBtn').classList.toggle('text-white', this.isSelectMode);
        
        if (!this.isSelectMode) {
            this.clearSelection();
        }
        
        this.renderItems();
    },

    updateSelectionUI() {
        const hasSelection = this.selectedItems.size > 0;
        document.getElementById('bulkDownloadBtn').classList.toggle('hidden', !hasSelection);
        document.getElementById('bulkDeleteBtn').classList.toggle('hidden', !hasSelection);
    },

    // Utility methods
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // UI state methods
    showLoading(show) {
        document.getElementById('loadingState').classList.toggle('hidden', !show);
    },

    showUploadProgress(show) {
        // Implement upload progress UI
    },

    showSuccess(message) {
        // Implement success notification
        console.log('Success:', message);
    },

    showError(message) {
        // Implement error notification
        console.error('Error:', message);
    }
};

// Global functions for backward compatibility
function navigateToFolder(folderId) {
    FileManager.navigateToFolder(folderId);
}

function toggleNewMenu(event) {
    event.stopPropagation();
    const menu = document.getElementById('newMenu');
    menu.classList.toggle('hidden');
}

function showCreateFolderModal() {
    // Implement folder creation modal
}

function showUploadModal() {
    document.getElementById('fileUpload').click();
}

function handleFileUpload(event) {
    FileManager.uploadFiles(event.target.files);
}

function handleFolderUpload(event) {
    FileManager.uploadFiles(event.target.files);
}

function refreshView() {
    FileManager.loadCurrentFolder(FileManager.currentFolderId);
}

function toggleSelectionMode() {
    FileManager.toggleSelectionMode();
}

function selectAllItems() {
    FileManager.selectAllItems();
}

function bulkDownload() {
    // Implement bulk download
}

function bulkDelete() {
    // Implement bulk delete
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    FileManager.init();
});