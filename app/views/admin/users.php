<?php 
ob_start();
$page_title = 'User Management';
?>

<!-- Page Header -->
<div class="mb-4 sm:mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">User Management</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Manage system users and their permissions</p>
        </div>
        <button onclick="openCreateUserModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg transition flex items-center justify-center text-sm sm:text-base shadow-md">
            <i class="fas fa-user-plus mr-2"></i>
            Add New User
        </button>
    </div>
</div>

<!-- Filters & Search -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-md p-3 sm:p-4 mb-4 sm:mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
        <div class="md:col-span-2">
            <div class="relative">
                <input type="text" id="searchUsers" placeholder="Search by name, email, or username..." 
                       class="w-full pl-9 sm:pl-10 pr-3 sm:pr-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
                <i class="fas fa-search absolute left-3 top-2.5 sm:top-3 text-gray-400 text-sm"></i>
            </div>
        </div>
        <select id="filterRole" class="border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>
        <select id="filterStatus" class="border border-gray-300 rounded-lg px-3 sm:px-4 py-2 sm:py-2.5 focus:ring-2 focus:ring-blue-500 text-sm sm:text-base">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full min-w-max">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">User</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Email</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Role</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Last Login</th>
                    <th class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="divide-y divide-gray-200">
                                <?php if (isset($users) && count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold mr-2 sm:mr-3 text-xs sm:text-sm flex-shrink-0">
                                                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="font-medium text-gray-800 text-sm sm:text-base truncate"><?= htmlspecialchars($user['full_name']) ?></p>
                                                        <p class="text-xs sm:text-sm text-gray-500 truncate">@<?= htmlspecialchars($user['username']) ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-700">
                                                <span class="block truncate max-w-xs"><?= htmlspecialchars($user['email']) ?></span>
                                            </td>
                                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                                <span class="px-2 sm:px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?>">
                                                    <i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?> mr-1"></i>
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4">
                                                <span class="px-2 sm:px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap <?= $user['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                                                    <?= ucfirst($user['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-600 whitespace-nowrap">
                                                <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                                            </td>
                                            <td class="px-3 sm:px-4 lg:px-6 py-3 sm:py-4 text-right">
                                                <div class="flex items-center justify-end space-x-1 sm:space-x-2">
                                                    <button onclick="editUser(<?= $user['id'] ?>)" class="p-1.5 sm:p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                                        <i class="fas fa-edit text-sm"></i>
                                                    </button>
                                                    <button onclick="toggleUserStatus(<?= $user['id'] ?>, '<?= $user['status'] ?>')" 
                                                            class="p-1.5 sm:p-2 text-<?= $user['status'] === 'active' ? 'yellow' : 'green' ?>-600 hover:bg-<?= $user['status'] === 'active' ? 'yellow' : 'green' ?>-50 rounded-lg transition" 
                                                            title="<?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                                        <i class="fas fa-<?= $user['status'] === 'active' ? 'ban' : 'check-circle' ?> text-sm"></i>
                                                    </button>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <button onclick="deleteUser(<?= $user['id'] ?>)" class="p-1.5 sm:p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                                            <i class="fas fa-trash text-sm"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 sm:py-12 text-center text-gray-500">
                                            <i class="fas fa-users text-3xl sm:text-4xl mb-2"></i>
                                            <p class="text-sm sm:text-base">No users found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        function openCreateUserModal() {
            Swal.fire({
                title: 'Create New User',
                html: `
                    <div class="text-left space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="fullName" class="swal2-input w-full" placeholder="John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" id="username" class="swal2-input w-full" placeholder="johndoe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" class="swal2-input w-full" placeholder="john@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" id="password" class="swal2-input w-full" placeholder="••••••••">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select id="role" class="swal2-input w-full">
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Create User',
                width: 600,
                preConfirm: () => {
                    const fullName = document.getElementById('fullName').value;
                    const username = document.getElementById('username').value;
                    const email = document.getElementById('email').value;
                    const password = document.getElementById('password').value;
                    const role = document.getElementById('role').value;

                    if (!fullName || !username || !email || !password) {
                        Swal.showValidationMessage('All fields are required');
                        return false;
                    }

                    return { fullName, username, email, password, role };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    ajaxRequest('<?= BASE_URL ?>/admin/users/create', {
                        full_name: data.fullName,
                        username: data.username,
                        email: data.email,
                        password: data.password,
                        role: data.role
                    }, 'POST').then(() => {
                        showToast('success', 'User created successfully');
                        setTimeout(() => location.reload(), 1000);
                    });
                }
            });
        }

        function editUser(id) {
            // Implement edit functionality
            showToast('info', 'Edit user feature coming soon');
        }

        function toggleUserStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';

            Swal.fire({
                title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
                text: `This will ${action} the user account`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, ' + action,
                confirmButtonColor: newStatus === 'active' ? '#10b981' : '#f59e0b'
            }).then((result) => {
                if (result.isConfirmed) {
                    ajaxRequest('<?= BASE_URL ?>/admin/users/update-status', { user_id: id, status: newStatus }, 'POST')
                        .then(() => {
                            showToast('success', `User ${action}d successfully`);
                            setTimeout(() => location.reload(), 1000);
                        });
                }
            });
        }

        function deleteUser(id) {
            Swal.fire({
                title: 'Delete User?',
                text: 'This action cannot be undone!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    ajaxRequest('<?= BASE_URL ?>/admin/users/delete', { user_id: id }, 'POST')
                        .then(() => {
                            showToast('success', 'User deleted successfully');
                            setTimeout(() => location.reload(), 1000);
                        });
                }
            });
        }

        // Search and filter
        document.getElementById('searchUsers')?.addEventListener('input', filterUsers);
        document.getElementById('filterRole')?.addEventListener('change', filterUsers);
        document.getElementById('filterStatus')?.addEventListener('change', filterUsers);

        function filterUsers() {
            const search = document.getElementById('searchUsers').value.toLowerCase();
            const role = document.getElementById('filterRole').value;
            const status = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('#usersTableBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const roleMatch = !role || row.textContent.includes(role);
                const statusMatch = !status || row.textContent.includes(status);
                const searchMatch = !search || text.includes(search);

                row.style.display = (roleMatch && statusMatch && searchMatch) ? '' : 'none';
            });
        }
    </script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
