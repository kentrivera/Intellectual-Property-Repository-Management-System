<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'IP Repository' ?> - <?= APP_NAME ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50">
    <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
    
    <!-- Include Admin or Staff Sidebar -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <?php require_once APP_PATH . '/views/components/sidebar-admin.php'; ?>
    <?php else: ?>
        <?php require_once APP_PATH . '/views/components/sidebar-staff.php'; ?>
    <?php endif; ?>
    
    <!-- Main Content Area -->
    <div class="lg:ml-72 min-h-screen flex flex-col">
        <!-- Include Header Component -->
        <?php 
        $pageTitle = $page_title ?? 'Dashboard';
        require_once APP_PATH . '/views/components/header.php'; 
        ?>
        
        <!-- Page Content Container -->
        <main class="flex-1 p-4 sm:p-6 bg-gradient-to-br from-gray-50 to-gray-100">
            <?php endif; ?>
            
            <!-- Content goes here -->
            <?= $content ?? '' ?>
            
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4 px-4 sm:px-6">
            <div class="flex flex-col sm:flex-row items-center justify-between text-sm text-gray-600">
                <p>&copy; <?= date('Y') ?> IP Repository Management System. All rights reserved.</p>
                <p class="mt-2 sm:mt-0">Version 1.0.0</p>
            </div>
        </footer>
    </div>
    
    <?php endif; ?>
    
    <script>
        // Base URL for AJAX calls
        const BASE_URL = '<?= BASE_URL ?>';
        
        // CSRF Token
        const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';
    </script>
</body>
</html>
