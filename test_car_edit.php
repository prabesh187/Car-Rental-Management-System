<?php
session_start();
require 'connection.php';

// Set admin session for testing
$_SESSION['login_admin'] = 'admin';

$conn = Connect();

echo "<h2>Car Update Functionality Test</h2>";

// Test 1: Check if cars table exists and has data
echo "<h3>1. Cars Table Check</h3>";
$cars_sql = "SELECT * FROM cars LIMIT 5";
$cars_result = $conn->query($cars_sql);

if ($cars_result && $cars_result->num_rows > 0) {
    echo "<p style='color: green;'>✓ Cars table exists with " . $cars_result->num_rows . " cars found</p>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Car ID</th><th>Car Name</th><th>Nameplate</th><th>AC Price</th><th>Non-AC Price</th><th>Availability</th></tr>";
    
    while ($car = $cars_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$car['car_id']}</td>";
        echo "<td>{$car['car_name']}</td>";
        echo "<td>{$car['car_nameplate']}</td>";
        echo "<td>{$car['ac_price']}</td>";
        echo "<td>{$car['non_ac_price']}</td>";
        echo "<td>{$car['car_availability']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ No cars found in database</p>";
}

// Test 2: Test update query structure
echo "<h3>2. Update Query Test</h3>";
if ($cars_result && $cars_result->num_rows > 0) {
    $cars_result->data_seek(0); // Reset pointer
    $test_car = $cars_result->fetch_assoc();
    $car_id = $test_car['car_id'];
    
    echo "<p>Testing update on Car ID: {$car_id}</p>";
    
    // Test the exact update query from admin_cars.php
    $test_name = "Test Car Update";
    $test_plate = "TEST123";
    $test_img = $test_car['car_img'];
    $test_ac_price = 15.50;
    $test_non_ac_price = 12.50;
    $test_ac_price_day = 1500.00;
    $test_non_ac_price_day = 1200.00;
    $test_availability = "yes";
    
    $sql = "UPDATE cars SET car_name=?, car_nameplate=?, car_img=?, ac_price=?, non_ac_price=?, ac_price_per_day=?, non_ac_price_per_day=?, car_availability=? WHERE car_id=?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssddddsi", $test_name, $test_plate, $test_img, $test_ac_price, $test_non_ac_price, $test_ac_price_day, $test_non_ac_price_day, $test_availability, $car_id);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Update query executed successfully</p>";
            
            // Verify the update
            $verify_sql = "SELECT * FROM cars WHERE car_id = ?";
            $verify_stmt = $conn->prepare($verify_sql);
            $verify_stmt->bind_param("i", $car_id);
            $verify_stmt->execute();
            $updated_car = $verify_stmt->get_result()->fetch_assoc();
            
            echo "<p><strong>Updated car details:</strong></p>";
            echo "<ul>";
            echo "<li>Name: {$updated_car['car_name']}</li>";
            echo "<li>Nameplate: {$updated_car['car_nameplate']}</li>";
            echo "<li>AC Price: {$updated_car['ac_price']}</li>";
            echo "<li>Non-AC Price: {$updated_car['non_ac_price']}</li>";
            echo "<li>AC Price/Day: {$updated_car['ac_price_per_day']}</li>";
            echo "<li>Non-AC Price/Day: {$updated_car['non_ac_price_per_day']}</li>";
            echo "<li>Availability: {$updated_car['car_availability']}</li>";
            echo "</ul>";
            
            // Restore original values
            $restore_sql = "UPDATE cars SET car_name=?, car_nameplate=? WHERE car_id=?";
            $restore_stmt = $conn->prepare($restore_sql);
            $restore_stmt->bind_param("ssi", $test_car['car_name'], $test_car['car_nameplate'], $car_id);
            $restore_stmt->execute();
            echo "<p style='color: blue;'>ℹ Original values restored</p>";
            
        } else {
            echo "<p style='color: red;'>✗ Update query failed: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Failed to prepare update statement: " . $conn->error . "</p>";
    }
}

// Test 3: Check admin_cars.php form structure
echo "<h3>3. Admin Cars Form Analysis</h3>";
if (file_exists('admin_cars.php')) {
    $admin_cars_content = file_get_contents('admin_cars.php');
    
    // Check for update form
    if (strpos($admin_cars_content, 'name="update_car"') !== false) {
        echo "<p style='color: green;'>✓ Update form button found</p>";
    } else {
        echo "<p style='color: red;'>✗ Update form button not found</p>";
    }
    
    // Check for form fields
    $required_fields = ['car_name', 'car_nameplate', 'ac_price', 'non_ac_price', 'ac_price_per_day', 'non_ac_price_per_day', 'car_availability'];
    foreach ($required_fields as $field) {
        if (strpos($admin_cars_content, 'name="' . $field . '"') !== false) {
            echo "<p style='color: green;'>✓ Field '{$field}' found</p>";
        } else {
            echo "<p style='color: red;'>✗ Field '{$field}' not found</p>";
        }
    }
    
    // Check for POST handling
    if (strpos($admin_cars_content, 'isset($_POST[\'update_car\'])') !== false) {
        echo "<p style='color: green;'>✓ POST update handler found</p>";
    } else {
        echo "<p style='color: red;'>✗ POST update handler not found</p>";
    }
} else {
    echo "<p style='color: red;'>✗ admin_cars.php file not found</p>";
}

// Test 4: Check for common issues
echo "<h3>4. Common Issues Check</h3>";

// Check session
if (isset($_SESSION['login_admin'])) {
    echo "<p style='color: green;'>✓ Admin session is set</p>";
} else {
    echo "<p style='color: red;'>✗ Admin session not set</p>";
}

// Check database connection
if ($conn && !$conn->connect_error) {
    echo "<p style='color: green;'>✓ Database connection is working</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

// Check for JavaScript errors that might prevent form submission
echo "<h3>5. Recommendations</h3>";
echo "<ul>";
echo "<li>Check browser console for JavaScript errors when updating cars</li>";
echo "<li>Verify that the edit form is properly populated with existing car data</li>";
echo "<li>Ensure the car_id is being passed correctly in the hidden field</li>";
echo "<li>Check if there are any validation errors preventing form submission</li>";
echo "<li>Verify that the form method is POST and action points to the correct file</li>";
echo "</ul>";

$conn->close();
?>