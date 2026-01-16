<?php ob_start(); ?>

<!-- Statistics Cards with Animation - Green Palette -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6">
    <!-- Total Users Card -->
    <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg sm:rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-4 sm:p-5 lg:p-6 text-white transform hover:scale-105 hover:-translate-y-1 cursor-pointer card-animate" onclick="window.location.href='<?= BASE_URL ?>/admin/users'">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-green-100 text-xs sm:text-xs font-semibold uppercase tracking-wide mb-1">Total Users</p>
                <p class="text-3xl sm:text-4xl font-bold mt-1 sm:mt-2 counter" data-target="<?= $stats['total_users'] ?>">0</p>
                <p class="text-green-100 text-xs mt-1 sm:mt-2 flex items-center">
                    <i class="fas fa-arrow-up mr-1"></i> <span class="hidden sm:inline">View all users</span><span class="sm:hidden">View all</span>
                </p>
            </div>
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-users text-white text-xl sm:text-2xl"></i>
            </div>
        </div>
    </div>
    
    <!-- IP Records Card -->
    <div class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-lg sm:rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-4 sm:p-5 lg:p-6 text-white transform hover:scale-105 hover:-translate-y-1 cursor-pointer card-animate" onclick="window.location.href='<?= BASE_URL ?>/admin/ip-records'">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-cyan-100 text-xs sm:text-xs font-semibold uppercase tracking-wide mb-1">IP Records</p>
                <p class="text-3xl sm:text-4xl font-bold mt-1 sm:mt-2 counter" data-target="<?= $stats['total_records'] ?>">0</p>
                <p class="text-cyan-100 text-xs mt-1 sm:mt-2 flex items-center">
                    <i class="fas fa-folder-open mr-1"></i> <span class="hidden sm:inline">Manage records</span><span class="sm:hidden">Manage</span>
                </p>
            </div>
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-database text-white text-xl sm:text-2xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Documents Card -->
    <div class="bg-gradient-to-br from-lime-500 to-green-500 rounded-lg sm:rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-4 sm:p-5 lg:p-6 text-white transform hover:scale-105 hover:-translate-y-1 cursor-pointer card-animate" onclick="window.location.href='<?= BASE_URL ?>/admin/ip-records'">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-lime-100 text-xs sm:text-xs font-semibold uppercase tracking-wide mb-1">Documents</p>
                <p class="text-3xl sm:text-4xl font-bold mt-1 sm:mt-2 counter" data-target="<?= $stats['total_documents'] ?>">0</p>
                <p class="text-lime-100 text-xs mt-1 sm:mt-2 flex items-center">
                    <i class="fas fa-file-alt mr-1"></i> <span class="hidden sm:inline">Browse docs</span><span class="sm:hidden">Browse</span>
                </p>
            </div>
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-file-pdf text-white text-xl sm:text-2xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Pending Requests Card -->
    <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg sm:rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-4 sm:p-5 lg:p-6 text-white transform hover:scale-105 hover:-translate-y-1 cursor-pointer card-animate" onclick="window.location.href='<?= BASE_URL ?>/admin/download-requests'">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-amber-100 text-xs sm:text-xs font-semibold uppercase tracking-wide mb-1">Pending Requests</p>
                <p class="text-3xl sm:text-4xl font-bold mt-1 sm:mt-2 counter" data-target="<?= $stats['pending_requests'] ?>">0</p>
                <p class="text-amber-100 text-xs mt-1 sm:mt-2 flex items-center">
                    <?php if ($stats['pending_requests'] > 0): ?>
                        <span class="animate-pulse"><i class="fas fa-exclamation-circle mr-1"></i> <span class="hidden sm:inline">Action needed</span><span class="sm:hidden">Action</span></span>
                    <?php else: ?>
                        <i class="fas fa-check-circle mr-1"></i> <span class="hidden sm:inline">All clear</span><span class="sm:hidden">Clear</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                <i class="fas fa-clock text-white text-xl sm:text-2xl <?= $stats['pending_requests'] > 0 ? 'animate-pulse' : '' ?>"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats & Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6">
    <!-- IP Statistics Chart -->
    <div class="lg:col-span-2 bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-5 lg:p-6 hover:shadow-xl transition-shadow duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center mb-2 sm:mb-0">
                <span class="w-1 h-5 sm:h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-2 sm:mr-3"></span>
                <span class="text-sm sm:text-base">IP Record Statistics</span>
            </h3>
            <button onclick="refreshStats()" class="text-xs sm:text-sm text-emerald-600 hover:text-emerald-700 font-medium flex items-center transition-colors self-start sm:self-auto">
                <i class="fas fa-sync-alt mr-1 sm:mr-1.5"></i> Refresh
            </button>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3">
            <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-yellow-50 to-amber-100 rounded-lg sm:rounded-xl border border-yellow-200 hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterByStatus('pending')">
                <div class="text-2xl sm:text-3xl font-bold text-yellow-700 mb-1 counter" data-target="<?= $stats['ip_stats']['pending'] ?? 0 ?>">0</div>
                <div class="text-xs font-semibold text-yellow-600 uppercase tracking-wide">Pending</div>
                <div class="mt-1.5 sm:mt-2 w-full bg-yellow-200 rounded-full h-1.5">
                    <div class="bg-yellow-500 h-1.5 rounded-full transition-all duration-1000 stat-bar" data-percent="<?= ($stats['total_records'] > 0) ? round(($stats['ip_stats']['pending'] ?? 0) / $stats['total_records'] * 100) : 0 ?>" style="width: 0%"></div>
                </div>
            </div>
            
            <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-emerald-50 to-green-100 rounded-lg sm:rounded-xl border border-emerald-200 hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterByStatus('approved')">
                <div class="text-2xl sm:text-3xl font-bold text-emerald-700 mb-1 counter" data-target="<?= $stats['ip_stats']['approved'] ?? 0 ?>">0</div>
                <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">Approved</div>
                <div class="mt-1.5 sm:mt-2 w-full bg-emerald-200 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-1000 stat-bar" data-percent="<?= ($stats['total_records'] > 0) ? round(($stats['ip_stats']['approved'] ?? 0) / $stats['total_records'] * 100) : 0 ?>" style="width: 0%"></div>
                </div>
            </div>
            
            <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-teal-50 to-cyan-100 rounded-lg sm:rounded-xl border border-teal-200 hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterByStatus('active')">
                <div class="text-2xl sm:text-3xl font-bold text-teal-700 mb-1 counter" data-target="<?= $stats['ip_stats']['active'] ?? 0 ?>">0</div>
                <div class="text-xs font-semibold text-teal-600 uppercase tracking-wide">Active</div>
                <div class="mt-1.5 sm:mt-2 w-full bg-teal-200 rounded-full h-1.5">
                    <div class="bg-teal-500 h-1.5 rounded-full transition-all duration-1000 stat-bar" data-percent="<?= ($stats['total_records'] > 0) ? round(($stats['ip_stats']['active'] ?? 0) / $stats['total_records'] * 100) : 0 ?>" style="width: 0%"></div>
                </div>
            </div>
            
            <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg sm:rounded-xl border border-red-200 hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterByStatus('rejected')">
                <div class="text-2xl sm:text-3xl font-bold text-red-700 mb-1 counter" data-target="<?= $stats['ip_stats']['rejected'] ?? 0 ?>">0</div>
                <div class="text-xs font-semibold text-red-600 uppercase tracking-wide">Rejected</div>
                <div class="mt-1.5 sm:mt-2 w-full bg-red-200 rounded-full h-1.5">
                    <div class="bg-red-500 h-1.5 rounded-full transition-all duration-1000 stat-bar" data-percent="<?= ($stats['total_records'] > 0) ? round(($stats['ip_stats']['rejected'] ?? 0) / $stats['total_records'] * 100) : 0 ?>" style="width: 0%"></div>
                </div>
            </div>
            
            <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-gray-50 to-slate-100 rounded-lg sm:rounded-xl border border-gray-200 hover:shadow-md transition-all duration-200 cursor-pointer" onclick="filterByStatus('expired')">
                <div class="text-2xl sm:text-3xl font-bold text-gray-700 mb-1 counter" data-target="<?= $stats['ip_stats']['expired'] ?? 0 ?>">0</div>
                <div class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Expired</div>
                <div class="mt-1.5 sm:mt-2 w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-gray-500 h-1.5 rounded-full transition-all duration-1000 stat-bar" data-percent="<?= ($stats['total_records'] > 0) ? round(($stats['ip_stats']['expired'] ?? 0) / $stats['total_records'] * 100) : 0 ?>" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

<!-- IP Statistics -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">IP Record Statistics</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Pending</span>
                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                    <?= $stats['ip_stats']['pending'] ?? 0 ?>
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Approved</span>
                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                    <?= $stats['ip_stats']['approved'] ?? 0 ?>
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Active</span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    <?= $stats['ip_stats']['active'] ?? 0 ?>
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Rejected</span>
                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                    <?= $stats['ip_stats']['rejected'] ?? 0 ?>
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Expired</span>
                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                    <?= $stats['ip_stats']['expired'] ?? 0 ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Pending Download Requests -->
<!-- Download Requests Panel -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-5 lg:p-6 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center">
                <span class="w-1 h-5 sm:h-6 bg-gradient-to-b from-amber-500 to-orange-600 rounded-full mr-2 sm:mr-3"></span>
                <span class="text-sm sm:text-base">Recent Requests</span>
            </h3>
            <span class="px-2 sm:px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">
                <?= count($pending_requests) ?> <span class="hidden sm:inline">pending</span>
            </span>
        </div>
        
        <?php if (empty($pending_requests)): ?>
            <div class="text-center py-6 sm:py-8">
                <div class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2 sm:mb-3">
                    <i class="fas fa-check-circle text-green-500 text-xl sm:text-2xl"></i>
                </div>
                <p class="text-gray-500 font-medium text-sm sm:text-base">No pending requests</p>
                <p class="text-gray-400 text-xs sm:text-sm mt-1">All caught up!</p>
            </div>
        <?php else: ?>
            <div class="space-y-2 sm:space-y-3 max-h-64 sm:max-h-80 overflow-y-auto pr-1 sm:pr-2 custom-scrollbar">
                <?php foreach ($pending_requests as $index => $request): ?>
                <div class="flex items-start gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200 border border-gray-100 request-item" style="animation-delay: <?= $index * 0.1 ?>s">
                    <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow">
                        <i class="fas fa-file-download text-white text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($request['document_name']) ?></p>
                        <p class="text-xs text-gray-600 mt-0.5">
                            <i class="fas fa-user text-gray-400 mr-1"></i>
                            <?= htmlspecialchars($request['requested_by_name']) ?>
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <i class="fas fa-clock text-gray-400 mr-1"></i>
                            <?= date('M d, Y - h:i A', strtotime($request['created_at'])) ?>
                        </p>
                    </div>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-bold flex-shrink-0">
                        Pending
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="<?= BASE_URL ?>/admin/download-requests" class="block mt-4 text-center px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg hover:from-emerald-600 hover:to-green-700 text-sm font-semibold transition-all duration-200 shadow hover:shadow-md">
                View All Requests <i class="fas fa-arrow-right ml-1"></i>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-5 lg:p-6 hover:shadow-xl transition-shadow duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6">
        <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center mb-2 sm:mb-0">
            <span class="w-1 h-5 sm:h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-2 sm:mr-3"></span>
            <span class="text-sm sm:text-base">Recent Activity</span>
        </h3>
        <div class="flex items-center gap-2">
            <button onclick="filterActivity('all')" class="px-2 sm:px-3 py-1 text-xs font-medium rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors activity-filter active">
                All
            </button>
            <button onclick="filterActivity('today')" class="px-2 sm:px-3 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors activity-filter">
                Today
            </button>
        </div>
    </div>
    
    <?php if (empty($recent_activity)): ?>
        <div class="text-center py-8 sm:py-12">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <i class="fas fa-history text-gray-400 text-2xl sm:text-3xl"></i>
            </div>
            <p class="text-gray-500 font-medium text-base sm:text-lg">No recent activity</p>
            <p class="text-gray-400 text-xs sm:text-sm mt-1">Activity logs will appear here</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto -mx-4 sm:mx-0 sm:rounded-lg border-y sm:border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden md:table-cell">Action</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
                        <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden lg:table-cell">Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recent_activity as $index => $activity): ?>
                    <tr class="hover:bg-emerald-50 transition-colors duration-150 activity-row" style="animation-delay: <?= $index * 0.05 ?>s" data-timestamp="<?= strtotime($activity['created_at']) ?>">
                        <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-xs shadow mr-1.5 sm:mr-2 flex-shrink-0">
                                    <?= strtoupper(substr($activity['full_name'] ?? 'S', 0, 1)) ?>
                                </div>
                                <span class="text-xs sm:text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($activity['full_name'] ?? 'System') ?></span>
                            </div>
                        </td>
                        <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap hidden md:table-cell">
                            <?php
                            $actionColors = [
                                'create' => 'bg-green-100 text-green-700',
                                'update' => 'bg-blue-100 text-blue-700',
                                'delete' => 'bg-red-100 text-red-700',
                                'login' => 'bg-purple-100 text-purple-700',
                                'logout' => 'bg-gray-100 text-gray-700',
                                'download' => 'bg-amber-100 text-amber-700'
                            ];
                            $actionType = strtolower($activity['action_type']);
                            $colorClass = $actionColors[$actionType] ?? 'bg-blue-100 text-blue-700';
                            ?>
                            <span class="px-2 sm:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $colorClass ?>">
                                <?= htmlspecialchars($activity['action_type']) ?>
                            </span>
                        </td>
                        <td class="px-3 sm:px-4 py-2 sm:py-3">
                            <p class="text-xs sm:text-sm text-gray-900 line-clamp-2"><?= htmlspecialchars($activity['description']) ?></p>
                            <p class="text-xs text-gray-500 mt-1 md:hidden">
                                <span class="font-medium"><?= htmlspecialchars($activity['action_type']) ?></span> • 
                                <?= date('M d, h:i A', strtotime($activity['created_at'])) ?>
                            </p>
                        </td>
                        <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500 hidden lg:table-cell">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-700"><?= date('M d, Y', strtotime($activity['created_at'])) ?></span>
                                <span class="text-xs text-gray-400"><?= date('h:i A', strtotime($activity['created_at'])) ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-center mt-3 sm:mt-4 gap-2 sm:gap-0 px-4 sm:px-0 pb-2 sm:pb-0">
            <p class="text-xs sm:text-sm text-gray-600">Showing <?= count($recent_activity) ?> activities</p>
            <a href="<?= BASE_URL ?>/admin/activity-logs" class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg hover:from-emerald-600 hover:to-green-700 text-xs sm:text-sm font-semibold transition-all duration-200 shadow hover:shadow-md text-center">
                View All Logs <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Dynamic JavaScript for Dashboard -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const target = parseInt(counter.dataset.target);
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        setTimeout(updateCounter, 100);
    });
    
    // Animate stat bars
    setTimeout(() => {
        document.querySelectorAll('.stat-bar').forEach(bar => {
            bar.style.width = bar.dataset.percent + '%';
        });
    }, 500);
    
    // Fade in cards
    const cards = document.querySelectorAll('.card-animate');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease-out';
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
    
    // Fade in request items
    const requestItems = document.querySelectorAll('.request-item');
    requestItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-20px)';
        setTimeout(() => {
            item.style.transition = 'all 0.4s ease-out';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, 600 + (index * 100));
    });
    
    // Fade in activity rows
    const activityRows = document.querySelectorAll('.activity-row');
    activityRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease-out';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, 800 + (index * 50));
    });
});

// Filter by status function
function filterByStatus(status) {
    window.location.href = `<?= BASE_URL ?>/admin/ip-records?status=${status}`;
}

// Refresh stats
function refreshStats() {
    location.reload();
}

// Filter activity
function filterActivity(type) {
    const buttons = document.querySelectorAll('.activity-filter');
    const rows = document.querySelectorAll('.activity-row');
    const now = Math.floor(Date.now() / 1000);
    const todayStart = now - (now % 86400);
    
    buttons.forEach(btn => btn.classList.remove('active', 'bg-emerald-100', 'text-emerald-700'));
    event.target.classList.add('active', 'bg-emerald-100', 'text-emerald-700');
    
    rows.forEach(row => {
        const timestamp = parseInt(row.dataset.timestamp);
        if (type === 'all' || timestamp >= todayStart) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<style>
/* Custom Scrollbar */
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

/* Line clamp utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive table */
@media (max-width: 768px) {
    .activity-row td {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    table {
        font-size: 0.875rem;
    }
}

@media (max-width: 640px) {
    .activity-row td {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }
    
    /* Ensure text doesn't overflow on mobile */
    .activity-row td:first-child {
        max-width: 100px;
    }
    
    .activity-row td:nth-child(3) {
        max-width: 150px;
    }
}
</style>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
