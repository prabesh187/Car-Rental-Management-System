<?php
/**
 * Test Login Password Compatibility
 * Diagnose and fix login issues with encrypted passwords
 */

require 'connection.php';

echo "<h2>🔐 Login Password Compatibility Test</h2>";
echo "<p>Testing login functionality with encrypted passwords...</p>";

$conn = Connect();

try {
    // Test 1: Check password formats in database
    echo "<h3>Test 1: Password Format Analysis</h3>";
    
    // Check customer passwords
    echo "<h4>Customer Password Analysis:</h4>";
    $customer_sql = "SELECT customer_username, customer_password, LENGTH(customer_password) as pwd_length FROM customers LIMIT 5";
    $customer_result = $conn->query($customer_sql);
    
    if ($customer_result && $customer_result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Username</th><th>Password Format</th><th>Length</th><th>Type</th></tr>";
        
        while ($customer = $customer_result->fetch_assoc()) {
            $password = $customer['customer_password'];
            $length = $customer['pwd_length'];
            $is_hashed = (strpos($password, '$2y$') === 0 || strpos($password, '$2a$') === 0 || strpos($password, '$2x$') === 0);
            $type = $is_hashed ? 'Hashed (bcrypt)' : 'Plain Text';
            $format = $is_hashed ? substr($password, 0, 20) . '...' : 'Plain text (hidden)';
            
            $color = $is_hashed ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$customer['customer_username']}</td>";
            echo "<td>{$format}</td>";
            echo "<td>{$length}</td>";
            echo "<td style='color: {$color};'>{$type}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No customers found in database</p>";
    }
    
    // Check client passwords
    echo "<h4>Client Password Analysis:</h4>";
    $client_sql = "SELECT client_username, client_password, LENGTH(client_password) as pwd_length FROM clients LIMIT 5";
    $client_result = $conn->query($client_sql);
    
    if ($client_result && $client_result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Username</th><th>Password Format</th><th>Length</th><th>Type</th></tr>";
        
        while ($client = $client_result->fetch_assoc()) {
            $password = $client['client_password'];
            $length = $client['pwd_length'];
            $is_hashed = (strpos($password, '$2y$') === 0 || strpos($password, '$2a$') === 0 || strpos($password, '$2x$') === 0);
            $type = $is_hashed ? 'Hashed (bcrypt)' : 'Plain Text';
            $format = $is_hashed ? substr($password, 0, 20) . '...' : 'Plain text (hidden)';
            
            $color = $is_hashed ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$client['client_username']}</td>";
            echo "<td>{$format}</td>";
            echo "<td>{$length}</td>";
            echo "<td style='color: {$color};'>{$type}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No clients found in database</p>";
    }
    
    // Test 2: Test password verification
    echo "<h3>Test 2: Password Verification Test</h3>";
    
    // Test with a known password
    $test_password = "testpass123";
    $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Password Hashing Test:</h4>";
    echo "<p><strong>Original Password:</strong> $test_password</p>";
    echo "<p><strong>Hashed Password:</strong> " . substr($hashed_password, 0, 30) . "...</p>";
    
    // Test verification
    if (password_verify($test_password, $hashed_password)) {
        echo "<p style='color: green;'>✅ Password verification working correctly</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed</p>";
    }
    echo "</div>";
    
    // Test 3: Login Logic Test
    echo "<h3>Test 3: Login Logic Simulation</h3>";
    
    // Get a sample user to test login logic
    $sample_customer_sql = "SELECT customer_username, customer_password FROM customers LIMIT 1";
    $sample_result = $conn->query($sample_customer_sql);
    
    if ($sample_result && $sample_result->num_rows > 0) {
        $sample_customer = $sample_result->fetch_assoc();
        $username = $sample_customer['customer_username'];
        $stored_password = $sample_customer['customer_password'];
        
        echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Sample Customer Login Test:</h4>";
        echo "<p><strong>Username:</strong> $username</p>";
        
        $is_hashed = (strpos($stored_password, '$2y$') === 0);
        
        if ($is_hashed) {
            echo "<p style='color: green;'>✅ Password is properly hashed</p>";
            echo "<p><strong>Login Method:</strong> Will use password_verify()</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Password is in plain text</p>";
            echo "<p><strong>Login Method:</strong> Will use direct comparison (with upgrade to hash)</p>";
        }
        echo "</div>";
    }
    
    // Test 4: Create test user with known password
    echo "<h3>Test 4: Create Test User for Login Testing</h3>";
    
    $test_username = "test_login_" . time();
    $test_password = "test123";
    $test_hashed = password_hash($test_password, PASSWORD_DEFAULT);
    
    // Insert test user
    $insert_sql = "INSERT INTO customers (customer_username, customer_name, customer_email, customer_phone, customer_address, customer_password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ssssss", $test_username, "Test User", "test@example.com", "1234567890", "Test Address", $test_hashed);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Test user created successfully</p>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Test Login Credentials:</h4>";
        echo "<p><strong>Username:</strong> $test_username</p>";
        echo "<p><strong>Password:</strong> $test_password</p>";
        echo "<p><strong>Stored Hash:</strong> " . substr($test_hashed, 0, 30) . "...</p>";
        echo "</div>";
        
        // Test login simulation
        echo "<h4>Login Simulation Test:</h4>";
        
        // Simulate login process
        $login_sql = "SELECT customer_username, customer_password FROM customers WHERE customer_username=? LIMIT 1";
        $login_stmt = $conn->prepare($login_sql);
        $login_stmt->bind_param("s", $test_username);
        $login_stmt->execute();
        $login_result = $login_stmt->get_result();
        
        if ($login_result->num_rows > 0) {
            $customer = $login_result->fetch_assoc();
            
            if (strpos($customer['customer_password'], '$2y$') === 0) {
                if (password_verify($test_password, $customer['customer_password'])) {
                    echo "<p style='color: green;'>✅ Login simulation successful with hashed password</p>";
                } else {
                    echo "<p style='color: red;'>❌ Login simulation failed - password_verify() returned false</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Password not hashed - would use plain text comparison</p>";
            }
        }
        
        // Clean up test user
        $cleanup_sql = "DELETE FROM customers WHERE customer_username = ?";
        $cleanup_stmt = $conn->prepare($cleanup_sql);
        $cleanup_stmt->bind_param("s", $test_username);
        $cleanup_stmt->execute();
        echo "<p style='color: blue;'>🧹 Test user cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to create test user: " . $conn->error . "</p>";
    }
    
    // Test 5: Check login file compatibility
    echo "<h3>Test 5: Login File Compatibility Check</h3>";
    
    $login_files = ['login_customer.php', 'login_client.php'];
    
    foreach ($login_files as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            
            echo "<h4>$file Analysis:</h4>";
            echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
            
            if (strpos($content, 'password_verify') !== false) {
                echo "<p style='color: green;'>✅ Contains password_verify() function</p>";
            } else {
                echo "<p style='color: red;'>❌ Missing password_verify() function</p>";
            }
            
            if (strpos($content, '$2y$') !== false) {
                echo "<p style='color: green;'>✅ Checks for bcrypt hash format</p>";
            } else {
                echo "<p style='color: red;'>❌ Missing bcrypt hash format check</p>";
            }
            
            if (strpos($content, 'password_hash') !== false) {
                echo "<p style='color: green;'>✅ Contains password upgrade logic</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Missing password upgrade logic</p>";
            }
            
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ $file not found</p>";
        }
    }
    
    // Test 6: Common Issues and Solutions
    echo "<h3>Test 6: Common Login Issues & Solutions</h3>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🔍 Possible Issues:</h4>";
    echo "<ol>";
    echo "<li><strong>Mixed Password Formats:</strong> Some users have hashed passwords, others have plain text</li>";
    echo "<li><strong>Login Form Issues:</strong> Form might not be submitting correctly</li>";
    echo "<li><strong>Session Issues:</strong> Session variables might not be set properly</li>";
    echo "<li><strong>Database Connection:</strong> Connection issues during login</li>";
    echo "<li><strong>PHP Errors:</strong> Syntax or logic errors in login files</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>💡 Solutions:</h4>";
    echo "<ol>";
    echo "<li><strong>Password Migration:</strong> Update all plain text passwords to hashed format</li>";
    echo "<li><strong>Login Compatibility:</strong> Ensure login handles both formats during transition</li>";
    echo "<li><strong>Error Logging:</strong> Add error logging to identify specific issues</li>";
    echo "<li><strong>Form Validation:</strong> Ensure forms are working correctly</li>";
    echo "</ol>";
    echo "</div>";
    
    // Final recommendations
    echo "<h3>🎯 Recommendations</h3>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Next Steps:</h4>";
    echo "<ol>";
    echo "<li><strong>Test Login:</strong> Try logging in with existing credentials</li>";
    echo "<li><strong>Check Browser Console:</strong> Look for JavaScript errors</li>";
    echo "<li><strong>Check PHP Errors:</strong> Enable error reporting to see PHP issues</li>";
    echo "<li><strong>Password Reset:</strong> If needed, reset passwords through admin panel</li>";
    echo "<li><strong>Migration Script:</strong> Run password migration if many users affected</li>";
    echo "</ol>";
    echo "</div>";
    
    // Quick actions
    echo "<h3>🚀 Quick Actions</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Test Login Pages:</h4>";
    echo "<ul>";
    echo "<li><a href='customerlogin.php' target='_blank' style='color: #007bff;'>✅ Customer Login</a></li>";
    echo "<li><a href='clientlogin.php' target='_blank' style='color: #007bff;'>✅ Client Login</a></li>";
    echo "<li><a href='admin_login.php' target='_blank' style='color: #007bff;'>✅ Admin Login</a></li>";
    echo "</ul>";
    
    echo "<h4>🔧 If Login Still Not Working:</h4>";
    echo "<ol>";
    echo "<li>Check if the username exists in the database</li>";
    echo "<li>Verify the password was entered correctly</li>";
    echo "<li>Check browser developer tools for errors</li>";
    echo "<li>Try creating a new user through admin panel</li>";
    echo "<li>Check PHP error logs</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
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
table {
    background: white;
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
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>