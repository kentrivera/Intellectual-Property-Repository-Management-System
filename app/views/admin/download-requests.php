<?php 
ob_start();
$page_title = 'Download Requests';

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

// Helper functions
function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700'
    ];
    return $badges[strtolower($status)] ?? 'bg-gray-100 text-gray-700';
}

function getStatusIcon($status) {
    $icons = [
        'pending' => 'clock',
        'approved' => 'check-circle',
        'rejected' => 'times-circle'
    ];
    return $icons[strtolower($status)] ?? 'circle';
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

<!-- Page Header -->
<div class="mb-4">
    <h1 class="text-xl font-bold text-gray-800">Download Requests</h1>
    <p class="text-xs text-gray-600 mt-0.5">Review and manage document download requests</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    <div class="bg-white rounded-lg shadow-sm p-3 border-l-3 border-amber-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-gray-500 uppercase">Pending</p>
                <h3 class="text-xl font-bold text-gray-800"><?= $stats['pending'] ?? 0 ?></h3>
            </div>
            <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-amber-600 text-sm"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-3 border-l-3 border-emerald-600">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-gray-500 uppercase">Approved</p>
                <h3 class="text-xl font-bold text-gray-800"><?= $stats['approved_today'] ?? 0 ?></h3>
            </div>
            <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-3 border-l-3 border-rose-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium text-gray-500 uppercase">Rejected</p>
                <h3 class="text-xl font-bold text-gray-800"><?= $stats['rejected_today'] ?? 0 ?></h3>
            </div>
            <div class="w-9 h-9 bg-rose-50 rounded-lg flex items-center justify-center">
                <i class="fas fa-times-circle text-rose-600 text-sm"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-emerald-600 to-green-700 rounded-lg shadow-sm p-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold text-emerald-50 uppercase">Total</p>
                <h3 class="text-xl font-bold text-white"><?= $stats['total'] ?? 0 ?></h3>
            </div>
            <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-download text-white text-sm"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-lg shadow-sm mb-4 overflow-x-auto">
    <div class="flex border-b border-gray-100 min-w-max">
        <a href="<?= buildQueryUrl(['status' => '', 'page' => 1]) ?>" class="status-tab px-4 py-2 text-xs font-semibold hover:bg-gray-50 transition border-b-2 whitespace-nowrap <?= $currentStatus === '' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/40' : 'border-transparent text-gray-600' ?>">
            All Requests
        </a>
        <a href="<?= buildQueryUrl(['status' => 'pending', 'page' => 1]) ?>" class="status-tab px-4 py-2 text-xs font-semibold hover:bg-gray-50 transition border-b-2 whitespace-nowrap <?= $currentStatus === 'pending' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/40' : 'border-transparent text-gray-600' ?>">
            Pending <span class="ml-1.5 px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px] font-bold"><?= $stats['pending'] ?? 0 ?></span>
        </a>
        <a href="<?= buildQueryUrl(['status' => 'approved', 'page' => 1]) ?>" class="status-tab px-4 py-2 text-xs font-semibold hover:bg-gray-50 transition border-b-2 whitespace-nowrap <?= $currentStatus === 'approved' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/40' : 'border-transparent text-gray-600' ?>">
            Approved
        </a>
        <a href="<?= buildQueryUrl(['status' => 'rejected', 'page' => 1]) ?>" class="status-tab px-4 py-2 text-xs font-semibold hover:bg-gray-50 transition border-b-2 whitespace-nowrap <?= $currentStatus === 'rejected' ? 'border-emerald-600 text-emerald-700 bg-emerald-50/40' : 'border-transparent text-gray-600' ?>">
            Rejected
        </a>
    </div>

    <div class="p-3">
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <div class="flex-1">
                <input id="requestSearch" type="text" value="<?= htmlspecialchars($currentQuery) ?>"
                    placeholder="Search requester, email, document..."
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            </div>
            <a href="<?= buildQueryUrl(['q' => '', 'page' => 1]) ?>" class="px-3 py-2 text-xs rounded-lg border border-gray-200 hover:bg-gray-50 font-medium">Clear</a>
        </div>
    </div>
</div>

<!-- Requests List -->
<div class="space-y-3">
    <?php if (isset($requests) && count($requests) > 0): ?>
        <?php foreach ($requests as $request): ?>
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-3 request-item" data-status="<?= strtolower($request['status']) ?>">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                    <!-- Request Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start space-x-3">
                            <!-- User Avatar -->
                            <div class="w-9 h-9 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center text-white font-bold flex-shrink-0 text-xs">
                                <?= strtoupper(substr(($request['requester_name'] ?? 'U'), 0, 1)) ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Requester Info -->
                                <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                    <h3 class="font-semibold text-sm text-gray-800"><?= htmlspecialchars($request['requester_name'] ?? 'Unknown') ?></h3>
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-500"><?= htmlspecialchars($request['requester_email']) ?></span>
                                </div>

                                <!-- Document Info -->
                                <div class="mb-2">
                                    <p class="text-xs font-medium text-gray-700 mb-0.5 flex items-center">
                                        <i class="fas fa-file-pdf text-red-500 mr-1.5 text-[10px]"></i>
                                        <?= htmlspecialchars($request['document_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        IP Record:
                                        <?php if (!empty($request['ip_record_id'])): ?>
                                            <a class="font-medium text-emerald-700 hover:text-emerald-800 hover:underline" href="<?= BASE_URL ?>/admin/ip-records/<?= (int)$request['ip_record_id'] ?>">
                                                <?= htmlspecialchars($request['ip_record_title']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="font-medium"><?= htmlspecialchars($request['ip_record_title']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <!-- Request Details -->
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= getStatusBadge($request['status']) ?>">
                                        <i class="fas fa-<?= getStatusIcon($request['status']) ?> mr-1"></i>
                                        <?= ucfirst($request['status']) ?>
                                    </span>
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= timeAgo($request['requested_at']) ?>
                                    </span>
                                </div>

                                <!-- Reason -->
                                <?php if (!empty($request['reason'])): ?>
                                    <div class="p-2 bg-emerald-50 rounded-lg border-l-3 border-emerald-500 mb-2">
                                        <p class="text-[10px] font-semibold text-emerald-900 mb-0.5 uppercase">Request Reason</p>
                                        <p class="text-xs text-emerald-800"><?= htmlspecialchars($request['reason']) ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Admin Response -->
                                <?php if (!empty($request['admin_response'])): ?>
                                    <div class="p-2 <?= $request['status'] === 'approved' ? 'bg-green-50 border-green-500' : 'bg-rose-50 border-rose-500' ?> rounded-lg border-l-3">
                                        <p class="text-xs font-semibold <?= $request['status'] === 'approved' ? 'text-green-900' : 'text-rose-900' ?> mb-0.5">
                                            Admin Response:
                                        </p>
                                        <p class="text-xs <?= $request['status'] === 'approved' ? 'text-green-800' : 'text-rose-800' ?>">
                                            <?= htmlspecialchars($request['admin_response']) ?>
                                        </p>
                                        <p class="text-[10px] <?= $request['status'] === 'approved' ? 'text-green-600' : 'text-rose-600' ?> mt-1">
                                            <i class="fas fa-user mr-1"></i>
                                            <?= htmlspecialchars($request['reviewed_by']) ?> • <?= date('M j, Y', strtotime($request['reviewed_at'])) ?>
                                        </p>
                                    </div>
                                <?php elseif (($request['status'] ?? '') !== 'pending' && !empty($request['reviewed_by']) && !empty($request['reviewed_at'])): ?>
                                    <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                                        <p class="text-[10px] text-gray-600">
                                            <i class="fas fa-user mr-1"></i>
                                            <?= htmlspecialchars($request['reviewed_by']) ?> • <?= date('M j, Y', strtotime($request['reviewed_at'])) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <?php if ($request['status'] === 'pending'): ?>
                        <div class="flex lg:flex-col gap-2 flex-shrink-0">
                            <button onclick="approveRequest(<?= $request['id'] ?>)" 
                                    class="flex-1 lg:flex-none bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center shadow-sm text-xs font-semibold">
                                <i class="fas fa-check mr-1.5"></i>
                                Approve
                            </button>
                            <button onclick="rejectRequest(<?= $request['id'] ?>)" 
                                    class="flex-1 lg:flex-none bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg transition flex items-center justify-center shadow-sm text-xs font-semibold">
                                <i class="fas fa-times mr-1.5"></i>
                                Reject
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <div class="flex items-center text-gray-500 text-xs">
                                <i class="fas fa-check-double mr-1.5"></i>
                                <span>Reviewed</span>
                            </div>

                            <div class="flex flex-wrap justify-end gap-1.5">
                                <?php if (($request['status'] ?? '') === 'approved'): ?>
                                    <button onclick="rejectRequest(<?= (int)$request['id'] ?>)"
                                            class="px-2.5 py-1.5 text-xs rounded-lg border border-rose-200 hover:bg-rose-50 text-rose-700 flex items-center gap-1">
                                        <i class="fas fa-times"></i>
                                        <span>Reject</span>
                                    </button>
                                <?php elseif (($request['status'] ?? '') === 'rejected'): ?>
                                    <button onclick="approveRequest(<?= (int)$request['id'] ?>)"
                                            class="px-2.5 py-1.5 text-xs rounded-lg border border-emerald-200 hover:bg-emerald-50 text-emerald-700 flex items-center gap-1">
                                        <i class="fas fa-check"></i>
                                        <span>Approve</span>
                                    </button>
                                <?php endif; ?>

                                <?php if (!empty($request['ip_record_id'])): ?>
                                    <a href="<?= BASE_URL ?>/admin/ip-records/<?= (int)$request['ip_record_id'] ?>"
                                       class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-700 flex items-center gap-1">
                                        <i class="fas fa-folder-open text-emerald-600"></i>
                                        <span>View</span>
                                    </a>
                                <?php endif; ?>

                                <?php if (($request['status'] ?? '') === 'approved' && !empty($request['download_token'])): ?>
                                    <button onclick="copyDownloadLink('<?= htmlspecialchars($request['download_token']) ?>')"
                                            class="px-2.5 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white flex items-center gap-1 shadow-sm">
                                        <i class="fas fa-link"></i>
                                        <span>Copy</span>
                                    </button>
                                    <a href="<?= BASE_URL ?>/document/download/<?= htmlspecialchars($request['download_token']) ?>" target="_blank" rel="noopener"
                                       class="px-2.5 py-1.5 text-xs rounded-lg border border-emerald-200 hover:bg-emerald-50 text-emerald-700 flex items-center gap-1">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-sm p-10 text-center">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
            <h3 class="text-base font-semibold text-gray-800 mb-1">No download requests</h3>
            <p class="text-sm text-gray-600">All requests have been processed</p>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($pagination) && ($pagination['total_pages'] ?? 1) > 1): ?>
    <div class="mt-4 bg-white rounded-lg shadow-sm p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div class="text-xs text-gray-600">
            Showing <span class="font-semibold"><?= (int)($pagination['start'] ?? 0) ?></span>–<span class="font-semibold"><?= (int)($pagination['end'] ?? 0) ?></span>
            of <span class="font-semibold"><?= (int)($pagination['total'] ?? 0) ?></span>
        </div>
        <div class="flex items-center justify-end gap-2">
            <?php $page = (int)($pagination['page'] ?? 1); $totalPages = (int)($pagination['total_pages'] ?? 1); ?>
            <a class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 hover:bg-gray-50 font-medium <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>"
               href="<?= buildQueryUrl(['page' => max(1, $page - 1)]) ?>">Prev</a>
            <span class="text-xs text-gray-600">Page <span class="font-semibold"><?= $page ?></span> of <span class="font-semibold"><?= $totalPages ?></span></span>
            <a class="px-3 py-1.5 text-xs rounded-lg border border-gray-200 hover:bg-gray-50 font-medium <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>"
               href="<?= buildQueryUrl(['page' => min($totalPages, $page + 1)]) ?>">Next</a>
        </div>
    </div>
<?php endif; ?>

<script>
        // SweetAlert2: minimalist + responsive styling
        // (SweetAlert popups are outside the page flow, so we size/style them with CSS + custom classes.)
        (function ensureSwalMinimalStyles() {
            const styleId = 'swal-minimal-styles';
            if (document.getElementById(styleId)) return;
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                .swal-minimal-popup{width:min(92vw, 640px)!important;max-width:640px!important;border-radius:14px!important;padding:1rem!important;}
                .swal-minimal-popup .swal2-title{font-size:1rem!important;font-weight:700!important;margin:0!important;line-height:1.25!important;}
                .swal-minimal-popup .swal2-html-container{margin:0.75rem 0 0!important;padding:0!important;color:#374151!important;}
                .swal-minimal-popup .swal2-input,
                .swal-minimal-popup .swal2-textarea{width:100%!important;box-sizing:border-box!important;margin:0.5rem 0 0!important;border:1px solid #e5e7eb!important;border-radius:10px!important;box-shadow:none!important;}
                .swal-minimal-popup .swal2-input:focus,
                .swal-minimal-popup .swal2-textarea:focus{border-color:#10b981!important;box-shadow:0 0 0 3px rgba(16,185,129,0.18)!important;}
                .swal-minimal-popup .swal2-actions{gap:0.5rem!important;margin:1rem 0 0!important;}
            `;
            document.head.appendChild(style);
        })();

        function swalBaseOptions() {
            return {
                buttonsStyling: false,
                customClass: {
                    popup: 'swal-minimal-popup',
                    actions: 'flex flex-col sm:flex-row',
                    confirmButton: 'w-full sm:w-auto px-4 py-2 rounded-lg text-sm font-semibold',
                    cancelButton: 'w-full sm:w-auto px-4 py-2 rounded-lg text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50'
                }
            };
        }

        function getDownloadLink(token) {
            if (!token) return '';
            return `<?= BASE_URL ?>/document/download/${token}`;
        }

        async function showApprovedLinkModal(res) {
            const token = res?.download_token;
            const url = res?.download_url || (token ? getDownloadLink(token) : '');

            if (!url) {
                return;
            }

            const base = swalBaseOptions();
            await Swal.fire({
                ...base,
                title: 'Approved',
                html: `
                    <div class="text-left">
                        <div class="text-xs text-gray-500">Download link</div>
                        <input class="swal2-input" value="${url}" readonly>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Copy link',
                cancelButtonText: 'Close',
                focusConfirm: false,
                customClass: {
                    ...base.customClass,
                    confirmButton: base.customClass.confirmButton + ' bg-emerald-600 text-white hover:bg-emerald-700'
                },
                preConfirm: async () => {
                    if (token) await copyDownloadLink(token);
                    return true;
                }
            });
        }

        async function copyDownloadLink(token) {
            try {
                const url = getDownloadLink(token);
                if (!url) {
                    Swal.fire({ icon: 'error', title: 'No token', text: 'This request has no download token.' });
                    return;
                }

                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(url);
                } else {
                    const ta = document.createElement('textarea');
                    ta.value = url;
                    ta.style.position = 'fixed';
                    ta.style.left = '-9999px';
                    document.body.appendChild(ta);
                    ta.focus();
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Download link copied',
                    showConfirmButton: false,
                    timer: 2000
                });
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Copy failed', text: 'Could not copy the link. Please copy it manually.' });
            }
        }

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

        function approveRequest(id) {
            const base = swalBaseOptions();
            Swal.fire({
                ...base,
                title: 'Approve request',
                html: `
                    <div class="text-left">
                        <div class="text-xs text-gray-500">Optional message</div>
                        <textarea id="approvalMessage" class="swal2-textarea" rows="3" placeholder="Add a short note (optional)"></textarea>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <div class="text-xs text-gray-500">Download limit</div>
                                <input type="number" id="downloadLimit" class="swal2-input" value="3" min="1" max="10">
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Expiry (hours)</div>
                                <input type="number" id="expiryHours" class="swal2-input" value="24" min="1" max="168">
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Approve',
                cancelButtonText: 'Cancel',
                focusConfirm: false,
                customClass: {
                    ...base.customClass,
                    confirmButton: base.customClass.confirmButton + ' bg-emerald-600 text-white hover:bg-emerald-700'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const message = document.getElementById('approvalMessage').value;
                    const limit = document.getElementById('downloadLimit').value;
                    const expiry = document.getElementById('expiryHours').value;
                    
                    ajaxRequest('<?= BASE_URL ?>/admin/download-requests/approve', {
                        request_id: id,
                        review_notes: message,
                        download_limit: limit,
                        expiry_hours: expiry
                    }, 'POST').then((res) => {
                        if (res && res.success) {
                            showToast(res.message || 'Request approved successfully', 'success');
                            showApprovedLinkModal(res).finally(() => {
                                setTimeout(() => location.reload(), 600);
                            });
                        } else {
                            showToast(res?.message || 'Failed to approve request', 'error');
                        }
                    }).catch(() => {
                        showToast('Failed to approve request', 'error');
                    });
                }
            });
        }

        function rejectRequest(id) {
            const base = swalBaseOptions();
            Swal.fire({
                ...base,
                title: 'Reject request',
                html: `
                    <div class="text-left">
                        <div class="text-xs text-gray-500">Reason (required)</div>
                        <textarea id="rejectionReason" class="swal2-textarea" rows="4" placeholder="Write a short reason…" required></textarea>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Reject',
                cancelButtonText: 'Cancel',
                focusConfirm: false,
                customClass: {
                    ...base.customClass,
                    confirmButton: base.customClass.confirmButton + ' bg-red-600 text-white hover:bg-red-700'
                },
                preConfirm: () => {
                    const reason = document.getElementById('rejectionReason').value;
                    if (!reason) {
                        Swal.showValidationMessage('Please provide a reason');
                        return false;
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    ajaxRequest('<?= BASE_URL ?>/admin/download-requests/reject', {
                        request_id: id,
                        review_notes: result.value
                    }, 'POST').then((res) => {
                        if (res && res.success) {
                            showToast(res.message || 'Request rejected', 'success');
                            setTimeout(() => location.reload(), 900);
                        } else {
                            showToast(res?.message || 'Failed to reject request', 'error');
                        }
                    }).catch(() => {
                        showToast('Failed to reject request', 'error');
                    });
                }
            });
        }
    </script>

<script src="<?= BASE_URL ?>/js/common.js?v=<?= filemtime(PUBLIC_PATH . '/js/common.js') ?>"></script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';