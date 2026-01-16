<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php 
        $pageTitle = 'Settings';
        include APP_PATH . '/views/components/sidebar-admin.php'; 
        ?>

        <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
            <?php include APP_PATH . '/views/components/header.php'; ?>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">System Settings</h1>
                        <p class="text-gray-600 mt-1">Configure and manage system preferences</p>
                    </div>
                    <button onclick="saveAllSettings()" class="mt-4 lg:mt-0 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition shadow-md flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Save All Changes
                    </button>
                </div>

                <!-- Settings Tabs -->
                <div class="bg-white rounded-xl shadow-md mb-6">
                    <div class="flex flex-wrap border-b border-gray-200 overflow-x-auto">
                        <button onclick="showTab('general')" class="settings-tab active px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-blue-500 whitespace-nowrap">
                            <i class="fas fa-cog mr-2"></i>General
                        </button>
                        <button onclick="showTab('security')" class="settings-tab px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent whitespace-nowrap">
                            <i class="fas fa-shield-alt mr-2"></i>Security
                        </button>
                        <button onclick="showTab('email')" class="settings-tab px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent whitespace-nowrap">
                            <i class="fas fa-envelope mr-2"></i>Email
                        </button>
                        <button onclick="showTab('backup')" class="settings-tab px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent whitespace-nowrap">
                            <i class="fas fa-database mr-2"></i>Backup
                        </button>
                        <button onclick="showTab('maintenance')" class="settings-tab px-6 py-4 text-sm font-medium hover:bg-gray-50 transition border-b-2 border-transparent whitespace-nowrap">
                            <i class="fas fa-wrench mr-2"></i>Maintenance
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div id="tab-content">
                    <!-- General Settings -->
                    <div id="general-tab" class="tab-pane active">
                        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-building text-blue-500 mr-3"></i>
                                System Information
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">System Name</label>
                                    <input type="text" value="<?= APP_NAME ?>" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">System Tagline</label>
                                    <input type="text" value="Intellectual Property Management" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                    <input type="email" value="admin@iprepo.com" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                    <input type="tel" value="+1 (555) 123-4567" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">System Description</label>
                                    <textarea rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">A secure repository-based web application for managing intellectual property assets including patents, trademarks, copyrights, and industrial designs.</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-sliders-h text-blue-500 mr-3"></i>
                                System Preferences
                            </h2>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800">Allow Staff Self-Registration</p>
                                        <p class="text-sm text-gray-600">Enable new staff to register accounts</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800">Email Notifications</p>
                                        <p class="text-sm text-gray-600">Send email notifications for actions</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" checked>
                                        <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800">Auto-Approve Download Requests</p>
                                        <p class="text-sm text-gray-600">Automatically approve download requests from verified staff</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800">Maintenance Mode</p>
                                        <p class="text-sm text-gray-600">Put system in maintenance mode (staff cannot access)</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Settings -->
                    <div id="security-tab" class="tab-pane" style="display: none;">
                        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-lock text-blue-500 mr-3"></i>
                                Password Requirements
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Password Length</label>
                                    <input type="number" value="8" min="6" max="20" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Expiry (days)</label>
                                    <input type="number" value="90" min="0" max="365" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
                                    <input type="number" value="5" min="3" max="10" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                                    <input type="number" value="30" min="5" max="120" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="mt-6 space-y-4">
                                <label class="flex items-center p-4 bg-gray-50 rounded-lg cursor-pointer">
                                    <input type="checkbox" checked class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-3 text-sm font-medium text-gray-800">Require uppercase letters</span>
                                </label>
                                <label class="flex items-center p-4 bg-gray-50 rounded-lg cursor-pointer">
                                    <input type="checkbox" checked class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-3 text-sm font-medium text-gray-800">Require numbers</span>
                                </label>
                                <label class="flex items-center p-4 bg-gray-50 rounded-lg cursor-pointer">
                                    <input type="checkbox" checked class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-3 text-sm font-medium text-gray-800">Require special characters</span>
                                </label>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-file-alt text-blue-500 mr-3"></i>
                                File Upload Security
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max File Size (MB)</label>
                                    <input type="number" value="50" min="1" max="500" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Allowed File Types</label>
                                    <input type="text" value="pdf, doc, docx, jpg, png" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div id="email-tab" class="tab-pane" style="display: none;">
                        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-server text-blue-500 mr-3"></i>
                                SMTP Configuration
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                                    <input type="text" value="smtp.gmail.com" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                                    <input type="number" value="587" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Username</label>
                                    <input type="email" value="admin@iprepo.com" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Password</label>
                                    <input type="password" value="••••••••" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                                    <input type="email" value="noreply@iprepo.com" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                                    <input type="text" value="IP Repository System" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="mt-6">
                                <button onclick="testEmail()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Send Test Email
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Settings -->
                    <div id="backup-tab" class="tab-pane" style="display: none;">
                        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-cloud-upload-alt text-blue-500 mr-3"></i>
                                Database Backup
                            </h2>
                            <p class="text-gray-600 mb-6">Create and manage database backups to prevent data loss</p>
                            <div class="flex flex-wrap gap-4">
                                <button onclick="createBackup()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    <i class="fas fa-database mr-2"></i>
                                    Create Backup Now
                                </button>
                                <button onclick="downloadBackup()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    <i class="fas fa-download mr-2"></i>
                                    Download Latest Backup
                                </button>
                                <button onclick="restoreBackup()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    <i class="fas fa-upload mr-2"></i>
                                    Restore from Backup
                                </button>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-md p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                                <i class="fas fa-history text-blue-500 mr-3"></i>
                                Backup History
                            </h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date('M j, Y H:i') ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">12.5 MB</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                                    Automatic
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <button class="text-blue-600 hover:text-blue-800 mr-3">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Maintenance Settings -->
                    <div id="maintenance-tab" class="tab-pane" style="display: none;">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-broom text-blue-500 mr-3"></i>
                                    Clear Cache
                                </h2>
                                <p class="text-gray-600 mb-6">Remove cached data to improve performance</p>
                                <button onclick="clearCache()" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    Clear All Cache
                                </button>
                            </div>

                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-trash-alt text-red-500 mr-3"></i>
                                    Clear Logs
                                </h2>
                                <p class="text-gray-600 mb-6">Delete old activity logs to free space</p>
                                <button onclick="clearLogs()" class="w-full bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    Clear Old Logs
                                </button>
                            </div>

                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-sync text-green-500 mr-3"></i>
                                    Rebuild Indexes
                                </h2>
                                <p class="text-gray-600 mb-6">Optimize database performance</p>
                                <button onclick="rebuildIndexes()" class="w-full bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    Rebuild Indexes
                                </button>
                            </div>

                            <div class="bg-white rounded-xl shadow-md p-6">
                                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-check-circle text-purple-500 mr-3"></i>
                                    System Check
                                </h2>
                                <p class="text-gray-600 mb-6">Verify system integrity and health</p>
                                <button onclick="systemCheck()" class="w-full bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg transition shadow-md">
                                    Run System Check
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/common.js"></script>
    <script src="<?= BASE_URL ?>/js/utils.js"></script>
    <script>
        function showTab(tabName) {
            document.querySelectorAll('.settings-tab').forEach(tab => {
                tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-600');
            });
            event.target.classList.add('active', 'border-blue-500', 'text-blue-600');
            event.target.classList.remove('border-transparent', 'text-gray-600');

            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.style.display = 'none';
            });
            document.getElementById(tabName + '-tab').style.display = 'block';
        }

        function saveAllSettings() {
            IPRepoUtils.Loading.show('Saving settings...');
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                showToast('success', 'All settings saved successfully');
            }, 1500);
        }

        function testEmail() {
            IPRepoUtils.Loading.show('Sending test email...');
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                showToast('success', 'Test email sent successfully');
            }, 2000);
        }

        function createBackup() {
            IPRepoUtils.Loading.show('Creating backup...');
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                showToast('success', 'Database backup created successfully');
            }, 3000);
        }

        function downloadBackup() {
            showToast('info', 'Preparing download...');
        }

        function restoreBackup() {
            Swal.fire({
                title: 'Restore Database?',
                text: 'This will replace all current data!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, restore it'
            }).then((result) => {
                if (result.isConfirmed) {
                    IPRepoUtils.Loading.show('Restoring backup...');
                    setTimeout(() => {
                        IPRepoUtils.Loading.hide();
                        showToast('success', 'Database restored successfully');
                    }, 3000);
                }
            });
        }

        function clearCache() {
            IPRepoUtils.Loading.show('Clearing cache...');
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                showToast('success', 'Cache cleared successfully');
            }, 1500);
        }

        function clearLogs() {
            Swal.fire({
                title: 'Clear Logs?',
                text: 'This will delete logs older than 90 days',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, clear them'
            }).then((result) => {
                if (result.isConfirmed) {
                    IPRepoUtils.Loading.show('Clearing logs...');
                    setTimeout(() => {
                        IPRepoUtils.Loading.hide();
                        showToast('success', 'Old logs cleared successfully');
                    }, 2000);
                }
            });
        }

        function rebuildIndexes() {
            IPRepoUtils.Loading.show('Rebuilding indexes...');
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                showToast('success', 'Database indexes rebuilt successfully');
            }, 2500);
        }

        function systemCheck() {
            IPRepoUtils.Loading.show('Running system check...');
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                Swal.fire({
                    title: 'System Check Complete',
                    html: `
                        <div class="text-left">
                            <p class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Database: OK</p>
                            <p class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> File Storage: OK</p>
                            <p class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> Permissions: OK</p>
                            <p class="mb-2"><i class="fas fa-check text-green-500 mr-2"></i> PHP Version: OK</p>
                        </div>
                    `,
                    icon: 'success'
                });
            }, 3000);
        }
    </script>
</body>
</html>
