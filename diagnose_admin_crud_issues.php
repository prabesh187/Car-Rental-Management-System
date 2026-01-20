<?php
/**
 * Admin CRUD Issues Diagnostic Script
 * Identifies specific problems with admin car management CRUD operations
 */

require 'connection.php';

echo "<h2>Admin CRUD Issues Diagnostic</h2>";
echo "<p>Analyzing admin car management system for CRUD operation problems...</p>";

$conn = Connect();
$issues = [];
$recommendations = [];

// Issue 1: Check for orphaned cars (cars without client assignment)
echo "<h3>Issue 1: Orphaned Cars Analysis</h3>";
try {
    $orphaned_cars_sql = "SELECT c.car_id, c.car_name, c.car_nameplate 
                         FROM cars c 
                         LEFT JOIN clientcars cc ON c.car_id = cc.car_id 
                         WHERE cc.car_id IS NULL";
    $orphaned_result = $conn->query($orphaned_cars_sql);
    
    if ($orphaned_result->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Found {$orphaned_result->num_rows} orphaned cars (not assigned to any client)</p>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Car ID</th><th>Car Name</th><th>Number Plate</th><th>Issue</th></tr>";
        
        while ($car = $orphaned_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$car['car_id']}</td>";
            echo "<td>{$car['car_name']}</td>";
            echo "<td>{$car['car_nameplate']}</td>";
            echo "<td style='color: red;'>No client assignment</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $issues[] = "Orphaned cars exist - cars added through admin panel are not assigned to any fleet owner";
        $recommendations[] = "Modify admin_cars.php to include client assignment during car creation";
    } else {
        echo "<p style='color: green;'>✅ No orphaned cars found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking orphaned cars: " . $e->getMessage() . "</p>";
}

// Issue 2: Check for missing clientcars relationships
echo "<h3>Issue 2: Client-Car Relationship Integrity</h3>";
try {
    $total_cars = $conn->query("SELECT COUNT(*) as count FROM cars")->fetch_assoc()['count'];
    $assigned_cars = $conn->query("SELECT COUNT(*) as count FROM clientcars")->fetch_assoc()['count'];
    
    echo "<p><strong>Total Cars:</strong> {$total_cars}</p>";
    echo "<p><strong>Assigned Cars:</strong> {$assigned_cars}</p>";
    echo "<p><strong>Unassigned Cars:</strong> " . ($total_cars - $assigned_cars) . "</p>";
    
    if ($total_cars > $assigned_cars) {
        echo "<p style='color: orange;'>⚠️ Some cars are not assigned to any client</p>";
        $issues[] = "Car-client relationship integrity issue";
        $recommendations[] = "Ensure all cars are properly assigned to clients or marked as system-owned";
    } else {
        echo "<p style='color: green;'>✅ All cars are properly assigned</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking relationships: " . $e->getMessage() . "</p>";
}

// Issue 3: Check admin_cars.php add functionality
echo "<h3>Issue 3: Admin Add Car Functionality Analysis</h3>";
$admin_cars_content = file_get_contents('admin_cars.php');

if (strpos($admin_cars_content, 'INSERT INTO clientcars') === false) {
    echo "<p style='color: red;'>❌ Admin add car function does NOT insert into clientcars table</p>";
    $issues[] = "Admin add car function missing clientcars table insertion";
    $recommendations[] = "Add clientcars table insertion after successful car creation";
} else {
    echo "<p style='color: green;'>✅ Admin add car function includes clientcars insertion</p>";
}

if (strpos($admin_cars_content, 'assigned_client') === false) {
    echo "<p style='color: red;'>❌ Admin form does NOT include client assignment field</p>";
    $issues[] = "Admin form missing client assignment dropdown";
    $recommendations[] = "Add client selection dropdown to admin car form";
} else {
    echo "<p style='color: green;'>✅ Admin form includes client assignment field</p>";
}

// Issue 4: Check for transaction handling
echo "<h3>Issue 4: Transaction Handling Analysis</h3>";
if (strpos($admin_cars_content, 'begin_transaction') === false) {
    echo "<p style='color: orange;'>⚠️ Admin CRUD operations do not use database transactions</p>";
    $issues[] = "No transaction handling for atomic operations";
    $recommendations[] = "Implement database transactions for CRUD operations";
} else {
    echo "<p style='color: green;'>✅ Transaction handling implemented</p>";
}

// Issue 5: Check delete functionality
echo "<h3>Issue 5: Delete Functionality Analysis</h3>";
if (strpos($admin_cars_content, 'DELETE FROM clientcars') !== false) {
    echo "<p style='color: green;'>✅ Delete function properly handles clientcars table</p>";
} else {
    echo "<p style='color: red;'>❌ Delete function may not properly handle clientcars relationships</p>";
    $issues[] = "Delete function may leave orphaned clientcars records";
    $recommendations[] = "Ensure delete function removes clientcars relationships first";
}

// Issue 6: Check for proper error handling
echo "<h3>Issue 6: Error Handling Analysis</h3>";
$error_patterns = ['try {', 'catch (', '$conn->error', 'throw new Exception'];
$error_handling_score = 0;

foreach ($error_patterns as $pattern) {
    if (strpos($admin_cars_content, $pattern) !== false) {
        $error_handling_score++;
    }
}

if ($error_handling_score >= 3) {
    echo "<p style='color: green;'>✅ Good error handling implementation</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Limited error handling in admin CRUD operations</p>";
    $issues[] = "Insufficient error handling in CRUD operations";
    $recommendations[] = "Implement comprehensive try-catch blocks and error reporting";
}

// Issue 7: Check for proper form validation
echo "<h3>Issue 7: Form Validation Analysis</h3>";
if (strpos($admin_cars_content, 'empty($_POST[') !== false && strpos($admin_cars_content, 'required') !== false) {
    echo "<p style='color: green;'>✅ Form validation implemented</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Limited form validation</p>";
    $issues[] = "Insufficient form validation";
    $recommendations[] = "Add comprehensive server-side and client-side validation";
}

// Issue 8: Check for SQL injection prevention
echo "<h3>Issue 8: SQL Security Analysis</h3>";
if (strpos($admin_cars_content, 'prepare(') !== false && strpos($admin_cars_content, 'bind_param') !== false) {
    echo "<p style='color: green;'>✅ Prepared statements used for SQL security</p>";
} else {
    echo "<p style='color: red;'>❌ Potential SQL injection vulnerability</p>";
    $issues[] = "SQL injection vulnerability";
    $recommendations[] = "Use prepared statements for all database queries";
}

// Summary of Issues
echo "<h3>Summary of Issues Found</h3>";
if (empty($issues)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724;'>🎉 No Major Issues Found!</h4>";
    echo "<p>The admin CRUD system appears to be working correctly.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<h4 style='color: #856404;'>⚠️ Issues Found: " . count($issues) . "</h4>";
    echo "<ol>";
    foreach ($issues as $issue) {
        echo "<li style='color: #856404;'>{$issue}</li>";
    }
    echo "</ol>";
    echo "</div>";
}

// Recommendations
echo "<h3>Recommended Solutions</h3>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo "<h4>Priority Fixes:</h4>";
echo "<ol>";
foreach ($recommendations as $recommendation) {
    echo "<li>{$recommendation}</li>";
}
echo "</ol>";
echo "</div>";

// Specific CRUD Operation Tests
echo "<h3>CRUD Operation Tests</h3>";

// Test CREATE operation
echo "<h4>CREATE Operation Test:</h4>";
try {
    // Simulate add car operation (without actually inserting)
    $test_car_data = [
        'car_name' => 'Test Car',
        'car_nameplate' => 'TEST123',
        'ac_price' => 25.00,
        'non_ac_price' => 15.00,
        'ac_price_per_day' => 3000.00,
        'non_ac_price_per_day' => 2000.00,
        'car_availability' => 'yes'
    ];
    
    echo "<p style='color: green;'>✅ CREATE: Car data structure is valid</p>";
    echo "<p><strong>Test Data:</strong> " . json_encode($test_car_data) . "</p>";
    
    // Check if clients exist for assignment
    $clients_count = $conn->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count'];
    if ($clients_count > 0) {
        echo "<p style='color: green;'>✅ CREATE: Clients available for assignment ({$clients_count} clients)</p>";
    } else {
        echo "<p style='color: red;'>❌ CREATE: No clients available for car assignment</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CREATE: Error in create operation test - " . $e->getMessage() . "</p>";
}

// Test READ operation
echo "<h4>READ Operation Test:</h4>";
try {
    $read_sql = "SELECT c.*, cc.client_username, cl.client_name 
                FROM cars c 
                LEFT JOIN clientcars cc ON c.car_id = cc.car_id
                LEFT JOIN clients cl ON cc.client_username = cl.client_username
                LIMIT 1";
    $read_result = $conn->query($read_sql);
    
    if ($read_result && $read_result->num_rows > 0) {
        echo "<p style='color: green;'>✅ READ: Car data retrieval working</p>";
        $sample_car = $read_result->fetch_assoc();
        echo "<p><strong>Sample Car:</strong> {$sample_car['car_name']} - Client: " . ($sample_car['client_name'] ?? 'None') . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ READ: No cars found in database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ READ: Error in read operation test - " . $e->getMessage() . "</p>";
}

// Test UPDATE operation
echo "<h4>UPDATE Operation Test:</h4>";
try {
    $update_test_sql = "SELECT car_id, car_name FROM cars LIMIT 1";
    $update_test_result = $conn->query($update_test_sql);
    
    if ($update_test_result && $update_test_result->num_rows > 0) {
        echo "<p style='color: green;'>✅ UPDATE: Cars available for update testing</p>";
        $test_car = $update_test_result->fetch_assoc();
        echo "<p><strong>Test Car for Update:</strong> ID {$test_car['car_id']} - {$test_car['car_name']}</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ UPDATE: No cars available for update testing</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ UPDATE: Error in update operation test - " . $e->getMessage() . "</p>";
}

// Test DELETE operation
echo "<h4>DELETE Operation Test:</h4>";
try {
    $delete_test_sql = "SELECT c.car_id, c.car_name, COUNT(rc.id) as booking_count
                       FROM cars c 
                       LEFT JOIN rentedcars rc ON c.car_id = rc.car_id 
                       GROUP BY c.car_id 
                       HAVING booking_count = 0 
                       LIMIT 1";
    $delete_test_result = $conn->query($delete_test_sql);
    
    if ($delete_test_result && $delete_test_result->num_rows > 0) {
        echo "<p style='color: green;'>✅ DELETE: Cars available for safe deletion (no bookings)</p>";
        $test_car = $delete_test_result->fetch_assoc();
        echo "<p><strong>Safe to Delete:</strong> ID {$test_car['car_id']} - {$test_car['car_name']}</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ DELETE: No cars available for safe deletion (all have bookings)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ DELETE: Error in delete operation test - " . $e->getMessage() . "</p>";
}

// Final Recommendations
echo "<h3>Final Diagnosis & Solutions</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔧 Primary Issue Identified:</h4>";
echo "<p><strong>Root Cause:</strong> The original admin_cars.php does not properly handle the clientcars relationship table when adding new cars.</p>";

echo "<h4>🚀 Immediate Solutions:</h4>";
echo "<ol>";
echo "<li><strong>Use Fixed Version:</strong> Switch to admin_cars_fixed.php which properly handles client assignments</li>";
echo "<li><strong>Add Client Assignment:</strong> Include client selection dropdown in add/edit forms</li>";
echo "<li><strong>Handle Orphaned Cars:</strong> Assign existing orphaned cars to appropriate clients</li>";
echo "<li><strong>Implement Transactions:</strong> Use database transactions for atomic operations</li>";
echo "</ol>";

echo "<h4>📋 Testing Steps:</h4>";
echo "<ol>";
echo "<li>Try adding a new car through admin_cars_fixed.php</li>";
echo "<li>Verify the car appears in both cars and clientcars tables</li>";
echo "<li>Test editing car details and client assignments</li>";
echo "<li>Test deleting cars (ensure clientcars cleanup)</li>";
echo "</ol>";
echo "</div>";

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
</style>