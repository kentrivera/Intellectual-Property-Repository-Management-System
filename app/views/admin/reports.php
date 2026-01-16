<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php 
        $pageTitle = 'Reports';
        include APP_PATH . '/views/components/sidebar-admin.php'; 
        ?>

        <div class="flex-1 flex flex-col overflow-hidden lg:ml-64">
            <?php include APP_PATH . '/views/components/header.php'; ?>

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">Reports & Analytics</h1>
                        <p class="text-gray-600 mt-1">View system statistics and generate reports</p>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-4 lg:mt-0">
                        <button onclick="exportReport('pdf')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition shadow-md text-sm">
                            <i class="fas fa-file-pdf mr-2"></i>Export PDF
                        </button>
                        <button onclick="exportReport('excel')" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition shadow-md text-sm">
                            <i class="fas fa-file-excel mr-2"></i>Export Excel
                        </button>
                        <button onclick="exportReport('csv')" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition shadow-md text-sm">
                            <i class="fas fa-file-csv mr-2"></i>Export CSV
                        </button>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input type="date" value="<?= date('Y-m-01') ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input type="date" value="<?= date('Y-m-d') ?>" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option>All Reports</option>
                                <option>IP Records</option>
                                <option>Downloads</option>
                                <option>Users</option>
                                <option>Activity</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button onclick="generateReport()" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition shadow-md">
                                <i class="fas fa-search mr-2"></i>Generate
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Overview Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                                <i class="fas fa-folder-open text-2xl"></i>
                            </div>
                            <span class="text-sm font-medium">This Month</span>
                        </div>
                        <h3 class="text-4xl font-bold mb-2">248</h3>
                        <p class="text-blue-100">Total IP Records</p>
                        <div class="mt-4 flex items-center">
                            <i class="fas fa-arrow-up mr-2"></i>
                            <span class="text-sm">12% from last month</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                                <i class="fas fa-download text-2xl"></i>
                            </div>
                            <span class="text-sm font-medium">This Month</span>
                        </div>
                        <h3 class="text-4xl font-bold mb-2">1,432</h3>
                        <p class="text-green-100">Downloads</p>
                        <div class="mt-4 flex items-center">
                            <i class="fas fa-arrow-up mr-2"></i>
                            <span class="text-sm">8% from last month</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <span class="text-sm font-medium">Active Users</span>
                        </div>
                        <h3 class="text-4xl font-bold mb-2">156</h3>
                        <p class="text-purple-100">Staff Members</p>
                        <div class="mt-4 flex items-center">
                            <i class="fas fa-arrow-up mr-2"></i>
                            <span class="text-sm">5% from last month</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-white bg-opacity-30 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <span class="text-sm font-medium">Pending</span>
                        </div>
                        <h3 class="text-4xl font-bold mb-2">23</h3>
                        <p class="text-yellow-100">Requests</p>
                        <div class="mt-4 flex items-center">
                            <i class="fas fa-arrow-down mr-2"></i>
                            <span class="text-sm">3% from last month</span>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- IP Records by Type -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-gray-800">IP Records by Type</h2>
                            <select class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                                <option>Last 30 Days</option>
                                <option>Last 90 Days</option>
                                <option>Last Year</option>
                            </select>
                        </div>
                        <canvas id="ipTypeChart" height="250"></canvas>
                    </div>

                    <!-- Monthly Activity -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-gray-800">Monthly Activity</h2>
                            <select class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                                <option>Last 6 Months</option>
                                <option>Last Year</option>
                            </select>
                        </div>
                        <canvas id="activityChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Download Requests Status -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">Download Request Status</h2>
                        <canvas id="requestStatusChart" height="250"></canvas>
                    </div>

                    <!-- Top Downloaded Documents -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">Top Downloaded Documents</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-red-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Patent_Application_2024.pdf</p>
                                        <p class="text-sm text-gray-600">Patent</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-blue-600">487</p>
                                    <p class="text-xs text-gray-500">downloads</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-green-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Trademark_Registration.pdf</p>
                                        <p class="text-sm text-gray-600">Trademark</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-blue-600">356</p>
                                    <p class="text-xs text-gray-500">downloads</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-purple-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Copyright_Certificate.pdf</p>
                                        <p class="text-sm text-gray-600">Copyright</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-blue-600">289</p>
                                    <p class="text-xs text-gray-500">downloads</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-pdf text-orange-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">Industrial_Design_2024.pdf</p>
                                        <p class="text-sm text-gray-600">Industrial Design</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-blue-600">234</p>
                                    <p class="text-xs text-gray-500">downloads</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Activity Table -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Recent User Activity</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                                JS
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">John Smith</p>
                                                <p class="text-sm text-gray-500">john@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                            Download
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">Patent_Application_2024.pdf</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 hours ago</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                                MD
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Mary Davis</p>
                                                <p class="text-sm text-gray-500">mary@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                            Created
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">New Trademark Record</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">3 hours ago</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/js/common.js"></script>
    <script src="<?= BASE_URL ?>/js/utils.js"></script>
    <script>
        // IP Type Chart
        const ipTypeCtx = document.getElementById('ipTypeChart').getContext('2d');
        new Chart(ipTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Patent', 'Trademark', 'Copyright', 'Industrial Design'],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Records',
                    data: [45, 52, 48, 65, 58, 71],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Downloads',
                    data: [120, 145, 158, 175, 165, 189],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Request Status Chart
        const requestCtx = document.getElementById('requestStatusChart').getContext('2d');
        new Chart(requestCtx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    label: 'Requests',
                    data: [23, 156, 12],
                    backgroundColor: ['#f59e0b', '#10b981', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        function generateReport() {
            IPRepoUtils.Loading.show('Generating report...');
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                showToast('success', 'Report generated successfully');
            }, 2000);
        }

        function exportReport(format) {
            IPRepoUtils.Loading.show(`Exporting to ${format.toUpperCase()}...`);
            setTimeout(() => {
                IPRepoUtils.Loading.hide();
                showToast('success', `Report exported as ${format.toUpperCase()}`);
            }, 1500);
        }
    </script>
</body>
</html>
