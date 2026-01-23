<!-- Staff Sidebar Navigation -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-[85vw] max-w-72 lg:w-72 bg-gradient-to-b from-emerald-950 via-emerald-900 to-emerald-800 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50">
    <!-- Logo -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-emerald-800/70">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-emerald-600/90 rounded-xl flex items-center justify-center shadow-sm ring-1 ring-white/10">
                <i class="fas fa-leaf text-lg"></i>
            </div>
            <div>
                <h2 class="font-bold text-lg">IP Repository</h2>
                <p class="text-xs text-emerald-200/90">Staff Portal</p>
            </div>
        </div>
        <button id="closeSidebar" class="lg:hidden text-emerald-200/80 hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="<?= BASE_URL ?>/staff/dashboard" class="nav-item flex items-center px-4 py-3 rounded-xl hover:bg-emerald-800/60 transition <?= strpos($_SERVER['REQUEST_URI'], '/staff/dashboard') !== false ? 'bg-emerald-800/60 border-l-4 border-emerald-300' : '' ?>">
                <i class="fas fa-home w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>

            <!-- Browse -->
            <div class="nav-group">
                <div class="nav-group-title px-4 py-2 text-xs font-semibold text-emerald-200/80 uppercase tracking-wider">
                    Browse
                </div>
                <a href="<?= BASE_URL ?>/staff/ip-records" class="nav-item flex items-center px-4 py-3 rounded-xl hover:bg-emerald-800/60 transition <?= strpos($_SERVER['REQUEST_URI'], '/staff/ip-records') !== false ? 'bg-emerald-800/60 border-l-4 border-emerald-300' : '' ?>">
                    <i class="fas fa-folder-open w-6"></i>
                    <span class="ml-3">IP Records</span>
                </a>
            </div>

            <!-- My Requests -->
            <div class="nav-group mt-4">
                <div class="nav-group-title px-4 py-2 text-xs font-semibold text-emerald-200/80 uppercase tracking-wider">
                    Downloads
                </div>
                <a href="<?= BASE_URL ?>/staff/my-requests" class="nav-item flex items-center px-4 py-3 rounded-xl hover:bg-emerald-800/60 transition <?= strpos($_SERVER['REQUEST_URI'], '/my-requests') !== false ? 'bg-emerald-800/60 border-l-4 border-emerald-300' : '' ?>">
                    <i class="fas fa-download w-6"></i>
                    <span class="ml-3">My Requests</span>
                    <?php if (isset($myPendingCount) && $myPendingCount > 0): ?>
                        <span class="ml-auto bg-yellow-400/90 text-emerald-950 text-xs rounded-full px-2 py-1 font-bold"><?= $myPendingCount ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Help -->
            <div class="nav-group mt-4">
                <div class="nav-group-title px-4 py-2 text-xs font-semibold text-emerald-200/80 uppercase tracking-wider">
                    Support
                </div>
                <a href="<?= BASE_URL ?>/staff/help" class="nav-item flex items-center px-4 py-3 rounded-xl hover:bg-emerald-800/60 transition <?= strpos($_SERVER['REQUEST_URI'], '/staff/help') !== false ? 'bg-emerald-800/60 border-l-4 border-emerald-300' : '' ?>">
                    <i class="fas fa-question-circle w-6"></i>
                    <span class="ml-3">Help & Guide</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- User Info -->
    <div class="border-t border-emerald-800/70 p-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center ring-1 ring-white/10">
                <span class="text-sm font-bold"><?= strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)) ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?= $_SESSION['full_name'] ?? 'Staff User' ?></p>
                <p class="text-xs text-emerald-200/80 truncate"><?= $_SESSION['email'] ?? '' ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden hidden transition-opacity duration-300 opacity-0"></div>
