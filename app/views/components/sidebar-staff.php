<!-- Staff Sidebar Navigation -->
<aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-indigo-900 to-indigo-800 text-white transform transition-transform duration-300 ease-in-out z-30 lg:translate-x-0">
    <!-- Logo -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-indigo-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <h2 class="font-bold text-lg">IP Repository</h2>
                <p class="text-xs text-indigo-300">Staff Portal</p>
            </div>
        </div>
        <button id="closeSidebar" class="lg:hidden text-indigo-400 hover:text-white">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="<?= BASE_URL ?>/staff/dashboard" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-indigo-700 transition <?= strpos($_SERVER['REQUEST_URI'], '/staff/dashboard') !== false ? 'bg-indigo-700 border-l-4 border-indigo-400' : '' ?>">
                <i class="fas fa-home w-6"></i>
                <span class="ml-3">Dashboard</span>
            </a>

            <!-- Browse -->
            <div class="nav-group">
                <div class="nav-group-title px-4 py-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                    Browse
                </div>
                <a href="<?= BASE_URL ?>/staff/ip-records" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-indigo-700 transition <?= strpos($_SERVER['REQUEST_URI'], '/staff/ip-records') !== false ? 'bg-indigo-700 border-l-4 border-indigo-400' : '' ?>">
                    <i class="fas fa-folder-open w-6"></i>
                    <span class="ml-3">IP Records</span>
                </a>
                <a href="<?= BASE_URL ?>/staff/search" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-indigo-700 transition <?= strpos($_SERVER['REQUEST_URI'], '/staff/search') !== false ? 'bg-indigo-700 border-l-4 border-indigo-400' : '' ?>">
                    <i class="fas fa-search w-6"></i>
                    <span class="ml-3">Search</span>
                </a>
            </div>

            <!-- My Requests -->
            <div class="nav-group mt-4">
                <div class="nav-group-title px-4 py-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                    Downloads
                </div>
                <a href="<?= BASE_URL ?>/staff/my-requests" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-indigo-700 transition <?= strpos($_SERVER['REQUEST_URI'], '/my-requests') !== false ? 'bg-indigo-700 border-l-4 border-indigo-400' : '' ?>">
                    <i class="fas fa-download w-6"></i>
                    <span class="ml-3">My Requests</span>
                    <?php if (isset($myPendingCount) && $myPendingCount > 0): ?>
                        <span class="ml-auto bg-yellow-500 text-white text-xs rounded-full px-2 py-1"><?= $myPendingCount ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Help -->
            <div class="nav-group mt-4">
                <div class="nav-group-title px-4 py-2 text-xs font-semibold text-indigo-300 uppercase tracking-wider">
                    Support
                </div>
                <a href="<?= BASE_URL ?>/staff/help" class="nav-item flex items-center px-4 py-3 rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-question-circle w-6"></i>
                    <span class="ml-3">Help & Guide</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- User Info -->
    <div class="border-t border-indigo-700 p-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-sm font-bold"><?= strtoupper(substr($_SESSION['full_name'] ?? 'S', 0, 1)) ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate"><?= $_SESSION['full_name'] ?? 'Staff User' ?></p>
                <p class="text-xs text-indigo-300 truncate"><?= $_SESSION['email'] ?? '' ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden"></div>
