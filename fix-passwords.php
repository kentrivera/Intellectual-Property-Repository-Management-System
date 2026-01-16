<?php
/**
 * Automatic Password Fix Script
 * Run this once to fix admin and staff passwords
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

echo "<h2>Password Fix Script</h2>";

try {
    $db = Database::getInstance();
    
    // Fix admin password
    $adminHash = '$2y$10$wYO9zt5Y3bXXhS/hM2f84uMUQKlfDJaic6FQCsCyiivHCPn6dPGgy';
    $result1 = $db->execute("UPDATE users SET password_hash = ? WHERE username = ?", [$adminHash, 'admin']);
    
    // Fix staff password
    $staffHash = '$2y$10$Y6owXDaYKhG5Ls9LEy2P1.JAL5eO59/wjnhIX7r8L6S.XnlFy4kHu';
    $result2 = $db->execute("UPDATE users SET password_hash = ? WHERE username = ?", [$staffHash, 'staff']);
    
    echo "✅ Admin password updated (admin / Admin@123)<br>";
    echo "✅ Staff password updated (staff / Staff@123)<br>";
    echo "<br>";
    
    // Verify
    $admin = $db->fetch("SELECT username, email FROM users WHERE username = ?", ['admin']);
    $staff = $db->fetch("SELECT username, email FROM users WHERE username = ?", ['staff']);
    
    echo "<h3>Verification:</h3>";
    echo "Admin: {$admin['username']} ({$admin['email']})<br>";
    echo "Staff: {$staff['username']} ({$staff['email']})<br>";
    
    // Test passwords
    $adminUser = $db->fetch("SELECT password_hash FROM users WHERE username = ?", ['admin']);
    $staffUser = $db->fetch("SELECT password_hash FROM users WHERE username = ?", ['staff']);
    
    echo "<br><h3>Password Tests:</h3>";
    if (password_verify('Admin@123', $adminUser['password_hash'])) {
        echo "✅ Admin password 'Admin@123' works!<br>";
    } else {
        echo "❌ Admin password still wrong<br>";
    }
    
    if (password_verify('Staff@123', $staffUser['password_hash'])) {
        echo "✅ Staff password 'Staff@123' works!<br>";
    } else {
        echo "❌ Staff password still wrong<br>";
    }
    
    echo "<br><hr>";
    echo "<h3>✅ PASSWORDS FIXED!</h3>";
    echo "<p>You can now login at: <a href='public/'>Login Page</a></p>";
    echo "<p><strong>Credentials:</strong><br>";
    echo "Username: admin<br>";
    echo "Password: Admin@123</p>";
    
    echo "<br><p style='color: red;'><strong>IMPORTANT:</strong> Delete this file after use for security!</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
