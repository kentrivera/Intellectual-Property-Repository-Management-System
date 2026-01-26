<?php ob_start(); ?>
<?php 
  $totalOverview = (int)($stats['ip_stats']['total'] ?? ($stats['total_records'] ?? 0));
?>

<!-- KPIs -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-4">
  <!-- Users -->
  <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg hover:shadow-xl transition-all">
    <div class="p-4 lg:p-5 flex items-center justify-between">
      <div class="min-w-0">
        <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wide">Total Users</p>
        <p class="text-2xl lg:text-3xl font-bold mt-1 counter" data-target="<?= (int)($stats['total_users'] ?? 0) ?>">0</p>
        <button class="mt-2 text-xs font-semibold text-emerald-50 underline decoration-emerald-200/60 underline-offset-4 hover:text-white" onclick="location.href='<?= BASE_URL ?>/admin/users'">Manage users</button>
      </div>
      <div class="w-10 h-10 lg:w-12 lg:h-12 bg-white/20 rounded-xl flex items-center justify-center shadow-md"><i class="fas fa-users text-lg lg:text-xl"></i></div>
    </div>
  </div>

  <!-- IP Records -->
  <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg hover:shadow-xl transition-all">
    <div class="p-4 lg:p-5 flex items-center justify-between">
      <div class="min-w-0">
        <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wide">IP Records</p>
        <p class="text-2xl lg:text-3xl font-bold mt-1 counter" data-target="<?= (int)($stats['total_records'] ?? 0) ?>">0</p>
        <button class="mt-2 text-xs font-semibold text-emerald-50 underline decoration-emerald-200/60 underline-offset-4 hover:text-white" onclick="location.href='<?= BASE_URL ?>/admin/ip-records'">Browse records</button>
      </div>
      <div class="w-10 h-10 lg:w-12 lg:h-12 bg-white/20 rounded-xl flex items-center justify-center shadow-md"><i class="fas fa-database text-lg lg:text-xl"></i></div>
    </div>
  </div>

  <!-- Documents -->
  <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg hover:shadow-xl transition-all">
    <div class="p-4 lg:p-5 flex items-center justify-between">
      <div class="min-w-0">
        <p class="text-emerald-100 text-xs font-semibold uppercase tracking-wide">Documents</p>
        <p class="text-2xl lg:text-3xl font-bold mt-1 counter" data-target="<?= (int)($stats['total_documents'] ?? 0) ?>">0</p>
        <button class="mt-2 text-xs font-semibold text-emerald-50 underline decoration-emerald-200/60 underline-offset-4 hover:text-white" onclick="location.href='<?= BASE_URL ?>/admin/ip-records'">View documents</button>
      </div>
      <div class="w-10 h-10 lg:w-12 lg:h-12 bg-white/20 rounded-xl flex items-center justify-center shadow-md"><i class="fas fa-file-alt text-lg lg:text-xl"></i></div>
    </div>
  </div>

  <!-- Requests -->
  <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg hover:shadow-xl transition-all">
    <div class="p-4 lg:p-5 flex items-center justify-between">
      <div class="min-w-0">
        <p class="text-amber-100 text-xs font-semibold uppercase tracking-wide">Pending Requests</p>
        <p class="text-2xl lg:text-3xl font-bold mt-1 counter" data-target="<?= (int)($stats['pending_requests'] ?? 0) ?>">0</p>
        <button class="mt-2 text-xs font-semibold text-amber-50 underline decoration-amber-200/60 underline-offset-4 hover:text-white" onclick="location.href='<?= BASE_URL ?>/admin/download-requests'">Review requests</button>
      </div>
      <div class="w-10 h-10 lg:w-12 lg:h-12 bg-white/20 rounded-xl flex items-center justify-center shadow-md"><i class="fas fa-clock text-lg lg:text-xl <?= !empty($stats['pending_requests']) ? 'animate-pulse' : '' ?>"></i></div>
    </div>
  </div>
</section>

<!-- Overview and Actions -->
<section class="grid grid-cols-1 xl:grid-cols-3 gap-4 lg:gap-6 mb-6">
  <!-- Status Overview -->
  <div class="xl:col-span-2 bg-white rounded-xl shadow-lg p-5 lg:p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-gray-800 flex items-center"><span class="w-1 h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-3"></span>Record Status Overview</h3>
      <button onclick="refreshStats()" class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold"><i class="fas fa-sync-alt mr-2"></i>Refresh</button>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3">
      <!-- Pending -->
      <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 hover:shadow-md cursor-pointer" onclick="filterByStatus('pending')">
        <div class="text-2xl font-bold text-amber-700 counter" data-target="<?= (int)($stats['ip_stats']['pending'] ?? 0) ?>">0</div>
        <div class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Pending</div>
        <div class="mt-2 h-1.5 bg-amber-200 rounded-full"><div class="h-1.5 rounded-full bg-amber-500 stat-bar" data-percent="<?= $totalOverview > 0 ? (int)round((($stats['ip_stats']['pending'] ?? 0) / $totalOverview) * 100) : 0 ?>" style="width:0%"></div></div>
      </div>
      <!-- Approved -->
      <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 hover:shadow-md cursor-pointer" onclick="filterByStatus('approved')">
        <div class="text-2xl font-bold text-emerald-700 counter" data-target="<?= (int)($stats['ip_stats']['approved'] ?? 0) ?>">0</div>
        <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Approved</div>
        <div class="mt-2 h-1.5 bg-emerald-200 rounded-full"><div class="h-1.5 rounded-full bg-emerald-500 stat-bar" data-percent="<?= $totalOverview > 0 ? (int)round((($stats['ip_stats']['approved'] ?? 0) / $totalOverview) * 100) : 0 ?>" style="width:0%"></div></div>
      </div>
      <!-- Active -->
      <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50 hover:shadow-md cursor-pointer" onclick="filterByStatus('active')">
        <div class="text-2xl font-bold text-emerald-700 counter" data-target="<?= (int)($stats['ip_stats']['active'] ?? 0) ?>">0</div>
        <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Active</div>
        <div class="mt-2 h-1.5 bg-emerald-200 rounded-full"><div class="h-1.5 rounded-full bg-emerald-600 stat-bar" data-percent="<?= $totalOverview > 0 ? (int)round((($stats['ip_stats']['active'] ?? 0) / $totalOverview) * 100) : 0 ?>" style="width:0%"></div></div>
      </div>
      <!-- Rejected -->
      <div class="p-4 rounded-xl border border-red-200 bg-red-50 hover:shadow-md cursor-pointer" onclick="filterByStatus('rejected')">
        <div class="text-2xl font-bold text-red-700 counter" data-target="<?= (int)($stats['ip_stats']['rejected'] ?? 0) ?>">0</div>
        <div class="text-xs font-semibold text-red-700 uppercase tracking-wide">Rejected</div>
        <div class="mt-2 h-1.5 bg-red-200 rounded-full"><div class="h-1.5 rounded-full bg-red-500 stat-bar" data-percent="<?= $totalOverview > 0 ? (int)round((($stats['ip_stats']['rejected'] ?? 0) / $totalOverview) * 100) : 0 ?>" style="width:0%"></div></div>
      </div>
      <!-- Expired -->
      <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 hover:shadow-md cursor-pointer" onclick="filterByStatus('expired')">
        <div class="text-2xl font-bold text-gray-700 counter" data-target="<?= (int)($stats['ip_stats']['expired'] ?? 0) ?>">0</div>
        <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Expired</div>
        <div class="mt-2 h-1.5 bg-gray-200 rounded-full"><div class="h-1.5 rounded-full bg-gray-500 stat-bar" data-percent="<?= $totalOverview > 0 ? (int)round((($stats['ip_stats']['expired'] ?? 0) / $totalOverview) * 100) : 0 ?>" style="width:0%"></div></div>
      </div>
    </div>
  </div>

  <!-- Actions & Requests -->
  <div class="space-y-4">
    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg p-5">
      <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center"><span class="w-1 h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-3"></span>Quick Actions</h3>
      <div class="grid grid-cols-2 gap-3">
        <button class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white shadow hover:from-emerald-600 hover:to-green-700" onclick="location.href='<?= BASE_URL ?>/admin/ip-records/create'"><i class="fas fa-plus"></i><span class="text-sm font-semibold">New Record</span></button>
        <button class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-100 text-emerald-700 hover:bg-emerald-200" onclick="location.href='<?= BASE_URL ?>/admin/ip-records'"><i class="fas fa-folder-open"></i><span class="text-sm font-semibold">Browse Records</span></button>
        <button class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-100 text-emerald-700 hover:bg-emerald-200" onclick="location.href='<?= BASE_URL ?>/admin/users'"><i class="fas fa-users"></i><span class="text-sm font-semibold">Manage Users</span></button>
        <button class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-amber-100 text-amber-700 hover:bg-amber-200" onclick="location.href='<?= BASE_URL ?>/admin/download-requests'"><i class="fas fa-file-download"></i><span class="text-sm font-semibold">Review Requests</span></button>
      </div>
    </div>

    <!-- Requests -->
    <div class="bg-white rounded-xl shadow-lg p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 flex items-center"><span class="w-1 h-6 bg-gradient-to-b from-amber-500 to-orange-600 rounded-full mr-3"></span>Recent Requests</h3>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700"><?= count($pending_requests) ?> pending</span>
      </div>
      <?php if (empty($pending_requests)): ?>
        <div class="text-center py-8">
          <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-check-circle text-emerald-600 text-2xl"></i></div>
          <p class="text-gray-600 font-medium">No pending requests</p>
          <p class="text-gray-400 text-sm">All caught up!</p>
        </div>
      <?php else: ?>
        <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar pr-2">
          <?php foreach ($pending_requests as $index => $request): ?>
            <div class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 transition-colors request-item" style="animation-delay: <?= $index * 0.1 ?>s">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-400 to-orange-600 text-white flex items-center justify-center flex-shrink-0 shadow"><i class="fas fa-file-download text-sm"></i></div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($request['document_name']) ?></p>
                <p class="text-xs text-gray-600 mt-0.5"><i class="fas fa-user text-gray-400 mr-1"></i><?= htmlspecialchars($request['requested_by_name']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-clock text-gray-400 mr-1"></i><?= date('M d, Y - h:i A', strtotime($request['created_at'])) ?></p>
              </div>
              <span class="px-2 py-1 rounded-lg text-xs font-bold bg-yellow-100 text-yellow-700 flex-shrink-0">Pending</span>
            </div>
          <?php endforeach; ?>
        </div>
        <a href="<?= BASE_URL ?>/admin/download-requests" class="block mt-4 text-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-semibold hover:from-emerald-600 hover:to-green-700 shadow">View all requests <i class="fas fa-arrow-right ml-1"></i></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Recent Activity -->
<section class="bg-white rounded-xl shadow-lg p-5 lg:p-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
    <h3 class="text-lg font-bold text-gray-800 flex items-center mb-2 sm:mb-0"><span class="w-1 h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-3"></span>Recent Activity</h3>
    <div class="flex items-center gap-2">
      <button onclick="filterActivity('all')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-100 text-emerald-700 hover:bg-emerald-200 activity-filter active">All</button>
      <button onclick="filterActivity('today')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 activity-filter">Today</button>
    </div>
  </div>

  <?php if (empty($recent_activity)): ?>
    <div class="text-center py-10">
      <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-history text-gray-400 text-3xl"></i></div>
      <p class="text-gray-600 font-medium">No recent activity</p>
      <p class="text-gray-400 text-sm">Activity logs will appear here</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto -mx-4 sm:mx-0 sm:rounded-lg border-y sm:border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden md:table-cell">Action</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Description</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden lg:table-cell">Time</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php foreach ($recent_activity as $index => $activity): ?>
          <tr class="hover:bg-emerald-50 transition-colors activity-row" style="animation-delay: <?= $index * 0.05 ?>s" data-timestamp="<?= strtotime($activity['created_at']) ?>">
            <td class="px-4 py-3 whitespace-nowrap">
              <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 text-white font-bold text-xs flex items-center justify-center mr-2 flex-shrink-0"><?= strtoupper(substr($activity['full_name'] ?? 'S', 0, 1)) ?></div>
                <span class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($activity['full_name'] ?? 'System') ?></span>
              </div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap hidden md:table-cell">
              <?php
                $actionColors = [
                  'create' => 'bg-green-100 text-green-700',
                  'update' => 'bg-blue-100 text-blue-700',
                  'delete' => 'bg-red-100 text-red-700',
                  'login' => 'bg-purple-100 text-purple-700',
                  'logout' => 'bg-gray-100 text-gray-700',
                  'download' => 'bg-amber-100 text-amber-700'
                ];
                $actionType = strtolower($activity['action_type']);
                $colorClass = $actionColors[$actionType] ?? 'bg-blue-100 text-blue-700';
              ?>
              <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $colorClass ?>"><?= htmlspecialchars($activity['action_type']) ?></span>
            </td>
            <td class="px-4 py-3">
              <p class="text-sm text-gray-900 line-clamp-2"><?= htmlspecialchars($activity['description']) ?></p>
              <p class="text-xs text-gray-500 mt-1 md:hidden"><span class="font-medium"><?= htmlspecialchars($activity['action_type']) ?></span> • <?= date('M d, h:i A', strtotime($activity['created_at'])) ?></p>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
              <div class="flex flex-col">
                <span class="font-medium text-gray-700"><?= date('M d, Y', strtotime($activity['created_at'])) ?></span>
                <span class="text-xs text-gray-400"><?= date('h:i A', strtotime($activity['created_at'])) ?></span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="flex flex-col sm:flex-row justify-between items-center mt-4 gap-2">
      <p class="text-sm text-gray-600">Showing <?= count($recent_activity) ?> activities</p>
      <a href="<?= BASE_URL ?>/admin/activity-logs" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-semibold hover:from-emerald-600 hover:to-green-700 shadow">View all logs <i class="fas fa-arrow-right ml-1"></i></a>
    </div>
  <?php endif; ?>
</section>

<!-- Dashboard Interactions -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Counter animation
  document.querySelectorAll('.counter').forEach(counter => {
    const target = parseInt(counter.dataset.target || '0', 10);
    const duration = 1200;
    const step = Math.max(1, Math.floor(target / (duration / 16)));
    let current = 0;
    const tick = () => {
      current += step;
      if (current < target) { counter.textContent = current; requestAnimationFrame(tick); }
      else { counter.textContent = target; }
    };
    setTimeout(() => requestAnimationFrame(tick), 100);
  });

  // Stat bars
  setTimeout(() => {
    document.querySelectorAll('.stat-bar').forEach(bar => {
      bar.style.width = (bar.dataset.percent || 0) + '%';
    });
  }, 300);
});

function filterByStatus(status) {
  location.href = '<?= BASE_URL ?>/admin/ip-records?status=' + encodeURIComponent(status);
}

function refreshStats() { location.reload(); }

function filterActivity(type) {
  const buttons = document.querySelectorAll('.activity-filter');
  const rows = document.querySelectorAll('.activity-row');
  const now = Math.floor(Date.now() / 1000);
  const todayStart = now - (now % 86400);
  buttons.forEach(btn => btn.classList.remove('active','bg-emerald-200','text-emerald-800'));
  event.target.classList.add('active','bg-emerald-200','text-emerald-800');
  rows.forEach(row => {
    const ts = parseInt(row.dataset.timestamp || '0', 10);
    row.style.display = (type === 'all' || ts >= todayStart) ? '' : 'none';
  });
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
@media (max-width: 768px) { .activity-row td { padding: 0.5rem 0.75rem; font-size: 0.875rem; } table { font-size: 0.875rem; } }
@media (max-width: 640px) { .activity-row td { padding: 0.5rem 0.75rem; font-size: 0.8125rem; } .activity-row td:first-child { max-width: 120px; } .activity-row td:nth-child(3) { max-width: 180px; } }
</style>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
