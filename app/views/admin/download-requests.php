<?php 
ob_start();
$page_title = 'Download Requests';

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
<div class="mb-4 sm:mb-6">
    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">Download Requests</h1>
    <p class="text-sm sm:text-base text-gray-600 mt-1">Review and manage document download requests</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
    <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-5 lg:p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Pending</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?= $stats['pending'] ?? 0 ?></h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-clock text-yellow-500 text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-5 lg:p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Approved Today</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?= $stats['approved_today'] ?? 0 ?></h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-500 text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-5 lg:p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Rejected Today</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?= $stats['rejected_today'] ?? 0 ?></h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-times-circle text-red-500 text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-5 lg:p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs sm:text-sm font-medium text-gray-600">Total Requests</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-800 mt-1 sm:mt-2"><?= $stats['total'] ?? 0 ?></h3>
            </div>
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-download text-blue-500 text-lg sm:text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-md mb-4 sm:mb-6 overflow-x-auto">
    <div class="flex border-b border-gray-200 min-w-max">
        <button onclick="filterByStatus('all')" class="status-tab active px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium hover:bg-gray-50 transition border-b-2 border-blue-500 whitespace-nowrap">
            All Requests
        </button>
        <button onclick="filterByStatus('pending')" class="status-tab px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent whitespace-nowrap">
            Pending <span class="ml-1 sm:ml-2 px-1.5 sm:px-2 py-0.5 sm:py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs"><?= $stats['pending'] ?? 0 ?></span>
        </button>
        <button onclick="filterByStatus('approved')" class="status-tab px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent whitespace-nowrap">
            Approved
        </button>
        <button onclick="filterByStatus('rejected')" class="status-tab px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent whitespace-nowrap">
            Rejected
        </button>
    </div>
</div>

<!-- Requests List -->
<div class="space-y-3 sm:space-y-4">
    <?php if (isset($requests) && count($requests) > 0): ?>
        <?php foreach ($requests as $request): ?>
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md hover:shadow-lg transition p-4 sm:p-5 lg:p-6 request-item" data-status="<?= strtolower($request['status']) ?>">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <!-- Request Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start space-x-3 sm:space-x-4">
                            <!-- User Avatar -->
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0 text-sm sm:text-base">
                                <?= strtoupper(substr($request['requester_name'], 0, 1)) ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Requester Info -->
                                <div class="flex flex-wrap items-center gap-1 sm:gap-2 mb-2">
                                    <h3 class="font-semibold text-sm sm:text-base text-gray-800 truncate"><?= htmlspecialchars($request['requester_name']) ?></h3>
                                    <span class="text-xs sm:text-sm text-gray-500 flex-shrink-0">requested access to</span>
                                </div>

                                <!-- Document Info -->
                                <div class="mb-3">
                                    <p class="text-sm sm:text-base lg:text-lg font-medium text-gray-800 mb-1 break-words">
                                        <i class="fas fa-file-pdf text-red-500 mr-1 sm:mr-2"></i>
                                        <?= htmlspecialchars($request['document_name']) ?>
                                    </p>
                                    <p class="text-xs sm:text-sm text-gray-600 truncate">
                                        IP Record: <span class="font-medium"><?= htmlspecialchars($request['ip_record_title']) ?></span>
                                    </p>
                                </div>

                                <!-- Request Details -->
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-3">
                                    <span class="px-2 sm:px-3 py-1 rounded-full text-xs font-semibold <?= getStatusBadge($request['status']) ?>">
                                        <i class="fas fa-<?= getStatusIcon($request['status']) ?> mr-1"></i>
                                        <?= ucfirst($request['status']) ?>
                                    </span>
                                    <span class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        <?= timeAgo($request['requested_at']) ?>
                                    </span>
                                    <span class="text-xs text-gray-500 hidden sm:flex items-center truncate">
                                        <i class="fas fa-envelope mr-1"></i>
                                        <?= htmlspecialchars($request['requester_email']) ?>
                                    </span>
                                </div>

                                <!-- Reason -->
                                <?php if (!empty($request['reason'])): ?>
                                    <div class="p-2 sm:p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500 mb-3">
                                        <p class="text-xs sm:text-sm font-medium text-blue-900 mb-1">Request Reason:</p>
                                        <p class="text-xs sm:text-sm text-blue-800"><?= htmlspecialchars($request['reason']) ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Admin Response -->
                                <?php if (!empty($request['admin_response'])): ?>
                                    <div class="p-2 sm:p-3 <?= $request['status'] === 'approved' ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500' ?> rounded-lg border-l-4">
                                        <p class="text-xs sm:text-sm font-medium <?= $request['status'] === 'approved' ? 'text-green-900' : 'text-red-900' ?> mb-1">
                                            Admin Response:
                                        </p>
                                        <p class="text-xs sm:text-sm <?= $request['status'] === 'approved' ? 'text-green-800' : 'text-red-800' ?>">
                                            <?= htmlspecialchars($request['admin_response']) ?>
                                        </p>
                                        <p class="text-xs <?= $request['status'] === 'approved' ? 'text-green-600' : 'text-red-600' ?> mt-2">
                                            <i class="fas fa-user mr-1"></i>
                                            Reviewed by <?= htmlspecialchars($request['reviewed_by']) ?> on <?= date('M j, Y', strtotime($request['reviewed_at'])) ?>
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
                                    class="flex-1 lg:flex-none bg-green-500 hover:bg-green-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition flex items-center justify-center shadow-md text-sm sm:text-base">
                                <i class="fas fa-check mr-1 sm:mr-2"></i>
                                Approve
                            </button>
                            <button onclick="rejectRequest(<?= $request['id'] ?>)" 
                                    class="flex-1 lg:flex-none bg-red-500 hover:bg-red-600 text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg transition flex items-center justify-center shadow-md text-sm sm:text-base">
                                <i class="fas fa-times mr-1 sm:mr-2"></i>
                                Reject
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center text-gray-500 text-xs sm:text-sm">
                            <i class="fas fa-check-double mr-2"></i>
                            <span>Reviewed</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-8 sm:p-12 text-center">
            <i class="fas fa-inbox text-4xl sm:text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">No download requests</h3>
            <p class="text-sm sm:text-base text-gray-600">All requests have been processed</p>
        </div>
    <?php endif; ?>
</div>

<script>
        function filterByStatus(status) {
            // Update tab styling
            const tabs = document.querySelectorAll('.status-tab');
            tabs.forEach(tab => {
                tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-600');
            });
            event.target.classList.add('active', 'border-blue-500', 'text-blue-600');
            event.target.classList.remove('border-transparent', 'text-gray-600');

            // Filter items
            const items = document.querySelectorAll('.request-item');
            items.forEach(item => {
                if (status === 'all' || item.dataset.status === status) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function approveRequest(id) {
            Swal.fire({
                title: 'Approve Download Request?',
                html: `
                    <div class="text-left">
                        <label class="block text-sm font-medium mb-2">Add a message (optional)</label>
                        <textarea id="approvalMessage" class="swal2-textarea w-full" rows="3" 
                                  placeholder="Your message to the requester..."></textarea>
                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-2">Download Limit</label>
                            <input type="number" id="downloadLimit" class="swal2-input" value="3" min="1" max="10">
                        </div>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Approve Request',
                confirmButtonColor: '#10b981',
                width: 600
            }).then((result) => {
                if (result.isConfirmed) {
                    const message = document.getElementById('approvalMessage').value;
                    const limit = document.getElementById('downloadLimit').value;
                    
                    ajaxRequest('<?= BASE_URL ?>/admin/download-requests/approve', {
                        request_id: id,
                        message: message,
                        download_limit: limit
                    }, 'POST').then(() => {
                        showToast('success', 'Request approved successfully');
                        setTimeout(() => location.reload(), 1000);
                    });
                }
            });
        }

        function rejectRequest(id) {
            Swal.fire({
                title: 'Reject Download Request?',
                html: `
                    <div class="text-left">
                        <label class="block text-sm font-medium mb-2">Reason for rejection *</label>
                        <textarea id="rejectionReason" class="swal2-textarea w-full" rows="4" 
                                  placeholder="Please provide a reason for rejection..." required></textarea>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Reject Request',
                confirmButtonColor: '#ef4444',
                width: 600,
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
                        reason: result.value
                    }, 'POST').then(() => {
                        showToast('success', 'Request rejected');
                        setTimeout(() => location.reload(), 1000);
                    });
                }
            });
        }

        function timeAgo(dateString) {
            return IPRepoUtils.Time.timeAgo(dateString);
        }
    </script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';