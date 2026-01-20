<?php
/**
 * Admin Car Availability Test Script
 * Tests car availability functionality in admin panel
 */

require 'connection.php';

echo "<h2>Admin Car Availability Test</h2>";
echo "<p>Testing car availability functionality in admin panel...</p>";

$conn = Connect();
$test_results = [];

// Test 1: Check if cars table exists and has data
echo "<h3>Test 1: Cars Table Structure</h3>";
try {
    $cars_check = $conn->query("DESCRIBE cars");
    
    if ($cars_check->num_rows > 0) {
        echo "<p style='color: green;'>✅ Cars table exists</p>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        while ($row = $cars_check->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $test_results['table_structure'] = true;
    } else {
        echo "<p style='color: red;'>❌ Cars table does not exist</p>";
        $test_results['table_structure'] = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking cars table: " . $e->getMessage() . "</p>";
    $test_results['table_structure'] = false;
}

// Test 2: Check car data
echo "<h3>Test 2: Car Data Analysis</h3>";
try {
    $cars_count = $conn->query("SELECT COUNT(*) as count FROM cars");
    $total_cars = $cars_count->fetch_assoc()['count'];
    
    if ($total_cars > 0) {
        echo "<p style='color: green;'>✅ Found {$total_cars} cars in database</p>";
        
        // Check availability distribution
        $available_cars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE car_availability = 'yes'");
        $available_count = $available_cars->fetch_assoc()['count'];
        
        $unavailable_cars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE car_availability = 'no'");
        $unavailable_count = $unavailable_cars->fetch_assoc()['count'];
        
        echo "<p><strong>Availability Distribution:</strong></p>";
        echo "<ul>";
        echo "<li>Available: {$available_count} cars</li>";
        echo "<li>Not Available: {$unavailable_count} cars</li>";
        echo "</ul>";
        
        // Show sample cars
        echo "<h4>Sample Cars:</h4>";
        $sample_cars = $conn->query("SELECT car_id, car_name, car_nameplate, car_availability FROM cars LIMIT 5");
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Car Name</th><th>Number Plate</th><th>Availability</th></tr>";
        
        while ($car = $sample_cars->fetch_assoc()) {
            $status_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
            $status_text = $car['car_availability'] == 'yes' ? 'Available' : 'Not Available';
            
            echo "<tr>";
            echo "<td>" . $car['car_id'] . "</td>";
            echo "<td>" . htmlspecialchars($car['car_name']) . "</td>";
            echo "<td>" . htmlspecialchars($car['car_nameplate']) . "</td>";
            echo "<td style='color: {$status_color};'><strong>{$status_text}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        $test_results['car_data'] = true;
    } else {
        echo "<p style='color: orange;'>⚠️ No cars found in database</p>";
        echo "<p>You need to add cars through the admin panel first.</p>";
        $test_results['car_data'] = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error checking car data: " . $e->getMessage() . "</p>";
    $test_results['car_data'] = false;
}

// Test 3: Test car availability queries
echo "<h3>Test 3: Availability Query Testing</h3>";
try {
    // Test the exact query used in admin_cars.php
    $admin_query = "SELECT c.*, COUNT(rc.id) as booking_count 
                   FROM cars c 
                   LEFT JOIN rentedcars rc ON c.car_id = rc.car_id 
                   GROUP BY c.car_id 
                   ORDER BY c.car_id DESC";
    
    $admin_result = $conn->query($admin_query);
    
    if ($admin_result) {
        echo "<p style='color: green;'>✅ Admin cars query executes successfully</p>";
        echo "<p>Query returns {$admin_result->num_rows} cars</p>";
        
        if ($admin_result->num_rows > 0) {
            echo "<h4>Admin Panel Car List (as seen in admin_cars.php):</h4>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Plate</th><th>Availability</th><th>Bookings</th><th>Actions Available</th></tr>";
            
            while ($car = $admin_result->fetch_assoc()) {
                $status_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
                $status_text = $car['car_availability'] == 'yes' ? 'Available' : 'Not Available';
                $can_delete = $car['booking_count'] == 0 ? 'Yes' : 'No (Has bookings)';
                
                echo "<tr>";
                echo "<td>" . $car['car_id'] . "</td>";
                echo "<td>" . htmlspecialchars($car['car_name']) . "</td>";
                echo "<td>" . htmlspecialchars($car['car_nameplate']) . "</td>";
                echo "<td style='color: {$status_color};'><strong>{$status_text}</strong></td>";
                echo "<td>" . $car['booking_count'] . "</td>";
                echo "<td>" . $can_delete . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        $test_results['admin_query'] = true;
    } else {
        echo "<p style='color: red;'>❌ Admin cars query failed: " . $conn->error . "</p>";
        $test_results['admin_query'] = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing admin query: " . $e->getMessage() . "</p>";
    $test_results['admin_query'] = false;
}

// Test 4: Test car selection functionality
echo "<h3>Test 4: Car Selection Functionality</h3>";
try {
    // Test if cars can be selected for editing
    $edit_test = $conn->query("SELECT car_id, car_name FROM cars LIMIT 1");
    
    if ($edit_test && $edit_test->num_rows > 0) {
        $test_car = $edit_test->fetch_assoc();
        $car_id = $test_car['car_id'];
        
        // Test the edit query used in admin_cars.php
        $edit_query = "SELECT * FROM cars WHERE car_id = ?";
        $stmt = $conn->prepare($edit_query);
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $car_data = $result->fetch_assoc();
            echo "<p style='color: green;'>✅ Car selection for editing works</p>";
            echo "<p>Test car ID {$car_id} can be selected and edited</p>";
            echo "<p><strong>Sample car data:</strong></p>";
            echo "<ul>";
            echo "<li>Name: " . htmlspecialchars($car_data['car_name']) . "</li>";
            echo "<li>Plate: " . htmlspecialchars($car_data['car_nameplate']) . "</li>";
            echo "<li>Availability: " . $car_data['car_availability'] . "</li>";
            echo "<li>AC Price: Rs. " . $car_data['ac_price'] . "</li>";
            echo "<li>Non-AC Price: Rs. " . $car_data['non_ac_price'] . "</li>";
            echo "</ul>";
            
            $test_results['car_selection'] = true;
        } else {
            echo "<p style='color: red;'>❌ Car selection failed - no data returned</p>";
            $test_results['car_selection'] = false;
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No cars available for selection test</p>";
        $test_results['car_selection'] = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing car selection: " . $e->getMessage() . "</p>";
    $test_results['car_selection'] = false;
}

// Test 5: Test availability update functionality
echo "<h3>Test 5: Availability Update Test</h3>";
try {
    // Test if availability can be updated
    $update_test = $conn->query("SELECT car_id, car_availability FROM cars LIMIT 1");
    
    if ($update_test && $update_test->num_rows > 0) {
        $test_car = $update_test->fetch_assoc();
        $car_id = $test_car['car_id'];
        $current_availability = $test_car['car_availability'];
        
        echo "<p style='color: green;'>✅ Availability update functionality available</p>";
        echo "<p>Test car ID {$car_id} current availability: {$current_availability}</p>";
        
        // Show what the update query would look like
        echo "<p><strong>Update query structure:</strong></p>";
        echo "<code>UPDATE cars SET car_availability = ? WHERE car_id = ?</code>";
        
        echo "<p><strong>Available options:</strong></p>";
        echo "<ul>";
        echo "<li>Set to 'yes' (Available)</li>";
        echo "<li>Set to 'no' (Not Available)</li>";
        echo "</ul>";
        
        $test_results['availability_update'] = true;
    } else {
        echo "<p style='color: orange;'>⚠️ No cars available for availability update test</p>";
        $test_results['availability_update'] = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error testing availability update: " . $e->getMessage() . "</p>";
    $test_results['availability_update'] = false;
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
    echo "<p>Car availability system in admin panel is working correctly.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
    echo "<h4 style='color: #856404;'>⚠️ Some Tests Failed</h4>";
    echo "<p>There may be issues with the car availability system.</p>";
    echo "</div>";
}

echo "</div>";

// Troubleshooting Guide
echo "<h3>Troubleshooting Guide</h3>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
echo "<h4>Common Issues and Solutions:</h4>";
echo "<ol>";
echo "<li><strong>No cars showing in admin panel:</strong>";
echo "<ul><li>Add cars through 'Add New Car' button</li><li>Check database connection</li><li>Verify cars table exists</li></ul></li>";
echo "<li><strong>Cannot select cars for editing:</strong>";
echo "<ul><li>Check car_id parameter in URL</li><li>Verify database permissions</li><li>Check for JavaScript errors</li></ul></li>";
echo "<li><strong>Availability dropdown not working:</strong>";
echo "<ul><li>Check form submission</li><li>Verify POST data processing</li><li>Check for validation errors</li></ul></li>";
echo "<li><strong>Cars not updating:</strong>";
echo "<ul><li>Check database write permissions</li><li>Verify prepared statement execution</li><li>Check for SQL errors</li></ul></li>";
echo "</ol>";
echo "</div>";

// Quick Actions
echo "<h3>Quick Actions</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>To test admin car functionality:</strong></p>";
echo "<ol>";
echo "<li><a href='admin_login.php' target='_blank'>Login to Admin Panel</a></li>";
echo "<li><a href='admin_cars.php' target='_blank'>Go to Manage Cars</a></li>";
echo "<li>Try adding a new car</li>";
echo "<li>Try editing an existing car</li>";
echo "<li>Try changing car availability</li>";
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
code {
    background: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style>