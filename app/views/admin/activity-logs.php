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
        'view' => 'bg-slate-500',
        'create' => 'bg-blue-500',
        'update' => 'bg-yellow-500',
        'delete' => 'bg-red-500',
        'archive' => 'bg-amber-600',
        'restore' => 'bg-emerald-600',
        'permanent_delete' => 'bg-red-700',
        'upload' => 'bg-purple-500',
        'download' => 'bg-indigo-500',
        'approve' => 'bg-green-600',
        'reject' => 'bg-red-600',
        'approve_download' => 'bg-green-600',
        'reject_download' => 'bg-red-600'
    ];
    return $colors[$action] ?? 'bg-gray-500';
}

function getActionIcon($action) {
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'view' => 'eye',
        'create' => 'plus-circle',
        'update' => 'edit',
        'delete' => 'trash',
        'archive' => 'archive',
        'restore' => 'undo',
        'permanent_delete' => 'trash-alt',
        'upload' => 'upload',
        'download' => 'download',
        'approve' => 'check-circle',
        'reject' => 'times-circle',
        'approve_download' => 'check-circle',
        'reject_download' => 'times-circle'
    ];
    return $icons[$action] ?? 'circle';
}
?>

<!-- Page Header -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-1">Monitor system activities and user actions</p>
        </div>
        <button onclick="exportLogs()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-lg transition flex items-center justify-center text-xs sm:text-sm shadow-sm">
            <i class="fas fa-download mr-2 text-[11px]"></i>
            Export Logs
        </button>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-emerald-100 p-3 sm:p-4 mb-4 sm:mb-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3 sm:gap-4">
        <div class="md:col-span-2">
            <div class="relative">
                <input type="text" id="searchLogs" placeholder="Search activities..." 
                       class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2 sm:py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 text-xs sm:text-sm">
                <i class="fas fa-search absolute left-3 top-2.5 sm:top-3 text-gray-400 text-[12px]"></i>
            </div>
        </div>
        <select id="filterAction" class="border border-gray-200 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 text-xs sm:text-sm bg-white">
            <option value="">All Actions</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
            <option value="view">View</option>
            <option value="create">Create</option>
            <option value="update">Update</option>
            <option value="delete">Delete</option>
            <option value="archive">Archive</option>
            <option value="restore">Restore</option>
            <option value="upload">Upload</option>
            <option value="download">Download</option>
            <option value="approve">Approve</option>
            <option value="reject">Reject</option>
        </select>
        <select id="filterUser" class="border border-gray-200 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 text-xs sm:text-sm bg-white">
            <option value="">All Users</option>
            <?php if (isset($users)): foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
            <?php endforeach; endif; ?>
        </select>
        <input type="date" id="filterDate" class="border border-gray-200 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 text-xs sm:text-sm">
    </div>
</div>

<!-- Activity Timeline -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
    <div class="divide-y divide-gray-100">
        <?php if (isset($activityLogs) && count($activityLogs) > 0): ?>
            <?php foreach ($activityLogs as $log): ?>
                <div class="p-3.5 sm:p-4 lg:p-5 hover:bg-emerald-50/40 transition">
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
                                    <p class="text-[12px] sm:text-[13px] font-medium text-gray-900 break-words">
                                        <?= htmlspecialchars($log['description']) ?>
                                    </p>
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-100">
                                            <i class="fas fa-user mr-1"></i>
                                            <span class="truncate"><?= htmlspecialchars(($log['full_name'] ?? $log['username'] ?? 'System')) ?></span>
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-[11px] font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                            <i class="fas fa-tag mr-1"></i>
                                            <?= ucfirst($log['action_type']) ?>
                                        </span>
                                        <?php if (!empty($log['entity_type'])): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-900 border border-emerald-100">
                                                <i class="fas fa-cube mr-1"></i>
                                                <?= ucfirst($log['entity_type']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($log['ip_address'])): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 sm:py-1 rounded-full text-[11px] font-medium bg-gray-50 text-gray-600 border border-gray-200 hidden sm:inline-flex">
                                                <i class="fas fa-network-wired mr-1"></i>
                                                <?= htmlspecialchars($log['ip_address']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-[11px] sm:text-xs text-gray-600 lg:ml-4 flex-shrink-0">
                                    <i class="fas fa-clock mr-1"></i>
                                    <span class="hidden sm:inline"><?= formatDateTime($log['created_at']) ?></span>
                                    <span class="sm:hidden"><?= date('M j, g:i A', strtotime($log['created_at'])) ?></span>
                                </div>
                            </div>

                            <!-- User Agent (collapsed by default) -->
                            <?php if (!empty($log['user_agent'])): ?>
                                <details class="mt-2">
                                    <summary class="text-[11px] text-gray-500 cursor-pointer hover:text-emerald-700">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        View details
                                    </summary>
                                    <div class="mt-2 text-[11px] text-gray-700 bg-emerald-50/40 p-2 rounded border border-emerald-100 break-all space-y-1">
                                        <div><strong>User Agent:</strong> <?= htmlspecialchars($log['user_agent']) ?></div>
                                        <?php if (!empty($log['metadata'])): ?>
                                            <?php $meta = json_decode($log['metadata'], true); ?>
                                            <?php if (is_array($meta) && !empty($meta)): ?>
                                                <div><strong>Metadata:</strong> <?= htmlspecialchars(json_encode($meta, JSON_UNESCAPED_SLASHES)) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <div class="p-3.5 sm:p-4 lg:p-5 bg-emerald-50/40 border-t border-emerald-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                    <p class="text-[11px] sm:text-xs text-gray-700 text-center sm:text-left">
                        Showing <span class="font-medium"><?= $pagination['start'] ?? 1 ?></span> to 
                        <span class="font-medium"><?= $pagination['end'] ?? 20 ?></span> of 
                        <span class="font-medium"><?= $pagination['total'] ?? 0 ?></span> results
                    </p>
                    <div class="flex space-x-2 justify-center">
                        <?php
                            $page = (int)($pagination['page'] ?? 1);
                            $totalPages = (int)($pagination['total_pages'] ?? 1);
                            $query = $_GET;
                        ?>
                        <?php if ($page > 1): ?>
                            <?php $query['page'] = $page - 1; ?>
                            <a href="?<?= htmlspecialchars(http_build_query($query)) ?>" class="px-3 sm:px-4 py-2 border border-emerald-200 rounded-lg hover:bg-emerald-50 transition text-[11px] sm:text-xs text-emerald-800">
                                <i class="fas fa-chevron-left mr-1"></i> <span class="hidden sm:inline">Previous</span><span class="sm:hidden">Prev</span>
                            </a>
                        <?php else: ?>
                            <span class="px-3 sm:px-4 py-2 border border-emerald-100 rounded-lg text-gray-400 text-[11px] sm:text-xs cursor-not-allowed">
                                <i class="fas fa-chevron-left mr-1"></i> <span class="hidden sm:inline">Previous</span><span class="sm:hidden">Prev</span>
                            </span>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <?php $query['page'] = $page + 1; ?>
                            <a href="?<?= htmlspecialchars(http_build_query($query)) ?>" class="px-3 sm:px-4 py-2 border border-emerald-200 rounded-lg hover:bg-emerald-50 transition text-[11px] sm:text-xs text-emerald-800">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        <?php else: ?>
                            <span class="px-3 sm:px-4 py-2 border border-emerald-100 rounded-lg text-gray-400 text-[11px] sm:text-xs cursor-not-allowed">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="p-8 sm:p-12 text-center text-gray-500">
                <i class="fas fa-history text-4xl sm:text-5xl mb-3 sm:mb-4 text-gray-300"></i>
                <p class="text-sm sm:text-base font-medium text-gray-800">No activity logs found</p>
                <p class="text-[11px] sm:text-xs mt-2">System activities will appear here</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/common.js?v=<?= filemtime(PUBLIC_PATH . '/js/common.js') ?>"></script>

<style>
    /* SweetAlert green theme (scoped via customClass) */
    .swal-emerald-popup .swal2-title { color: #111827; }
    .swal-emerald-popup .swal2-html-container { color: #374151; }
    .swal-emerald-popup .swal2-input,
    .swal-emerald-popup .swal2-select {
        border: 1px solid #d1d5db;
        box-shadow: none;
    }
    .swal-emerald-popup .swal2-input:focus,
    .swal-emerald-popup .swal2-select:focus {
        border-color: #34d399;
        box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.25);
        outline: none;
    }
</style>

<script>
    // Initialize filter controls from server-provided values
    (function initActivityLogFilters() {
        const filters = <?= json_encode($filters ?? []) ?>;
        const searchEl = document.getElementById('searchLogs');
        const actionEl = document.getElementById('filterAction');
        const userEl = document.getElementById('filterUser');
        const dateEl = document.getElementById('filterDate');

        if (searchEl && typeof filters.q === 'string') searchEl.value = filters.q;
        if (actionEl && typeof filters.action_type === 'string') actionEl.value = filters.action_type;
        if (userEl && typeof filters.user_id === 'string') userEl.value = filters.user_id;
        if (dateEl && typeof filters.date === 'string') dateEl.value = filters.date;
    })();

    function buildUrlWithFilters(next = {}) {
        const url = new URL(window.location.href);
        const params = url.searchParams;

        const searchEl = document.getElementById('searchLogs');
        const actionEl = document.getElementById('filterAction');
        const userEl = document.getElementById('filterUser');
        const dateEl = document.getElementById('filterDate');

        const q = (next.q ?? (searchEl?.value || '')).trim();
        const action = (next.action_type ?? (actionEl?.value || '')).trim();
        const user = (next.user_id ?? (userEl?.value || '')).toString().trim();
        const date = (next.date ?? (dateEl?.value || '')).trim();

        if (q) params.set('q', q); else params.delete('q');
        if (action) params.set('action_type', action); else params.delete('action_type');
        if (user) params.set('user_id', user); else params.delete('user_id');
        if (date) params.set('date', date); else params.delete('date');

        // reset to first page on filter changes
        params.delete('page');
        params.delete('export');

        url.search = params.toString();
        return url.toString();
    }

    let searchDebounce = null;
    function onFilterChanged(isSearch = false) {
        if (isSearch) {
            if (searchDebounce) clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                window.location.href = buildUrlWithFilters();
            }, 250);
            return;
        }
        window.location.href = buildUrlWithFilters();
    }

    document.getElementById('searchLogs')?.addEventListener('input', () => onFilterChanged(true));
    document.getElementById('filterAction')?.addEventListener('change', () => onFilterChanged(false));
    document.getElementById('filterUser')?.addEventListener('change', () => onFilterChanged(false));
    document.getElementById('filterDate')?.addEventListener('change', () => onFilterChanged(false));

    function exportLogs() {
        Swal.fire({
            title: 'Export Activity Logs',
            icon: 'info',
            iconColor: '#059669',
            html: `
                <div class="text-left space-y-3">
                    <div class="text-sm">Export uses the current filters on this page.</div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Format</label>
                        <select id="exportFormat" class="swal2-input w-full">
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Export',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'swal-emerald-popup'
            },
            preConfirm: () => ({
                format: document.getElementById('exportFormat')?.value || 'csv'
            })
        }).then((result) => {
            if (!result.isConfirmed) return;
            const format = (result.value?.format || 'csv').toLowerCase();
            const url = new URL(buildUrlWithFilters());
            url.searchParams.set('export', format);
            window.location.href = url.toString();
        });
    }
    </script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
