<?php ob_start(); ?>

<div class="space-y-4 sm:space-y-6">
  <!-- Header -->
  <div class="bg-white rounded-xl shadow-lg p-4 sm:p-5">
    <h2 class="text-lg font-bold text-gray-800 flex items-center"><span class="w-1 h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-3"></span>Settings</h2>
    <p class="text-xs text-gray-500 mt-1">Manage your account preferences</p>
  </div>

  <!-- Preferences -->
  <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
    <form id="accountSettingsForm" method="post" action="#" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-semibold text-gray-600">Full Name</label>
          <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" class="mt-1 w-full border-2 border-emerald-200/50 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400" placeholder="Your name">
        </div>
        <div>
          <label class="text-xs font-semibold text-gray-600">Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="mt-1 w-full border-2 border-emerald-200/50 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400" placeholder="you@example.com">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-semibold text-gray-600">New Password</label>
          <input type="password" name="password" class="mt-1 w-full border-2 border-emerald-200/50 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400" placeholder="••••••••">
        </div>
        <div>
          <label class="text-xs font-semibold text-gray-600">Confirm Password</label>
          <input type="password" name="password_confirm" class="mt-1 w-full border-2 border-emerald-200/50 rounded-xl px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-400" placeholder="••••••••">
        </div>
      </div>

      <div class="flex items-center justify-end gap-2">
        <a href="<?= BASE_URL ?>/profile" class="px-4 py-2 rounded-xl bg-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-300">Cancel</a>
        <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-semibold hover:from-emerald-600 hover:to-green-700 shadow">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
// Placeholder submission handling (client-side)
document.getElementById('accountSettingsForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  IPRepo.showToast('Settings saved (demo)', 'success');
});
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
