<?php ob_start(); ?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">IP Records</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['total_records'] ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-folder text-green-600 text-xl"></i>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/staff/ip-records" class="text-sm text-blue-600 hover:text-blue-700 mt-2 inline-block">
            Browse →
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Documents</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['total_documents'] ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-file-pdf text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">My Requests</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['my_requests'] ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-download text-blue-600 text-xl"></i>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/staff/my-requests" class="text-sm text-blue-600 hover:text-blue-700 mt-2 inline-block">
            View →
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Pending</p>
                <p class="text-3xl font-bold text-gray-800 mt-2"><?= $stats['pending_requests'] ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Search Box -->
<div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-8 mb-6">
    <h3 class="text-2xl font-bold text-white mb-4">Search Repository</h3>
    <form action="<?= BASE_URL ?>/staff/search" method="GET" class="flex gap-3">
        <input type="text" name="q" placeholder="Search by file name, title, tags, or keywords..." 
               class="flex-1 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-white"
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="px-8 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-gray-100 transition">
            <i class="fas fa-search mr-2"></i>Search
        </button>
    </form>
</div>

<!-- My Recent Requests -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold mb-4">My Recent Download Requests</h3>
    <?php if (empty($my_requests)): ?>
        <div class="text-center py-8">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
            <p class="text-gray-500">No download requests yet</p>
            <a href="<?= BASE_URL ?>/staff/ip-records" class="mt-4 inline-block px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                Browse IP Records
            </a>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Document</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">IP Record</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Requested</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_requests as $request): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm">
                            <?= htmlspecialchars($request['document_name']) ?>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            <?= htmlspecialchars($request['ip_title']) ?>
                        </td>
                        <td class="py-3 px-4 text-sm">
                            <?php if ($request['status'] === 'pending'): ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Pending</span>
                            <?php elseif ($request['status'] === 'approved'): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Approved</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-500">
                            <?= date('M d, Y', strtotime($request['created_at'])) ?>
                        </td>
                        <td class="py-3 px-4 text-sm">
                            <?php if ($request['status'] === 'approved' && strtotime($request['token_expires_at']) > time() && $request['download_count'] < $request['download_limit']): ?>
                                <a href="<?= BASE_URL ?>/document/download/<?= $request['download_token'] ?>" 
                                   class="text-blue-600 hover:text-blue-700">
                                    <i class="fas fa-download mr-1"></i>Download
                                </a>
                            <?php elseif ($request['status'] === 'approved'): ?>
                                <span class="text-gray-400 text-xs">Expired</span>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <a href="<?= BASE_URL ?>/staff/my-requests" class="block mt-4 text-center text-blue-600 hover:text-blue-700 text-sm font-medium">
            View All Requests →
        </a>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
