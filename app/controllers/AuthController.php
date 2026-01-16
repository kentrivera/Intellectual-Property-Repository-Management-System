<?php
/**
 * Authentication Controller
 * Handles login, logout, and authentication
 */

require_once APP_PATH . '/models/User.php';
require_once APP_PATH . '/models/ActivityLog.php';

class AuthController extends Controller {
    private $userModel;
    private $activityLog;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Show login page
     */
    public function showLogin() {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/login', [
            'csrf_token' => $this->generateCSRF()
        ]);
    }
    
    /**
     * Process login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
        }
        
        $username = $this->sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';
        
        // Validate CSRF
        if (!$this->validateCSRF($csrfToken)) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
        }
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->json(['success' => false, 'message' => 'Username and password are required']);
        }
        
        // Find user
        $user = $this->userModel->findByUsername($username);
        
        if (!$user) {
            // Log failed attempt
            $this->activityLog->log([
                'user_id' => null,
                'action_type' => 'login_failed',
                'description' => "Failed login attempt for username: {$username}"
            ]);
            
            $this->json(['success' => false, 'message' => 'Invalid username or password']);
        }
        
        // Check if user is active
        if ($user['status'] !== 'active') {
            $this->json(['success' => false, 'message' => 'Your account has been deactivated. Please contact administrator.']);
        }
        
        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            // Log failed attempt
            $this->activityLog->log([
                'user_id' => $user['id'],
                'action_type' => 'login_failed',
                'description' => "Failed login attempt for user: {$user['username']}"
            ]);
            
            $this->json(['success' => false, 'message' => 'Invalid username or password']);
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        
        // Update last login
        $this->userModel->updateLastLogin($user['id']);
        
        // Log successful login
        $this->activityLog->log([
            'user_id' => $user['id'],
            'action_type' => 'login',
            'description' => "User {$user['username']} logged in successfully"
        ]);
        
        // Return success with redirect URL
        $redirectUrl = $user['role'] === 'admin' ? '/admin/dashboard' : '/staff/dashboard';
        
        $this->json([
            'success' => true, 
            'message' => 'Login successful', 
            'redirect' => $redirectUrl
        ]);
    }
    
    /**
     * Logout
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            // Log logout
            $this->activityLog->log([
                'user_id' => $this->getCurrentUserId(),
                'action_type' => 'logout',
                'description' => "User {$_SESSION['username']} logged out"
            ]);
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
        
        $this->redirect('/login');
    }
    
    /**
     * Check authentication status (for AJAX)
     */
    public function checkAuth() {
        $this->json([
            'authenticated' => $this->isLoggedIn(),
            'user' => $this->isLoggedIn() ? [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role']
            ] : null
        ]);
    }
}
