/**
 * Common JavaScript Functions
 * Shared utilities for the IP Repository System
 */

// Toast notification helper
function showToast(message, type = 'info') {
    const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';
    
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Confirmation dialog
async function confirmAction(title, text, confirmText = 'Yes, proceed') {
    const result = await Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel'
    });
    
    return result.isConfirmed;
}

// AJAX request helper
async function ajaxRequest(url, data = {}, method = 'POST') {
    try {
        const formData = new FormData();
        
        // Add CSRF token if available
        if (typeof CSRF_TOKEN !== 'undefined') {
            formData.append('csrf_token', CSRF_TOKEN);
        }
        
        // Add data
        for (const key in data) {
            formData.append(key, data[key]);
        }
        
        const response = await fetch(url, {
            method: method,
            body: method === 'POST' ? formData : null
        });
        
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        throw error;
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Format datetime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    const dateOptions = { year: 'numeric', month: 'short', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit' };
    
    return date.toLocaleDateString('en-US', dateOptions) + ' ' + 
           date.toLocaleTimeString('en-US', timeOptions);
}

// File upload handler
function handleFileUpload(inputElement, maxSize = 10485760) {
    const file = inputElement.files[0];
    
    if (!file) {
        return { valid: false, message: 'No file selected' };
    }
    
    if (file.size > maxSize) {
        return { 
            valid: false, 
            message: `File size exceeds maximum allowed size (${formatFileSize(maxSize)})` 
        };
    }
    
    const allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    const fileExt = file.name.split('.').pop().toLowerCase();
    
    if (!allowedTypes.includes(fileExt)) {
        return { 
            valid: false, 
            message: `File type not allowed. Allowed: ${allowedTypes.join(', ')}` 
        };
    }
    
    return { 
        valid: true, 
        file: file,
        name: file.name,
        size: file.size,
        type: fileExt
    };
}

// Loading overlay
function showLoading(message = 'Processing...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function hideLoading() {
    Swal.close();
}

// Table row actions
function setupTableActions() {
    // Delete confirmation
    document.querySelectorAll('[data-action="delete"]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const confirmed = await confirmAction(
                'Are you sure?',
                'This action cannot be undone.',
                'Yes, delete it'
            );
            
            if (confirmed) {
                const url = btn.getAttribute('data-url');
                const id = btn.getAttribute('data-id');
                
                try {
                    showLoading('Deleting...');
                    const result = await ajaxRequest(url, { id: id });
                    hideLoading();
                    
                    if (result.success) {
                        showToast(result.message, 'success');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    hideLoading();
                    showToast('An error occurred', 'error');
                }
            }
        });
    });
}

// Form validation
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('border-red-500');
        } else {
            input.classList.remove('border-red-500');
        }
    });
    
    return isValid;
}

// Search with debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Copy to clipboard
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copied to clipboard', 'success');
    } catch (error) {
        showToast('Failed to copy', 'error');
    }
}

// Initialize common features on page load
document.addEventListener('DOMContentLoaded', function() {
    // Setup table actions
    setupTableActions();
    
    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert-auto-hide').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
    
    // Confirm before leaving if form is dirty
    let formChanged = false;
    document.querySelectorAll('form input, form textarea, form select').forEach(field => {
        field.addEventListener('change', () => {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // Reset form changed on submit
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            formChanged = false;
        });
    });
});

// Export functions for use in other scripts
window.IPRepo = {
    showToast,
    confirmAction,
    ajaxRequest,
    formatFileSize,
    formatDate,
    formatDateTime,
    handleFileUpload,
    showLoading,
    hideLoading,
    validateForm,
    debounce,
    copyToClipboard
};
