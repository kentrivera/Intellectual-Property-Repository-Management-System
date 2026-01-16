<?php
/**
 * Add New Admin User Script
 * Creates a fresh admin account with working credentials
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Add Admin User</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid green; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid red; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid blue; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>";

echo "<h1>🔧 Admin User Creator</h1>";

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance();
    echo "<div class='success'>✅ Database connected successfully!</div>";
    
    // Check if form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        
        if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
            echo "<div class='error'>❌ All fields are required!</div>";
        } else {
            // Check if username exists
            $existing = $db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
            if ($existing) {
                echo "<div class='error'>❌ Username already exists!</div>";
            } else {
                // Create password hash
                $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
                
                // Insert new admin
                $sql = "INSERT INTO users (username, email, password_hash, full_name, role, status) 
                        VALUES (?, ?, ?, ?, 'admin', 'active')";
                
                $userId = $db->insert($sql, [$username, $email, $passwordHash, $fullName]);
                
                if ($userId) {
                    echo "<div class='success'>";
                    echo "<h2>✅ Admin User Created Successfully!</h2>";
                    echo "<p><strong>Username:</strong> {$username}</p>";
                    echo "<p><strong>Email:</strong> {$email}</p>";
                    echo "<p><strong>Password:</strong> {$password}</p>";
                    echo "<p><strong>Role:</strong> admin</p>";
                    echo "<hr>";
                    echo "<p><a href='public/' style='background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>❌ Failed to create user!</div>";
                }
            }
        }
    }
    
    // Show current users
    echo "<h2>📋 Current Users in Database</h2>";
    $users = $db->fetchAll("SELECT id, username, email, full_name, role, status, created_at FROM users ORDER BY id");
    
    if ($users) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Full Name</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $user) {
            $rowColor = $user['role'] === 'admin' ? '#ffe6e6' : '#e6f3ff';
            echo "<tr style='background: {$rowColor}'>";
            echo "<td>{$user['id']}</td>";
            echo "<td><strong>{$user['username']}</strong></td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['full_name']}</td>";
            echo "<td><span style='background: " . ($user['role'] === 'admin' ? '#ff6b6b' : '#4ecdc4') . "; color: white; padding: 3px 8px; border-radius: 3px;'>{$user['role']}</span></td>";
            echo "<td>{$user['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test existing passwords
    echo "<h2>🔑 Test Existing Credentials</h2>";
    echo "<table>";
    echo "<tr><th>Username</th><th>Test Password</th><th>Result</th></tr>";
    
    $testAccounts = [
        ['username' => 'admin', 'password' => 'Admin@123'],
        ['username' => 'staff', 'password' => 'Staff@123']
    ];
    
    foreach ($testAccounts as $test) {
        $user = $db->fetch("SELECT password_hash FROM users WHERE username = ?", [$test['username']]);
        if ($user) {
            $works = password_verify($test['password'], $user['password_hash']);
            echo "<tr>";
            echo "<td><strong>{$test['username']}</strong></td>";
            echo "<td>{$test['password']}</td>";
            echo "<td>" . ($works ? "✅ <span style='color: green;'>WORKS</span>" : "❌ <span style='color: red;'>FAILED</span>") . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
    // Show form
    echo "<hr>";
    echo "<h2>➕ Create New Admin User</h2>";
    echo "<form method='POST' style='background: #f9f9f9; padding: 20px; border-radius: 5px;'>";
    echo "<div style='margin: 10px 0;'>";
    echo "<label style='display: inline-block; width: 150px;'><strong>Username:</strong></label>";
    echo "<input type='text' name='username' required style='padding: 8px; width: 300px;' placeholder='Enter username'>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label style='display: inline-block; width: 150px;'><strong>Email:</strong></label>";
    echo "<input type='email' name='email' required style='padding: 8px; width: 300px;' placeholder='admin@example.com'>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label style='display: inline-block; width: 150px;'><strong>Full Name:</strong></label>";
    echo "<input type='text' name='full_name' required style='padding: 8px; width: 300px;' placeholder='John Doe'>";
    echo "</div>";
    
    echo "<div style='margin: 10px 0;'>";
    echo "<label style='display: inline-block; width: 150px;'><strong>Password:</strong></label>";
    echo "<input type='text' name='password' required style='padding: 8px; width: 300px;' placeholder='Choose a strong password' value='Admin@2026'>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<button type='submit' style='background: #4CAF50; color: white; padding: 10px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>Create Admin User</button>";
    echo "</div>";
    echo "</form>";
    
    echo "<div class='info'>";
    echo "<strong>💡 Tip:</strong> After creating the admin user, you can delete this file (add-admin.php) for security.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Database Connection Error</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Check database credentials in config/config.php</li>";
    echo "<li>Verify database 'ip_repository_db' exists</li>";
    echo "<li>Import database/schema.sql in phpMyAdmin</li>";
    echo "</ol>";
    echo "</div>";
}

echo "</body></html>";
?>
