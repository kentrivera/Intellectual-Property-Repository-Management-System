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
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php 
        $pageTitle = 'My Download Requests';
        include APP_PATH . '/views/components/sidebar-staff.php'; 
        ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
            <!-- Header -->
            <?php include APP_PATH . '/views/components/header.php'; ?>

            <!-- My Requests Content -->
            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">My Download Requests</h1>
                    <p class="text-gray-600 mt-1">Track the status of your document download requests</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Pending</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['pending'] ?? 0 ?></h3>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Approved</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['approved'] ?? 0 ?></h3>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Rejected</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['rejected'] ?? 0 ?></h3>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-times-circle text-red-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Downloaded</p>
                                <h3 class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['downloaded'] ?? 0 ?></h3>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-download text-blue-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="bg-white rounded-xl shadow-md mb-6 overflow-hidden">
                    <div class="flex flex-wrap border-b border-gray-200">
                        <button onclick="filterRequests('all')" class="filter-tab active px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-blue-500">
                            All Requests
                        </button>
                        <button onclick="filterRequests('pending')" class="filter-tab px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent">
                            Pending
                        </button>
                        <button onclick="filterRequests('approved')" class="filter-tab px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent">
                            Approved
                        </button>
                        <button onclick="filterRequests('rejected')" class="filter-tab px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent">
                            Rejected
                        </button>
                    </div>
                </div>

                <!-- Requests List -->
                <div class="space-y-4">
                    <?php if (isset($requests) && count($requests) > 0): ?>
                        <?php foreach ($requests as $request): ?>
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition overflow-hidden request-item" data-status="<?= strtolower($request['status']) ?>">
                                <div class="p-6">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
                                        <!-- Document Info -->
                                        <div class="flex-1">
                                            <div class="flex items-start space-x-4">
                                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-file-pdf text-blue-600 text-xl"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="text-lg font-semibold text-gray-800 mb-1">
                                                        <?= htmlspecialchars($request['document_name']) ?>
                                                    </h3>
                                                    <p class="text-sm text-gray-600 mb-2">
                                                        IP Record: <span class="font-medium"><?= htmlspecialchars($request['ip_record_title']) ?></span>
                                                    </p>
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= getStatusBadge($request['status']) ?>">
                                                            <?= ucfirst($request['status']) ?>
                                                        </span>
                                                        <span class="text-xs text-gray-500">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Requested <?= timeAgo($request['requested_at']) ?>
                                                        </span>
                                                    </div>

                                                    <!-- Reason Section -->
                                                    <?php if (!empty($request['reason'])): ?>
                                                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                                            <p class="text-sm text-gray-700">
                                                                <strong>Reason:</strong> <?= htmlspecialchars($request['reason']) ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Admin Response -->
                                                    <?php if (!empty($request['admin_response'])): ?>
                                                        <div class="mt-3 p-3 <?= $request['status'] === 'approved' ? 'bg-green-50' : 'bg-red-50' ?> rounded-lg">
                                                            <p class="text-sm font-medium <?= $request['status'] === 'approved' ? 'text-green-800' : 'text-red-800' ?>">
                                                                Admin Response:
                                                            </p>
                                                            <p class="text-sm <?= $request['status'] === 'approved' ? 'text-green-700' : 'text-red-700' ?> mt-1">
                                                                <?= htmlspecialchars($request['admin_response']) ?>
                                                            </p>
                                                            <p class="text-xs <?= $request['status'] === 'approved' ? 'text-green-600' : 'text-red-600' ?> mt-1">
                                                                <i class="fas fa-user mr-1"></i>
                                                                Reviewed by <?= htmlspecialchars($request['reviewed_by']) ?> on <?= date('M j, Y', strtotime($request['reviewed_at'])) ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="mt-4 lg:mt-0 lg:ml-6 flex flex-col space-y-2">
                                            <?php if ($request['status'] === 'approved' && !empty($request['download_token'])): ?>
                                                <?php if ($request['downloads_remaining'] > 0): ?>
                                                    <button onclick="downloadDocument('<?= $request['download_token'] ?>')" 
                                                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition flex items-center justify-center">
                                                        <i class="fas fa-download mr-2"></i>
                                                        Download
                                                    </button>
                                                    <p class="text-xs text-gray-600 text-center">
                                                        <?= $request['downloads_remaining'] ?> downloads left
                                                    </p>
                                                    <?php if ($request['download_expires']): ?>
                                                        <p class="text-xs text-gray-500 text-center">
                                                            Expires <?= date('M j, Y', strtotime($request['download_expires'])) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm text-center">
                                                        Download limit reached
                                                    </div>
                                                <?php endif; ?>
                                            <?php elseif ($request['status'] === 'pending'): ?>
                                                <div class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg text-sm text-center">
                                                    <i class="fas fa-hourglass-half mr-1"></i>
                                                    Awaiting approval
                                                </div>
                                            <?php elseif ($request['status'] === 'rejected'): ?>
                                                <button onclick="requestAgain(<?= $request['document_id'] ?>)" 
                                                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition flex items-center justify-center">
                                                    <i class="fas fa-redo mr-2"></i>
                                                    Request Again
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow-md p-12 text-center">
                            <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">No download requests yet</h3>
                            <p class="text-gray-600 mb-4">Start by browsing IP records and requesting document downloads</p>
                            <a href="<?= BASE_URL ?>/staff/ip-records" class="inline-flex items-center bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition">
                                <i class="fas fa-folder-open mr-2"></i>
                                Browse IP Records
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/common.js"></script>
    <script>
        function filterRequests(status) {
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
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

        function downloadDocument(token) {
            showLoading('Preparing download...');
            
            // Create download link
            const downloadUrl = '<?= BASE_URL ?>/document/download/' + token;
            window.location.href = downloadUrl;
            
            setTimeout(() => {
                hideLoading();
                showToast('success', 'Download started!');
                setTimeout(() => location.reload(), 2000);
            }, 1000);
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
                    }, 'POST').then(() => {
                        showToast('success', 'Download request submitted successfully');
                        setTimeout(() => location.reload(), 1000);
                    });
                }
            });
        }

        function timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60
            };
            
            for (const [unit, secondsInUnit] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInUnit);
                if (interval >= 1) {
                    return interval + ' ' + unit + (interval > 1 ? 's' : '') + ' ago';
                }
            }
            return 'just now';
        }
    </script>
</body>
</html>

<?php
function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-yellow-100 text-yellow-700',
        'approved' => 'bg-green-100 text-green-700',
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
