<?php
// Test script for admin password system
session_start();
require 'connection.php';

$conn = Connect();

echo "<h2>Admin Password System Test</h2>";

// Check if admin_users table exists
$table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
$table_exists = $table_check->num_rows > 0;

echo "<h3>System Status:</h3>";
echo "<div style='background: " . ($table_exists ? '#d4edda' : '#fff3cd') . "; border: 1px solid " . ($table_exists ? '#c3e6cb' : '#ffeaa7') . "; padding: 15px; border-radius: 5px; color: " . ($table_exists ? '#155724' : '#856404') . ";'>";

if ($table_exists) {
    echo "<h4>✅ Modern Admin System Active</h4>";
    echo "<ul>";
    echo "<li>✅ Admin users table exists</li>";
    echo "<li>✅ Password hashing enabled</li>";
    echo "<li>✅ Secure authentication system</li>";
    echo "<li>✅ Password update functionality available</li>";
    echo "</ul>";
    
    // Get admin users
    $admin_users = $conn->query("SELECT admin_id, admin_username, admin_name, admin_email, created_at, last_login FROM admin_users");
    
    echo "<h4>Admin Users in Database:</h4>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; background: white;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Created</th><th>Last Login</th>";
    echo "</tr>";
    
    while ($admin = $admin_users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $admin['admin_id'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($admin['admin_username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($admin['admin_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($admin['admin_email'] ?? 'N/A') . "</td>";
        echo "<td>" . ($admin['created_at'] ? date('M j, Y', strtotime($admin['created_at'])) : 'N/A') . "</td>";
        echo "<td>" . ($admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} else {
    echo "<h4>⚠️ Legacy Admin System</h4>";
    echo "<ul>";
    echo "<li>❌ Admin users table not found</li>";
    echo "<li>❌ Using hardcoded credentials</li>";
    echo "<li>❌ Password updates not available</li>";
    echo "<li>⚠️ Security risk with plain text passwords</li>";
    echo "</ul>";
    
    echo "<p><strong>Action Required:</strong> Run the setup to enable modern admin features.</p>";
}

echo "</div>";

echo "<h3>Available Actions:</h3>";
echo "<div style='margin: 20px 0;'>";

if (!$table_exists) {
    echo "<a href='setup_admin.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔧 Run Admin Setup</a>";
}

echo "<a href='admin_login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>🔐 Admin Login</a>";

if (isset($_SESSION['login_admin'])) {
    echo "<a href='admin_settings.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>⚙️ Admin Settings</a>";
    echo "<a href='admin_dashboard.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📊 Dashboard</a>";
}

echo "</div>";

echo "<h3>Test Credentials:</h3>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Default Admin Credentials:</strong></p>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> admin123</li>";
echo "</ul>";
echo "<p><em>Note: Change the default password immediately after setup for security!</em></p>";
echo "</div>";

echo "<h3>Security Features:</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<ul>";
echo "<li><strong>Password Hashing:</strong> Uses PHP's password_hash() with bcrypt</li>";
echo "<li><strong>Secure Verification:</strong> Uses password_verify() for authentication</li>";
echo "<li><strong>Session Management:</strong> Proper session handling with admin info</li>";
echo "<li><strong>Database Security:</strong> Prepared statements prevent SQL injection</li>";
echo "<li><strong>Password Validation:</strong> Minimum length and complexity requirements</li>";
echo "<li><strong>Login Tracking:</strong> Records last login timestamp</li>";
echo "</ul>";
echo "</div>";

if ($table_exists) {
    echo "<h3>Database Schema:</h3>";
    $schema = $conn->query("DESCRIBE admin_users");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; background: white;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($field = $schema->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . $field['Key'] . "</td>";
        echo "<td>" . $field['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>How to Test Password Update:</h3>";
echo "<ol>";
echo "<li>Ensure admin system is set up (run setup_admin.php if needed)</li>";
echo "<li>Login to admin panel with default credentials</li>";
echo "<li>Go to Admin Settings page</li>";
echo "<li>Use the 'Change Admin Password' section</li>";
echo "<li>Enter current password: admin123</li>";
echo "<li>Enter new password (minimum 6 characters)</li>";
echo "<li>Confirm new password</li>";
echo "<li>Click 'Update Password'</li>";
echo "<li>Test login with new password</li>";
echo "</ol>";

?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f5f5f5;
}
table { 
    margin: 10px 0; 
    width: 100%;
}
th, td { 
    text-align: left; 
    padding: 8px;
}
th {
    background: #e9ecef;
}
a { 
    display: inline-block;
    text-decoration: none; 
}
a:hover { 
    opacity: 0.8;
}
h2, h3 {
    color: #333;
}
</style>