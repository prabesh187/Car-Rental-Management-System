<?php
session_start();
require 'connection.php';

// Set admin session for testing
$_SESSION['login_admin'] = 'admin';

$conn = Connect();

echo "<h2>Car Update Debug</h2>";

// Check if we have cars to test with
$cars_sql = "SELECT * FROM cars LIMIT 1";
$cars_result = $conn->query($cars_sql);

if ($cars_result && $cars_result->num_rows > 0) {
    $test_car = $cars_result->fetch_assoc();
    echo "<h3>Testing with Car ID: " . $test_car['car_id'] . "</h3>";
    echo "<p>Current car name: " . $test_car['car_name'] . "</p>";
    
    // Simulate the exact POST data that would come from the form
    $_POST['update_car'] = '1';
    $_POST['car_id'] = $test_car['car_id'];
    $_POST['car_name'] = 'Updated Test Car';
    $_POST['car_nameplate'] = 'UPD123';
    $_POST['ac_price'] = '15.50';
    $_POST['non_ac_price'] = '12.50';
    $_POST['ac_price_per_day'] = '1500.00';
    $_POST['non_ac_price_per_day'] = '1200.00';
    $_POST['car_availability'] = 'yes';
    
    echo "<h3>Simulating Update Process:</h3>";
    
    // Execute the same code as in admin_cars.php
    if (isset($_POST['update_car'])) {
        echo "<p>✓ Update POST detected</p>";
        
        $car_id = $_POST['car_id'];
        $car_name = $conn->real_escape_string($_POST['car_name']);
        $car_nameplate = $conn->real_escape_string($_POST['car_nameplate']);
        $ac_price = $conn->real_escape_string($_POST['ac_price']);
        $non_ac_price = $conn->real_escape_string($_POST['non_ac_price']);
        $ac_price_per_day = $conn->real_escape_string($_POST['ac_price_per_day']);
        $non_ac_price_per_day = $conn->real_escape_string($_POST['non_ac_price_per_day']);
        $car_availability = $_POST['car_availability'];
        
        echo "<p>✓ Data extracted from POST</p>";
        
        // Get current car image
        $current_img_sql = "SELECT car_img FROM cars WHERE car_id = ?";
        $current_stmt = $conn->prepare($current_img_sql);
        $current_stmt->bind_param("i", $car_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $current_car = $current_result->fetch_assoc();
        $car_img = $current_car['car_img']; // Keep current image by default
        
        echo "<p>✓ Current image retrieved: " . $car_img . "</p>";
        
        $sql = "UPDATE cars SET car_name=?, car_nameplate=?, car_img=?, ac_price=?, non_ac_price=?, ac_price_per_day=?, non_ac_price_per_day=?, car_availability=? WHERE car_id=?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            echo "<p>✓ Update statement prepared</p>";
            
            $stmt->bind_param("sssddddsi", $car_name, $car_nameplate, $car_img, $ac_price, $non_ac_price, $ac_price_per_day, $non_ac_price_per_day, $car_availability, $car_id);
            
            if ($stmt->execute()) {
                echo "<p style='color: green;'>✓ Car updated successfully!</p>";
                
                // Verify the update
                $verify_sql = "SELECT * FROM cars WHERE car_id = ?";
                $verify_stmt = $conn->prepare($verify_sql);
                $verify_stmt->bind_param("i", $car_id);
                $verify_stmt->execute();
                $updated_car = $verify_stmt->get_result()->fetch_assoc();
                
                echo "<h4>Updated Car Details:</h4>";
                echo "<ul>";
                echo "<li>Name: " . $updated_car['car_name'] . "</li>";
                echo "<li>Nameplate: " . $updated_car['car_nameplate'] . "</li>";
                echo "<li>AC Price: " . $updated_car['ac_price'] . "</li>";
                echo "<li>Non-AC Price: " . $updated_car['non_ac_price'] . "</li>";
                echo "<li>AC Price/Day: " . $updated_car['ac_price_per_day'] . "</li>";
                echo "<li>Non-AC Price/Day: " . $updated_car['non_ac_price_per_day'] . "</li>";
                echo "<li>Availability: " . $updated_car['car_availability'] . "</li>";
                echo "</ul>";
                
                // Restore original values
                $restore_sql = "UPDATE cars SET car_name=?, car_nameplate=? WHERE car_id=?";
                $restore_stmt = $conn->prepare($restore_sql);
                $restore_stmt->bind_param("ssi", $test_car['car_name'], $test_car['car_nameplate'], $car_id);
                if ($restore_stmt->execute()) {
                    echo "<p style='color: blue;'>ℹ Original values restored</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ Could not restore original values</p>";
                }
                
            } else {
                echo "<p style='color: red;'>✗ Update failed: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Could not prepare statement: " . $conn->error . "</p>";
        }
    }
    
} else {
    echo "<p style='color: red;'>No cars found to test with</p>";
}

echo "<h3>Potential Issues to Check:</h3>";
echo "<ul>";
echo "<li><strong>Form Action:</strong> Make sure the form in admin_cars.php has method='POST'</li>";
echo "<li><strong>Hidden Field:</strong> Verify car_id is properly set in the hidden input</li>";
echo "<li><strong>JavaScript Validation:</strong> Check if JS validation is preventing form submission</li>";
echo "<li><strong>Required Fields:</strong> Ensure all required fields are filled</li>";
echo "<li><strong>Price Validation:</strong> Make sure all prices are greater than 0</li>";
echo "<li><strong>Session:</strong> Verify admin is logged in</li>";
echo "<li><strong>File Permissions:</strong> Check if the script has write permissions</li>";
echo "</ul>";

$conn->close();
?>