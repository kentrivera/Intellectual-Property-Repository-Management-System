<!-- Top Header -->
<header id="appHeader" class="bg-gradient-to-r from-white via-emerald-50/30 to-white backdrop-blur-sm shadow-md border-b border-emerald-100/30 sticky top-0 z-10">
    <div class="flex items-center justify-between px-3 sm:px-4 lg:px-6 py-1.5 sm:py-2 lg:py-2.5">
        <!-- Mobile Menu Button -->
        <button id="menuToggle" class="lg:hidden text-gray-700 hover:text-emerald-600 p-1 hover:bg-emerald-50 rounded-xl transition-all hover:scale-105 active:scale-95 shadow-sm hover:shadow-md">
            <i class="fas fa-bars text-lg sm:text-xl"></i>
        </button>

        <!-- Page Title (Mobile) -->
        <h1 class="text-xs sm:text-sm font-bold bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent lg:hidden truncate"><?= $pageTitle ?? 'Dashboard' ?></h1>

        <!-- Search Bar (Desktop) -->
        <div class="hidden lg:flex flex-1 max-w-xl xl:max-w-2xl">
            <div class="relative w-full">
                  <input type="text" 
                       id="globalSearch" 
                       placeholder="Search IP records, documents, users..." 
                       autocomplete="off"
                       role="combobox"
                       aria-autocomplete="list"
                       aria-expanded="false"
                       aria-controls="globalSearchDropdown"
                      class="w-full pl-9 pr-24 py-1.5 text-xs sm:text-sm border-2 border-emerald-200/50 bg-white/80 backdrop-blur-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 hover:border-emerald-300 shadow-sm hover:shadow-md transition-all">
                  <i class="fas fa-search absolute left-3 top-2 text-emerald-500 text-sm"></i>
                
                <!-- Clear button -->
                <button id="globalSearchClear" type="button" aria-label="Clear search" 
                    class="hidden absolute right-12 top-1.5 inline-flex items-center justify-center w-6 h-6 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 transition-all hover:scale-110 active:scale-90">
                    <i class="fas fa-times text-xs"></i>
                </button>
                
                <!-- Loading spinner -->
                <div id="globalSearchLoading" class="hidden absolute right-12 top-1.5 text-emerald-600">
                    <i class="fas fa-spinner fa-spin text-sm"></i>
                </div>
                
                <!-- Search button -->
                <button id="globalSearchBtn" type="button" aria-label="Search"
                    class="absolute right-1.5 top-1 inline-flex items-center justify-center w-6 h-6 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 text-white hover:from-emerald-600 hover:to-green-700 shadow-sm hover:shadow-md transition-all hover:scale-105 active:scale-95">
                    <i class="fas fa-magnifying-glass"></i>
                </button>

                <!-- Typeahead dropdown -->
                <div id="globalSearchDropdown" role="listbox" class="hidden absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-50 max-h-[500px] overflow-y-auto custom-scrollbar animate-slideDown">
                    <div class="p-3 text-xs text-gray-500">Type to search…</div>
                </div>
            </div>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-3">
            <!-- Quick Actions (Desktop) -->
            <button class="hidden xl:flex items-center px-2 py-1 text-xs font-semibold text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 rounded-xl shadow-sm hover:shadow-md transition-all hover:scale-105 active:scale-95" onclick="window.location.href='<?= BASE_URL ?>/admin/ip-records/create'">
                <i class="fas fa-plus mr-1.5"></i> New Record
            </button>
            
            <!-- Notifications -->
            <div class="relative">
                <button id="notificationBtn" class="relative p-1 sm:p-1.5 text-gray-600 hover:text-emerald-600 hover:bg-gradient-to-br hover:from-emerald-50 hover:to-green-50 rounded-xl shadow-sm hover:shadow-md transition-all hover:scale-105 active:scale-95">
                    <i class="fas fa-bell text-sm sm:text-base"></i>
                    <span id="notificationBadge" class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 sm:w-4 sm:h-4 bg-gradient-to-br from-red-500 to-red-600 text-white text-[10px] rounded-full flex items-center justify-center shadow-md hidden animate-pulse ring-2 ring-white">0</span>
                </button>
                
                <!-- Notification Dropdown -->
                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-64 sm:w-72 bg-white rounded-2xl shadow-xl border border-emerald-100 z-50 animate-fadeIn overflow-hidden">
                    <div class="flex items-center justify-between p-2.5 sm:p-3.5 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                        <h3 class="font-bold text-sm bg-gradient-to-r from-emerald-600 to-green-600 bg-clip-text text-transparent">Notifications</h3>
                        <button id="markAllReadBtn" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold px-2 py-1 rounded-lg hover:bg-white/50 transition-all">Mark all read</button>
                    </div>
                    <div id="notificationList" class="max-h-72 overflow-y-auto custom-scrollbar">
                        <div class="p-5 text-center text-gray-500">
                            <i class="fas fa-inbox text-xl sm:text-2xl mb-2 text-gray-300"></i>
                            <p class="text-xs sm:text-sm">No new notifications</p>
                            <p class="text-[11px] text-gray-400 mt-1">You're all caught up!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="relative">
                <button id="userMenuBtn" class="flex items-center space-x-1.5 sm:space-x-2 p-1.5 sm:p-2 hover:bg-gradient-to-br hover:from-emerald-50 hover:to-green-50 rounded-xl transition-all group hover:shadow-md hover:scale-105 active:scale-95">
                    <div class="w-6 h-6 sm:w-7 sm:h-7 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center shadow-md group-hover:shadow-lg transition-all ring-2 ring-emerald-200 group-hover:ring-emerald-300">
                        <span class="text-[11px] sm:text-xs font-bold text-white"><?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?></span>
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-xs font-medium text-gray-800 leading-tight"><?= $_SESSION['full_name'] ?? 'User' ?></p>
                        <p class="text-xs text-gray-500 leading-tight"><?= ucfirst($_SESSION['role'] ?? 'staff') ?></p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-600 text-xs group-hover:text-emerald-600 transition-colors"></i>
                </button>

                <!-- User Dropdown -->
                <div id="userMenuDropdown" class="hidden absolute right-0 mt-2 w-56 sm:w-64 bg-white rounded-2xl shadow-2xl border border-emerald-100 z-50 animate-fadeIn overflow-hidden">
                    <div class="p-3 sm:p-4 border-b border-emerald-200 bg-gradient-to-br from-emerald-100 via-green-50 to-emerald-50">
                        <p class="font-bold text-sm text-gray-900 truncate"><?= $_SESSION['full_name'] ?? 'User' ?></p>
                        <p class="text-xs text-gray-600 truncate mt-0.5"><?= $_SESSION['email'] ?? '' ?></p>
                        <span class="inline-block mt-2 px-3 py-1 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-xs rounded-full font-semibold shadow-md">
                            <i class="fas fa-shield-alt mr-1"></i><?= ucfirst($_SESSION['role'] ?? 'staff') ?>
                        </span>
                    </div>
                    <div class="py-1.5">
                        <a href="<?= BASE_URL ?>/profile" class="flex items-center px-3 sm:px-4 py-2.5 mx-2 my-1 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-700 rounded-xl transition-all hover:translate-x-1">
                            <i class="fas fa-user w-5 text-sm"></i>
                            <span class="ml-2 font-medium">My Profile</span>
                        </a>
                        <a href="<?= BASE_URL ?>/settings" class="flex items-center px-3 sm:px-4 py-2.5 mx-2 my-1 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-700 rounded-xl transition-all hover:translate-x-1">
                            <i class="fas fa-cog w-5 text-sm"></i>
                            <span class="ml-2 font-medium">Settings</span>
                        </a>
                        <a href="<?= BASE_URL ?>/help" class="flex items-center px-3 sm:px-4 py-2.5 mx-2 my-1 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-green-50 hover:text-emerald-700 rounded-xl transition-all hover:translate-x-1">
                            <i class="fas fa-question-circle w-5 text-sm"></i>
                            <span class="ml-2 font-medium">Help & Support</span>
                        </a>
                        <hr class="my-2 mx-3 border-gray-200">
                        <a href="#" onclick="confirmLogout(event)" class="flex items-center px-3 sm:px-4 py-2.5 mx-2 my-1 text-sm text-red-600 hover:bg-gradient-to-r hover:from-red-50 hover:to-red-100 rounded-xl transition-all hover:translate-x-1">
                            <i class="fas fa-sign-out-alt w-5 text-sm"></i>
                            <span class="ml-2 font-semibold">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Search Bar -->
    <div class="lg:hidden px-3 sm:px-4 pb-1.5">
        <div class="relative">
            <input type="text" 
                   id="mobileSearch"
                   placeholder="Search records, documents..." 
                   autocomplete="off"
                   role="combobox"
                   aria-autocomplete="list"
                   aria-expanded="false"
                   aria-controls="mobileSearchDropdown"
                   class="w-full pl-9 pr-20 py-1.5 text-sm border-2 border-emerald-200/50 bg-white/80 backdrop-blur-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400 hover:border-emerald-300 shadow-sm hover:shadow-md transition-all">
            <i class="fas fa-search absolute left-3 top-2 text-emerald-500 text-sm"></i>
            
            <!-- Clear button -->
                <button id="mobileSearchClear" type="button" aria-label="Clear search"
                    class="hidden absolute right-10 top-1 inline-flex items-center justify-center w-6 h-6 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                <i class="fas fa-times text-xs"></i>
            </button>
            
            <!-- Loading spinner -->
            <div id="mobileSearchLoading" class="hidden absolute right-10 top-1.5 text-emerald-600">
                <i class="fas fa-spinner fa-spin text-sm"></i>
            </div>
            
                <button id="mobileSearchBtn" type="button" aria-label="Search"
                    class="absolute right-1.5 top-1 inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 text-white hover:from-emerald-600 hover:to-green-700 shadow-sm hover:shadow-md transition-all hover:scale-105 active:scale-95">
                <i class="fas fa-magnifying-glass"></i>
            </button>

            <!-- Typeahead dropdown (mobile) -->
            <div id="mobileSearchDropdown" role="listbox" class="hidden absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-50 max-h-[400px] overflow-y-auto custom-scrollbar animate-slideDown">
                <div class="p-3 text-xs text-gray-500">Type to search…</div>
            </div>
        </div>
    </div>
</header>

<!-- File Preview Modal -->
<div id="filePreviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-2 sm:px-4 py-4 sm:py-8">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeFilePreviewModal()"></div>

        <!-- Modal panel -->
        <div class="inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full sm:max-w-lg md:max-w-2xl lg:max-w-4xl max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-3 sm:px-6 py-3 sm:py-4 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i id="filePreviewIcon" class="fas fa-file text-white text-sm sm:text-lg"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 id="filePreviewTitle" class="text-sm sm:text-lg font-bold text-white truncate">File Preview</h3>
                        <p id="filePreviewMeta" class="text-xs text-emerald-100 truncate">Loading...</p>
                    </div>
                </div>
                <button onclick="closeFilePreviewModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-1.5 sm:p-2 transition flex-shrink-0 ml-2">
                    <i class="fas fa-times text-lg sm:text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div id="filePreviewContent" class="bg-white px-3 sm:px-6 py-4 sm:py-6 overflow-y-auto flex-1 custom-scrollbar">
                <div class="flex items-center justify-center py-12 sm:py-20">
                    <i class="fas fa-spinner fa-spin text-emerald-600 text-2xl sm:text-4xl"></i>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gradient-to-r from-gray-50 to-white px-3 sm:px-6 py-3 sm:py-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-0 border-t border-gray-200 flex-shrink-0">
                <a id="filePreviewRecordLink" href="#" class="text-xs sm:text-sm text-emerald-600 hover:text-emerald-700 font-semibold flex items-center justify-center sm:justify-start gap-2 px-3 py-2 sm:p-0 hover:bg-emerald-50 sm:hover:bg-transparent rounded-lg transition-all">
                    <i class="fas fa-folder-open"></i>
                    <span>View Full Record</span>
                </a>
                <div class="flex gap-2">
                    <button id="filePreviewDownload" onclick="downloadFileFromModal()" class="flex-1 sm:flex-none px-4 py-2.5 sm:py-2 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-xl hover:from-emerald-700 hover:to-green-700 shadow-md hover:shadow-lg transition-all font-semibold text-xs sm:text-sm flex items-center justify-center gap-2 hover:scale-105 active:scale-95">
                        <i class="fas fa-download"></i>
                        <span>Download</span>
                    </button>
                    <button onclick="closeFilePreviewModal()" class="flex-1 sm:flex-none px-4 py-2.5 sm:py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 shadow-sm hover:shadow-md transition-all font-semibold text-xs sm:text-sm hover:scale-105 active:scale-95">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add animations -->
<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-12px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}

.animate-slideDown {
    animation: slideDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #cbd5e1, #94a3b8);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #94a3b8, #64748b);
}

/* Search result hover effects */
#globalSearchDropdown a:hover,
#mobileSearchDropdown a:hover {
    transform: translateX(2px);
}

/* Highlight marks */
mark {
    background: linear-gradient(120deg, #fef08a 0%, #fde047 100%);
    padding: 0 2px;
    border-radius: 2px;
    font-weight: 600;
}

/* Tab animation */
[data-tab] {
    position: relative;
    overflow: hidden;
}

[data-tab]::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

[data-tab]:active::before {
    width: 200px;
    height: 200px;
}

/* Status badges with glow */
.bg-green-100 {
    box-shadow: 0 0 8px rgba(34, 197, 94, 0.2);
}

.bg-yellow-100 {
    box-shadow: 0 0 8px rgba(234, 179, 8, 0.2);
}

.bg-emerald-100 {
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.2);
}

.bg-red-100 {
    box-shadow: 0 0 8px rgba(239, 68, 68, 0.2);
}

/* Improved focus states */
#globalSearch:focus,
#mobileSearch:focus {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Loading spinner enhancement */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>

<script>
// Responsive sidebar (mobile drawer)
function initResponsiveSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const menuBtn = document.getElementById('menuToggle');
    const closeBtn = document.getElementById('closeSidebar');

    if (!sidebar || !overlay) return;

    const lgMedia = window.matchMedia('(min-width: 1024px)');

    function lockBodyScroll(lock) {
        // Only lock on small screens; on lg the sidebar is not an overlay.
        if (lgMedia.matches) {
            document.body.style.overflow = '';
            return;
        }
        document.body.style.overflow = lock ? 'hidden' : '';
    }

    function showOverlay() {
        overlay.classList.remove('hidden');
        // ensure opacity transition applies
        requestAnimationFrame(() => overlay.classList.remove('opacity-0'));
    }

    function hideOverlay() {
        overlay.classList.add('opacity-0');
        window.setTimeout(() => overlay.classList.add('hidden'), 300);
    }

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        showOverlay();
        lockBodyScroll(true);
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        hideOverlay();
        lockBodyScroll(false);
    }

    function toggleSidebar() {
        const isClosed = sidebar.classList.contains('-translate-x-full');
        if (isClosed) openSidebar();
        else closeSidebar();
    }

    // Expose for any legacy onclick usage (no-op if unused)
    window.toggleSidebar = toggleSidebar;

    menuBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleSidebar();
    });

    closeBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        closeSidebar();
    });

    overlay.addEventListener('click', () => closeSidebar());

    // Close on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSidebar();
    });

    // When resizing to lg, ensure overlay/scroll state is reset
    lgMedia.addEventListener('change', () => {
        if (lgMedia.matches) {
            overlay.classList.add('hidden');
            overlay.classList.add('opacity-0');
            lockBodyScroll(false);
        } else {
            // On small screens, default to closed to avoid layout jumps
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            overlay.classList.add('opacity-0');
            lockBodyScroll(false);
        }
    });

    // Ensure correct initial state
    if (!lgMedia.matches) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        overlay.classList.add('opacity-0');
        lockBodyScroll(false);
    }

    // Close drawer after tapping a link (mobile)
    sidebar.addEventListener('click', (e) => {
        if (lgMedia.matches) return;
        const link = e.target.closest('a');
        if (link && link.getAttribute('href')) closeSidebar();
    });
}

// Toggle user menu
document.getElementById('userMenuBtn')?.addEventListener('click', () => {
    document.getElementById('userMenuDropdown').classList.toggle('hidden');
});

// Toggle notifications
document.getElementById('notificationBtn')?.addEventListener('click', () => {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('hidden');

    // When opening dropdown, mark as seen locally
    if (!dropdown.classList.contains('hidden')) {
        try {
            const role = '<?= $_SESSION['role'] ?? 'staff' ?>';
            const userId = '<?= (int)($_SESSION['user_id'] ?? 0) ?>';
            const seenKey = `iprepo_notif_seen_${role}_${userId}`;
            localStorage.setItem(seenKey, String(Date.now()));
            // Refresh UI immediately
            window.__refreshNotifications && window.__refreshNotifications();
        } catch (e) {
            // ignore
        }
    }
});

document.getElementById('markAllReadBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    try {
        const role = '<?= $_SESSION['role'] ?? 'staff' ?>';
        const userId = '<?= (int)($_SESSION['user_id'] ?? 0) ?>';
        const seenKey = `iprepo_notif_seen_${role}_${userId}`;
        localStorage.setItem(seenKey, String(Date.now()));
        window.__refreshNotifications && window.__refreshNotifications();
    } catch (err) {
        // ignore
    }
});

// Close dropdowns when clicking outside
document.addEventListener('click', (e) => {
    if (!e.target.closest('#userMenuBtn') && !e.target.closest('#userMenuDropdown')) {
        document.getElementById('userMenuDropdown')?.classList.add('hidden');
    }
    if (!e.target.closest('#notificationBtn') && !e.target.closest('#notificationDropdown')) {
        document.getElementById('notificationDropdown')?.classList.add('hidden');
    }
});

// Header search configuration (used by public/js/header-search.js)
window.IPRepoHeaderSearch = {
    role: '<?= $_SESSION['role'] ?? 'staff' ?>',
    baseUrl: '<?= BASE_URL ?>',
    suggestionsUrl: '<?= BASE_URL ?>/search/suggestions'
};

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

// Working header search (desktop + mobile)
// Keep at end so elements exist and config is set.
try {
    const s = document.createElement('script');
    s.src = '<?= BASE_URL ?>/js/header-search.js?v=<?= filemtime(PUBLIC_PATH . '/js/header-search.js') ?>';
    s.defer = true;
    document.body.appendChild(s);
} catch (e) {
    // ignore
}

// Notifications (header)
(function initHeaderNotifications() {
    const badgeEl = document.getElementById('notificationBadge');
    const listEl = document.getElementById('notificationList');
    if (!badgeEl || !listEl) return;

    const role = '<?= $_SESSION['role'] ?? 'staff' ?>';
    const userId = '<?= (int)($_SESSION['user_id'] ?? 0) ?>';
    const endpoint = role === 'admin'
        ? '<?= BASE_URL ?>/admin/notifications'
        : '<?= BASE_URL ?>/staff/notifications';

    const seenKey = `iprepo_notif_seen_${role}_${userId}`;

    function parseSeenMs() {
        const raw = localStorage.getItem(seenKey);
        const n = raw ? parseInt(raw, 10) : 0;
        return Number.isFinite(n) ? n : 0;
    }

    function timeAgoShort(iso) {
        const t = Date.parse(iso);
        if (!t) return '';
        const s = Math.floor((Date.now() - t) / 1000);
        if (s < 60) return 'just now';
        const m = Math.floor(s / 60);
        if (m < 60) return `${m}m`;
        const h = Math.floor(m / 60);
        if (h < 24) return `${h}h`;
        const d = Math.floor(h / 24);
        return `${d}d`;
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setBadge(count) {
        if (!count || count <= 0) {
            badgeEl.classList.add('hidden');
            badgeEl.textContent = '0';
            return;
        }
        badgeEl.classList.remove('hidden');
        badgeEl.textContent = String(Math.min(99, count));
    }

    function render(items, unreadCount) {
        setBadge(unreadCount);
        if (!items || items.length === 0) {
            listEl.innerHTML = `
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-inbox text-2xl sm:text-3xl mb-2 text-gray-300"></i>
                    <p class="text-sm">No new notifications</p>
                    <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
                </div>
            `;
            return;
        }

        listEl.innerHTML = items.map((n) => {
            const status = (n.status || '').toLowerCase();
            const icon = status === 'approved' ? 'check-circle' : status === 'rejected' ? 'times-circle' : 'clock';
            const color = status === 'approved' ? 'text-emerald-600 bg-emerald-50' : status === 'rejected' ? 'text-red-600 bg-red-50' : 'text-yellow-600 bg-yellow-50';
            const title = escapeHtml(n.title || 'Notification');
            const body = escapeHtml(n.body || '');
            const url = escapeHtml(n.url || '#');
            const when = escapeHtml(timeAgoShort(n.event_at || ''));

            return `
                <a href="${url}" class="block px-3 sm:px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition">
                    <div class="flex gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center ${color}">
                            <i class="fas fa-${icon}"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-800 truncate">${title}</p>
                                <span class="text-xs text-gray-400 flex-shrink-0">${when}</span>
                            </div>
                            <p class="text-xs text-gray-600 mt-0.5 line-clamp-2">${body}</p>
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }

    async function refresh() {
        try {
            const res = await fetch(`${endpoint}?limit=10`, { credentials: 'same-origin' });
            const json = await res.json();
            const items = (json && json.items) ? json.items : [];
            const seenMs = parseSeenMs();

            const unread = items.reduce((acc, n) => {
                const t = Date.parse(n.event_at || '');
                if (t && t > seenMs) return acc + 1;
                return acc;
            }, 0);

            render(items, unread);
        } catch (e) {
            // Fail silently; keep existing UI
        }
    }

    window.__refreshNotifications = refresh;

    // Initial load + light polling
    refresh();
    setInterval(refresh, 45000);
})();

function updateAppHeaderHeight() {
    const headerEl = document.getElementById('appHeader');
    if (!headerEl) return;
    const height = Math.ceil(headerEl.getBoundingClientRect().height);
    document.documentElement.style.setProperty('--app-header-height', `${height}px`);
}

// Init components
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initResponsiveSidebar);
    document.addEventListener('DOMContentLoaded', updateAppHeaderHeight);
} else {
    initResponsiveSidebar();
    updateAppHeaderHeight();
}

// Keep header height in sync (mobile header can change with wrapping)
window.addEventListener('resize', debounce(updateAppHeaderHeight, 100));
window.addEventListener('orientationchange', () => setTimeout(updateAppHeaderHeight, 50));

// One extra tick after load for fonts/layout settling
setTimeout(updateAppHeaderHeight, 0);

// File Preview Modal Functions
let currentFileData = null;

function openFilePreviewModal(fileData) {
    currentFileData = fileData;
    const modal = document.getElementById('filePreviewModal');
    const title = document.getElementById('filePreviewTitle');
    const meta = document.getElementById('filePreviewMeta');
    const icon = document.getElementById('filePreviewIcon');
    const content = document.getElementById('filePreviewContent');
    const recordLink = document.getElementById('filePreviewRecordLink');
    
    if (!modal) return;
    
    // Set title and metadata
    title.textContent = fileData.name || 'File Preview';
    meta.textContent = fileData.recordTitle ? `From: ${fileData.recordTitle}` : '';
    
    // Update icon based on file type
    const ext = (fileData.name || '').split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'ppt': 'fa-file-powerpoint',
        'pptx': 'fa-file-powerpoint',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image',
        'zip': 'fa-file-zipper',
        'rar': 'fa-file-zipper',
        'txt': 'fa-file-lines',
    };
    icon.className = `fas ${iconMap[ext] || 'fa-file'} text-white text-lg`;
    
    // Update record link
    if (fileData.recordId) {
        recordLink.href = fileData.recordLink || '#';
        recordLink.classList.remove('hidden');
    } else {
        recordLink.classList.add('hidden');
    }
    
    // Load file preview
    content.innerHTML = '<div class="flex items-center justify-center py-20"><i class="fas fa-spinner fa-spin text-emerald-600 text-4xl"></i></div>';
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Fetch file details
    fetchFilePreview(fileData);
}

function closeFilePreviewModal() {
    const modal = document.getElementById('filePreviewModal');
    if (!modal) return;
    
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    currentFileData = null;
}

function fetchFilePreview(fileData) {
    const content = document.getElementById('filePreviewContent');
    const role = '<?= $_SESSION['role'] ?? 'staff' ?>';
    const downloadBtn = document.getElementById('filePreviewDownload');
    
    // Check if staff user has existing request
    if (role === 'staff' && fileData.id) {
        checkDownloadRequestStatus(fileData.id).then(status => {
            if (status) {
                updateDownloadButton(status);
            }
        });
    }
    
    // Show file preview based on type
    const ext = (fileData.name || '').split('.').pop().toLowerCase();
    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    const pdfExts = ['pdf'];
    
    if (imageExts.includes(ext)) {
        // Image preview
        content.innerHTML = `
            <div class="text-center">
                <img src="${fileData.url || '#'}" alt="${fileData.name}" class="max-w-full h-auto rounded-lg shadow-lg mx-auto" 
                     onerror="this.parentElement.innerHTML='<div class=\\'text-center text-gray-500 py-10\\'><i class=\\'fas fa-exclamation-triangle text-4xl mb-3 text-yellow-500\\'></i><p>Unable to load image preview</p></div>'">
            </div>
        `;
    } else if (pdfExts.includes(ext)) {
        // PDF preview
        content.innerHTML = `
            <div class="w-full" style="height: 60vh;">
                <iframe src="${fileData.url || '#'}" class="w-full h-full border-0 rounded-lg" 
                        onerror="this.parentElement.innerHTML='<div class=\\'text-center text-gray-500 py-10\\'><i class=\\'fas fa-exclamation-triangle text-4xl mb-3 text-yellow-500\\'></i><p>Unable to load PDF preview</p></div>'">
                </iframe>
            </div>
        `;
    } else {
        // File information view
        content.innerHTML = `
            <div class="text-center py-10">
                <div class="w-20 h-20 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas ${getFileIcon(ext)} text-emerald-600 text-4xl"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-800 mb-2">${fileData.name || 'Unknown File'}</h4>
                <p class="text-sm text-gray-600 mb-4">File Type: ${ext.toUpperCase() || 'Unknown'}</p>
                ${fileData.size ? `<p class="text-sm text-gray-500 mb-4">Size: ${formatFileSize(fileData.size)}</p>` : ''}
                ${fileData.recordTitle ? `
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg inline-block">
                        <p class="text-xs text-gray-500 mb-1">Associated Record</p>
                        <p class="text-sm font-medium text-gray-800">${fileData.recordTitle}</p>
                    </div>
                ` : ''}
                <div class="mt-8">
                    <button onclick="downloadFileFromModal()" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium flex items-center gap-2 mx-auto">
                        <i class="fas fa-download"></i>
                        Download File
                    </button>
                </div>
            </div>
        `;
    }
}

async function checkDownloadRequestStatus(documentId) {
    try {
        const response = await fetch(`<?= BASE_URL ?>/staff/check-download-status?document_id=${documentId}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Failed to check download status:', error);
        return null;
    }
}

function updateDownloadButton(status) {
    const downloadBtn = document.getElementById('filePreviewDownload');
    if (!downloadBtn) return;
    
    if (status.has_pending) {
        downloadBtn.innerHTML = '<i class="fas fa-clock mr-2"></i>Request Pending';
        downloadBtn.className = 'px-4 py-2 bg-yellow-500 text-white rounded-lg cursor-not-allowed transition font-medium text-sm flex items-center gap-2';
        downloadBtn.disabled = true;
        downloadBtn.title = 'You have a pending download request for this file';
    } else if (status.has_approved) {
        downloadBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Approved - Download';
        downloadBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium text-sm flex items-center gap-2';
        downloadBtn.disabled = false;
        downloadBtn.title = 'Download approved - click to download';
        // Update onclick to use approved download link
        downloadBtn.onclick = () => {
            window.open(status.download_url, '_blank');
        };
    }
}

function getFileIcon(ext) {
    const iconMap = {
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'ppt': 'fa-file-powerpoint',
        'pptx': 'fa-file-powerpoint',
        'jpg': 'fa-file-image',
        'jpeg': 'fa-file-image',
        'png': 'fa-file-image',
        'gif': 'fa-file-image',
        'zip': 'fa-file-zipper',
        'rar': 'fa-file-zipper',
        'txt': 'fa-file-lines',
    };
    return iconMap[ext] || 'fa-file';
}

function formatFileSize(bytes) {
    if (!bytes) return 'Unknown';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    if (bytes < 1024 * 1024 * 1024) return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
}

async function downloadFileFromModal() {
    if (!currentFileData || !currentFileData.id) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'File information not available'
        });
        return;
    }
    
    const role = '<?= $_SESSION['role'] ?? 'staff' ?>';
    
    if (role === 'admin') {
        // Admin can download directly
        window.open(`<?= BASE_URL ?>/document/preview/${currentFileData.id}?download=1`, '_blank');
    } else {
        // Staff needs to request download permission
        const { value: reason } = await Swal.fire({
            title: 'Request Download Permission',
            html: `
                <div class="text-left mb-4">
                    <p class="text-sm text-gray-600 mb-3">You need permission to download this file:</p>
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <p class="text-sm font-semibold text-gray-800">${currentFileData.name}</p>
                        ${currentFileData.recordTitle ? `<p class="text-xs text-gray-500 mt-1">From: ${currentFileData.recordTitle}</p>` : ''}
                    </div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for download request:</label>
                </div>
            `,
            input: 'textarea',
            inputPlaceholder: 'Please explain why you need to download this file...',
            inputAttributes: {
                'aria-label': 'Download reason',
                'class': 'swal2-textarea',
                'rows': 4
            },
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-paper-plane mr-2"></i>Submit Request',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a reason for your download request';
                }
                if (value.length < 10) {
                    return 'Please provide a more detailed reason (at least 10 characters)';
                }
            },
            showLoaderOnConfirm: true,
            preConfirm: async (reason) => {
                try {
                    const formData = new FormData();
                    formData.append('document_id', currentFileData.id);
                    if (currentFileData.recordId) {
                        formData.append('ip_record_id', currentFileData.recordId);
                    }
                    formData.append('reason', reason);
                    
                    const response = await fetch('<?= BASE_URL ?>/staff/request-download', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    
                    const data = await response.json();
                    
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to submit request');
                    }
                    
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(`Request failed: ${error.message}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
        
        if (reason) {
            // Request submitted successfully
            Swal.fire({
                icon: 'success',
                title: 'Request Submitted!',
                html: `
                    <p class="text-sm text-gray-600 mb-3">Your download request has been submitted successfully.</p>
                    <div class="bg-emerald-50 p-4 rounded-lg mb-3">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-emerald-600 text-2xl"></i>
                            <div class="text-left">
                                <p class="text-sm font-semibold text-gray-800 mb-1">What happens next?</p>
                                <ul class="text-xs text-gray-600 space-y-1">
                                    <li>• An administrator will review your request</li>
                                    <li>• You'll be notified when it's approved</li>
                                    <li>• Check "My Requests" to track status</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Got it!'
            }).then(() => {
                // Close the preview modal
                closeFilePreviewModal();
            });
        }
    }
}

// Close modal on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeFilePreviewModal();
    }
});

// Expose function globally for search results
window.openFilePreviewModal = openFilePreviewModal;

// Logout confirmation
function confirmLogout(event) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Logout Confirmation',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-sign-out-alt mr-2"></i>Yes, Logout',
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancel',
        reverseButtons: true,
        backdrop: true,
        allowOutsideClick: false,
        customClass: {
            confirmButton: 'px-6 py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg transition-all',
            cancelButton: 'px-6 py-2.5 rounded-lg font-semibold shadow-md hover:shadow-lg transition-all'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Logging out...',
                text: 'Please wait',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Clear browser storage
            localStorage.clear();
            sessionStorage.clear();
            
            // Perform logout and refresh
            performLogoutAndRefresh();
        }
    });
}

// Function to logout and force browser refresh
function performLogoutAndRefresh() {
    // Use replace to prevent back button issues
    window.location.replace('<?= BASE_URL ?>/logout');
    
    // Force reload after a brief delay (backup refresh)
    setTimeout(() => {
        window.location.reload(true);
    }, 100);
}
</script>
