<?php 
ob_start();
$page_title = 'User Management';
?>

<style>
    .user-card {
        transition: all 0.2s ease;
    }
    .user-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .action-btn {
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        transform: scale(1.1);
    }
    @media (max-width: 768px) {
        .mobile-menu {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
        }
    }
    /* SweetAlert2 button alignment */
    .swal2-actions {
        justify-content: flex-start !important;
    }
</style>

<!-- Page Header -->
<div class="mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Users</h1>
            <p class="text-xs text-gray-500 mt-0.5">Manage system users and permissions</p>
        </div>
        <button onclick="openCreateUserModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2 shadow-sm text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Add User</span>
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Total Users</p>
                <p class="text-xl font-bold text-gray-900" id="totalUsers"><?= count($users ?? []) ?></p>
            </div>
            <div class="bg-green-50 p-2 rounded-lg">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Active</p>
                <p class="text-xl font-bold text-green-600" id="activeUsers">
                    <?= isset($users) ? count(array_filter($users, fn($u) => $u['status'] === 'active')) : 0 ?>
                </p>
            </div>
            <div class="bg-green-50 p-2 rounded-lg">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Admins</p>
                <p class="text-xl font-bold text-green-700" id="adminUsers">
                    <?= isset($users) ? count(array_filter($users, fn($u) => $u['role'] === 'admin')) : 0 ?>
                </p>
            </div>
            <div class="bg-green-50 p-2 rounded-lg">
                <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Search -->
<div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 mb-5">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-2.5 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="searchUsers" placeholder="Search users..." 
                       class="w-full pl-8 pr-3 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-xs">
            </div>
        </div>
        <select id="filterRole" class="border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-green-500 focus:border-transparent text-xs">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>
        <select id="filterStatus" class="border border-gray-200 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-green-500 focus:border-transparent text-xs">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Status</th>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Last Login</th>
                    <th class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="usersTableBody" class="divide-y divide-gray-200">
                <?php if (isset($users) && count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50 transition"
                            data-user-id="<?= (int)$user['id'] ?>"
                            data-full-name="<?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-username="<?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-email="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-role="<?= htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            data-status="<?= htmlspecialchars($user['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                    </div>
                                    <div class="ml-2">
                                        <p class="font-medium text-gray-900 text-xs"><?= htmlspecialchars($user['full_name']) ?></p>
                                        <p class="text-[10px] text-gray-500">@<?= htmlspecialchars($user['username']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-700 hidden md:table-cell">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        Admin
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-800">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Staff
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">
                                        <span class="w-1 h-1 bg-green-500 rounded-full mr-1"></span>
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-800">
                                        <span class="w-1 h-1 bg-gray-500 rounded-full mr-1"></span>
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-[11px] text-gray-500 hidden lg:table-cell">
                                <?= $user['last_login'] ? date('M j, Y g:i A', strtotime($user['last_login'])) : 'Never' ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="editUser(<?= $user['id'] ?>)" class="action-btn p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button onclick="toggleUserStatus(<?= $user['id'] ?>, '<?= $user['status'] ?>')" 
                                            class="action-btn p-2 text-<?= $user['status'] === 'active' ? 'amber' : 'green' ?>-600 hover:bg-<?= $user['status'] === 'active' ? 'amber' : 'green' ?>-50 rounded-lg" 
                                            title="<?= $user['status'] === 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <?php if ($user['status'] === 'active'): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        <?php endif; ?>
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button onclick="deleteUser(<?= $user['id'] ?>)" class="action-btn p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-sm text-gray-500">No users found</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/common.js"></script>

<script>
// User data store for client-side operations
let allUsers = [];

if (typeof ajaxRequest !== 'function') {
    console.error('ajaxRequest() is not available. Ensure /js/common.js is loaded.');
}

document.addEventListener('DOMContentLoaded', function() {
    // Store user data for filtering
    const rows = document.querySelectorAll('#usersTableBody tr[data-user-id]');
    rows.forEach(row => {
        const fullName = (row.dataset.fullName || '').toLowerCase();
        const username = (row.dataset.username || '').toLowerCase();
        const email = (row.dataset.email || '').toLowerCase();
        const role = (row.dataset.role || '').toLowerCase();
        const status = (row.dataset.status || '').toLowerCase();
        allUsers.push({
            id: row.getAttribute('data-user-id'),
            element: row,
            role,
            status,
            text: `${fullName} ${username} ${email}`
        });
    });
});

function openCreateUserModal() {
    Swal.fire({
        title: '<span class="text-lg font-bold text-gray-900">Create New User</span>',
        html: `
            <div class="text-left space-y-3 px-2">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" id="fullName" class="swal2-input w-full m-0 text-xs py-2" placeholder="John Doe">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Username *</label>
                    <input type="text" id="username" class="swal2-input w-full m-0 text-xs py-2" placeholder="johndoe">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" id="email" class="swal2-input w-full m-0 text-xs py-2" placeholder="john@example.com">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Password *</label>
                    <input type="password" id="password" class="swal2-input w-full m-0 text-xs py-2" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Role *</label>
                    <select id="role" class="swal2-input w-full m-0 text-xs py-2">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Create User',
        confirmButtonColor: '#16a34a',
        cancelButtonText: 'Cancel',
        width: window.innerWidth < 640 ? '95%' : '450px',
        customClass: {
            popup: 'rounded-xl p-4',
            title: 'text-base',
            htmlContainer: 'text-xs',
            actions: 'justify-start',
            confirmButton: 'px-4 py-2 rounded-lg text-xs font-medium',
            cancelButton: 'px-4 py-2 rounded-lg text-xs font-medium'
        },
        preConfirm: () => {
            const fullName = document.getElementById('fullName').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;

            if (!fullName || !username || !email || !password) {
                Swal.showValidationMessage('All fields are required');
                return false;
            }

            if (password.length < 6) {
                Swal.showValidationMessage('Password must be at least 6 characters');
                return false;
            }

            return { fullName, username, email, password, role };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            Swal.fire({
                title: 'Creating user...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            ajaxRequest('<?= BASE_URL ?>/admin/users/create', {
                full_name: data.fullName,
                username: data.username,
                email: data.email,
                password: data.password,
                role: data.role
            }, 'POST').then(response => {
                Swal.close();
                if (response.success) {
                    showToast('User created successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Error', response.message || 'Failed to create user', 'error');
                }
            }).catch(error => {
                Swal.close();
                Swal.fire('Error', 'Failed to create user', 'error');
            });
        }
    });
}

function editUser(id) {
    // Get user row data
    const row = document.querySelector(`tr[data-user-id="${id}"]`);
    if (!row) return;

    const currentName = row.dataset.fullName || '';
    const currentUsername = row.dataset.username || '';
    const currentEmail = row.dataset.email || '';
    const currentRole = (row.dataset.role || 'staff').toLowerCase() === 'admin' ? 'admin' : 'staff';

    Swal.fire({
        title: '<span class="text-lg font-bold text-gray-900">Edit User</span>',
        html: `
            <div class="text-left space-y-3 px-2">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="editFullName" class="swal2-input w-full m-0 text-xs py-2" value="${currentName}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="editUsername" class="swal2-input w-full m-0 text-xs py-2" value="${currentUsername}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="editEmail" class="swal2-input w-full m-0 text-xs py-2" value="${currentEmail}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" id="editPassword" class="swal2-input w-full m-0 text-xs py-2" placeholder="Leave blank to keep current">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Role</label>
                    <select id="editRole" class="swal2-input w-full m-0 text-xs py-2">
                        <option value="staff" ${currentRole === 'staff' ? 'selected' : ''}>Staff</option>
                        <option value="admin" ${currentRole === 'admin' ? 'selected' : ''}>Admin</option>
                    </select>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update User',
        confirmButtonColor: '#16a34a',
        cancelButtonText: 'Cancel',
        width: window.innerWidth < 640 ? '95%' : '450px',
        customClass: {
            popup: 'rounded-xl p-4',
            title: 'text-base',
            htmlContainer: 'text-xs',
            actions: 'justify-start',
            confirmButton: 'px-4 py-2 rounded-lg text-xs font-medium',
            cancelButton: 'px-4 py-2 rounded-lg text-xs font-medium'
        },
        preConfirm: () => {
            const fullName = document.getElementById('editFullName').value.trim();
            const username = document.getElementById('editUsername').value.trim();
            const email = document.getElementById('editEmail').value.trim();
            const password = document.getElementById('editPassword').value;
            const role = document.getElementById('editRole').value;

            if (!fullName || !username || !email) {
                Swal.showValidationMessage('Name, username, and email are required');
                return false;
            }

            if (password && password.length < 6) {
                Swal.showValidationMessage('Password must be at least 6 characters');
                return false;
            }

            return { fullName, username, email, password, role };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const data = result.value;
            Swal.fire({
                title: 'Updating user...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const updateData = {
                user_id: id,
                full_name: data.fullName,
                username: data.username,
                email: data.email,
                role: data.role
            };

            if (data.password) {
                updateData.password = data.password;
            }

            ajaxRequest('<?= BASE_URL ?>/admin/users/update', updateData, 'POST').then(response => {
                Swal.close();
                if (response.success) {
                    showToast('User updated successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Error', response.message || 'Failed to update user', 'error');
                }
            }).catch(error => {
                Swal.close();
                Swal.fire('Error', 'Failed to update user', 'error');
            });
        }
    });
}

function toggleUserStatus(id, currentStatus) {
    if (!currentStatus) {
        const row = document.querySelector(`tr[data-user-id="${id}"]`);
        currentStatus = row?.dataset?.status || '';
    }
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';

    Swal.fire({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
        text: `This will ${action} the user account`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Yes, ${action}`,
        confirmButtonColor: newStatus === 'active' ? '#16a34a' : '#f59e0b',
        cancelButtonText: 'Cancel',
        width: window.innerWidth < 640 ? '95%' : '400px',
        customClass: {
            popup: 'rounded-xl text-sm',
            actions: 'justify-start',
            confirmButton: 'px-4 py-2 rounded-lg text-xs font-medium',
            cancelButton: 'px-4 py-2 rounded-lg text-xs font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            ajaxRequest('<?= BASE_URL ?>/admin/users/update-status', { 
                user_id: id, 
                status: newStatus 
            }, 'POST').then(response => {
                Swal.close();
                if (response.success) {
                    showToast(`User ${action}d successfully`, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Error', response.message || 'Failed to update status', 'error');
                }
            }).catch(error => {
                Swal.close();
                Swal.fire('Error', 'Failed to update status', 'error');
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
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
        width: window.innerWidth < 640 ? '95%' : '400px',
        customClass: {
            popup: 'rounded-xl text-sm',
            actions: 'justify-start',
            confirmButton: 'px-4 py-2 rounded-lg text-xs font-medium',
            cancelButton: 'px-4 py-2 rounded-lg text-xs font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting user...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            ajaxRequest('<?= BASE_URL ?>/admin/users/delete', { user_id: id }, 'POST').then(response => {
                Swal.close();
                if (response.success) {
                    showToast('User deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Error', response.message || 'Failed to delete user', 'error');
                }
            }).catch(error => {
                Swal.close();
                Swal.fire('Error', 'Failed to delete user', 'error');
            });
        }
    });
}

// Search and filter functionality
document.getElementById('searchUsers')?.addEventListener('input', filterUsers);
document.getElementById('filterRole')?.addEventListener('change', filterUsers);
document.getElementById('filterStatus')?.addEventListener('change', filterUsers);

function filterUsers() {
    const search = document.getElementById('searchUsers').value.toLowerCase();
    const role = document.getElementById('filterRole').value.toLowerCase();
    const status = document.getElementById('filterStatus').value.toLowerCase();

    let visibleCount = 0;

    allUsers.forEach(user => {
        const searchMatch = !search || user.text.includes(search);
        const roleMatch = !role || user.role === role;
        const statusMatch = !status || user.status === status;

        if (searchMatch && roleMatch && statusMatch) {
            user.element.style.display = '';
            visibleCount++;
        } else {
            user.element.style.display = 'none';
        }
    });

    // Show message if no results
    const tbody = document.getElementById('usersTableBody');
    let noResultsRow = tbody.querySelector('.no-results-row');
    
    if (visibleCount === 0 && (search || role || status)) {
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.className = 'no-results-row';
            noResultsRow.innerHTML = `
                <td colspan="6" class="px-4 py-8 text-center">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-sm text-gray-500">No users match your filters</p>
                </td>
            `;
            tbody.appendChild(noResultsRow);
        }
    } else if (noResultsRow) {
        noResultsRow.remove();
    }
}
</script>

<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/main.php';
?>
