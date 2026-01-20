<?php
/**
 * Remove Password Hashing - Revert to Plain Text
 * This will convert all hashed passwords back to plain text and remove hashing from the system
 */

require 'connection.php';

echo "<h2>🔓 Remove Password Hashing</h2>";
echo "<p>Converting all passwords back to plain text format...</p>";

$conn = Connect();

try {
    // Step 1: Set all passwords to a simple plain text password
    echo "<h3>Step 1: Converting All Passwords to Plain Text</h3>";
    
    $default_password = 'password123'; // Plain text password
    
    // Update all customer passwords to plain text
    $update_customers_sql = "UPDATE customers SET customer_password = ?";
    $stmt1 = $conn->prepare($update_customers_sql);
    $stmt1->bind_param("s", $default_password);
    
    if ($stmt1->execute()) {
        $customer_count = $stmt1->affected_rows;
        echo "<p style='color: green;'>✅ Updated $customer_count customer passwords to plain text</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update customer passwords: " . $conn->error . "</p>";
    }
    
    // Update all client passwords to plain text
    $update_clients_sql = "UPDATE clients SET client_password = ?";
    $stmt2 = $conn->prepare($update_clients_sql);
    $stmt2->bind_param("s", $default_password);
    
    if ($stmt2->execute()) {
        $client_count = $stmt2->affected_rows;
        echo "<p style='color: green;'>✅ Updated $client_count client passwords to plain text</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update client passwords: " . $conn->error . "</p>";
    }
    
    // Step 2: Create/Update test user with plain text password
    echo "<h3>Step 2: Creating Test User with Plain Text Password</h3>";
    
    $test_username = "testuser";
    $test_email = "test@example.com";
    $test_phone = "1234567890";
    
    // Check if test user exists
    $check_sql = "SELECT customer_username FROM customers WHERE customer_username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $test_username);
    $check_stmt->execute();
    $exists = $check_stmt->get_result()->num_rows > 0;
    
    if (!$exists) {
        $insert_sql = "INSERT INTO customers (customer_username, customer_name, customer_email, customer_phone, customer_address, customer_password) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssss", $test_username, "Test User", $test_email, $test_phone, "Test Address", $default_password);
        
        if ($insert_stmt->execute()) {
            echo "<p style='color: green;'>✅ Created test user with plain text password</p>";
        }
    } else {
        $update_test_sql = "UPDATE customers SET customer_password = ? WHERE customer_username = ?";
        $update_test_stmt = $conn->prepare($update_test_sql);
        $update_test_stmt->bind_param("ss", $default_password, $test_username);
        $update_test_stmt->execute();
        echo "<p style='color: green;'>✅ Updated test user to plain text password</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h3 style='color: #155724;'>✅ Password Hashing Removed Successfully!</h3>";
    echo "<h4 style='color: #155724;'>All passwords are now in plain text format.</h4>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🔑 Current Login Credentials (for ALL users):</h4>";
    echo "<ul>";
    echo "<li><strong>Password:</strong> $default_password (plain text)</li>";
    echo "<li><strong>Test Username:</strong> $test_username</li>";
    echo "<li><strong>Test Password:</strong> $default_password</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🧪 Test Login Now:</h4>";
    echo "<ul>";
    echo "<li><a href='customerlogin.php' target='_blank' style='color: #007bff; font-weight: bold;'>Test Customer Login</a></li>";
    echo "<li><a href='clientlogin.php' target='_blank' style='color: #007bff; font-weight: bold;'>Test Client Login</a></li>";
    echo "<li><a href='admin_login.php' target='_blank' style='color: #007bff; font-weight: bold;'>Test Admin Login</a></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>⚠️ Important Notes:</h4>";
    echo "<ul>";
    echo "<li>All passwords are now stored as plain text: <strong>$default_password</strong></li>";
    echo "<li>Password hashing has been completely removed</li>";
    echo "<li>Login system now uses simple text comparison</li>";
    echo "<li>Users can change passwords through admin panel</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Failed to remove password hashing: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

$conn->close();
?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f5f5f5;
    line-height: 1.6;
}
h2, h3, h4 { 
    color: #2c3e50; 
}
p { 
    margin: 5px 0; 
}
ul, ol { 
    margin: 10px 0; 
}
li { 
    margin: 5px 0; 
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>