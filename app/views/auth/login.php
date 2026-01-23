<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-emerald-600 via-green-600 to-teal-700 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-8">
        <!-- Logo/Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg mb-3 shadow-md">
                <i class="fas fa-shield-alt text-white text-xl"></i>
            </div>
            <h1 class="text-xl font-bold text-gray-800">IP Repository System</h1>
            <p class="text-gray-600 text-sm mt-1">Please sign in to continue</p>
        </div>
        
        <!-- Login Form -->
        <form id="loginForm" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" name="username" id="username" required
                           class="block w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                           placeholder="Enter your username">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400 text-sm"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                           class="block w-full pl-9 pr-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
                           placeholder="Enter your password">
                </div>
            </div>
            
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-emerald-600 to-green-600 text-white py-2.5 rounded-lg font-semibold hover:from-emerald-700 hover:to-green-700 transition duration-200 flex items-center justify-center shadow-md text-sm">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Sign In
            </button>
        </form>
        
        <!-- Demo Credentials -->
        <div class="mt-5 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
            <p class="text-[10px] font-bold text-emerald-900 mb-2 uppercase tracking-wide">Demo Credentials:</p>
            <div class="text-xs text-emerald-800 space-y-1">
                <p><strong class="font-semibold">Admin:</strong> admin / Admin@123</p>
                <p><strong class="font-semibold">Staff:</strong> staff / Staff@123</p>
            </div>
        </div>
    </div>
    
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch(BASE_URL + '/login', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false,
                        iconColor: '#059669',
                        confirmButtonColor: '#059669'
                    }).then(() => {
                        window.location.href = BASE_URL + result.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: result.message,
                        confirmButtonColor: '#059669'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    confirmButtonColor: '#059669'
                });
            }
        });
    </script>
</body>
</html>
