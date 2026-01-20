<?php
/**
 * Test User Registration System
 * Comprehensive test to identify issues with user registration and credentials
 */

require 'connection.php';

echo "<h2>🔧 User Registration System Test</h2>";
echo "<p>Testing all user registration functionality...</p>";

$conn = Connect();

try {
    // Test 1: Database Connection
    echo "<h3>Test 1: Database Connection</h3>";
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
        exit();
    }
    
    // Test 2: Check if user tables exist
    echo "<h3>Test 2: User Tables Structure</h3>";
    
    $tables = ['customers', 'clients', 'admin'];
    foreach ($tables as $table) {
        $check_table = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($check_table);
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
            
            // Check table structure
            $describe = "DESCRIBE $table";
            $structure = $conn->query($describe);
            
            echo "<div style='margin-left: 20px; background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
            echo "<h5>$table table structure:</h5>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background: #e9ecef;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            
            while ($field = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$field['Field']}</td>";
                echo "<td>{$field['Type']}</td>";
                echo "<td>{$field['Null']}</td>";
                echo "<td>{$field['Key']}</td>";
                echo "<td>{$field['Default']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div><br>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
        }
    }
    
    // Test 3: Test Customer Registration Process
    echo "<h3>Test 3: Customer Registration Process</h3>";
    
    // Test customer signup form
    echo "<h4>Customer Signup Form Test:</h4>";
    echo "<p><strong>Form Location:</strong> customersignup.php</p>";
    echo "<p><strong>Action:</strong> customer_registered_success.php</p>";
    
    if (file_exists('customersignup.php')) {
        echo "<p style='color: green;'>✅ Customer signup form exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Customer signup form missing</p>";
    }
    
    if (file_exists('customer_registered_success.php')) {
        echo "<p style='color: green;'>✅ Customer registration handler exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Customer registration handler missing</p>";
    }
    
    // Test 4: Test Client Registration Process
    echo "<h3>Test 4: Client/Employee Registration Process</h3>";
    
    echo "<h4>Client Signup Form Test:</h4>";
    echo "<p><strong>Form Location:</strong> clientsignup.php</p>";
    echo "<p><strong>Action:</strong> client_registered_success.php</p>";
    
    if (file_exists('clientsignup.php')) {
        echo "<p style='color: green;'>✅ Client signup form exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Client signup form missing</p>";
    }
    
    if (file_exists('client_registered_success.php')) {
        echo "<p style='color: green;'>✅ Client registration handler exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Client registration handler missing</p>";
    }
    
    // Test 5: Admin User Management
    echo "<h3>Test 5: Admin User Management</h3>";
    
    $admin_files = ['admin_customers.php', 'admin_clients.php'];
    foreach ($admin_files as $file) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✅ $file exists</p>";
        } else {
            echo "<p style='color: red;'>❌ $file missing</p>";
        }
    }
    
    // Test 6: Check existing users
    echo "<h3>Test 6: Current User Data</h3>";
    
    // Check customers
    $customer_count_sql = "SELECT COUNT(*) as count FROM customers";
    $customer_result = $conn->query($customer_count_sql);
    $customer_count = $customer_result->fetch_assoc()['count'];
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Current User Statistics:</h4>";
    echo "<ul>";
    echo "<li><strong>Customers:</strong> $customer_count</li>";
    
    // Check clients
    $client_count_sql = "SELECT COUNT(*) as count FROM clients";
    $client_result = $conn->query($client_count_sql);
    $client_count = $client_result->fetch_assoc()['count'];
    echo "<li><strong>Clients/Employees:</strong> $client_count</li>";
    
    // Check admins
    $admin_count_sql = "SELECT COUNT(*) as count FROM admin";
    $admin_result = $conn->query($admin_count_sql);
    $admin_count = $admin_result->fetch_assoc()['count'];
    echo "<li><strong>Administrators:</strong> $admin_count</li>";
    echo "</ul>";
    echo "</div>";
    
    // Test 7: Test Registration Functionality
    echo "<h3>Test 7: Registration Functionality Test</h3>";
    
    // Test if we can add a test customer
    echo "<h4>Testing Customer Registration:</h4>";
    
    $test_customer_username = "test_customer_" . time();
    $test_customer_data = [
        'customer_name' => 'Test Customer',
        'customer_username' => $test_customer_username,
        'customer_email' => 'test@example.com',
        'customer_phone' => '1234567890',
        'customer_address' => 'Test Address',
        'customer_password' => password_hash('testpass123', PASSWORD_DEFAULT)
    ];
    
    $insert_customer_sql = "INSERT INTO customers (customer_name, customer_username, customer_email, customer_phone, customer_address, customer_password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_customer_sql);
    $stmt->bind_param("ssssss", 
        $test_customer_data['customer_name'],
        $test_customer_data['customer_username'],
        $test_customer_data['customer_email'],
        $test_customer_data['customer_phone'],
        $test_customer_data['customer_address'],
        $test_customer_data['customer_password']
    );
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Test customer registration successful</p>";
        
        // Clean up test data
        $cleanup_sql = "DELETE FROM customers WHERE customer_username = ?";
        $cleanup_stmt = $conn->prepare($cleanup_sql);
        $cleanup_stmt->bind_param("s", $test_customer_username);
        $cleanup_stmt->execute();
        echo "<p style='color: blue;'>🧹 Test data cleaned up</p>";
    } else {
        echo "<p style='color: red;'>❌ Test customer registration failed: " . $conn->error . "</p>";
    }
    
    // Test 8: Password Hashing Check
    echo "<h3>Test 8: Password Security Check</h3>";
    
    // Check if passwords are hashed
    $sample_customer_sql = "SELECT customer_username, customer_password FROM customers LIMIT 1";
    $sample_result = $conn->query($sample_customer_sql);
    
    if ($sample_result && $sample_result->num_rows > 0) {
        $sample_customer = $sample_result->fetch_assoc();
        $password = $sample_customer['customer_password'];
        
        if (strlen($password) >= 60 && (strpos($password, '$2y$') === 0 || strpos($password, '$2a$') === 0 || strpos($password, '$2x$') === 0)) {
            echo "<p style='color: green;'>✅ Passwords are properly hashed (bcrypt)</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Passwords may not be properly hashed</p>";
            echo "<p><strong>Sample password format:</strong> " . substr($password, 0, 20) . "...</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ No customers found to check password format</p>";
    }
    
    // Test 9: Common Registration Issues
    echo "<h3>Test 9: Common Registration Issues Check</h3>";
    
    $issues = [];
    
    // Check for duplicate constraints
    $check_constraints_sql = "SHOW CREATE TABLE customers";
    $constraints_result = $conn->query($check_constraints_sql);
    $create_table = $constraints_result->fetch_assoc()['Create Table'];
    
    if (strpos($create_table, 'UNIQUE') !== false) {
        echo "<p style='color: green;'>✅ Unique constraints exist on customers table</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No unique constraints found - may allow duplicate usernames/emails</p>";
        $issues[] = "Missing unique constraints";
    }
    
    // Check form validation
    $signup_content = file_get_contents('customersignup.php');
    if (strpos($signup_content, 'required') !== false) {
        echo "<p style='color: green;'>✅ Form validation attributes found</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Form validation may be missing</p>";
        $issues[] = "Missing form validation";
    }
    
    // Test 10: Admin Panel User Management
    echo "<h3>Test 10: Admin Panel User Management</h3>";
    
    // Check if admin can add users
    echo "<h4>Admin Customer Management:</h4>";
    if (file_exists('admin_customers.php')) {
        $admin_content = file_get_contents('admin_customers.php');
        if (strpos($admin_content, 'add_customer') !== false) {
            echo "<p style='color: green;'>✅ Admin can add customers</p>";
        } else {
            echo "<p style='color: red;'>❌ Admin customer addition functionality missing</p>";
            $issues[] = "Admin cannot add customers";
        }
    }
    
    echo "<h4>Admin Client Management:</h4>";
    if (file_exists('admin_clients.php')) {
        $admin_client_content = file_get_contents('admin_clients.php');
        if (strpos($admin_client_content, 'add_client') !== false) {
            echo "<p style='color: green;'>✅ Admin can add clients</p>";
        } else {
            echo "<p style='color: red;'>❌ Admin client addition functionality missing</p>";
            $issues[] = "Admin cannot add clients";
        }
    }
    
    // Final Summary
    echo "<h3>🎯 Final Summary</h3>";
    
    if (empty($issues)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724;'>✅ All Tests Passed!</h4>";
        echo "<p style='color: #155724;'>User registration system appears to be working correctly.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
        echo "<h4 style='color: #856404;'>⚠️ Issues Found:</h4>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li style='color: #856404;'>$issue</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Quick Actions
    echo "<h3>🚀 Quick Actions</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Registration Links:</h4>";
    echo "<ul>";
    echo "<li><a href='customersignup.php' target='_blank' style='color: #007bff;'>✅ Customer Registration</a></li>";
    echo "<li><a href='clientsignup.php' target='_blank' style='color: #007bff;'>✅ Client/Employee Registration</a></li>";
    echo "<li><a href='admin_customers.php?action=add' target='_blank' style='color: #007bff;'>✅ Admin Add Customer</a></li>";
    echo "<li><a href='admin_clients.php?action=add' target='_blank' style='color: #007bff;'>✅ Admin Add Client</a></li>";
    echo "</ul>";
    
    echo "<h4>🔧 If Registration Still Not Working:</h4>";
    echo "<ol>";
    echo "<li>Check if web server (Apache/Nginx) is running</li>";
    echo "<li>Verify PHP is properly configured</li>";
    echo "<li>Check database permissions</li>";
    echo "<li>Look for JavaScript errors in browser console</li>";
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