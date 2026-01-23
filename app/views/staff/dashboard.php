<?php
ob_start();

$stats = $stats ?? [];
$my_requests = $my_requests ?? [];
$trend = $trend ?? [];
$recent_activity = $recent_activity ?? [];

$searchQuery = (string)($_GET['q'] ?? '');

$weekTotal = 0;
$maxTrend = 0;
foreach ($trend as $t) {
    $c = (int)($t['count'] ?? 0);
    $weekTotal += $c;
    if ($c > $maxTrend) $maxTrend = $c;
}
$maxTrend = max(1, $maxTrend);

function staffStatusBadge($status) {
    $status = strtolower((string)$status);
    $map = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-red-100 text-red-700'
    ];
    return $map[$status] ?? 'bg-gray-100 text-gray-700';
}

function timeAgo($datetime) {
    if (!$datetime) return '';
    $time = strtotime($datetime);
    if (!$time) return '';
    $diff = time() - $time;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}

function isActiveApprovedRequest($r) {
    if (($r['status'] ?? '') !== 'approved') return false;
    if (empty($r['download_token'])) return false;
    $expiresAt = $r['token_expires_at'] ?? null;
    if (!$expiresAt) return false;
    if (strtotime($expiresAt) <= time()) return false;
    $count = (int)($r['download_count'] ?? 0);
    $limit = (int)($r['download_limit'] ?? 0);
    if ($limit <= 0) return false;
    return $count < $limit;
}
?>

<!-- Header / Quick actions -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3">
        <div>
            <h1 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">Staff Dashboard</h1>
            <p class="text-[11px] sm:text-xs text-gray-600 mt-1">Quick overview of records, requests, and your recent activity.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/staff/ip-records" class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm">
                <i class="fas fa-folder-open"></i>
                Browse records
            </a>
            <a href="<?= BASE_URL ?>/staff/my-requests" class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-2 rounded-lg border border-emerald-200 text-emerald-800 hover:bg-emerald-50">
                <i class="fas fa-download"></i>
                My requests
            </a>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-6 gap-2 sm:gap-3 mb-4 sm:mb-6">
    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">IP Records</p>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= (int)($stats['total_records'] ?? 0) ?></h3>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                <i class="fas fa-folder text-emerald-700 text-sm sm:text-base"></i>
            </div>
        </div>
    </div>

    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">Documents</p>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= (int)($stats['total_documents'] ?? 0) ?></h3>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                <i class="fas fa-file-pdf text-emerald-700 text-sm sm:text-base"></i>
            </div>
        </div>
    </div>

    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">My Requests</p>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= (int)($stats['my_requests'] ?? 0) ?></h3>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                <i class="fas fa-list-check text-emerald-700 text-sm sm:text-base"></i>
            </div>
        </div>
    </div>

    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">Pending</p>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= (int)($stats['pending_requests'] ?? 0) ?></h3>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-yellow-50 rounded-lg flex items-center justify-center border border-yellow-100">
                <i class="fas fa-clock text-yellow-600 text-sm sm:text-base"></i>
            </div>
        </div>
    </div>

    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">Approved</p>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= (int)($stats['approved_requests'] ?? 0) ?></h3>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                <i class="fas fa-check-circle text-emerald-700 text-sm sm:text-base"></i>
            </div>
        </div>
        <p class="text-[10px] sm:text-[11px] text-gray-500 mt-2">Active: <span class="font-semibold text-emerald-800"><?= (int)($stats['active_approved'] ?? 0) ?></span></p>
    </div>

    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">Rejected</p>
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= (int)($stats['rejected_requests'] ?? 0) ?></h3>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-red-50 rounded-lg flex items-center justify-center border border-red-100">
                <i class="fas fa-times-circle text-red-600 text-sm sm:text-base"></i>
            </div>
        </div>
        <p class="text-[10px] sm:text-[11px] text-gray-500 mt-2">Downloaded: <span class="font-semibold text-emerald-800"><?= (int)($stats['downloaded'] ?? 0) ?></span></p>
    </div>
</div>

<!-- Search + Trend -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
    <div class="lg:col-span-2 bg-gradient-to-br from-emerald-600 to-green-700 rounded-xl shadow-md p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-white">Search repository</h3>
                <p class="text-[11px] sm:text-xs text-emerald-50/90 mt-0.5">Find records, titles, and documents quickly.</p>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/staff/search" method="GET" class="mt-3 flex flex-col sm:flex-row gap-2">
            <input type="text" name="q" placeholder="Search by title, filename, tags…"
                   class="flex-1 px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-white/50 text-sm"
                   value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit" class="px-5 py-2.5 bg-white text-emerald-700 rounded-lg font-semibold hover:bg-emerald-50 transition text-sm inline-flex items-center justify-center gap-2">
                <i class="fas fa-search"></i>
                Search
            </button>
        </form>
    </div>

    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900">Requests (7 days)</h3>
                <p class="text-[11px] text-gray-600 mt-0.5">Total: <span class="font-semibold text-emerald-800"><?= (int)$weekTotal ?></span></p>
            </div>
            <div class="w-9 h-9 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-chart-column text-emerald-700 text-sm"></i>
            </div>
        </div>

        <div class="mt-4 flex items-end gap-2 h-24">
            <?php foreach ($trend as $day): ?>
                <?php
                    $count = (int)($day['count'] ?? 0);
                    $pct = (int)round(($count / $maxTrend) * 100);
                    $pct = max(8, $pct);
                ?>
                <div class="flex-1 flex flex-col items-center gap-1" title="<?= htmlspecialchars(($day['date'] ?? '') . ': ' . $count) ?>">
                    <div class="w-full h-24 bg-emerald-100 rounded-lg overflow-hidden flex items-end">
                        <div class="w-full bg-emerald-600" style="height: <?= $pct ?>%;"></div>
                    </div>
                    <div class="text-[10px] text-gray-500"><?= htmlspecialchars((string)($day['label'] ?? '')) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent requests + Activity -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">
    <div class="lg:col-span-2 bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm">
        <div class="p-4 sm:p-5 border-b border-emerald-100 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-gray-900">My recent requests</h3>
                <p class="text-[11px] sm:text-xs text-gray-600 mt-0.5">Latest approvals, rejections, and downloads.</p>
            </div>
            <a href="<?= BASE_URL ?>/staff/my-requests" class="text-xs font-semibold text-emerald-800 hover:text-emerald-900 hover:underline whitespace-nowrap">
                View all
            </a>
        </div>

        <div class="p-4 sm:p-5">
            <?php if (empty($my_requests)): ?>
                <div class="py-8 text-center">
                    <i class="fas fa-inbox text-4xl text-emerald-200 mb-3"></i>
                    <p class="text-sm font-semibold text-gray-900">No download requests yet</p>
                    <p class="text-xs text-gray-600 mt-1">Browse records and request access when needed.</p>
                    <a href="<?= BASE_URL ?>/staff/ip-records" class="mt-4 inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition text-xs font-semibold shadow-sm">
                        <i class="fas fa-folder-open"></i>
                        Browse IP Records
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($my_requests as $r): ?>
                        <div class="bg-white rounded-xl border border-emerald-100 p-3 sm:p-4 hover:shadow-sm transition">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-900 break-words">
                                            <i class="fas fa-file-pdf text-emerald-700 mr-1"></i>
                                            <?= htmlspecialchars((string)($r['document_name'] ?? '')) ?>
                                        </p>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold <?= staffStatusBadge($r['status'] ?? '') ?>">
                                            <?= ucfirst((string)($r['status'] ?? '')) ?>
                                        </span>
                                    </div>
                                    <p class="text-[11px] sm:text-xs text-gray-600 mt-1 truncate">
                                        IP Record: <span class="font-semibold text-emerald-900"><?= htmlspecialchars((string)($r['ip_record_title'] ?? ($r['ip_title'] ?? ''))) ?></span>
                                    </p>
                                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-1 flex items-center gap-1">
                                        <i class="fas fa-clock"></i>
                                        Requested <?= timeAgo($r['requested_at'] ?? ($r['created_at'] ?? null)) ?>
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 sm:flex-col sm:items-end sm:justify-start">
                                    <?php if (isActiveApprovedRequest($r)): ?>
                                        <a href="<?= BASE_URL ?>/document/download/<?= htmlspecialchars((string)($r['download_token'] ?? '')) ?>" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm">
                                            <i class="fas fa-download text-xs"></i>
                                            Download
                                        </a>
                                        <div class="text-[10px] sm:text-[11px] text-gray-600">
                                            <?= max(0, (int)($r['download_limit'] ?? 0) - (int)($r['download_count'] ?? 0)) ?> left
                                        </div>
                                    <?php elseif (($r['status'] ?? '') === 'approved'): ?>
                                        <div class="text-[11px] sm:text-xs text-gray-500">Expired / limit reached</div>
                                    <?php else: ?>
                                        <div class="text-[11px] sm:text-xs text-gray-400">—</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm">
        <div class="p-4 sm:p-5 border-b border-emerald-100">
            <h3 class="text-sm sm:text-base font-bold text-gray-900">Recent activity</h3>
            <p class="text-[11px] sm:text-xs text-gray-600 mt-0.5">Your latest actions in the system.</p>
        </div>

        <div class="p-4 sm:p-5">
            <?php if (empty($recent_activity)): ?>
                <div class="py-6 text-center">
                    <i class="fas fa-circle-info text-3xl text-emerald-200 mb-2"></i>
                    <p class="text-xs text-gray-600">No recent activity yet.</p>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($recent_activity as $a): ?>
                        <div class="flex gap-3 p-3 rounded-xl border border-emerald-100 bg-white">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                                <?php
                                    $action = strtolower((string)($a['action_type'] ?? ''));
                                    $icon = 'bolt';
                                    if ($action === 'view') $icon = 'eye';
                                    if ($action === 'create') $icon = 'plus';
                                    if ($action === 'update') $icon = 'pen';
                                    if ($action === 'delete') $icon = 'trash';
                                ?>
                                <i class="fas fa-<?= $icon ?> text-emerald-700 text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-900 break-words"><?= htmlspecialchars((string)($a['description'] ?? '')) ?></p>
                                <p class="text-[11px] text-gray-500 mt-0.5"><?= timeAgo($a['created_at'] ?? null) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
