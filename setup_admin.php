<?php
// Setup script to create admin table and initialize admin user
require 'connection.php';

$conn = Connect();

echo "<h2>Admin System Setup</h2>";

try {
    // Create admin_users table
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS `admin_users` (
      `admin_id` int(11) NOT NULL AUTO_INCREMENT,
      `admin_username` varchar(50) NOT NULL,
      `admin_password` varchar(255) NOT NULL,
      `admin_email` varchar(100) DEFAULT NULL,
      `admin_name` varchar(100) DEFAULT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `last_login` timestamp NULL DEFAULT NULL,
      PRIMARY KEY (`admin_id`),
      UNIQUE KEY `admin_username` (`admin_username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    
    if ($conn->query($create_table_sql)) {
        echo "<p style='color: green;'>✅ Admin table created successfully!</p>";
    } else {
        throw new Exception("Error creating admin table: " . $conn->error);
    }
    
    // Check if admin user already exists
    $check_admin = $conn->query("SELECT admin_id FROM admin_users WHERE admin_username = 'admin'");
    
    if ($check_admin->num_rows == 0) {
        // Create default admin user
        $default_password = 'admin123';
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $insert_admin_sql = "INSERT INTO admin_users (admin_username, admin_password, admin_email, admin_name) 
                            VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_admin_sql);
        $admin_email = 'admin@carrentals.com';
        $admin_name = 'System Administrator';
        $stmt->bind_param("ssss", $admin_username = 'admin', $hashed_password, $admin_email, $admin_name);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Default admin user created successfully!</p>";
            echo "<p><strong>Username:</strong> admin</p>";
            echo "<p><strong>Password:</strong> admin123</p>";
        } else {
            throw new Exception("Error creating admin user: " . $conn->error);
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Admin user already exists.</p>";
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li><a href='admin_login.php'>Login to Admin Panel</a></li>";
    echo "<li><a href='admin_settings.php'>Update Admin Password</a> (after login)</li>";
    echo "</ul>";
    
    echo "<h3>Database Structure:</h3>";
    $describe = $conn->query("DESCRIBE admin_users");
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $describe->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f5f5f5;
}
table { 
    background: white; 
    margin: 10px 0; 
}
th, td { 
    text-align: left; 
    padding: 8px;
}
th {
    background: #e9ecef;
}
a { 
    color: #007bff; 
    text-decoration: none; 
}
a:hover { 
    text-decoration: underline; 
}
</style>