<?php ob_start(); ?>

<div class="max-w-7xl mx-auto">
    <!-- Search Form -->
    <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
        <form action="<?= BASE_URL ?>/staff/search" method="GET" class="space-y-4">
            <div class="flex gap-3">
                <input type="text" name="q" placeholder="Search by file name, title, tags, keywords, or owner..." 
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit" class="px-8 py-3 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-600 transition">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </div>
        </form>
    </div>

    <?php if (!empty($keyword)): ?>
    
    <!-- IP Records Results -->
    <?php if (!empty($results['ip_records'])): ?>
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">IP Records (<?= count($results['ip_records']) ?>)</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <?php foreach ($results['ip_records'] as $record): ?>
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <a href="<?= BASE_URL ?>/staff/ip-records/<?= $record['id'] ?>" 
                               class="text-lg font-medium text-blue-600 hover:text-blue-700">
                                <?= htmlspecialchars($record['title']) ?>
                            </a>
                            <p class="text-sm text-gray-600 mt-1">
                                <?= htmlspecialchars(substr($record['description'], 0, 150)) ?>...
                            </p>
                            <div class="flex gap-4 mt-2 text-sm text-gray-500">
                                <span><i class="fas fa-tag mr-1"></i><?= htmlspecialchars($record['type_name']) ?></span>
                                <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($record['owner']) ?></span>
                                <span><i class="fas fa-file mr-1"></i><?= $record['document_count'] ?> documents</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-<?= $record['status'] === 'active' ? 'green' : 'gray' ?>-100 
                                     text-<?= $record['status'] === 'active' ? 'green' : 'gray' ?>-800 rounded-full text-xs">
                            <?= ucfirst($record['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Documents Results -->
    <?php if (!empty($results['documents'])): ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Documents (<?= count($results['documents']) ?>)</h3>
        </div>
        <div class="p-6">
            <div class="grid gap-4">
                <?php foreach ($results['documents'] as $doc): ?>
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                                <div>
                                    <h4 class="font-medium"><?= htmlspecialchars($doc['original_name']) ?></h4>
                                    <p class="text-sm text-gray-600">
                                        IP Record: <a href="<?= BASE_URL ?>/staff/ip-records/<?= $doc['ip_record_id'] ?>" 
                                                     class="text-blue-600 hover:text-blue-700">
                                            <?= htmlspecialchars($doc['ip_title']) ?>
                                        </a>
                                    </p>
                                    <div class="flex gap-4 mt-1 text-xs text-gray-500">
                                        <span><?= number_format($doc['file_size'] / 1024, 2) ?> KB</span>
                                        <span><?= ucfirst($doc['file_type']) ?></span>
                                        <span>v<?= $doc['current_version'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>/staff/ip-records/<?= $doc['ip_record_id'] ?>" 
                           class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600">
                            View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($results['ip_records']) && empty($results['documents'])): ?>
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Results Found</h3>
        <p class="text-gray-500">Try using different keywords or check your spelling</p>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- No search yet -->
    <div class="bg-white rounded-lg shadow p-12 text-center">
        <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Start Searching</h3>
        <p class="text-gray-500">Enter keywords above to search for IP records and documents</p>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
