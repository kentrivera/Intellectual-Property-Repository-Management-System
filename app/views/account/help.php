<?php ob_start(); ?>

<div class="space-y-4 sm:space-y-6">
  <!-- Header -->
  <div class="bg-white rounded-xl shadow-lg p-4 sm:p-5">
    <h2 class="text-lg font-bold text-gray-800 flex items-center"><span class="w-1 h-6 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-3"></span>Help & Support</h2>
    <p class="text-xs text-gray-500 mt-1">Find guidance and contact support</p>
  </div>

  <!-- Help Content -->
  <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 space-y-5">
    <div>
      <h3 class="text-sm font-semibold text-gray-800">Quick Guides</h3>
      <ul class="mt-2 list-disc list-inside text-sm text-gray-700">
        <li><a class="text-emerald-700 hover:underline" href="<?= BASE_URL ?>/admin/ip-records">Managing IP Records</a></li>
        <li><a class="text-emerald-700 hover:underline" href="<?= BASE_URL ?>/admin/download-requests">Handling Download Requests</a></li>
        <li><a class="text-emerald-700 hover:underline" href="<?= BASE_URL ?>/staff/help">Staff Help Center</a></li>
      </ul>
    </div>

    <div>
      <h3 class="text-sm font-semibold text-gray-800">FAQ</h3>
      <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
          <p class="text-xs font-bold text-gray-700">How do I change my password?</p>
          <p class="text-xs text-gray-600 mt-1">Go to Settings and update your password fields.</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
          <p class="text-xs font-bold text-gray-700">Where are my download requests?</p>
          <p class="text-xs text-gray-600 mt-1">Admins: see Download Requests; Staff: My Requests.</p>
        </div>
      </div>
    </div>

    <div>
      <h3 class="text-sm font-semibold text-gray-800">Contact Support</h3>
      <p class="text-sm text-gray-700 mt-1">Email: <span class="font-mono">support@example.com</span> • Phone: <span class="font-mono">+1-800-000-0000</span></p>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
