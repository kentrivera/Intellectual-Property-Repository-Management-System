<?php ob_start(); ?>

<?php
    $keyword = (string)($keyword ?? '');
    $results = $results ?? ['ip_records' => [], 'documents' => []];

    function statusPillClass($status) {
        $status = strtolower((string)$status);
        if ($status === 'active') return 'bg-emerald-100 text-emerald-800';
        if ($status === 'archived') return 'bg-gray-100 text-gray-700';
        return 'bg-gray-100 text-gray-700';
    }
?>

<div class="max-w-7xl mx-auto">
    <!-- Search Form -->
    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-4 sm:p-5 mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-gray-900">Search</h1>
                <p class="text-[11px] sm:text-xs text-gray-600 mt-0.5">Search IP records and documents using keywords.</p>
            </div>
        </div>

        <form action="<?= BASE_URL ?>/staff/search" method="GET" class="mt-3">
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" name="q" placeholder="Search by title, filename, tags, keywords, owner…"
                       class="flex-1 px-3 py-2.5 rounded-lg border border-emerald-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400"
                       value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit" class="px-5 py-2.5 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700 transition text-sm inline-flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </div>

            <?php if (!empty($keyword)): ?>
                <div class="mt-2 text-[11px] sm:text-xs text-gray-600">
                    Showing results for <span class="font-semibold text-emerald-900">"<?= htmlspecialchars($keyword) ?>"</span>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!empty($keyword)): ?>
    
    <!-- IP Records Results -->
    <?php if (!empty($results['ip_records'])): ?>
    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm mb-4 sm:mb-6">
        <div class="px-4 sm:px-5 py-3 border-b border-emerald-100 flex items-center justify-between gap-3">
            <h3 class="text-sm sm:text-base font-bold text-gray-900">IP Records</h3>
            <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-900 text-[11px] font-semibold border border-emerald-100">
                <?= count($results['ip_records']) ?>
            </span>
        </div>
        <div class="p-4 sm:p-5">
            <div class="space-y-2 sm:space-y-3">
                <?php foreach ($results['ip_records'] as $record): ?>
                <div class="bg-white rounded-xl border border-emerald-100 p-3 sm:p-4 hover:shadow-sm transition">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="<?= BASE_URL ?>/staff/ip-records/<?= (int)$record['id'] ?>"
                                   class="text-sm sm:text-base font-bold text-emerald-800 hover:text-emerald-900 hover:underline break-words">
                                    <?= htmlspecialchars((string)($record['title'] ?? '')) ?>
                                </a>
                                <span class="px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold <?= statusPillClass($record['status'] ?? '') ?>">
                                    <?= ucfirst((string)($record['status'] ?? '')) ?>
                                </span>
                            </div>

                            <?php if (!empty($record['description'])): ?>
                                <p class="text-[11px] sm:text-xs text-gray-600 mt-1">
                                    <?= htmlspecialchars(mb_strimwidth((string)$record['description'], 0, 180, '…')) ?>
                                </p>
                            <?php endif; ?>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-[11px] sm:text-xs text-gray-600">
                                <?php if (!empty($record['type_name'])): ?>
                                    <span class="inline-flex items-center"><i class="fas fa-tag mr-1 text-emerald-700"></i><?= htmlspecialchars((string)$record['type_name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($record['owner'])): ?>
                                    <span class="inline-flex items-center"><i class="fas fa-user mr-1 text-emerald-700"></i><?= htmlspecialchars((string)$record['owner']) ?></span>
                                <?php endif; ?>
                                <span class="inline-flex items-center"><i class="fas fa-file mr-1 text-emerald-700"></i><?= (int)($record['document_count'] ?? 0) ?> documents</span>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <a href="<?= BASE_URL ?>/staff/ip-records/<?= (int)$record['id'] ?>"
                               class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm">
                                <i class="fas fa-folder-open text-xs"></i>
                                Open
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Documents Results -->
    <?php if (!empty($results['documents'])): ?>
    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm">
        <div class="px-4 sm:px-5 py-3 border-b border-emerald-100 flex items-center justify-between gap-3">
            <h3 class="text-sm sm:text-base font-bold text-gray-900">Documents</h3>
            <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-900 text-[11px] font-semibold border border-emerald-100">
                <?= count($results['documents']) ?>
            </span>
        </div>
        <div class="p-4 sm:p-5">
            <div class="space-y-2 sm:space-y-3">
                <?php foreach ($results['documents'] as $doc): ?>
                <div class="bg-white rounded-xl border border-emerald-100 p-3 sm:p-4 hover:shadow-sm transition">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-pdf text-emerald-700 text-sm sm:text-base"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 break-words"><?= htmlspecialchars((string)($doc['original_name'] ?? '')) ?></p>
                                    <p class="text-[11px] sm:text-xs text-gray-600 mt-0.5 truncate">
                                        IP Record:
                                        <a href="<?= BASE_URL ?>/staff/ip-records/<?= (int)($doc['ip_record_id'] ?? 0) ?>"
                                           class="font-semibold text-emerald-800 hover:text-emerald-900 hover:underline">
                                            <?= htmlspecialchars((string)($doc['ip_title'] ?? '')) ?>
                                        </a>
                                    </p>

                                    <div class="flex flex-wrap gap-x-3 gap-y-1 mt-2 text-[10px] sm:text-[11px] text-gray-600">
                                        <?php if (!empty($doc['file_size'])): ?>
                                            <span><?= number_format(((float)$doc['file_size']) / 1024, 2) ?> KB</span>
                                        <?php endif; ?>
                                        <?php if (!empty($doc['file_type'])): ?>
                                            <span><?= ucfirst((string)$doc['file_type']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($doc['current_version'])): ?>
                                            <span>v<?= (int)$doc['current_version'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <div class="flex flex-row sm:flex-col gap-2">
                                <button type="button"
                                        onclick="previewDocument(<?= (int)($doc['id'] ?? 0) ?>, '<?= htmlspecialchars((string)($doc['original_name'] ?? ''), ENT_QUOTES) ?>')"
                                        class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm">
                                    <i class="fas fa-eye text-xs"></i>
                                    Preview
                                </button>
                                <a href="<?= BASE_URL ?>/staff/ip-records/<?= (int)($doc['ip_record_id'] ?? 0) ?>"
                                   class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-emerald-200 hover:bg-emerald-50 text-emerald-800 text-xs font-semibold">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                    View record
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($results['ip_records']) && empty($results['documents'])): ?>
    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-8 sm:p-12 text-center">
        <i class="fas fa-search text-4xl text-emerald-200 mb-3"></i>
        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1">No results found</h3>
        <p class="text-xs sm:text-sm text-gray-600">Try different keywords or check spelling.</p>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- No search yet -->
    <div class="bg-white/80 backdrop-blur rounded-xl border border-emerald-100 shadow-sm p-8 sm:p-12 text-center">
        <i class="fas fa-magnifying-glass text-4xl text-emerald-200 mb-3"></i>
        <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1">Start searching</h3>
        <p class="text-xs sm:text-sm text-gray-600">Enter keywords above to find IP records and documents.</p>
    </div>
    <?php endif; ?>
</div>

<style>
    .swal-preview-popup{width:min(96vw, 980px)!important;max-width:980px!important;border-radius:14px!important;padding:0!important;overflow:hidden!important;}
    .swal-preview-title{padding:14px 16px!important;margin:0!important;text-align:left!important;font-size:14px!important;font-weight:800!important;color:#111827!important;}
    .swal-preview-html{margin:0!important;padding:0!important;}
</style>

<script>
    function previewDocument(documentId, name) {
        const id = Number(documentId || 0);
        if (!id) {
            Swal.fire({ icon: 'error', title: 'Preview unavailable', text: 'Missing document id.' });
            return;
        }

        const title = (name && String(name).trim()) ? String(name).trim() : 'Document preview';
        const url = '<?= BASE_URL ?>/document/preview/' + encodeURIComponent(String(id));

        Swal.fire({
            title,
            html: `
                <div class="w-full bg-gray-50 border-t border-gray-100">
                    <iframe src="${url}" title="Preview" class="w-full" style="height: min(72vh, 760px); border: 0;"></iframe>
                </div>
            `,
            showCancelButton: true,
            cancelButtonText: 'Close',
            showConfirmButton: true,
            confirmButtonText: 'Open in new tab',
            focusConfirm: false,
            customClass: {
                popup: 'swal-preview-popup',
                title: 'swal-preview-title',
                htmlContainer: 'swal-preview-html',
                actions: 'flex flex-col sm:flex-row gap-2 p-3 bg-white border-t border-gray-100',
                confirmButton: 'w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700',
                cancelButton: 'w-full sm:w-auto px-4 py-2 rounded-lg text-xs font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50'
            },
            buttonsStyling: false,
            preConfirm: () => {
                window.open(url, '_blank', 'noopener');
                return false;
            }
        });
    }
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
