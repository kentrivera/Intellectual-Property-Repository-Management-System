<?php ob_start(); ?>

<div class="space-y-4 sm:space-y-6">
  <!-- Header -->
  <div class="bg-white rounded-xl shadow-lg p-4 sm:p-5">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-bold text-gray-800 flex items-center"><span class="w-1 h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-3"></span>My Profile</h2>
      <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700"><?= ucfirst($_SESSION['role'] ?? 'user') ?></span>
    </div>
  </div>

  <!-- Profile Card -->
  <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6">
    <div class="flex items-start gap-4">
      <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center text-2xl font-bold">
        <?= strtoupper(substr($user['full_name'] ?? $_SESSION['full_name'] ?? 'U', 0, 1)) ?>
      </div>
      <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <p class="text-xs text-gray-500">Full Name</p>
          <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($user['full_name'] ?? $_SESSION['full_name'] ?? 'User') ?></p>
        </div>
        <div>
          <p class="text-xs text-gray-500">Username</p>
          <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($user['username'] ?? $_SESSION['username'] ?? '') ?></p>
        </div>
        <div>
          <p class="text-xs text-gray-500">Email</p>
          <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($user['email'] ?? $_SESSION['email'] ?? '') ?></p>
        </div>
        <div>
          <p class="text-xs text-gray-500">Role</p>
          <p class="text-sm font-semibold text-gray-900"><?= ucfirst($_SESSION['role'] ?? 'user') ?></p>
        </div>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
      <a href="<?= BASE_URL ?>/settings" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-semibold hover:from-emerald-600 hover:to-green-700 shadow"><i class="fas fa-cog"></i>Account Settings</a>
      <a href="<?= BASE_URL ?>/help" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-100 text-emerald-700 text-sm font-semibold hover:bg-emerald-200"><i class="fas fa-question-circle"></i>Help & Support</a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
