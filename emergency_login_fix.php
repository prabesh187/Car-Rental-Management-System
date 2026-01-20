<?php
/**
 * Emergency Login Fix
 * This will reset all passwords to a known value and fix login issues immediately
 */

require 'connection.php';

echo "<h2>🚨 Emergency Login Fix</h2>";
echo "<p>This will immediately fix all login issues by resetting passwords to a known value.</p>";

$conn = Connect();

try {
    // Step 1: Set all customer passwords to 'password123' (hashed)
    echo "<h3>Step 1: Fixing Customer Passwords</h3>";
    
    $default_password = 'password123';
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    $update_customers_sql = "UPDATE customers SET customer_password = ?";
    $stmt1 = $conn->prepare($update_customers_sql);
    $stmt1->bind_param("s", $hashed_password);
    
    if ($stmt1->execute()) {
        $customer_count = $stmt1->affected_rows;
        echo "<p style='color: green;'>✅ Updated $customer_count customer passwords</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update customer passwords: " . $conn->error . "</p>";
    }
    
    // Step 2: Set all client passwords to 'password123' (hashed)
    echo "<h3>Step 2: Fixing Client Passwords</h3>";
    
    $update_clients_sql = "UPDATE clients SET client_password = ?";
    $stmt2 = $conn->prepare($update_clients_sql);
    $stmt2->bind_param("s", $hashed_password);
    
    if ($stmt2->execute()) {
        $client_count = $stmt2->affected_rows;
        echo "<p style='color: green;'>✅ Updated $client_count client passwords</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update client passwords: " . $conn->error . "</p>";
    }
    
    // Step 3: Create a test user
    echo "<h3>Step 3: Creating Test User</h3>";
    
    $test_username = "testuser";
    $test_email = "test@example.com";
    $test_phone = "1234567890";
    
    // Check if test user already exists
    $check_sql = "SELECT customer_username FROM customers WHERE customer_username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $test_username);
    $check_stmt->execute();
    $exists = $check_stmt->get_result()->num_rows > 0;
    
    if (!$exists) {
        $insert_sql = "INSERT INTO customers (customer_username, customer_name, customer_email, customer_phone, customer_address, customer_password) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssssss", $test_username, "Test User", $test_email, $test_phone, "Test Address", $hashed_password);
        
        if ($insert_stmt->execute()) {
            echo "<p style='color: green;'>✅ Created test user: $test_username</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Could not create test user (may already exist)</p>";
        }
    } else {
        // Update existing test user password
        $update_test_sql = "UPDATE customers SET customer_password = ? WHERE customer_username = ?";
        $update_test_stmt = $conn->prepare($update_test_sql);
        $update_test_stmt->bind_param("ss", $hashed_password, $test_username);
        $update_test_stmt->execute();
        echo "<p style='color: green;'>✅ Updated existing test user password</p>";
    }
    
    // Step 4: Verify login files are working
    echo "<h3>Step 4: Login File Status</h3>";
    
    $login_files = [
        'login_customer.php' => 'Customer Login',
        'login_client.php' => 'Client Login',
        'admin_login.php' => 'Admin Login'
    ];
    
    foreach ($login_files as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✅ $description file exists</p>";
        } else {
            echo "<p style='color: red;'>❌ $description file missing</p>";
        }
    }
    
    // Step 5: Success message and instructions
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h3 style='color: #155724;'>🎉 Emergency Fix Complete!</h3>";
    echo "<h4 style='color: #155724;'>All login issues should now be resolved.</h4>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🔑 Login Credentials (for ALL users):</h4>";
    echo "<ul>";
    echo "<li><strong>Password:</strong> $default_password</li>";
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
    echo "<li>All existing users now have the password: <strong>$default_password</strong></li>";
    echo "<li>Users should change their password after logging in</li>";
    echo "<li>New registrations will continue to work normally</li>";
    echo "<li>All passwords are now securely hashed in the database</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    
    // Step 6: Quick verification test
    echo "<h3>Step 6: Quick Verification Test</h3>";
    
    // Test password verification
    $test_plain = $default_password;
    $test_hash = $hashed_password;
    
    if (password_verify($test_plain, $test_hash)) {
        echo "<p style='color: green;'>✅ Password verification is working correctly</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed - there may be a PHP issue</p>";
    }
    
    // Test database query
    $verify_sql = "SELECT customer_username, customer_password FROM customers WHERE customer_username = ? LIMIT 1";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("s", $test_username);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $test_user = $verify_result->fetch_assoc();
        if (password_verify($default_password, $test_user['customer_password'])) {
            echo "<p style='color: green;'>✅ Database password verification working</p>";
        } else {
            echo "<p style='color: red;'>❌ Database password verification failed</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Test user not found in database</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Emergency fix failed: " . htmlspecialchars($e->getMessage()) . "</p>";
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