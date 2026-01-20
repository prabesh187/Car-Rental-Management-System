<?php
/**
 * Password Migration Script
 * This script migrates existing plain text passwords to hashed passwords
 * Run this ONCE after implementing password hashing system
 */

require 'connection.php';

echo "<h2>Password Migration Script</h2>";
echo "<p>This script will convert all plain text passwords to secure hashed passwords.</p>";

$conn = Connect();

try {
    // Start transaction for data integrity
    $conn->begin_transaction();
    
    $migrated_customers = 0;
    $migrated_clients = 0;
    $skipped_customers = 0;
    $skipped_clients = 0;
    
    // Migrate customer passwords
    echo "<h3>Migrating Customer Passwords...</h3>";
    $customer_query = "SELECT customer_id, customer_username, customer_password FROM customers";
    $customer_result = $conn->query($customer_query);
    
    if ($customer_result->num_rows > 0) {
        while ($customer = $customer_result->fetch_assoc()) {
            // Check if password is already hashed (bcrypt starts with $2y$)
            if (strpos($customer['customer_password'], '$2y$') !== 0) {
                // Password is plain text, hash it
                $hashed_password = password_hash($customer['customer_password'], PASSWORD_DEFAULT);
                
                $update_query = "UPDATE customers SET customer_password = ? WHERE customer_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("si", $hashed_password, $customer['customer_id']);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green;'>✅ Migrated password for customer: " . htmlspecialchars($customer['customer_username']) . "</p>";
                    $migrated_customers++;
                } else {
                    throw new Exception("Failed to update customer: " . $customer['customer_username']);
                }
            } else {
                echo "<p style='color: blue;'>ℹ️ Password already hashed for customer: " . htmlspecialchars($customer['customer_username']) . "</p>";
                $skipped_customers++;
            }
        }
    }
    
    // Migrate client passwords
    echo "<h3>Migrating Client Passwords...</h3>";
    $client_query = "SELECT client_id, client_username, client_password FROM clients";
    $client_result = $conn->query($client_query);
    
    if ($client_result->num_rows > 0) {
        while ($client = $client_result->fetch_assoc()) {
            // Check if password is already hashed (bcrypt starts with $2y$)
            if (strpos($client['client_password'], '$2y$') !== 0) {
                // Password is plain text, hash it
                $hashed_password = password_hash($client['client_password'], PASSWORD_DEFAULT);
                
                $update_query = "UPDATE clients SET client_password = ? WHERE client_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("si", $hashed_password, $client['client_id']);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green;'>✅ Migrated password for client: " . htmlspecialchars($client['client_username']) . "</p>";
                    $migrated_clients++;
                } else {
                    throw new Exception("Failed to update client: " . $client['client_username']);
                }
            } else {
                echo "<p style='color: blue;'>ℹ️ Password already hashed for client: " . htmlspecialchars($client['client_username']) . "</p>";
                $skipped_clients++;
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<h3 style='color: green;'>Migration Complete!</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Migration Summary:</h4>";
    echo "<ul>";
    echo "<li><strong>Customers:</strong> {$migrated_customers} migrated, {$skipped_customers} already hashed</li>";
    echo "<li><strong>Clients:</strong> {$migrated_clients} migrated, {$skipped_clients} already hashed</li>";
    echo "</ul>";
    echo "</div>";
    
    if ($migrated_customers > 0 || $migrated_clients > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724;'>✅ Security Improvement Complete</h4>";
        echo "<p>All passwords are now securely hashed using bcrypt algorithm.</p>";
        echo "<p><strong>Important:</strong> Users can continue logging in with their existing passwords - the system will automatically handle both hashed and plain text passwords during the transition.</p>";
        echo "</div>";
    }
    
    echo "<h4>Next Steps:</h4>";
    echo "<ul>";
    echo "<li>✅ Password hashing implemented in login systems</li>";
    echo "<li>✅ New registrations will use hashed passwords</li>";
    echo "<li>✅ Existing passwords migrated to hashed format</li>";
    echo "<li>✅ Backward compatibility maintained for transition period</li>";
    echo "</ul>";
    
    echo "<p><strong>Note:</strong> You can safely delete this migration script after running it once.</p>";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Migration Failed</h4>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>All changes have been rolled back. Please check the error and try again.</p>";
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
ul { 
    margin: 10px 0; 
}
li { 
    margin: 5px 0; 
}
</style>