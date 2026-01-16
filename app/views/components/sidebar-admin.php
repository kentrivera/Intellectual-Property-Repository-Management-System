<?php
// Helper function to check active state
if (!function_exists('isActiveLink')) {
    function isActiveLink($path) {
        return strpos($_SERVER['REQUEST_URI'], $path) !== false;
    }
}
?>

<!-- Admin Sidebar Navigation -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-72 bg-slate-900 text-slate-300 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 shadow-xl flex flex-col h-full border-r border-slate-800">
    <!-- Brand Logo -->
    <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950/50">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="flex items-center gap-3 group">
            <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg shadow-lg shadow-emerald-500/30 flex items-center justify-center transform group-hover:scale-105 transition-transform duration-300">
                <i class="fas fa-shield-alt text-white text-lg"></i>
            </div>
            <div>
                <h2 class="font-bold text-lg text-white tracking-tight leading-tight">IP Repository</h2>
                <p class="text-[10px] uppercase tracking-wider text-emerald-400 font-semibold">Admin Portal</p>
            </div>
        </a>
        <button id="closeSidebar" class="lg:hidden ml-auto text-slate-400 hover:text-white transition-colors">
            <i class="fas fa-arrow-left text-xl"></i>
        </button>
    </div>

    <!-- Scrollable Navigation -->
    <div class="flex-1 overflow-y-auto px-4 py-6 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
        <nav class="space-y-6">
            
            <!-- Dashboard -->
            <div>
                <a href="<?= BASE_URL ?>/admin/dashboard" 
                   class="flex items-center px-4 py-3 rounded-xl transition-all duration-200 group relative overflow-hidden <?= isActiveLink('/admin/dashboard') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/40 font-medium' : 'hover:bg-slate-800/80 hover:text-white' ?>">
                    <?php if (isActiveLink('/admin/dashboard')): ?>
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-green-600 opacity-100"></div>
                    <?php else: ?>
                        <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500 rounded-r-md opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <?php endif; ?>
                    
                    <i class="fas fa-home w-6 <?= isActiveLink('/admin/dashboard') ? 'text-emerald-100 relative z-10' : 'text-slate-400 group-hover:text-emerald-400 transition-colors' ?>"></i>
                    <span class="ml-3 relative z-10">Dashboard</span>
                </a>
            </div>

            <!-- IP Management Group -->
            <div>
                <h3 class="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center">
                    <span class="flex-1">IP Repository</span>
                    <i class="fas fa-folder-tree text-[10px]"></i>
                </h3>
                <div class="space-y-1">
                    <a href="<?= BASE_URL ?>/admin/ip-records" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group <?= isActiveLink('/admin/ip-records') ? 'bg-slate-800/80 text-white' : 'hover:bg-slate-800/50 hover:text-white' ?>">
                        <div class="w-6 flex justify-center">
                            <i class="fas fa-database text-sm <?= isActiveLink('/admin/ip-records') ? 'text-emerald-400' : 'text-slate-500 group-hover:text-emerald-400 transition-colors' ?>"></i>
                        </div>
                        <span class="ml-3 text-sm font-medium">All Records</span>
                        <?php if (isset($totalRecords) && $totalRecords > 0): ?>
                            <span class="ml-auto bg-slate-700 text-slate-300 py-0.5 px-2 rounded-full text-[10px]"><?= $totalRecords ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="<?= BASE_URL ?>/admin/download-requests" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group <?= isActiveLink('/download-requests') ? 'bg-slate-800/80 text-white' : 'hover:bg-slate-800/50 hover:text-white' ?>">
                        <div class="w-6 flex justify-center">
                            <i class="fas fa-file-download text-sm <?= isActiveLink('/download-requests') ? 'text-amber-400' : 'text-slate-500 group-hover:text-amber-400 transition-colors' ?>"></i>
                        </div>
                        <span class="ml-3 text-sm font-medium">Requests</span>
                        <?php if (isset($pendingRequestsCount) && $pendingRequestsCount > 0): ?>
                            <span class="ml-auto animate-pulse bg-amber-500/10 text-amber-500 border border-amber-500/20 py-0.5 px-2 rounded-full text-[10px] font-bold"><?= $pendingRequestsCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Admin Tools -->
            <div>
                <h3 class="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center">
                    <span class="flex-1">Administration</span>
                    <i class="fas fa-users-cog text-[10px]"></i>
                </h3>
                <div class="space-y-1">
                    <a href="<?= BASE_URL ?>/admin/users" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group <?= isActiveLink('/admin/users') ? 'bg-slate-800/80 text-white' : 'hover:bg-slate-800/50 hover:text-white' ?>">
                        <div class="w-6 flex justify-center">
                            <i class="fas fa-users text-sm <?= isActiveLink('/admin/users') ? 'text-purple-400' : 'text-slate-500 group-hover:text-purple-400 transition-colors' ?>"></i>
                        </div>
                        <span class="ml-3 text-sm font-medium">User Management</span>
                    </a>
                    
                    <a href="<?= BASE_URL ?>/admin/activity-logs" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group <?= isActiveLink('/activity-logs') ? 'bg-slate-800/80 text-white' : 'hover:bg-slate-800/50 hover:text-white' ?>">
                        <div class="w-6 flex justify-center">
                            <i class="fas fa-history text-sm <?= isActiveLink('/activity-logs') ? 'text-emerald-400' : 'text-slate-500 group-hover:text-emerald-400 transition-colors' ?>"></i>
                        </div>
                        <span class="ml-3 text-sm font-medium">Activity Logs</span>
                    </a>

                    <a href="<?= BASE_URL ?>/admin/trash" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group <?= isActiveLink('/admin/trash') ? 'bg-slate-800/80 text-white' : 'hover:bg-slate-800/50 hover:text-white' ?>">
                        <div class="w-6 flex justify-center">
                            <i class="fas fa-trash-alt text-sm <?= isActiveLink('/admin/trash') ? 'text-red-400' : 'text-slate-500 group-hover:text-red-400 transition-colors' ?>"></i>
                        </div>
                        <span class="ml-3 text-sm font-medium">Recycle Bin</span>
                    </a>
                </div>
            </div>

            <!-- System Info -->
            <div>
                <h3 class="px-4 text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center">
                    <span class="flex-1">System</span>
                    <i class="fas fa-server text-[10px]"></i>
                </h3>
                <div class="space-y-1">
                    <a href="<?= BASE_URL ?>/admin/reports" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group <?= isActiveLink('/admin/reports') ? 'bg-slate-800/80 text-white' : 'hover:bg-slate-800/50 hover:text-white' ?>">
                        <div class="w-6 flex justify-center">
                            <i class="fas fa-chart-pie text-sm <?= isActiveLink('/admin/reports') ? 'text-pink-400' : 'text-slate-500 group-hover:text-pink-400 transition-colors' ?>"></i>
                        </div>
                        <span class="ml-3 text-sm font-medium">Analytics & Reports</span>
                    </a>

                    <a href="<?= BASE_URL ?>/admin/settings" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all duration-200 group <?= isActiveLink('/admin/settings') ? 'bg-slate-800/80 text-white' : 'hover:bg-slate-800/50 hover:text-white' ?>">
                        <div class="w-6 flex justify-center">
                            <i class="fas fa-cogs text-sm <?= isActiveLink('/admin/settings') ? 'text-cyan-400' : 'text-slate-500 group-hover:text-cyan-400 transition-colors' ?>"></i>
                        </div>
                        <span class="ml-3 text-sm font-medium">Settings</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    <!-- User Profile Strip -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/30">
        <div class="relative group">
            <button class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-slate-800 transition-colors">
                <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center shadow-lg text-white font-bold border-2 border-slate-700">
                        <?= strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div class="flex-1 text-left overflow-hidden">
                    <p class="text-sm font-semibold text-white truncate"><?= $_SESSION['full_name'] ?? 'Admin User' ?></p>
                    <p class="text-[10px] text-slate-400 truncate uppercase tracking-wider">Administrator</p>
                </div>
                <i class="fas fa-chevron-up text-xs text-slate-500 group-hover:text-white transition-colors"></i>
            </button>
            
            <!-- Pop-up Menu -->
            <div class="absolute bottom-full left-0 w-full mb-2 bg-slate-800 rounded-xl shadow-2xl border border-slate-700 invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 transform translate-y-2 group-hover:translate-y-0">
                <a href="<?= BASE_URL ?>/admin/settings" class="block px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-700 hover:text-white first:rounded-t-xl transition-colors">
                    <i class="fas fa-user-circle mr-2 w-4"></i> Profile
                </a>
                <div class="h-px bg-slate-700 my-0"></div>
                <a href="#" onclick="confirmLogout(event)" class="block px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 last:rounded-b-xl transition-colors">
                    <i class="fas fa-sign-out-alt mr-2 w-4"></i> Logout
                </a>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-30 lg:hidden hidden transition-opacity duration-300 opacity-0" onclick="toggleSidebar()"></div>

<script>
    // Initialize Sidebar State
    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const menuBtn = document.getElementById('menuToggle');
        const closeBtn = document.getElementById('closeSidebar');

        function open() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => overlay.classList.remove('opacity-0'), 10);
        }

        function close() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('opacity-0');
            setTimeout(() => overlay.classList.add('hidden'), 300);
        }

        if (menuBtn) menuBtn.onclick = open;
        if (closeBtn) closeBtn.onclick = close;
        window.toggleSidebar = () => {
             if (sidebar.classList.contains('-translate-x-full')) open(); else close();
        };
    }

    // Run on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }

    // Logout confirmation function
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