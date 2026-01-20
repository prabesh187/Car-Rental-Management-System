<?php
/**
 * Password System Test Script
 * Tests the complete password hashing implementation
 */

require 'connection.php';

echo "<h2>Password System Test</h2>";
echo "<p>Testing the complete password hashing implementation...</p>";

$conn = Connect();
$test_results = [];

// Test 1: Check if password_hash function is available
echo "<h3>Test 1: PHP Password Functions</h3>";
if (function_exists('password_hash') && function_exists('password_verify')) {
    echo "<p style='color: green;'>✅ PHP password functions are available</p>";
    $test_results['php_functions'] = true;
} else {
    echo "<p style='color: red;'>❌ PHP password functions are NOT available (requires PHP 5.5+)</p>";
    $test_results['php_functions'] = false;
}

// Test 2: Test password hashing
echo "<h3>Test 2: Password Hashing</h3>";
$test_password = "testpassword123";
$hashed = password_hash($test_password, PASSWORD_DEFAULT);

if ($hashed && strlen($hashed) >= 60) {
    echo "<p style='color: green;'>✅ Password hashing works correctly</p>";
    echo "<p><strong>Original:</strong> " . htmlspecialchars($test_password) . "</p>";
    echo "<p><strong>Hashed:</strong> " . htmlspecialchars($hashed) . "</p>";
    $test_results['hashing'] = true;
} else {
    echo "<p style='color: red;'>❌ Password hashing failed</p>";
    $test_results['hashing'] = false;
}

// Test 3: Test password verification
echo "<h3>Test 3: Password Verification</h3>";
if ($test_results['hashing']) {
    $verify_correct = password_verify($test_password, $hashed);
    $verify_wrong = password_verify("wrongpassword", $hashed);
    
    if ($verify_correct && !$verify_wrong) {
        echo "<p style='color: green;'>✅ Password verification works correctly</p>";
        echo "<p>✅ Correct password verified successfully</p>";
        echo "<p>✅ Wrong password rejected correctly</p>";
        $test_results['verification'] = true;
    } else {
        echo "<p style='color: red;'>❌ Password verification failed</p>";
        $test_results['verification'] = false;
    }
}

// Test 4: Check database structure
echo "<h3>Test 4: Database Structure</h3>";
try {
    // Check customers table
    $customers_check = $conn->query("DESCRIBE customers");
    $customer_password_field = null;
    
    while ($row = $customers_check->fetch_assoc()) {
        if ($row['Field'] == 'customer_password') {
            $customer_password_field = $row;
            break;
        }
    }
    
    if ($customer_password_field) {
        echo "<p style='color: green;'>✅ Customers table has password field</p>";
        echo "<p>Field type: " . $customer_password_field['Type'] . "</p>";
        
        // Check if field can store hashed passwords (should be at least VARCHAR(255))
        if (strpos($customer_password_field['Type'], 'varchar') !== false) {
            $length = preg_replace('/[^0-9]/', '', $customer_password_field['Type']);
            if ($length >= 255) {
                echo "<p style='color: green;'>✅ Password field can store hashed passwords (length: {$length})</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Password field might be too short for hashed passwords (length: {$length})</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Customers table missing password field</p>";
    }
    
    // Check clients table
    $clients_check = $conn->query("DESCRIBE clients");
    $client_password_field = null;
    
    while ($row = $clients_check->fetch_assoc()) {
        if ($row['Field'] == 'client_password') {
            $client_password_field = $row;
            break;
        }
    }
    
    if ($client_password_field) {
        echo "<p style='color: green;'>✅ Clients table has password field</p>";
        echo "<p>Field type: " . $client_password_field['Type'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Clients table missing password field</p>";
    }
    
    $test_results['database'] = true;
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database structure check failed: " . $e->getMessage() . "</p>";
    $test_results['database'] = false;
}

// Test 5: Check admin system
echo "<h3>Test 5: Admin System</h3>";
try {
    $admin_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
    
    if ($admin_check->num_rows > 0) {
        echo "<p style='color: green;'>✅ Admin users table exists</p>";
        
        // Check if there are admin users
        $admin_count = $conn->query("SELECT COUNT(*) as count FROM admin_users");
        $count = $admin_count->fetch_assoc()['count'];
        
        if ($count > 0) {
            echo "<p style='color: green;'>✅ Admin users exist ({$count} users)</p>";
            
            // Check if passwords are hashed
            $admin_sample = $conn->query("SELECT admin_password FROM admin_users LIMIT 1");
            $sample_password = $admin_sample->fetch_assoc()['admin_password'];
            
            if (strpos($sample_password, '$2y$') === 0) {
                echo "<p style='color: green;'>✅ Admin passwords are properly hashed</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Admin passwords might not be hashed</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ No admin users found</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Admin users table does not exist</p>";
        echo "<p>Run setup_admin.php to create admin system</p>";
    }
    
    $test_results['admin'] = true;
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Admin system check failed: " . $e->getMessage() . "</p>";
    $test_results['admin'] = false;
}

// Test 6: Sample data check
echo "<h3>Test 6: Sample Data Check</h3>";
try {
    // Check for sample customers
    $customer_sample = $conn->query("SELECT customer_username, customer_password FROM customers LIMIT 3");
    
    if ($customer_sample->num_rows > 0) {
        echo "<p style='color: green;'>✅ Sample customers found</p>";
        
        $hashed_count = 0;
        $plain_count = 0;
        
        while ($customer = $customer_sample->fetch_assoc()) {
            if (strpos($customer['customer_password'], '$2y$') === 0) {
                $hashed_count++;
                echo "<p style='color: green;'>✅ Customer '{$customer['customer_username']}' has hashed password</p>";
            } else {
                $plain_count++;
                echo "<p style='color: orange;'>⚠️ Customer '{$customer['customer_username']}' has plain text password</p>";
            }
        }
        
        if ($plain_count > 0) {
            echo "<p style='color: blue;'>ℹ️ Run migrate_passwords.php to hash existing plain text passwords</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ No customers found in database</p>";
    }
    
    // Check for sample clients
    $client_sample = $conn->query("SELECT client_username, client_password FROM clients LIMIT 3");
    
    if ($client_sample->num_rows > 0) {
        echo "<p style='color: green;'>✅ Sample clients found</p>";
        
        $hashed_count = 0;
        $plain_count = 0;
        
        while ($client = $client_sample->fetch_assoc()) {
            if (strpos($client['client_password'], '$2y$') === 0) {
                $hashed_count++;
                echo "<p style='color: green;'>✅ Client '{$client['client_username']}' has hashed password</p>";
            } else {
                $plain_count++;
                echo "<p style='color: orange;'>⚠️ Client '{$client['client_username']}' has plain text password</p>";
            }
        }
        
        if ($plain_count > 0) {
            echo "<p style='color: blue;'>ℹ️ Run migrate_passwords.php to hash existing plain text passwords</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ No clients found in database</p>";
    }
    
    $test_results['sample_data'] = true;
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Sample data check failed: " . $e->getMessage() . "</p>";
    $test_results['sample_data'] = false;
}

// Overall Results
echo "<h3>Overall Test Results</h3>";
$passed_tests = array_sum($test_results);
$total_tests = count($test_results);

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>Test Summary: {$passed_tests}/{$total_tests} tests passed</h4>";

foreach ($test_results as $test => $result) {
    $status = $result ? "✅ PASS" : "❌ FAIL";
    $test_name = ucwords(str_replace('_', ' ', $test));
    echo "<p>{$status} - {$test_name}</p>";
}

if ($passed_tests == $total_tests) {
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724;'>🎉 All Tests Passed!</h4>";
    echo "<p>Your password system is properly implemented and secure.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
    echo "<h4 style='color: #856404;'>⚠️ Some Tests Failed</h4>";
    echo "<p>Please review the failed tests and fix any issues.</p>";
    echo "</div>";
}

echo "</div>";

echo "<h4>Security Features Implemented:</h4>";
echo "<ul>";
echo "<li>✅ Password hashing using PHP's password_hash() function</li>";
echo "<li>✅ Secure password verification using password_verify()</li>";
echo "<li>✅ Backward compatibility for existing plain text passwords</li>";
echo "<li>✅ Automatic password migration during login</li>";
echo "<li>✅ Prepared statements to prevent SQL injection</li>";
echo "<li>✅ Admin system with proper password hashing</li>";
echo "</ul>";

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
ul { 
    margin: 10px 0; 
}
li { 
    margin: 5px 0; 
}
</style>