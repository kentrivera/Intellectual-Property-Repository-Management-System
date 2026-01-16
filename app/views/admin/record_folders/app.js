// Global state
let currentFolderId = 1;
let currentFolderName = 'Root';
let navigationHistory = [];
let allFolders = [];
let currentParentId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadContent(1);
});

// Load folder content
async function loadContent(folderId, addToHistory = true) {
    currentFolderId = folderId;
    
    // Add to navigation history
    if (addToHistory) {
        // Only add if it's different from the last item
        if (navigationHistory.length === 0 || navigationHistory[navigationHistory.length - 1] !== folderId) {
            navigationHistory.push(folderId);
        }
    }
    
    showLoading();
    
    try {
        await Promise.all([
            loadBreadcrumb(folderId),
            loadFiles(folderId),
            loadAllFoldersTree()
        ]);
        
        // Update back button state
        updateBackButton();
    } catch (error) {
        showNotification('Error loading content: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Load breadcrumb navigation
async function loadBreadcrumb(folderId) {
    try {
        const response = await fetch(`api_folders.php?action=get_breadcrumb&folder_id=${folderId}`);
        const breadcrumb = await response.json();
        
        if (breadcrumb.error) {
            throw new Error(breadcrumb.error);
        }
        
        const breadcrumbEl = document.getElementById('breadcrumb');
        breadcrumbEl.innerHTML = '<i class="fas fa-home text-gray-500"></i>';
        
        // Build path string from all breadcrumb items
        let pathParts = [];
        
        breadcrumb.forEach((folder, index) => {
            if (index > 0) {
                breadcrumbEl.innerHTML += '<i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>';
            }
            
            // Add folder name to path
            pathParts.push(folder.name);
            
            const isLast = index === breadcrumb.length - 1;
            const link = document.createElement('span');
            link.textContent = folder.name;
            
            if (isLast) {
                link.className = 'text-blue-600 font-semibold';
                currentFolderName = folder.name;
                currentParentId = folder.parent_id;
                
                // Update folder name display
                const folderNameEl = document.getElementById('currentFolderName');
                if (folderNameEl) {
                    folderNameEl.textContent = folder.name || 'Home';
                }
            } else {
                link.className = 'text-gray-600 hover:text-blue-600 cursor-pointer';
                link.onclick = () => loadContent(folder.id);
            }
            
            breadcrumbEl.appendChild(link);
        });
        
        // Update path display - use forward slashes
        const pathEl = document.getElementById('currentPath');
        const pathDisplay = document.getElementById('pathDisplay');
        
        if (pathEl) {
            const fullPath = '/' + pathParts.join('/');
            pathEl.textContent = fullPath;
            
            // Show path only if not at root (Home)
            if (pathDisplay) {
                if (breadcrumb.length > 1 || (breadcrumb[0] && breadcrumb[0].name !== 'Home')) {
                    pathDisplay.classList.remove('hidden');
                    pathDisplay.classList.add('flex');
                } else {
                    pathDisplay.classList.add('hidden');
                    pathDisplay.classList.remove('flex');
                }
            }
        }
    } catch (error) {
        console.error('Error loading breadcrumb:', error);
    }
}

// Load folders in current directory
async function loadFolders(parentId) {
    try {
        const response = await fetch(`api_folders.php?action=get_folders&parent_id=${parentId}`);
        const folders = await response.json();
        
        if (folders.error) {
            throw new Error(folders.error);
        }
        
        const foldersListEl = document.getElementById('foldersList');
        
        if (folders.length === 0) {
            foldersListEl.innerHTML = '<p class="text-gray-500 text-sm italic text-center py-4">No subfolders in this location</p>';
            return;
        }
        
        foldersListEl.innerHTML = '';
        
        // Get file counts for each folder
        const getFilesInFolder = async (folderId) => {
            try {
                const response = await fetch(`api_folders.php?action=get_files&folder_id=${folderId}`);
                const files = await response.json();
                return Array.isArray(files) ? files.length : 0;
            } catch {
                return 0;
            }
        };
        
        // Get subfolder counts
        const subfolderCounts = {};
        folders.forEach(folder => {
            const count = allFolders.filter(f => f.parent_id == folder.id).length;
            subfolderCounts[folder.id] = count;
        });
        
        folders.forEach(folder => {
            const folderEl = document.createElement('div');
            folderEl.className = 'group p-3 bg-gradient-to-r from-yellow-50 to-amber-50 hover:from-yellow-100 hover:to-amber-100 rounded-lg cursor-pointer transition-all duration-200 flex items-center justify-between border border-yellow-200 hover:border-yellow-400 hover:shadow-md';
            const subfolderCount = subfolderCounts[folder.id] || 0;
            const fileCount = parseInt(folder.file_count) || 0;
            
            let contentInfo = '';
            if (subfolderCount > 0 || fileCount > 0) {
                const parts = [];
                if (subfolderCount > 0) parts.push(`<i class="fas fa-folder text-xs mr-1"></i>${subfolderCount}`);
                if (fileCount > 0) parts.push(`<i class="fas fa-file text-xs mr-1"></i>${fileCount}`);
                contentInfo = `<span class="text-xs text-gray-500 flex items-center gap-2">${parts.join('<span>•</span>')}</span>`;
            }
            
            folderEl.innerHTML = `
                <div class=\"flex items-center space-x-2.5 flex-1 min-w-0\" onclick=\"loadContent(${folder.id})\">
                    <i class=\"fas fa-folder text-yellow-500 group-hover:text-yellow-600 text-xl transition-colors duration-200 flex-shrink-0\"></i>
                    <div class=\"flex flex-col min-w-0 flex-1\">
                        <span class=\"text-gray-800 group-hover:text-gray-900 font-semibold text-sm truncate transition-colors duration-200\">${escapeHtml(folder.name)}</span>
                        ${contentInfo}
                    </div>
                </div>
                <div class=\"flex items-center space-x-1.5 opacity-60 group-hover:opacity-100 transition-opacity duration-200\">
                    <button onclick=\"event.stopPropagation(); showEditFolderModal(${folder.id}, '${escapeHtml(folder.name)}')\" class=\"text-blue-600 hover:text-blue-700 hover:bg-blue-100 p-1.5 rounded transition-all duration-200\" title=\"Edit\">
                        <i class=\"fas fa-edit text-sm\"></i>
                    </button>
                    <button onclick=\"event.stopPropagation(); deleteFolder(${folder.id})\" class=\"text-red-600 hover:text-red-700 hover:bg-red-100 p-1.5 rounded transition-all duration-200\" title=\"Delete\">
                        <i class=\"fas fa-trash text-sm\"></i>
                    </button>
                </div>
            `;
            
            foldersListEl.appendChild(folderEl);
        });
        
        updateItemCount();
    } catch (error) {
        console.error('Error loading folders:', error);
        document.getElementById('foldersList').innerHTML = '<p class="text-red-500 text-sm">Error loading folders</p>';
    }
}

// Load files in current directory
async function loadFiles(folderId) {
    try {
        const [filesResponse, foldersResponse] = await Promise.all([
            fetch(`api_folders.php?action=get_files&folder_id=${folderId}`),
            fetch(`api_folders.php?action=get_folders&parent_id=${folderId}`)
        ]);
        
        const files = await filesResponse.json();
        const folders = await foldersResponse.json();
        
        if (files.error) {
            throw new Error(files.error);
        }
        
        const filesListEl = document.getElementById('filesList');
        
        if (files.length === 0 && (!folders || folders.length === 0)) {
            filesListEl.innerHTML = '<p class="text-gray-500 text-sm italic">No items</p>';
            return;
        }
        
        filesListEl.innerHTML = '';
        
        // Render folders first
        if (folders && folders.length > 0) {
            folders.forEach(folder => {
                const folderEl = document.createElement('div');
                folderEl.className = 'drive-item flex items-center justify-between p-2 rounded hover:bg-gray-100 cursor-pointer transition';
                
                const subfolderCount = parseInt(folder.subfolder_count) || 0;
                const fileCount = parseInt(folder.file_count) || 0;
                const totalItems = subfolderCount + fileCount;
                
                folderEl.innerHTML = `
                    <div class="flex items-center gap-3 flex-1 min-w-0" onclick="loadContent(${folder.id})">
                        <i class="fas fa-folder text-green-600 text-base flex-shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-gray-800 truncate font-medium">${escapeHtml(folder.name)}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                ${totalItems > 0 ? `${subfolderCount} folder(s), ${fileCount} file(s)` : 'Empty'}
                            </div>
                        </div>
                    </div>
                    <div class="relative flex-shrink-0">
                        <button onclick="event.stopPropagation(); toggleItemMenu(this)" class="drive-icon-btn text-gray-600 hover:text-gray-800" title="More">
                            <i class="fas fa-ellipsis-v text-xs"></i>
                        </button>
                        <div class="item-menu hidden absolute right-0 mt-1 w-40 bg-white rounded shadow-lg border border-gray-200 z-50 py-1 text-xs">
                            <button onclick="event.stopPropagation(); loadContent(${folder.id}); closeAllMenus()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition text-gray-700 flex items-center gap-2">
                                <i class="fas fa-arrow-right text-green-600 w-3"></i>
                                <span>Open</span>
                            </button>
                            <button onclick="event.stopPropagation(); showEditFolderModal(${folder.id}, '${escapeHtml(folder.name)}'); closeAllMenus()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition text-gray-700 flex items-center gap-2">
                                <i class="fas fa-edit text-blue-600 w-3"></i>
                                <span>Rename</span>
                            </button>
                            <button onclick="event.stopPropagation(); deleteFolder(${folder.id}); closeAllMenus()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition text-gray-700 flex items-center gap-2">
                                <i class="fas fa-trash text-red-600 w-3"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                `;
                
                filesListEl.appendChild(folderEl);
            });
        }
        
        // Render files
        files.forEach(file => {
            const fileEl = document.createElement('div');
            fileEl.className = 'drive-item flex items-center justify-between p-2 rounded hover:bg-gray-100 cursor-pointer transition';
            
            const icon = getFileIcon(file.file_type);
            const formattedSize = formatBytes(file.file_size);
            const formattedDate = formatDate(file.created_at);
            
            fileEl.innerHTML = `
                <div class="flex items-center gap-3 flex-1 min-w-0" onclick="previewFile(${file.id}, '${escapeHtml(file.original_name)}', '${file.file_type}', ${file.file_size})">
                    <i class="${icon} text-base flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-800 truncate">${escapeHtml(file.original_name)}</div>
                        <div class="text-xs text-gray-500 mt-0.5">${formattedSize} • ${formattedDate}</div>
                    </div>
                </div>
                <div class="relative flex-shrink-0">
                    <button onclick="event.stopPropagation(); toggleItemMenu(this)" class="drive-icon-btn text-gray-600 hover:text-gray-800" title="More">
                        <i class="fas fa-ellipsis-v text-xs"></i>
                    </button>
                    <div class="item-menu hidden absolute right-0 mt-1 w-40 bg-white rounded shadow-lg border border-gray-200 z-50 py-1 text-xs">
                        <button onclick="event.stopPropagation(); previewFile(${file.id}, '${escapeHtml(file.original_name)}', '${file.file_type}', ${file.file_size}); closeAllMenus()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition text-gray-700 flex items-center gap-2">
                            <i class="fas fa-eye text-purple-600 w-3"></i>
                            <span>Preview</span>
                        </button>
                        <button onclick="event.stopPropagation(); downloadFile(${file.id}); closeAllMenus()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition text-gray-700 flex items-center gap-2">
                            <i class="fas fa-download text-green-600 w-3"></i>
                            <span>Download</span>
                        </button>
                        <button onclick="event.stopPropagation(); showEditFileModal(${file.id}, '${escapeHtml(file.original_name)}', '${escapeHtml(file.description || '')}'); closeAllMenus()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition text-gray-700 flex items-center gap-2">
                            <i class="fas fa-edit text-blue-600 w-3"></i>
                            <span>Rename</span>
                        </button>
                        <button onclick="event.stopPropagation(); deleteFile(${file.id}); closeAllMenus()" class="w-full text-left px-3 py-2 hover:bg-gray-100 transition text-gray-700 flex items-center gap-2">
                            <i class="fas fa-trash text-red-600 w-3"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            `;
            
            filesListEl.appendChild(fileEl);
        });
        
        updateItemCount();
    } catch (error) {
        console.error('Error loading files:', error);
        document.getElementById('filesList').innerHTML = '<p class="text-red-500 text-sm">Error loading files</p>';
    }
}

// Create new folder
async function createFolder(event) {
    event.preventDefault();
    
    const folderName = document.getElementById('folderName').value.trim();
    
    if (!folderName) {
        showNotification('Please enter a folder name', 'error');
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch('api_folders.php?action=create_folder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: folderName,
                parent_id: currentFolderId
            })
        });
        
        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        showNotification('Folder created successfully', 'success');
        hideCreateFolderModal();
        loadSearchData(true); // Refresh search cache
        loadContent(currentFolderId);
        document.getElementById('folderName').value = '';
    } catch (error) {
        showNotification('Error creating folder: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Update folder
async function updateFolder(event) {
    event.preventDefault();
    
    const folderId = document.getElementById('editFolderId').value;
    const folderName = document.getElementById('editFolderName').value.trim();
    
    if (!folderName) {
        showNotification('Please enter a folder name', 'error');
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch('api_folders.php?action=update_folder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: folderId,
                name: folderName
            })
        });
        
        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        showNotification('Folder updated successfully', 'success');
        hideEditFolderModal();
        loadSearchData(true); // Refresh search cache
        loadContent(currentFolderId);
    } catch (error) {
        showNotification('Error updating folder: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Delete folder
async function deleteFolder(folderId) {
    const result = await Swal.fire({
        title: 'Delete folder?',
        text: "This will delete the folder and all its contents (subfolders and files). This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    });
    
    if (!result.isConfirmed) {
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch(`api_folders.php?action=delete_folder&id=${folderId}`);
        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        hideLoading();
        
        await Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'Folder and all its contents have been deleted.',
            confirmButtonColor: '#16a34a',
            timer: 2000
        });
        
        loadSearchData(true); // Refresh search cache
        loadContent(currentFolderId);
    } catch (error) {
        hideLoading();
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error deleting folder: ' + error.message,
            confirmButtonColor: '#16a34a'
        });
    }
}

// Upload file
async function uploadFile(event) {
    event.preventDefault();
    
    const fileInput = document.getElementById('fileInput');
    const description = document.getElementById('fileDescription').value.trim();
    
    if (!fileInput.files.length) {
        showNotification('Please select a file', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    formData.append('folder_id', currentFolderId);
    formData.append('description', description);
    
    showLoading();
    
    try {
        const response = await fetch('upload.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        showNotification('File uploaded successfully', 'success');
        hideUploadModal();
        loadSearchData(true); // Refresh search cache
        loadContent(currentFolderId);
        fileInput.value = '';
        document.getElementById('fileDescription').value = '';
    } catch (error) {
        showNotification('Error uploading file: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Update file
async function updateFile(event) {
    event.preventDefault();
    
    const fileId = document.getElementById('editFileId').value;
    const fileName = document.getElementById('editFileName').value.trim();
    const description = document.getElementById('editFileDescription').value.trim();
    
    if (!fileName) {
        showNotification('Please enter a file name', 'error');
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch('api_files.php?action=update_file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: fileId,
                name: fileName,
                description: description
            })
        });
        
        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        showNotification('File updated successfully', 'success');
        hideEditFileModal();
        loadSearchData(true); // Refresh search cache
        loadContent(currentFolderId);
    } catch (error) {
        showNotification('Error updating file: ' + error.message, 'error');
    } finally {
        hideLoading();
    }
}

// Delete file
async function deleteFile(fileId) {
    const result = await Swal.fire({
        title: 'Delete file?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    });
    
    if (!result.isConfirmed) {
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch(`api_files.php?action=delete_file&id=${fileId}`);
        const result = await response.json();
        
        if (result.error) {
            throw new Error(result.error);
        }
        
        hideLoading();
        
        await Swal.fire({
            icon: 'success',
            title: 'Deleted!',
            text: 'File has been deleted.',
            confirmButtonColor: '#16a34a',
            timer: 2000
        });
        
        loadSearchData(true); // Refresh search cache
        loadContent(currentFolderId);
    } catch (error) {
        hideLoading();
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error deleting file: ' + error.message,
            confirmButtonColor: '#16a34a'
        });
    }
}

// Download file
function downloadFile(fileId) {
    // Open in new window to preview/download
    window.open(`api_files.php?action=view&id=${fileId}`, '_blank');
}

// Modal functions
function showCreateFolderModal() {
    document.getElementById('createFolderModal').classList.remove('hidden');
}

function hideCreateFolderModal() {
    document.getElementById('createFolderModal').classList.add('hidden');
    document.getElementById('folderName').value = '';
}

function showUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function hideUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('fileInput').value = '';
    document.getElementById('fileDescription').value = '';
}

function showEditFileModal(fileId, fileName, description) {
    document.getElementById('editFileId').value = fileId;
    document.getElementById('editFileName').value = fileName;
    document.getElementById('editFileDescription').value = description;
    document.getElementById('editFileModal').classList.remove('hidden');
}

function hideEditFileModal() {
    document.getElementById('editFileModal').classList.add('hidden');
}

function showEditFolderModal(folderId, folderName) {
    document.getElementById('editFolderId').value = folderId;
    document.getElementById('editFolderName').value = folderName;
    document.getElementById('editFolderModal').classList.remove('hidden');
}

function hideEditFolderModal() {
    document.getElementById('editFolderModal').classList.add('hidden');
}

function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// Utility functions
function refreshContent() {
    loadContent(currentFolderId);
}

function updateItemCount() {
    const currentFolderPanelCount = document.querySelectorAll('#foldersList > div').length;
    const contentPanelCount = document.querySelectorAll('#filesList > div').length;
    const total = contentPanelCount;
    
    document.getElementById('itemCount').textContent = `${total} item${total !== 1 ? 's' : ''}`;
}

function getFileIcon(fileType) {
    if (!fileType) return 'fas fa-file text-gray-500';
    
    if (fileType.includes('image')) return 'fas fa-file-image text-blue-500';
    if (fileType.includes('pdf')) return 'fas fa-file-pdf text-red-500';
    if (fileType.includes('word') || fileType.includes('document')) return 'fas fa-file-word text-blue-600';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'fas fa-file-excel text-green-600';
    if (fileType.includes('zip') || fileType.includes('rar') || fileType.includes('compressed')) return 'fas fa-file-archive text-yellow-600';
    if (fileType.includes('text')) return 'fas fa-file-alt text-gray-600';
    
    return 'fas fa-file text-gray-500';
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes} min ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    if (days < 7) return `${days} day${days > 1 ? 's' : ''} ago`;
    
    return date.toLocaleDateString();
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showNotification(message, type = 'info') {
    const iconMap = {
        success: 'success',
        error: 'error',
        info: 'info',
        warning: 'warning'
    };
    
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: iconMap[type] || 'info',
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

// File Preview Functions
let currentPreviewFileId = null;

function previewFile(fileId, fileName, fileType, fileSize) {
    currentPreviewFileId = fileId;
    
    const modal = document.getElementById('previewModal');
    const fileNameEl = document.getElementById('previewFileName');
    const fileInfoEl = document.getElementById('previewFileInfo');
    const contentEl = document.getElementById('previewContent');
    
    fileNameEl.textContent = fileName;
    fileInfoEl.textContent = `${fileType} • ${formatBytes(fileSize)}`;
    
    // Show loading
    contentEl.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600"></i>
            <span class="ml-3 text-gray-600">Loading preview...</span>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // Load preview based on file type
    loadFilePreview(fileId, fileType, contentEl);
}

function loadFilePreview(fileId, fileType, contentEl) {
    const fileUrl = `api_files.php?action=view&id=${fileId}`;
    
    if (fileType.includes('image')) {
        // Image preview
        contentEl.innerHTML = `
            <div class="flex justify-center">
                <img src="${fileUrl}" alt="Preview" class="max-w-full h-auto rounded-lg shadow-lg" onload="this.classList.add('fade-in')" onerror="this.parentElement.innerHTML='<p class=\\"text-red-500\\">Failed to load image</p>'">
            </div>
        `;
    } else if (fileType.includes('pdf')) {
        // PDF preview
        contentEl.innerHTML = `
            <iframe src="${fileUrl}" class="w-full h-[600px] border rounded-lg" onload="console.log('PDF loaded')" onerror="this.parentElement.innerHTML='<p class=\\"text-red-500\\">Failed to load PDF</p>'"></iframe>
        `;
    } else if (fileType.includes('text') || fileType.includes('json') || fileType.includes('xml') || fileType.includes('javascript') || fileType.includes('html') || fileType.includes('css')) {
        // Text file preview
        fetch(fileUrl)
            .then(response => response.text())
            .then(text => {
                contentEl.innerHTML = `
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg font-mono text-sm overflow-auto max-h-[500px]">
                        <pre class="whitespace-pre-wrap">${escapeHtml(text)}</pre>
                    </div>
                `;
            })
            .catch(error => {
                contentEl.innerHTML = '<p class="text-red-500">Failed to load text file</p>';
            });
    } else if (fileType.includes('video')) {
        // Video preview
        contentEl.innerHTML = `
            <div class="flex justify-center">
                <video controls class="max-w-full h-auto rounded-lg shadow-lg">
                    <source src="${fileUrl}" type="${fileType}">
                    Your browser does not support video playback.
                </video>
            </div>
        `;
    } else if (fileType.includes('audio')) {
        // Audio preview
        contentEl.innerHTML = `
            <div class="flex justify-center items-center py-12">
                <div class="text-center">
                    <i class="fas fa-music text-6xl text-blue-500 mb-4"></i>
                    <audio controls class="w-full max-w-md">
                        <source src="${fileUrl}" type="${fileType}">
                        Your browser does not support audio playback.
                    </audio>
                </div>
            </div>
        `;
    } else if (fileType.includes('word') || fileType.includes('document')) {
        // Word document - try to open in new window
        contentEl.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-file-word text-6xl text-blue-600 mb-4"></i>
                <p class="text-gray-600 mb-4">Microsoft Word document</p>
                <button onclick="window.open('api_files.php?action=view&id=${fileId}', '_blank')" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center space-x-2 mx-auto">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Open in New Window</span>
                </button>
                <p class="text-sm text-gray-500 mt-4">Click the button to open and preview the document</p>
            </div>
        `;
    } else if (fileType.includes('excel') || fileType.includes('spreadsheet')) {
        // Excel document
        contentEl.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-file-excel text-6xl text-green-600 mb-4"></i>
                <p class="text-gray-600 mb-4">Microsoft Excel spreadsheet</p>
                <button onclick="window.open('api_files.php?action=view&id=${fileId}', '_blank')" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center space-x-2 mx-auto">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Open in New Window</span>
                </button>
                <p class="text-sm text-gray-500 mt-4">Click the button to open and preview the spreadsheet</p>
            </div>
        `;
    } else if (fileType.includes('zip') || fileType.includes('rar') || fileType.includes('compressed')) {
        // Archive file
        contentEl.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-file-archive text-6xl text-yellow-600 mb-4"></i>
                <p class="text-gray-600 mb-4">Archive file</p>
                <p class="text-sm text-gray-500">Click the download button below to download the archive.</p>
            </div>
        `;
    } else {
        // Try to open in new window for other types
        contentEl.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-file text-6xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 mb-4">Preview in modal not available for this file type.</p>
                <button onclick="window.open('api_files.php?action=view&id=${fileId}', '_blank')" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 flex items-center space-x-2 mx-auto">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Open in New Window</span>
                </button>
                <p class="text-sm text-gray-500 mt-4">Click to open in a new window or download using the button below</p>
            </div>
        `;
    }
}

function hidePreviewModal() {
    document.getElementById('previewModal').classList.add('hidden');
    currentPreviewFileId = null;
}

function downloadFileFromPreview() {
    if (currentPreviewFileId) {
        // Force download instead of view
        const link = document.createElement('a');
        link.href = `api_files.php?action=download&id=${currentPreviewFileId}`;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Navigation functions
function goHome() {
    navigationHistory = [];
    loadContent(1, true);
    showNotification('Navigated to home', 'info');
}

function goBack() {
    if (navigationHistory.length > 1) {
        // Remove current folder from history
        navigationHistory.pop();
        // Get previous folder
        const previousFolderId = navigationHistory[navigationHistory.length - 1];
        // Load without adding to history
        navigationHistory.pop(); // Remove again because loadContent will add it
        loadContent(previousFolderId, true);
    } else if (currentParentId) {
        // If no history but we have a parent, go to parent
        navigationHistory = [];
        loadContent(currentParentId, true);
    }
}

function updateBackButton() {
    const backButton = document.getElementById('backButton');
    
    if (currentFolderId === 1) {
        if (backButton) {
            backButton.classList.add('hidden');
        }
    } else {
        if (backButton) {
            backButton.classList.remove('hidden');
        }
    }
}

// Load all folders tree
async function loadAllFoldersTree() {
    try {
        const response = await fetch('api_folders.php?action=get_all_folders');
        let folders = await response.json();
        
        if (folders.error) {
            // Fallback: get root folders
            const rootResponse = await fetch('api_folders.php?action=get_folders&parent_id=1');
            folders = await rootResponse.json();
        }
        
        allFolders = folders;
        renderFolderTree();
    } catch (error) {
        console.error('Error loading folder tree:', error);
    }
}

function renderFolderTree() {
    const treeEl = document.getElementById('folderTree');
    
    if (!allFolders || allFolders.length === 0) {
        treeEl.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-folder text-gray-300 text-3xl mb-2"></i>
                <p class="text-gray-500 text-xs sm:text-sm">No folders</p>
            </div>
        `;
        return;
    }
    
    // Build tree structure
    const tree = buildTree(allFolders, null);
    treeEl.innerHTML = renderTreeNodes(tree, 0);
}

function buildTree(folders, parentId) {
    return folders
        .filter(f => {
            if (parentId === null) {
                return f.parent_id === null || f.parent_id == 1 || f.id == 1;
            }
            return f.parent_id == parentId;
        })
        .map(folder => ({
            ...folder,
            children: buildTree(folders, folder.id)
        }));
}

function renderTreeNodes(nodes, level) {
    let html = '';
    
    nodes.forEach((node, index) => {
        const indent = level * 14;
        const hasChildren = node.children && node.children.length > 0;
        const isActive = node.id == currentFolderId;
        const isInPath = isNodeInCurrentPath(node.id);
        const shouldExpand = isInPath || level === 0;
        const fileCount = parseInt(node.file_count) || 0;
        const subfolderCount = parseInt(node.subfolder_count) || 0;
        const totalItems = fileCount + subfolderCount;
        
        html += `
            <div class="folder-tree-item" data-folder-id="${node.id}">
                <div class="group flex items-center justify-between py-1.5 px-2 rounded cursor-pointer transition-all duration-150
                            ${isActive ? 'bg-blue-500 text-white' : 'hover:bg-gray-100'}"
                     onclick="loadContent(${node.id})" 
                     style="padding-left: ${indent + 8}px">
                    
                    <div class="flex items-center flex-1 min-w-0">
                        ${hasChildren ? `
                            <i class="fas fa-chevron-${shouldExpand ? 'down' : 'right'} text-xs ${isActive ? 'text-white' : 'text-gray-400'} mr-1.5 tree-chevron transition-transform duration-150" 
                               onclick="event.stopPropagation(); toggleTreeNode(this)"></i>` : '<span class="w-3.5 mr-1.5"></span>'}
                        
                        <i class="fas ${isActive ? 'fa-folder-open' : 'fa-folder'} ${isActive ? 'text-yellow-200' : 'text-yellow-500'} text-sm mr-2 transition-colors duration-150"></i>
                        
                        <span class="text-xs sm:text-sm ${isActive ? 'font-semibold text-white' : 'text-gray-700'} truncate flex-1">
                            ${escapeHtml(node.name)}
                        </span>
                    </div>
                    
                    ${totalItems > 0 ? `
                        <span class="text-[10px] sm:text-xs ${isActive ? 'text-white text-opacity-80' : 'text-gray-500'} ml-2">
                            ${totalItems}
                        </span>` : ''}
                </div>
                
                ${hasChildren ? `<div class="tree-children ${shouldExpand ? '' : 'hidden'}">${renderTreeNodes(node.children, level + 1)}</div>` : ''}
            </div>
        `;
    });
    
    return html;
}

function toggleTreeNode(chevron) {
    const parent = chevron.closest('.folder-tree-item');
    const children = parent.querySelector('.tree-children');
    
    if (children) {
        if (children.classList.contains('hidden')) {
            children.classList.remove('hidden');
            chevron.classList.remove('fa-chevron-right');
            chevron.classList.add('fa-chevron-down');
        } else {
            children.classList.add('hidden');
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-right');
        }
    }
}

function toggleFolderTree() {
    const treeEl = document.getElementById('folderTree');
    const icon = document.getElementById('treeToggleIcon');
    
    if (treeEl.classList.contains('hidden')) {
        treeEl.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        treeEl.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function isNodeInCurrentPath(nodeId) {
    // Check if this node is in the path to the current folder
    if (nodeId == currentFolderId) return true;
    
    const node = allFolders.find(f => f.id == currentFolderId);
    if (!node) return false;
    
    // Build path from current folder to root
    let current = node;
    while (current) {
        if (current.id == nodeId) return true;
        current = allFolders.find(f => f.id == current.parent_id);
    }
    
    return false;
}

// Toggle actions menu dropdown
function toggleActionsMenu() {
    const menu = document.getElementById('actionsMenu');
    menu.classList.toggle('hidden');
}

// Toggle individual item menu
function toggleItemMenu(button) {
    // Close all other menus first
    closeAllMenus();
    
    // Toggle this menu
    const menu = button.nextElementSibling;
    if (menu && menu.classList.contains('item-menu')) {
        menu.classList.toggle('hidden');
    }
}

// Close all dropdown menus
function closeAllMenus() {
    document.querySelectorAll('.item-menu').forEach(menu => {
        menu.classList.add('hidden');
    });
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('actionsMenu');
    const menuBtn = document.getElementById('actionsMenuBtn');
    
    if (menu && menuBtn && !menu.contains(event.target) && !menuBtn.contains(event.target)) {
        menu.classList.add('hidden');
    }
    
    // Close item menus if clicking outside
    if (!event.target.closest('.relative')) {
        closeAllMenus();
    }
});

// Folder tree search functionality
function filterFolderTree(searchTerm) {
    const searchInput = document.getElementById('folderSearchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const searchResultText = document.getElementById('searchResultText');
    const folderItems = document.querySelectorAll('.folder-tree-item');
    
    searchTerm = searchTerm.toLowerCase().trim();
    
    // Show/hide clear button
    if (searchTerm) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
        searchResultText.classList.add('hidden');
    }
    
    if (!searchTerm) {
        // Reset all visibility
        folderItems.forEach(item => {
            item.style.display = '';
            item.classList.remove('search-highlight');
        });
        return;
    }
    
    let matchCount = 0;
    
    folderItems.forEach(item => {
        const folderName = item.getAttribute('data-folder-name') || '';
        const matches = folderName.includes(searchTerm);
        
        if (matches) {
            item.style.display = '';
            item.classList.add('search-highlight');
            matchCount++;
            
            // Expand parent folders to show match
            let parent = item.parentElement;
            while (parent) {
                if (parent.classList.contains('tree-children')) {
                    parent.classList.remove('hidden');
                    const chevron = parent.previousElementSibling?.querySelector('.tree-chevron');
                    if (chevron) {
                        chevron.classList.remove('fa-chevron-right');
                        chevron.classList.add('fa-chevron-down');
                    }
                }
                parent = parent.parentElement;
            }
        } else {
            item.style.display = 'none';
            item.classList.remove('search-highlight');
        }
    });
    
    // Show search results
    searchResultText.textContent = `${matchCount} match${matchCount !== 1 ? 'es' : ''}`;
    searchResultText.classList.remove('hidden');
}

function clearFolderSearch() {
    const searchInput = document.getElementById('folderSearchInput');
    searchInput.value = '';
    filterFolderTree('');
}

// Expand all folders in tree
function expandAllFolders() {
    const allChildren = document.querySelectorAll('.tree-children');
    const allChevrons = document.querySelectorAll('.tree-chevron');
    
    allChildren.forEach(child => child.classList.remove('hidden'));
    allChevrons.forEach(chevron => {
        chevron.classList.remove('fa-chevron-right');
        chevron.classList.add('fa-chevron-down');
    });
    
    showNotification('All folders expanded', 'info');
}

// Collapse all folders in tree
function collapseAllFolders() {
    const allChildren = document.querySelectorAll('.tree-children');
    const allChevrons = document.querySelectorAll('.tree-chevron');
    
    // Collapse all except root level
    document.querySelectorAll('.folder-tree-item').forEach(item => {
        const children = item.querySelector('.tree-children');
        if (children && item.parentElement.id !== 'folderTree') {
            children.classList.add('hidden');
            const chevron = item.querySelector('.tree-chevron');
            if (chevron) {
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-right');
            }
        }
    });
    
    showNotification('All folders collapsed', 'info');
}

function expandToCurrentFolder() {
    // Expand all parent folders of the current folder
    const currentNode = document.querySelector(`[data-folder-id="${currentFolderId}"]`);
    if (currentNode) {
        let parent = currentNode.parentElement;
        while (parent) {
            if (parent.classList.contains('tree-children') && parent.classList.contains('hidden')) {
                parent.classList.remove('hidden');
                const chevron = parent.previousElementSibling?.querySelector('.tree-chevron');
                if (chevron) {
                    chevron.classList.remove('fa-chevron-right');
                    chevron.classList.add('fa-chevron-down');
                }
            }
            parent = parent.parentElement;
        }
        
        // Scroll to current folder
        setTimeout(() => {
            currentNode.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
}

// Search functionality
let searchTimeout = null;
let allSearchData = { folders: [], files: [] };
let searchDataLoaded = false;

async function loadSearchData(forceReload = false) {
    if (searchDataLoaded && !forceReload) {
        return; // Data already loaded, skip
    }
    
    try {
        const [foldersResponse, filesResponse] = await Promise.all([
            fetch('api_folders.php?action=get_all_folders'),
            fetch('api_files.php?action=get_all_files')
        ]);
        
        const folders = await foldersResponse.json();
        const files = await filesResponse.json();
        
        allSearchData.folders = Array.isArray(folders) ? folders : [];
        allSearchData.files = Array.isArray(files) ? files : [];
        searchDataLoaded = true;
    } catch (error) {
        console.error('Error loading search data:', error);
    }
}

function handleSearch(event) {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const query = searchInput.value.trim().toLowerCase();
    
    // Show/hide clear button
    if (query) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
        // If empty, reload current folder content
        if (event.key === 'Enter' || event.type === 'click') {
            loadContent(currentFolderId, false);
        }
        return;
    }
    
    // Debounce search
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        performSearch(query);
    }, 300);
    
    // Immediate search on Enter key
    if (event.key === 'Enter') {
        clearTimeout(searchTimeout);
        performSearch(query);
    }
}

async function performSearch(query) {
    if (!query) {
        loadContent(currentFolderId, false);
        return;
    }
    
    // Ensure search data is loaded (only loads once)
    await loadSearchData();
    
    // Filter folders and files - search in name and path
    const matchedFolders = allSearchData.folders.filter(folder => 
        folder.name.toLowerCase().includes(query) ||
        (folder.path && folder.path.toLowerCase().includes(query))
    );
    
    // Filter files - search in filename, description, and folder path
    const matchedFiles = allSearchData.files.filter(file => 
        file.original_name.toLowerCase().includes(query) || 
        (file.description && file.description.toLowerCase().includes(query)) ||
        (file.folder_name && file.folder_name.toLowerCase().includes(query)) ||
        (file.folder_path && file.folder_path.toLowerCase().includes(query))
    );
    
    // Display search results
    displaySearchResults(matchedFolders, matchedFiles, query);
}

function displaySearchResults(folders, files, query) {
    const filesList = document.getElementById('filesList');
    const folderNameEl = document.getElementById('currentFolderName');
    const pathDisplay = document.getElementById('pathDisplay');
    const itemCount = document.getElementById('itemCount');
    
    // Update header
    folderNameEl.textContent = `Search results for "${query}"`;
    pathDisplay.classList.add('hidden');
    
    // Clear current content
    filesList.innerHTML = '';
    
    const totalItems = folders.length + files.length;
    itemCount.textContent = `${totalItems} result${totalItems !== 1 ? 's' : ''}`;
    
    if (totalItems === 0) {
        filesList.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-search text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500 text-sm">No results found for "${query}"</p>
            </div>
        `;
        return;
    }
    
    // Display matched folders
    if (folders.length > 0) {
        const folderSection = document.createElement('div');
        folderSection.innerHTML = `<div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2 px-2">Folders (${folders.length})</div>`;
        filesList.appendChild(folderSection);
        
        folders.forEach(folder => {
            const folderItem = createSearchFolderItem(folder);
            filesList.appendChild(folderItem);
        });
    }
    
    // Display matched files
    if (files.length > 0) {
        const fileSection = document.createElement('div');
        fileSection.innerHTML = `<div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2 px-2 mt-4">Files (${files.length})</div>`;
        filesList.appendChild(fileSection);
        
        files.forEach(file => {
            const fileItem = createSearchFileItem(file);
            filesList.appendChild(fileItem);
        });
    }
}

function createSearchFolderItem(folder) {
    const div = document.createElement('div');
    div.className = 'drive-item flex items-center justify-between p-2 rounded hover:bg-gray-100 cursor-pointer transition';
    div.onclick = () => {
        clearSearch();
        loadContent(folder.id);
    };
    
    const subfolderCount = parseInt(folder.subfolder_count) || 0;
    const fileCount = parseInt(folder.file_count) || 0;
    
    div.innerHTML = `
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <i class="fas fa-folder text-green-600 text-base flex-shrink-0"></i>
            <div class="min-w-0 flex-1">
                <div class="text-sm text-gray-800 truncate font-medium">${escapeHtml(folder.name)}</div>
                <div class="text-xs text-gray-500 truncate">
                    ${escapeHtml(folder.path || '/')} • ${subfolderCount} folder(s), ${fileCount} file(s)
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
            <button onclick="event.stopPropagation(); clearSearch(); loadContent(${folder.id})" class="drive-icon-btn text-gray-600 hover:text-green-600" title="Open">
                <i class="fas fa-folder-open text-xs"></i>
            </button>
        </div>
    `;
    
    return div;
}

function createSearchFileItem(file) {
    const div = document.createElement('div');
    div.className = 'drive-item flex items-center justify-between p-2 rounded hover:bg-gray-100 transition';
    
    const fileIcon = getFileIcon(file.file_type);
    const fileSize = formatBytes(file.file_size);
    const folderLocation = file.folder_name ? `in ${escapeHtml(file.folder_name)}` : 'in Root';
    
    div.innerHTML = `
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <i class="${fileIcon} text-base flex-shrink-0"></i>
            <div class="min-w-0 flex-1">
                <div class="text-sm text-gray-800 truncate">${escapeHtml(file.original_name)}</div>
                <div class="text-xs text-gray-500">${fileSize} • ${folderLocation}</div>
            </div>
        </div>
        <div class="flex items-center gap-1 flex-shrink-0">
            <button onclick="event.stopPropagation(); previewFile(${file.id}, '${escapeHtml(file.original_name)}', '${file.file_type}', ${file.file_size})" class="drive-icon-btn text-gray-600 hover:text-green-600" title="Preview">
                <i class="fas fa-eye text-xs"></i>
            </button>
            <button onclick="event.stopPropagation(); downloadFile(${file.id})" class="drive-icon-btn text-gray-600 hover:text-green-600" title="Download">
                <i class="fas fa-download text-xs"></i>
            </button>
            <button onclick="event.stopPropagation(); deleteFile(${file.id})" class="drive-icon-btn text-gray-600 hover:text-red-600" title="Delete">
                <i class="fas fa-trash text-xs"></i>
            </button>
        </div>
    `;
    
    return div;
}

function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    
    searchInput.value = '';
    clearBtn.classList.add('hidden');
    
    // Reload current folder
    loadContent(currentFolderId, false);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load search data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSearchData();
});
