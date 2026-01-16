<?php 
ob_start();
$page_title = 'Activity Logs';

// Helper functions
function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

function getActionColor($action) {
    $colors = [
        'login' => 'bg-green-500',
        'logout' => 'bg-gray-500',
        'create' => 'bg-blue-500',
        'update' => 'bg-yellow-500',
        'delete' => 'bg-red-500',
        'upload' => 'bg-purple-500',
        'download' => 'bg-indigo-500',
        'approve' => 'bg-green-600',
        'reject' => 'bg-red-600'
    ];
    return $colors[$action] ?? 'bg-gray-500';
}

function getActionIcon($action) {
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'create' => 'plus-circle',
        'update' => 'edit',
        'delete' => 'trash',
        'upload' => 'upload',
        'download' => 'download',
        'approve' => 'check-circle',
        'reject' => 'times-circle'
    ];
    return $icons[$action] ?? 'circle';
}
?>

<!-- Page Header -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Activity Logs</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Monitor all system activities and user actions</p>
        </div>
        <button onclick="exportLogs()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition flex items-center justify-center text-sm sm:text-base shadow-md">
            <i class="fas fa-download mr-2"></i>
            Export Logs
        </button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-md p-3 sm:p-4 mb-4 sm:mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 sm:gap-4">
        <div class="md:col-span-2">
            <div class="relative">
                <input type="text" id="searchLogs" placeholder="Search activities..." 
                       class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                <i class="fas fa-search absolute left-3 top-2.5 sm:top-3 text-gray-400 text-sm"></i>
            </div>
        </div>
        <select id="filterAction" class="border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                            <option value="">All Actions</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="create">Create</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                            <option value="upload">Upload</option>
                            <option value="download">Download</option>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                        </select>
                        <select id="filterUser" class="border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                            <option value="">All Users</option>
                            <?php if (isset($users)): foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <input type="date" id="filterDate" class="border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                    </div>
                </div>

<!-- Activity Timeline -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
    <div class="divide-y divide-gray-200">
        <?php if (isset($activityLogs) && count($activityLogs) > 0): ?>
            <?php foreach ($activityLogs as $log): ?>
                <div class="p-4 sm:p-5 lg:p-6 hover:bg-gray-50 transition">
                    <div class="flex items-start space-x-3 sm:space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center <?= getActionColor($log['action_type']) ?>">
                                <i class="fas fa-<?= getActionIcon($log['action_type']) ?> text-white text-xs sm:text-sm"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-medium text-gray-800 break-words">
                                        <?= htmlspecialchars($log['description']) ?>
                                    </p>
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-user mr-1"></i>
                                            <span class="truncate"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></span>
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?= ucfirst($log['action_type']) ?>
                                        </span>
                                        <?php if (!empty($log['entity_type'])): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                <i class="fas fa-cube mr-1"></i>
                                                <?= ucfirst($log['entity_type']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($log['ip_address'])): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hidden sm:inline-flex">
                                                <i class="fas fa-network-wired mr-1"></i>
                                                <?= htmlspecialchars($log['ip_address']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-xs sm:text-sm text-gray-500 lg:ml-4 flex-shrink-0">
                                    <i class="fas fa-clock mr-1"></i>
                                    <span class="hidden sm:inline"><?= formatDateTime($log['created_at']) ?></span>
                                    <span class="sm:hidden"><?= date('M j, g:i A', strtotime($log['created_at'])) ?></span>
                                </div>
                            </div>

                            <!-- User Agent (collapsed by default) -->
                            <?php if (!empty($log['user_agent'])): ?>
                                <details class="mt-2">
                                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        View details
                                    </summary>
                                    <div class="mt-2 text-xs text-gray-600 bg-gray-50 p-2 rounded break-all">
                                        <strong>User Agent:</strong> <?= htmlspecialchars(substr($log['user_agent'], 0, 100)) ?>...
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <div class="p-4 sm:p-5 lg:p-6 bg-gray-50 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                    <p class="text-xs sm:text-sm text-gray-700 text-center sm:text-left">
                        Showing <span class="font-medium"><?= $pagination['start'] ?? 1 ?></span> to 
                        <span class="font-medium"><?= $pagination['end'] ?? 20 ?></span> of 
                        <span class="font-medium"><?= $pagination['total'] ?? 0 ?></span> results
                    </p>
                    <div class="flex space-x-2 justify-center">
                        <button class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition text-xs sm:text-sm">
                            <i class="fas fa-chevron-left mr-1"></i> <span class="hidden sm:inline">Previous</span><span class="sm:hidden">Prev</span>
                        </button>
                        <button class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition text-xs sm:text-sm">
                            Next <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="p-8 sm:p-12 text-center text-gray-500">
                <i class="fas fa-history text-4xl sm:text-5xl mb-3 sm:mb-4 text-gray-300"></i>
                <p class="text-base sm:text-lg">No activity logs found</p>
                <p class="text-xs sm:text-sm mt-2">System activities will appear here</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
        function exportLogs() {
            Swal.fire({
                title: 'Export Activity Logs',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Format</label>
                            <select id="exportFormat" class="swal2-input w-full">
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date Range</label>
                            <select id="dateRange" class="swal2-input w-full">
                                <option value="today">Today</option>
                                <option value="week">Last 7 Days</option>
                                <option value="month">Last 30 Days</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Export',
                preConfirm: () => {
                    return {
                        format: document.getElementById('exportFormat').value,
                        range: document.getElementById('dateRange').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    showToast('info', 'Preparing export...');
                    // Implement export logic
                    setTimeout(() => {
                        showToast('success', 'Export completed! Download started.');
                    }, 1500);
                }
            });
        }

        // Filter functionality
        document.getElementById('searchLogs')?.addEventListener('input', filterLogs);
        document.getElementById('filterAction')?.addEventListener('change', filterLogs);
        document.getElementById('filterUser')?.addEventListener('change', filterLogs);
        document.getElementById('filterDate')?.addEventListener('change', filterLogs);

        function filterLogs() {
            // Implement filtering logic
            console.log('Filtering logs...');
        }
    </script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
