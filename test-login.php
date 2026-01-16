<?php
/**
 * Login Test Script
 * Debug login issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

echo "<h2>Login System Test</h2>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db = Database::getInstance();
    echo "✅ Database connection successful!<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "<strong>Check config.php database credentials</strong><br>";
    exit;
}

// Test 2: Check if users table exists
echo "<h3>2. Users Table Check</h3>";
try {
    $result = $db->query("SHOW TABLES LIKE 'users'");
    if ($result) {
        echo "✅ Users table exists<br>";
    } else {
        echo "❌ Users table not found<br>";
        echo "<strong>Import database/schema.sql first!</strong><br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check for admin user
echo "<h3>3. Admin User Check</h3>";
try {
    $user = $db->fetch("SELECT * FROM users WHERE username = ?", ['admin']);
    if ($user) {
        echo "✅ Admin user found<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Status: " . $user['status'] . "<br>";
        echo "Password Hash: " . substr($user['password_hash'], 0, 20) . "...<br>";
    } else {
        echo "❌ Admin user not found in database<br>";
        echo "<strong>Database not imported or admin user missing!</strong><br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Error fetching user: " . $e->getMessage() . "<br>";
    exit;
}

// Test 4: Password Verification
echo "<h3>4. Password Hash Test</h3>";
$testPassword = 'Admin@123';
echo "Testing password: <strong>{$testPassword}</strong><br>";

if (isset($user['password_hash'])) {
    if (password_verify($testPassword, $user['password_hash'])) {
        echo "✅ Password verification successful!<br>";
    } else {
        echo "❌ Password verification FAILED<br>";
        echo "<strong>The password in database doesn't match 'Admin@123'</strong><br>";
        echo "Run this SQL to reset password:<br>";
        echo "<pre>UPDATE users SET password_hash = '" . password_hash($testPassword, PASSWORD_BCRYPT) . "' WHERE username = 'admin';</pre>";
    }
}

// Test 5: Session Test
echo "<h3>5. Session Test</h3>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "✅ Session working<br>";

// Test 6: All Users List
echo "<h3>6. All Users in Database</h3>";
try {
    $users = $db->fetchAll("SELECT id, username, email, role, status FROM users");
    if ($users) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>{$u['id']}</td>";
            echo "<td>{$u['username']}</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td>{$u['role']}</td>";
            echo "<td>{$u['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No users found in database<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Summary</h3>";
echo "If all tests passed, the login should work.<br>";
echo "Login URL: <a href='public/'>Go to Login Page</a><br>";
echo "<br>";
echo "<strong>Default Credentials:</strong><br>";
echo "Username: admin<br>";
echo "Password: Admin@123<br>";
?>
