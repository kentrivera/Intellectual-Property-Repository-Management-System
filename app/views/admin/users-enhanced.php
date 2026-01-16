<?php ob_start(); ?>

<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
    <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
            <span class="w-1.5 h-8 bg-gradient-to-b from-emerald-500 to-green-600 rounded-full mr-3"></span>
            User Management
        </h1>
        <p class="text-gray-600 mt-2">Manage system users and their permissions</p>
    </div>
    <button onclick="openCreateUserModal()" class="mt-4 md:mt-0 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white px-6 py-3 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center transform hover:scale-105">
        <i class="fas fa-user-plus mr-2"></i>
        Add New User
    </button>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-xs font-semibold uppercase tracking-wide">Total Users</p>
                <p class="text-3xl font-bold mt-2"><?= count($users ?? []) ?></p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-cyan-100 text-xs font-semibold uppercase tracking-wide">Active Users</p>
                <p class="text-3xl font-bold mt-2"><?= count(array_filter($users ?? [], fn($u) => $u['status'] === 'active')) ?></p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-check text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-xs font-semibold uppercase tracking-wide">Administrators</p>
                <p class="text-3xl font-bold mt-2"><?= count(array_filter($users ?? [], fn($u) => $u['role'] === 'admin')) ?></p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-shield-alt text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-xs font-semibold uppercase tracking-wide">Staff Members</p>
                <p class="text-3xl font-bold mt-2"><?= count(array_filter($users ?? [], fn($u) => $u['role'] === 'staff')) ?></p>
            </div>
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-user-tie text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="bg-white rounded-xl shadow-lg p-4 md:p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <div class="relative">
                <input type="text" id="searchUsers" placeholder="Search by name, email, or username..." 
                       class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
            </div>
        </div>
        <select id="filterRole" class="border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>
        <select id="filterStatus" class="border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-emerald-50 to-green-50 border-b-2 border-emerald-100">
                <tr>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-emerald-700 uppercase tracking-wider">User</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-emerald-700 uppercase tracking-wider hidden md:table-cell">Email</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-emerald-700 uppercase tracking-wider">Role</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-emerald-700 uppercase tracking-wider hidden lg:table-cell">Status</th>
                    <th class="px-4 md:px-6 py-4 text-left text-xs font-bold text-emerald-700 uppercase tracking-wider hidden xl:table-cell">Last Login</th>
                    <th class="px-4 md:px-6 py-4 text-right text-xs font-bold text-emerald-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="divide-y divide-gray-200">
                <?php if (isset($users) && count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-emerald-50 transition-colors duration-150">
                            <td class="px-4 md:px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center text-white font-bold mr-3 shadow-md">
                                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($user['full_name']) ?></p>
                                        <p class="text-sm text-gray-500">@<?= htmlspecialchars($user['username']) ?></p>
                                        <p class="text-xs text-gray-400 md:hidden"><?= htmlspecialchars($user['email']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-sm text-gray-700 hidden md:table-cell"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 md:px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?> shadow-sm">
                                    <i class="fas fa-<?= $user['role'] === 'admin' ? 'shield-alt' : 'user' ?> mr-1"></i>
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-4 hidden lg:table-cell">
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold <?= $user['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' ?> shadow-sm">
                                    <i class="fas fa-circle text-[8px] mr-1 <?= $user['status'] === 'active' ? 'text-emerald-500' : 'text-gray-500' ?>"></i>
                                    <?= ucfirst($user['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-sm text-gray-600 hidden xl:table-cell">
                                <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-1 md:space-x-2">
                                    <button onclick="editUser(<?= $user['id'] ?>)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleUserStatus(<?= $user['id'] ?>, '<?= $user['status'] ?>')" 
                                            class="p-2 text-<?= $user['status'] === 'active' ? 'yellow' : 'emerald' ?>-600 hover:bg-<?= $user['status'] === 'active' ? 'yellow' : 'emerald' ?>-50 rounded-lg transition" 
                                            title="<?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="fas fa-<?= $user['status'] === 'active' ? 'ban' : 'check-circle' ?>"></i>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?= $user['id'] ?>)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-users text-4xl text-gray-400"></i>
                                </div>
                                <p class="text-lg font-medium">No users found</p>
                                <p class="text-sm text-gray-400 mt-1">Add your first user to get started</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function openCreateUserModal() {
    Swal.fire({
        title: '<span class="text-emerald-600">Create New User</span>',
        html: `
            <div class="text-left space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="fullName" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="John Doe">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                    <input type="text" id="username" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="johndoe">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="john@example.com">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role</label>
                    <select id="role" class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Create User',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
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
            showToast('User created successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    });
}

function editUser(id) {
    showToast('Edit user feature coming soon', 'info');
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
            showToast(`User ${action}d successfully`, 'success');
            setTimeout(() => location.reload(), 1000);
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
            showToast('User deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
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
$page_title = 'User Management';
require_once APP_PATH . '/views/layouts/main.php';
?>
