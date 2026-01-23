<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="bg-gradient-to-br from-emerald-50 via-gray-50 to-white text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <?php
        $pageTitle = 'My Download Requests';
        include APP_PATH . '/views/components/sidebar-staff.php';
        ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col lg:ml-72">
            <!-- Header -->
            <?php include APP_PATH . '/views/components/header.php'; ?>

            <!-- Page Content -->
            <main class="flex-1 p-3 sm:p-4 lg:p-5">
                <!-- Page Header -->
                <div class="mb-4 sm:mb-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h1 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900">My Download Requests</h1>
                            <p class="text-[11px] sm:text-xs text-gray-600 mt-0.5">Track approvals and download access.</p>
                        </div>
                        <a href="<?= BASE_URL ?>/staff/ip-records" class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm">
                            <i class="fas fa-folder-open"></i>
                            Browse records
                        </a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <?php
                    $filters = $filters ?? ['status' => '', 'q' => ''];
                    $pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total' => 0, 'start' => 0, 'end' => 0];
                    $currentStatus = (string)($filters['status'] ?? '');
                    $currentQuery = (string)($filters['q'] ?? '');

                    function buildQueryUrl($overrides = []) {
                        $params = array_merge($_GET ?? [], $overrides);
                        foreach ($params as $k => $v) {
                            if ($v === '' || $v === null) unset($params[$k]);
                        }
                        return '?' . http_build_query($params);
                    }
                ?>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 mb-4 sm:mb-5">
                    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">Pending</p>
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= $stats['pending'] ?? 0 ?></h3>
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
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= $stats['approved'] ?? 0 ?></h3>
                            </div>
                            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                                <i class="fas fa-check-circle text-emerald-700 text-sm sm:text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">Rejected</p>
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= $stats['rejected'] ?? 0 ?></h3>
                            </div>
                            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-red-50 rounded-lg flex items-center justify-center border border-red-100">
                                <i class="fas fa-times-circle text-red-600 text-sm sm:text-base"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-gray-600">Downloaded</p>
                                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mt-1"><?= $stats['downloaded'] ?? 0 ?></h3>
                            </div>
                            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                                <i class="fas fa-download text-emerald-700 text-sm sm:text-base"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm mb-4 sm:mb-5 overflow-x-auto">
                    <div class="flex gap-1 p-2 min-w-max border-b border-emerald-100">
                        <a href="<?= buildQueryUrl(['status' => '', 'page' => 1]) ?>" class="px-3 py-2 text-[11px] sm:text-xs font-semibold rounded-lg transition whitespace-nowrap <?= $currentStatus === '' ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-emerald-50' ?>">
                            All
                        </a>
                        <a href="<?= buildQueryUrl(['status' => 'pending', 'page' => 1]) ?>" class="px-3 py-2 text-[11px] sm:text-xs font-semibold rounded-lg transition whitespace-nowrap <?= $currentStatus === 'pending' ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-emerald-50' ?>">
                            Pending
                        </a>
                        <a href="<?= buildQueryUrl(['status' => 'approved', 'page' => 1]) ?>" class="px-3 py-2 text-[11px] sm:text-xs font-semibold rounded-lg transition whitespace-nowrap <?= $currentStatus === 'approved' ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-emerald-50' ?>">
                            Approved
                        </a>
                        <a href="<?= buildQueryUrl(['status' => 'rejected', 'page' => 1]) ?>" class="px-3 py-2 text-[11px] sm:text-xs font-semibold rounded-lg transition whitespace-nowrap <?= $currentStatus === 'rejected' ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-emerald-50' ?>">
                            Rejected
                        </a>
                        <a href="<?= buildQueryUrl(['status' => 'expired', 'page' => 1]) ?>" class="px-3 py-2 text-[11px] sm:text-xs font-semibold rounded-lg transition whitespace-nowrap <?= $currentStatus === 'expired' ? 'bg-emerald-600 text-white' : 'text-gray-700 hover:bg-emerald-50' ?>">
                            Expired
                        </a>
                    </div>

                    <div class="p-3">
                        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-center">
                            <div class="flex-1">
                                <label class="block text-[10px] sm:text-[11px] font-semibold text-gray-600 mb-1">Search</label>
                                <input id="requestSearch" type="text" value="<?= htmlspecialchars($currentQuery) ?>"
                                    placeholder="Search document or IP record…"
                                    class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400">
                            </div>
                            <div class="flex gap-2 sm:pt-5">
                                <a href="<?= buildQueryUrl(['q' => '', 'page' => 1]) ?>" class="px-3 py-2 text-xs sm:text-sm rounded-lg border border-emerald-200 hover:bg-emerald-50 text-emerald-800">Clear</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requests List -->
                <div class="space-y-2 sm:space-y-3">
                    <?php if (isset($requests) && count($requests) > 0): ?>
                        <?php foreach ($requests as $request): ?>
                            <?php $displayStatus = $request['effective_status'] ?? $request['status']; ?>
                            <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm hover:shadow-md transition">
                                <div class="p-3 sm:p-4">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                                        <!-- Document Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start gap-3">
                                                <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-file-pdf text-emerald-700 text-sm sm:text-base"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h3 class="text-sm font-semibold text-gray-900 break-words">
                                                            <?= htmlspecialchars($request['document_name']) ?>
                                                        </h3>
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold <?= getStatusBadge($displayStatus) ?>">
                                                            <?= ucfirst($displayStatus) ?>
                                                        </span>
                                                    </div>
                                                    <p class="text-[11px] sm:text-xs text-gray-600 mt-1 truncate">
                                                        IP Record: <span class="font-semibold text-emerald-900"><?= htmlspecialchars($request['ip_record_title']) ?></span>
                                                    </p>
                                                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-1 flex items-center gap-1">
                                                        <i class="fas fa-clock"></i>
                                                        Requested <?= timeAgo($request['requested_at']) ?>
                                                    </p>

                                                    <!-- Reason Section -->
                                                    <?php if (!empty($request['reason'])): ?>
                                                        <div class="mt-2 p-2 rounded-lg bg-emerald-50 border border-emerald-100">
                                                            <p class="text-[11px] sm:text-xs text-emerald-900">
                                                                <span class="font-bold">Reason:</span> <?= htmlspecialchars($request['reason']) ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Admin Response -->
                                                    <?php if (!empty($request['admin_response'])): ?>
                                                        <div class="mt-2 p-2 rounded-lg <?= ($request['status'] === 'approved') ? 'bg-emerald-50 border border-emerald-100' : 'bg-red-50 border border-red-100' ?>">
                                                            <p class="text-[11px] sm:text-xs font-bold <?= ($request['status'] === 'approved') ? 'text-emerald-900' : 'text-red-900' ?>">Admin response</p>
                                                            <p class="text-[11px] sm:text-xs <?= ($request['status'] === 'approved') ? 'text-emerald-800' : 'text-red-800' ?> mt-1">
                                                                <?= htmlspecialchars($request['admin_response']) ?>
                                                            </p>
                                                            <?php if (!empty($request['reviewed_by']) && !empty($request['reviewed_at'])): ?>
                                                                <p class="text-[10px] sm:text-[11px] <?= ($request['status'] === 'approved') ? 'text-emerald-700' : 'text-red-700' ?> mt-1">
                                                                    <i class="fas fa-user mr-1"></i>
                                                                    <?= htmlspecialchars($request['reviewed_by']) ?> • <?= date('M j, Y', strtotime($request['reviewed_at'])) ?>
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex flex-col gap-2 lg:items-end lg:min-w-44">
                                            <?php if ($displayStatus === 'approved' && !empty($request['download_token'])): ?>
                                                <?php if ($request['downloads_remaining'] > 0): ?>
                                                    <button onclick="downloadDocument('<?= $request['download_token'] ?>')" 
                                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center text-xs sm:text-sm font-semibold shadow-sm">
                                                        <i class="fas fa-download mr-2 text-xs"></i>
                                                        Download
                                                    </button>
                                                    <p class="text-[10px] sm:text-[11px] text-gray-600 text-center lg:text-right">
                                                        <?= $request['downloads_remaining'] ?> downloads left
                                                    </p>
                                                    <?php if ($request['download_expires']): ?>
                                                        <p class="text-[10px] sm:text-[11px] text-gray-500 text-center lg:text-right">
                                                            Expires <?= date('M j, Y', strtotime($request['download_expires'])) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="px-3 py-2 bg-gray-50 text-gray-700 rounded-lg text-xs sm:text-sm text-center border border-gray-100">
                                                        Download limit reached
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif ($displayStatus === 'pending'): ?>
                                                <div class="px-3 py-2 bg-yellow-50 text-yellow-800 rounded-lg text-xs sm:text-sm text-center border border-yellow-100">
                                                    <i class="fas fa-hourglass-half mr-1 text-xs"></i>
                                                    Awaiting approval
                                                </div>
                                            <?php elseif ($displayStatus === 'rejected'): ?>
                                                <button onclick="requestAgain(<?= $request['document_id'] ?>)" 
                                                        class="bg-emerald-700 hover:bg-emerald-800 text-white px-4 py-2 rounded-lg transition flex items-center justify-center text-xs sm:text-sm font-semibold shadow-sm">
                                                    <i class="fas fa-redo mr-2 text-xs"></i>
                                                    Request Again
                                                </button>
                                            <?php elseif ($displayStatus === 'expired'): ?>
                                                <div class="px-3 py-2 bg-gray-50 text-gray-700 rounded-lg text-xs sm:text-sm text-center border border-gray-100">
                                                    <i class="fas fa-hourglass-end mr-1 text-xs"></i>
                                                    Token expired
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-10 text-center">
                            <i class="fas fa-inbox text-4xl text-emerald-200 mb-3"></i>
                            <h3 class="text-base font-bold text-gray-900 mb-1">No download requests yet</h3>
                            <p class="text-xs text-gray-600 mb-4">Browse records and request access to documents.</p>
                            <a href="<?= BASE_URL ?>/staff/ip-records" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg transition text-xs font-semibold shadow-sm">
                                <i class="fas fa-folder-open mr-2"></i>
                                Browse IP Records
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
                    <div class="mt-4 sm:mt-6 bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="text-[11px] sm:text-xs text-gray-600">
                            Showing <span class="font-medium"><?= (int)($pagination['start'] ?? 0) ?></span>–<span class="font-medium"><?= (int)($pagination['end'] ?? 0) ?></span>
                            of <span class="font-medium"><?= (int)($pagination['total'] ?? 0) ?></span>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <?php $page = (int)($pagination['page'] ?? 1); $totalPages = (int)($pagination['total_pages'] ?? 1); ?>
                            <a class="px-3 py-2 text-xs rounded-lg border border-emerald-200 hover:bg-emerald-50 text-emerald-800 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>"
                               href="<?= buildQueryUrl(['page' => max(1, $page - 1)]) ?>">Prev</a>
                            <span class="text-[11px] sm:text-xs text-gray-600">Page <span class="font-semibold"><?= $page ?></span> of <span class="font-semibold"><?= $totalPages ?></span></span>
                            <a class="px-3 py-2 text-xs rounded-lg border border-emerald-200 hover:bg-emerald-50 text-emerald-800 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>"
                               href="<?= buildQueryUrl(['page' => min($totalPages, $page + 1)]) ?>">Next</a>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/common.js?v=<?= filemtime(PUBLIC_PATH . '/js/common.js') ?>"></script>
    <script>
        const debounceSearch = (fn, wait = 300) => {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), wait);
            };
        };

        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('requestSearch');
            if (!search) return;
            search.addEventListener('input', debounceSearch(() => {
                const url = new URL(window.location.href);
                const value = search.value.trim();
                if (value) url.searchParams.set('q', value);
                else url.searchParams.delete('q');
                url.searchParams.set('page', '1');
                window.location.href = url.toString();
            }, 400));
        });

        // Notify staff when a request status changes (approved/rejected)
        // Uses localStorage diff so no DB/schema changes are needed.
        (function notifyOnStatusChanges() {
            try {
                const storageKey = 'my_requests_status_map_v1';
                const prev = JSON.parse(localStorage.getItem(storageKey) || '{}');

                const current = (window.__MY_REQUESTS__ || []).reduce((acc, r) => {
                    const key = String(r.id);
                    acc[key] = {
                        status: r.effective_status || r.status || 'pending',
                        name: r.document_name || 'document'
                    };
                    return acc;
                }, {});

                // Show toasts for transitions into approved/rejected
                Object.keys(current).forEach((id) => {
                    const before = prev[id]?.status;
                    const after = current[id]?.status;
                    if (!before || !after || before === after) return;

                    const name = current[id]?.name || 'document';
                    if (after === 'approved') {
                        showToast(`Approved: ${name}`, 'success');
                    } else if (after === 'rejected') {
                        showToast(`Rejected: ${name}`, 'error');
                    }
                });

                localStorage.setItem(storageKey, JSON.stringify(current));
            } catch (e) {
                // ignore
            }
        })();

        function downloadDocument(token) {
            if (!token) {
                showToast('Missing download token', 'error');
                return;
            }

            const downloadUrl = '<?= BASE_URL ?>/document/download/' + token;

            // Open in a new tab to avoid interrupting this page (and allow the UI to update).
            const a = document.createElement('a');
            a.href = downloadUrl;
            a.target = '_blank';
            a.rel = 'noopener';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            showToast('Download opened', 'success');
            // Refresh counts/status shortly after; token route increments download_count server-side.
            setTimeout(() => location.reload(), 1200);
        }

        function requestAgain(documentId) {
            Swal.fire({
                title: 'Request Download Again?',
                html: `
                    <div class="text-left">
                        <label class="block text-sm font-medium mb-2">Reason for request</label>
                        <textarea id="requestReason" class="swal2-textarea w-full" rows="3" 
                                  placeholder="Please provide a reason for your download request..."></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Submit Request',
                confirmButtonColor: '#059669',
                preConfirm: () => {
                    const reason = document.getElementById('requestReason').value;
                    if (!reason) {
                        Swal.showValidationMessage('Please provide a reason');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ajaxRequest('<?= BASE_URL ?>/staff/request-download', {
                        document_id: documentId,
                        reason: result.value
                    }, 'POST').then((res) => {
                        if (res && res.success) {
                            showToast(res.message || 'Download request submitted successfully', 'success');
                            setTimeout(() => location.reload(), 900);
                        } else {
                            showToast(res?.message || 'Failed to submit request', 'error');
                        }
                    }).catch(() => {
                        showToast('Failed to submit request', 'error');
                    });
                }
            });
        }
    </script>

    <script>
        // Minimal request data for notification diffing
        window.__MY_REQUESTS__ = <?= json_encode(array_map(function($r) {
            return [
                'id' => (int)($r['id'] ?? 0),
                'status' => (string)($r['status'] ?? ''),
                'effective_status' => (string)($r['effective_status'] ?? ''),
                'document_name' => (string)($r['document_name'] ?? ''),
            ];
        }, $requests ?? []), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
</body>
</html>

<?php
function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-red-100 text-red-700',
        'expired' => 'bg-gray-100 text-gray-700'
    ];
    return $badges[strtolower($status)] ?? 'bg-gray-100 text-gray-700';
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', $time);
}
?>
