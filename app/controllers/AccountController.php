<?php
/**
 * Account Controller
 * Handles user profile, settings, and help/support pages
 */

require_once APP_PATH . '/models/User.php';

class AccountController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->userModel = new User();
    }

    /**
     * Profile page
     */
    public function profile() {
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->findById($userId);

        $this->view('account/profile', [
            'user' => $user,
            'page_title' => 'My Profile',
            'csrf_token' => $this->generateCSRF()
        ]);
    }

    /**
     * Settings page (account-level preferences)
     */
    public function settings() {
        $userId = $this->getCurrentUserId();
        $user = $this->userModel->findById($userId);

        $this->view('account/settings', [
            'user' => $user,
            'page_title' => 'Settings',
            'csrf_token' => $this->generateCSRF()
        ]);
    }

    /**
     * Help & Support page
     */
    public function help() {
        $this->view('account/help', [
            'page_title' => 'Help & Support'
        ]);
    }
}
