/**
 * Enhanced Utilities for IP Repository System
 * Additional helper functions and mobile support
 */

// ===========================
// MOBILE DETECTION & UTILITIES
// ===========================

const DeviceUtils = {
    isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
    isTouch: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
    
    isMobileDevice() {
        return this.isMobile || window.innerWidth < 768;
    },
    
    isTablet() {
        return this.isTouch && window.innerWidth >= 768 && window.innerWidth < 1024;
    },
    
    isDesktop() {
        return !this.isMobileDevice() && !this.isTablet();
    }
};

// ===========================
// ENHANCED LOADING STATES
// ===========================

class LoadingManager {
    constructor() {
        this.overlay = null;
    }
    
    show(message = 'Loading...', options = {}) {
        this.hide(); // Remove existing
        
        this.overlay = document.createElement('div');
        this.overlay.className = 'loading-overlay';
        this.overlay.innerHTML = `
            <div class="bg-white rounded-2xl p-8 text-center shadow-2xl max-w-sm mx-4">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-800 font-semibold text-lg mb-2">${message}</p>
                ${options.subtitle ? `<p class="text-gray-600 text-sm">${options.subtitle}</p>` : ''}
            </div>
        `;
        
        document.body.appendChild(this.overlay);
        document.body.style.overflow = 'hidden';
    }
    
    hide() {
        if (this.overlay) {
            this.overlay.remove();
            this.overlay = null;
            document.body.style.overflow = '';
        }
    }
    
    showProgress(message, percentage) {
        this.hide();
        
        this.overlay = document.createElement('div');
        this.overlay.className = 'loading-overlay';
        this.overlay.innerHTML = `
            <div class="bg-white rounded-2xl p-8 text-center shadow-2xl max-w-sm mx-4">
                <p class="text-gray-800 font-semibold text-lg mb-4">${message}</p>
                <div class="progress-bar mb-2">
                    <div class="progress-bar-fill" style="width: ${percentage}%"></div>
                </div>
                <p class="text-gray-600 text-sm">${percentage}% complete</p>
            </div>
        `;
        
        document.body.appendChild(this.overlay);
    }
}

const loading = new LoadingManager();

// ===========================
// NETWORK STATUS MONITOR
// ===========================

class NetworkMonitor {
    constructor() {
        this.isOnline = navigator.onLine;
        this.listeners = [];
        this.init();
    }
    
    init() {
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }
    
    handleOnline() {
        this.isOnline = true;
        showToast('success', 'Connection restored', 2000);
        this.notifyListeners(true);
    }
    
    handleOffline() {
        this.isOnline = false;
        showToast('error', 'No internet connection', 5000);
        this.notifyListeners(false);
    }
    
    onStatusChange(callback) {
        this.listeners.push(callback);
    }
    
    notifyListeners(status) {
        this.listeners.forEach(callback => callback(status));
    }
    
    check() {
        return this.isOnline;
    }
}

const networkMonitor = new NetworkMonitor();

// ===========================
// ENHANCED FORM VALIDATION
// ===========================

class FormValidator {
    static validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    static validatePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 15;
    }
    
    static validateURL(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }
    
    static validatePassword(password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return re.test(password);
    }
    
    static validateRequired(value) {
        return value !== null && value !== undefined && value.toString().trim() !== '';
    }
    
    static validateMinLength(value, min) {
        return value.length >= min;
    }
    
    static validateMaxLength(value, max) {
        return value.length <= max;
    }
    
    static validateFileSize(file, maxSizeMB) {
        return file.size <= (maxSizeMB * 1024 * 1024);
    }
    
    static validateFileType(file, allowedTypes) {
        return allowedTypes.includes(file.type);
    }
}

// ===========================
// LOCAL STORAGE MANAGER
// ===========================

class StorageManager {
    static set(key, value, expiryDays = null) {
        try {
            const item = {
                value: value,
                timestamp: Date.now(),
                expiry: expiryDays ? Date.now() + (expiryDays * 24 * 60 * 60 * 1000) : null
            };
            localStorage.setItem(key, JSON.stringify(item));
            return true;
        } catch (error) {
            console.error('Storage error:', error);
            return false;
        }
    }
    
    static get(key, defaultValue = null) {
        try {
            const itemStr = localStorage.getItem(key);
            if (!itemStr) return defaultValue;
            
            const item = JSON.parse(itemStr);
            
            // Check expiry
            if (item.expiry && Date.now() > item.expiry) {
                this.remove(key);
                return defaultValue;
            }
            
            return item.value;
        } catch (error) {
            console.error('Storage error:', error);
            return defaultValue;
        }
    }
    
    static remove(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Storage error:', error);
            return false;
        }
    }
    
    static clear() {
        try {
            localStorage.clear();
            return true;
        } catch (error) {
            console.error('Storage error:', error);
            return false;
        }
    }
}

// ===========================
// TIME & DATE UTILITIES
// ===========================

class TimeUtils {
    static timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        const intervals = [
            { label: 'year', seconds: 31536000 },
            { label: 'month', seconds: 2592000 },
            { label: 'week', seconds: 604800 },
            { label: 'day', seconds: 86400 },
            { label: 'hour', seconds: 3600 },
            { label: 'minute', seconds: 60 }
        ];
        
        for (const interval of intervals) {
            const count = Math.floor(seconds / interval.seconds);
            if (count >= 1) {
                return `${count} ${interval.label}${count > 1 ? 's' : ''} ago`;
            }
        }
        return 'just now';
    }
    
    static formatDate(date, format = 'medium') {
        const d = new Date(date);
        const formats = {
            short: { month: 'short', day: 'numeric' },
            medium: { year: 'numeric', month: 'short', day: 'numeric' },
            long: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' }
        };
        
        return d.toLocaleDateString('en-US', formats[format] || formats.medium);
    }
    
    static formatTime(date) {
        const d = new Date(date);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    
    static formatDateTime(date) {
        return `${this.formatDate(date)} ${this.formatTime(date)}`;
    }
}

// ===========================
// STRING UTILITIES
// ===========================

class StringUtils {
    static truncate(str, length = 50, suffix = '...') {
        if (str.length <= length) return str;
        return str.substring(0, length - suffix.length) + suffix;
    }
    
    static capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }
    
    static capitalizeWords(str) {
        return str.split(' ').map(word => this.capitalize(word)).join(' ');
    }
    
    static slugify(str) {
        return str.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/--+/g, '-')
            .trim();
    }
    
    static highlight(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }
}

// ===========================
// FILE UTILITIES
// ===========================

class FileUtils {
    static formatSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }
    
    static getExtension(filename) {
        return filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2);
    }
    
    static getIcon(filename) {
        const ext = this.getExtension(filename).toLowerCase();
        const icons = {
            pdf: 'fa-file-pdf',
            doc: 'fa-file-word',
            docx: 'fa-file-word',
            xls: 'fa-file-excel',
            xlsx: 'fa-file-excel',
            ppt: 'fa-file-powerpoint',
            pptx: 'fa-file-powerpoint',
            jpg: 'fa-file-image',
            jpeg: 'fa-file-image',
            png: 'fa-file-image',
            gif: 'fa-file-image',
            zip: 'fa-file-archive',
            rar: 'fa-file-archive',
            txt: 'fa-file-alt'
        };
        return icons[ext] || 'fa-file';
    }
    
    static download(url, filename) {
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// ===========================
// CLIPBOARD UTILITIES
// ===========================

class ClipboardUtils {
    static async copy(text) {
        try {
            await navigator.clipboard.writeText(text);
            showToast('success', 'Copied to clipboard');
            return true;
        } catch (error) {
            console.error('Clipboard error:', error);
            showToast('error', 'Failed to copy');
            return false;
        }
    }
    
    static async paste() {
        try {
            return await navigator.clipboard.readText();
        } catch (error) {
            console.error('Clipboard error:', error);
            return null;
        }
    }
}

// ===========================
// EXPORT TO GLOBAL SCOPE
// ===========================

window.IPRepoUtils = {
    Device: DeviceUtils,
    Loading: loading,
    Network: networkMonitor,
    Validator: FormValidator,
    Storage: StorageManager,
    Time: TimeUtils,
    String: StringUtils,
    File: FileUtils,
    Clipboard: ClipboardUtils
};

console.log('Enhanced utilities loaded');
