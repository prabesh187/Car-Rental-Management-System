<?php
/**
 * Fix Login Passwords - Migration Script
 * Convert plain text passwords to hashed format and fix login issues
 */

require 'connection.php';

echo "<h2>🔐 Password Migration & Login Fix</h2>";
echo "<p>Converting plain text passwords to secure hashed format...</p>";

$conn = Connect();

try {
    // Step 1: Analyze current password formats
    echo "<h3>Step 1: Password Format Analysis</h3>";
    
    // Check customers
    $customer_analysis_sql = "SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN customer_password LIKE '$2y$%' OR customer_password LIKE '$2a$%' OR customer_password LIKE '$2x$%' THEN 1 ELSE 0 END) as hashed,
                                SUM(CASE WHEN customer_password NOT LIKE '$2y$%' AND customer_password NOT LIKE '$2a$%' AND customer_password NOT LIKE '$2x$%' THEN 1 ELSE 0 END) as plain_text
                              FROM customers";
    $customer_analysis = $conn->query($customer_analysis_sql)->fetch_assoc();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Customer Password Analysis:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Customers:</strong> {$customer_analysis['total']}</li>";
    echo "<li><strong>Hashed Passwords:</strong> <span style='color: green;'>{$customer_analysis['hashed']}</span></li>";
    echo "<li><strong>Plain Text Passwords:</strong> <span style='color: red;'>{$customer_analysis['plain_text']}</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Check clients
    $client_analysis_sql = "SELECT 
                              COUNT(*) as total,
                              SUM(CASE WHEN client_password LIKE '$2y$%' OR client_password LIKE '$2a$%' OR client_password LIKE '$2x$%' THEN 1 ELSE 0 END) as hashed,
                              SUM(CASE WHEN client_password NOT LIKE '$2y$%' AND client_password NOT LIKE '$2a$%' AND client_password NOT LIKE '$2x$%' THEN 1 ELSE 0 END) as plain_text
                            FROM clients";
    $client_analysis = $conn->query($client_analysis_sql)->fetch_assoc();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Client Password Analysis:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Clients:</strong> {$client_analysis['total']}</li>";
    echo "<li><strong>Hashed Passwords:</strong> <span style='color: green;'>{$client_analysis['hashed']}</span></li>";
    echo "<li><strong>Plain Text Passwords:</strong> <span style='color: red;'>{$client_analysis['plain_text']}</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Step 2: Migration Options
    echo "<h3>Step 2: Migration Options</h3>";
    
    $total_plain_text = $customer_analysis['plain_text'] + $client_analysis['plain_text'];
    
    if ($total_plain_text > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>⚠️ Action Required</h4>";
        echo "<p>Found <strong>$total_plain_text</strong> users with plain text passwords that need to be migrated.</p>";
        echo "</div>";
        
        // Show migration options
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Migration Options:</h4>";
        echo "<ol>";
        echo "<li><strong>Option 1:</strong> <a href='?action=reset_passwords' style='color: #007bff;'>Reset all plain text passwords to 'password123'</a> (Recommended)</li>";
        echo "<li><strong>Option 2:</strong> <a href='?action=show_passwords' style='color: #007bff;'>Show current passwords for manual migration</a></li>";
        echo "<li><strong>Option 3:</strong> Let users reset their own passwords through login (automatic upgrade)</li>";
        echo "</ol>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>✅ All Good!</h4>";
        echo "<p>All passwords are already properly hashed. No migration needed.</p>";
        echo "</div>";
    }
    
    // Handle migration actions
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        if ($action === 'reset_passwords') {
            echo "<h3>Step 3: Password Reset Migration</h3>";
            
            $default_password = 'password123';
            $hashed_default = password_hash($default_password, PASSWORD_DEFAULT);
            $updated_customers = 0;
            $updated_clients = 0;
            
            // Update customers with plain text passwords
            $update_customers_sql = "UPDATE customers SET customer_password = ? WHERE customer_password NOT LIKE '$2y$%' AND customer_password NOT LIKE '$2a$%' AND customer_password NOT LIKE '$2x$%'";
            $stmt1 = $conn->prepare($update_customers_sql);
            $stmt1->bind_param("s", $hashed_default);
            
            if ($stmt1->execute()) {
                $updated_customers = $stmt1->affected_rows;
            }
            
            // Update clients with plain text passwords
            $update_clients_sql = "UPDATE clients SET client_password = ? WHERE client_password NOT LIKE '$2y$%' AND client_password NOT LIKE '$2a$%' AND client_password NOT LIKE '$2x$%'";
            $stmt2 = $conn->prepare($update_clients_sql);
            $stmt2->bind_param("s", $hashed_default);
            
            if ($stmt2->execute()) {
                $updated_clients = $stmt2->affected_rows;
            }
            
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Migration Complete!</h4>";
            echo "<ul>";
            echo "<li><strong>Customers Updated:</strong> $updated_customers</li>";
            echo "<li><strong>Clients Updated:</strong> $updated_clients</li>";
            echo "<li><strong>New Default Password:</strong> $default_password</li>";
            echo "</ul>";
            echo "<p><strong>Important:</strong> Inform users that their password has been reset to '$default_password' and they should change it after logging in.</p>";
            echo "</div>";
            
        } elseif ($action === 'show_passwords') {
            echo "<h3>Step 3: Current Plain Text Passwords</h3>";
            
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>⚠️ Security Warning</h4>";
            echo "<p>The following passwords are currently stored in plain text. This is a security risk!</p>";
            echo "</div>";
            
            // Show customer plain text passwords
            if ($customer_analysis['plain_text'] > 0) {
                echo "<h4>Customers with Plain Text Passwords:</h4>";
                $plain_customers_sql = "SELECT customer_username, customer_password FROM customers WHERE customer_password NOT LIKE '$2y$%' AND customer_password NOT LIKE '$2a$%' AND customer_password NOT LIKE '$2x$%'";
                $plain_customers = $conn->query($plain_customers_sql);
                
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr style='background: #f0f0f0;'><th>Username</th><th>Current Password</th><th>Action</th></tr>";
                
                while ($customer = $plain_customers->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$customer['customer_username']}</td>";
                    echo "<td style='color: red;'>{$customer['customer_password']}</td>";
                    echo "<td><a href='?action=hash_single&type=customer&username=" . urlencode($customer['customer_username']) . "&password=" . urlencode($customer['customer_password']) . "' style='color: #007bff;'>Hash This Password</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            // Show client plain text passwords
            if ($client_analysis['plain_text'] > 0) {
                echo "<h4>Clients with Plain Text Passwords:</h4>";
                $plain_clients_sql = "SELECT client_username, client_password FROM clients WHERE client_password NOT LIKE '$2y$%' AND client_password NOT LIKE '$2a$%' AND client_password NOT LIKE '$2x$%'";
                $plain_clients = $conn->query($plain_clients_sql);
                
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr style='background: #f0f0f0;'><th>Username</th><th>Current Password</th><th>Action</th></tr>";
                
                while ($client = $plain_clients->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$client['client_username']}</td>";
                    echo "<td style='color: red;'>{$client['client_password']}</td>";
                    echo "<td><a href='?action=hash_single&type=client&username=" . urlencode($client['client_username']) . "&password=" . urlencode($client['client_password']) . "' style='color: #007bff;'>Hash This Password</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } elseif ($action === 'hash_single') {
            $type = $_GET['type'];
            $username = $_GET['username'];
            $password = $_GET['password'];
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($type === 'customer') {
                $update_sql = "UPDATE customers SET customer_password = ? WHERE customer_username = ?";
            } else {
                $update_sql = "UPDATE clients SET client_password = ? WHERE client_username = ?";
            }
            
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ss", $hashed_password, $username);
            
            if ($stmt->execute()) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>✅ Password Hashed Successfully</h4>";
                echo "<p><strong>User:</strong> $username ($type)</p>";
                echo "<p><strong>Original Password:</strong> $password</p>";
                echo "<p><strong>Hashed Password:</strong> " . substr($hashed_password, 0, 30) . "...</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>❌ Error</h4>";
                echo "<p>Failed to update password for $username</p>";
                echo "</div>";
            }
        }
    }
    
    // Step 4: Test Login Functionality
    echo "<h3>Step 4: Test Login Functionality</h3>";
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🧪 Login Test</h4>";
    echo "<p>After migration, test the login functionality:</p>";
    echo "<ul>";
    echo "<li><a href='customerlogin.php' target='_blank' style='color: #007bff;'>Test Customer Login</a></li>";
    echo "<li><a href='clientlogin.php' target='_blank' style='color: #007bff;'>Test Client Login</a></li>";
    echo "<li><a href='admin_login.php' target='_blank' style='color: #007bff;'>Test Admin Login</a></li>";
    echo "</ul>";
    echo "</div>";
    
    // Step 5: Create test user for login testing
    echo "<h3>Step 5: Create Test User</h3>";
    
    if (isset($_GET['create_test_user'])) {
        $test_username = "testuser_" . time();
        $test_password = "test123";
        $test_hashed = password_hash($test_password, PASSWORD_DEFAULT);
        
        $insert_sql = "INSERT INTO customers (customer_username, customer_name, customer_email, customer_phone, customer_address, customer_password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssssss", $test_username, "Test User", "test@example.com", "1234567890", "Test Address", $test_hashed);
        
        if ($stmt->execute()) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>✅ Test User Created</h4>";
            echo "<p><strong>Username:</strong> $test_username</p>";
            echo "<p><strong>Password:</strong> $test_password</p>";
            echo "<p>Use these credentials to test login functionality.</p>";
            echo "<p><a href='customerlogin.php' target='_blank' style='color: #007bff;'>Test Login Now</a></p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Create Test User</h4>";
        echo "<p>Create a test user with known credentials to verify login functionality.</p>";
        echo "<p><a href='?create_test_user=1' style='color: #007bff;'>Create Test User</a></p>";
        echo "</div>";
    }
    
    // Final recommendations
    echo "<h3>🎯 Final Recommendations</h3>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Next Steps:</h4>";
    echo "<ol>";
    echo "<li><strong>Run Migration:</strong> Use Option 1 to reset all plain text passwords</li>";
    echo "<li><strong>Test Login:</strong> Verify that login works with the new passwords</li>";
    echo "<li><strong>Inform Users:</strong> Let users know their passwords have been reset</li>";
    echo "<li><strong>Monitor:</strong> Check that new registrations use hashed passwords</li>";
    echo "<li><strong>Security:</strong> Ensure all future passwords are properly hashed</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Migration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
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