<!-- Top Header -->
<header id="appHeader" class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
    <div class="flex items-center justify-between px-3 sm:px-4 lg:px-6 py-2 sm:py-2.5 lg:py-3">
        <!-- Mobile Menu Button -->
        <button id="menuToggle" class="lg:hidden text-gray-600 hover:text-gray-900 p-1.5 hover:bg-gray-100 rounded-lg transition-colors">
            <i class="fas fa-bars text-lg sm:text-xl"></i>
        </button>

        <!-- Page Title (Mobile) -->
        <h1 class="text-base sm:text-lg font-bold text-gray-800 lg:hidden truncate"><?= $pageTitle ?? 'Dashboard' ?></h1>

        <!-- Search Bar (Desktop) -->
        <div class="hidden lg:flex flex-1 max-w-xl xl:max-w-2xl">
            <div class="relative w-full">
                <input type="text" 
                       id="globalSearch" 
                       placeholder="Search IP records, documents, users..." 
                       class="w-full pl-9 pr-10 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                <button id="globalSearchBtn" type="button" aria-label="Search"
                        class="absolute right-1.5 top-1.5 inline-flex items-center justify-center w-8 h-8 rounded-md text-emerald-700 hover:bg-emerald-50 transition">
                    <i class="fas fa-magnifying-glass"></i>
                </button>

                <!-- Typeahead banner -->
                <div id="globalSearchDropdown" class="hidden absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50">
                    <div class="p-3 text-xs text-gray-500">Type to search…</div>
                </div>
            </div>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center space-x-1 sm:space-x-2 lg:space-x-3">
            <!-- Quick Actions (Desktop) -->
            <button class="hidden xl:flex items-center px-2.5 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors" onclick="window.location.href='<?= BASE_URL ?>/admin/ip-records/create'">
                <i class="fas fa-plus mr-1.5"></i> New Record
            </button>
            
            <!-- Notifications -->
            <div class="relative">
                <button id="notificationBtn" class="relative p-1.5 sm:p-2 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                    <i class="fas fa-bell text-base sm:text-lg"></i>
                    <span id="notificationBadge" class="absolute -top-0.5 -right-0.5 w-4 h-4 sm:w-5 sm:h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center shadow-md hidden animate-pulse">0</span>
                </button>
                
                <!-- Notification Dropdown -->
                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 animate-fadeIn">
                    <div class="flex items-center justify-between p-3 sm:p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-sm sm:text-base text-gray-800">Notifications</h3>
                        <button id="markAllReadBtn" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Mark all read</button>
                    </div>
                    <div id="notificationList" class="max-h-80 overflow-y-auto custom-scrollbar">
                        <div class="p-6 text-center text-gray-500">
                            <i class="fas fa-inbox text-2xl sm:text-3xl mb-2 text-gray-300"></i>
                            <p class="text-sm">No new notifications</p>
                            <p class="text-xs text-gray-400 mt-1">You're all caught up!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="relative">
                <button id="userMenuBtn" class="flex items-center space-x-1.5 sm:space-x-2 p-1.5 sm:p-2 hover:bg-emerald-50 rounded-lg transition-colors group">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                        <span class="text-xs sm:text-sm font-bold text-white"><?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?></span>
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-800 leading-tight"><?= $_SESSION['full_name'] ?? 'User' ?></p>
                        <p class="text-xs text-gray-500 leading-tight"><?= ucfirst($_SESSION['role'] ?? 'staff') ?></p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-600 text-xs group-hover:text-emerald-600 transition-colors"></i>
                </button>

                <!-- User Dropdown -->
                <div id="userMenuDropdown" class="hidden absolute right-0 mt-2 w-56 sm:w-64 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 animate-fadeIn">
                    <div class="p-3 sm:p-4 border-b border-gray-200 bg-gradient-to-br from-emerald-50 to-green-50">
                        <p class="font-semibold text-sm text-gray-800 truncate"><?= $_SESSION['full_name'] ?? 'User' ?></p>
                        <p class="text-xs text-gray-600 truncate mt-0.5"><?= $_SESSION['email'] ?? '' ?></p>
                        <span class="inline-block mt-2 px-2.5 py-0.5 bg-emerald-100 text-emerald-800 text-xs rounded-full font-medium">
                            <i class="fas fa-shield-alt mr-1"></i><?= ucfirst($_SESSION['role'] ?? 'staff') ?>
                        </span>
                    </div>
                    <div class="py-1.5">
                        <a href="<?= BASE_URL ?>/profile" class="flex items-center px-3 sm:px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <i class="fas fa-user w-5 text-sm"></i>
                            <span class="ml-2">My Profile</span>
                        </a>
                        <a href="<?= BASE_URL ?>/settings" class="flex items-center px-3 sm:px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <i class="fas fa-cog w-5 text-sm"></i>
                            <span class="ml-2">Settings</span>
                        </a>
                        <a href="<?= BASE_URL ?>/help" class="flex items-center px-3 sm:px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <i class="fas fa-question-circle w-5 text-sm"></i>
                            <span class="ml-2">Help & Support</span>
                        </a>
                        <hr class="my-1.5">
                        <a href="#" onclick="confirmLogout(event)" class="flex items-center px-3 sm:px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt w-5 text-sm"></i>
                            <span class="ml-2 font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Search Bar -->
    <div class="lg:hidden px-3 sm:px-4 pb-2.5">
        <div class="relative">
            <input type="text" 
                   id="mobileSearch"
                   placeholder="Search..." 
                   class="w-full pl-9 pr-10 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 transition-all">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
            <button id="mobileSearchBtn" type="button" aria-label="Search"
                    class="absolute right-1.5 top-1.5 inline-flex items-center justify-center w-8 h-8 rounded-md text-emerald-700 hover:bg-emerald-50 transition">
                <i class="fas fa-magnifying-glass"></i>
            </button>

            <!-- Typeahead banner (mobile) -->
            <div id="mobileSearchDropdown" class="hidden absolute left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50">
                <div class="p-3 text-xs text-gray-500">Type to search…</div>
            </div>
        </div>
    </div>
</header>

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

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
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
