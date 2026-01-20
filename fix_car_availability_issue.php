<?php
/**
 * Fix Car Availability Issue
 * Diagnose and fix why customers get "Selected car is not available" error
 */

require 'connection.php';
$conn = Connect();

echo "<h2>🔍 Diagnosing Car Availability Issue</h2>";
echo "<p>Investigating why customers get 'Selected car is not available or does not exist' error...</p>";

// Step 1: Check all cars and their availability status
echo "<h3>Step 1: Current Car Status</h3>";

$all_cars_sql = "SELECT car_id, car_name, car_nameplate, car_availability FROM cars ORDER BY car_id";
$all_cars_result = $conn->query($all_cars_sql);

if ($all_cars_result && mysqli_num_rows($all_cars_result) > 0) {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>All Cars in Database:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px;'>Car ID</th>";
    echo "<th style='padding: 8px;'>Car Name</th>";
    echo "<th style='padding: 8px;'>Number Plate</th>";
    echo "<th style='padding: 8px;'>Availability</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "</tr>";
    
    $available_count = 0;
    $unavailable_count = 0;
    
    while ($car = mysqli_fetch_assoc($all_cars_result)) {
        $status_color = ($car['car_availability'] == 'yes') ? 'green' : 'red';
        $status_text = ($car['car_availability'] == 'yes') ? '✅ Available' : '❌ Not Available';
        
        if ($car['car_availability'] == 'yes') {
            $available_count++;
        } else {
            $unavailable_count++;
        }
        
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $car['car_id'] . "</td>";
        echo "<td style='padding: 8px;'><strong>" . $car['car_name'] . "</strong></td>";
        echo "<td style='padding: 8px;'>" . $car['car_nameplate'] . "</td>";
        echo "<td style='padding: 8px;'>" . $car['car_availability'] . "</td>";
        echo "<td style='padding: 8px; color: $status_color;'>$status_text</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Summary:</strong> $available_count available, $unavailable_count unavailable</p>";
    echo "</div>";
    
    if ($available_count == 0) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4 style='color: #721c24;'>🚨 PROBLEM IDENTIFIED!</h4>";
        echo "<p style='color: #721c24;'>No cars are marked as available. This is why customers get the error.</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>❌ No cars found in database!</p>";
}

// Step 2: Check for currently rented cars
echo "<h3>Step 2: Currently Rented Cars</h3>";

$rented_cars_sql = "SELECT DISTINCT rc.car_id, c.car_name, c.car_nameplate 
                    FROM rentedcars rc 
                    JOIN cars c ON rc.car_id = c.car_id 
                    WHERE rc.return_status = 'NR'";
$rented_cars_result = $conn->query($rented_cars_sql);

if ($rented_cars_result && mysqli_num_rows($rented_cars_result) > 0) {
    echo "<p style='color: blue;'>ℹ️ Currently rented cars (should remain unavailable):</p>";
    echo "<ul>";
    while ($rented_car = mysqli_fetch_assoc($rented_cars_result)) {
        echo "<li>ID: " . $rented_car['car_id'] . " - " . $rented_car['car_name'] . " (" . $rented_car['car_nameplate'] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: green;'>✅ No cars are currently rented</p>";
}

// Step 3: Fix car availability
echo "<h3>Step 3: Fixing Car Availability</h3>";

// Make all non-rented cars available
$fix_availability_sql = "UPDATE cars c 
                         LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                         SET c.car_availability = 'yes' 
                         WHERE rc.car_id IS NULL";

if ($conn->query($fix_availability_sql)) {
    $affected_rows = $conn->affected_rows;
    echo "<p style='color: green;'>✅ Fixed availability for $affected_rows cars</p>";
} else {
    echo "<p style='color: red;'>❌ Error fixing car availability: " . $conn->error . "</p>";
}

// Step 4: Verify the fix
echo "<h3>Step 4: Verification After Fix</h3>";

$available_cars_sql = "SELECT COUNT(*) as count FROM cars WHERE car_availability = 'yes'";
$available_cars_result = $conn->query($available_cars_sql);
$available_cars_count = mysqli_fetch_assoc($available_cars_result)['count'];

$total_cars_sql = "SELECT COUNT(*) as count FROM cars";
$total_cars_result = $conn->query($total_cars_sql);
$total_cars_count = mysqli_fetch_assoc($total_cars_result)['count'];

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4 style='color: #155724;'>📊 Current Status After Fix:</h4>";
echo "<p style='color: #155724;'><strong>Total Cars:</strong> $total_cars_count</p>";
echo "<p style='color: #155724;'><strong>Available Cars:</strong> $available_cars_count</p>";
echo "<p style='color: #155724;'><strong>Unavailable Cars:</strong> " . ($total_cars_count - $available_cars_count) . "</p>";

if ($available_cars_count > 0) {
    echo "<h4 style='color: #155724;'>🎉 SUCCESS!</h4>";
    echo "<p style='color: #155724;'>Cars are now available for booking. The error should be resolved!</p>";
} else {
    echo "<h4 style='color: #721c24;'>⚠️ Still No Available Cars</h4>";
    echo "<p style='color: #721c24;'>There might be a deeper issue with the car data.</p>";
}
echo "</div>";

// Step 5: Show available cars for booking
if ($available_cars_count > 0) {
    echo "<h3>Step 5: Cars Now Available for Booking</h3>";
    
    $show_available_sql = "SELECT car_id, car_name, car_nameplate, ac_price, non_ac_price 
                           FROM cars 
                           WHERE car_availability = 'yes' 
                           ORDER BY car_id 
                           LIMIT 10";
    $show_available_result = $conn->query($show_available_sql);
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Available Cars for Customer Booking:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px;'>Car ID</th>";
    echo "<th style='padding: 8px;'>Car Name</th>";
    echo "<th style='padding: 8px;'>Number Plate</th>";
    echo "<th style='padding: 8px;'>AC Price/km</th>";
    echo "<th style='padding: 8px;'>Non-AC Price/km</th>";
    echo "</tr>";
    
    while ($available_car = mysqli_fetch_assoc($show_available_result)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $available_car['car_id'] . "</td>";
        echo "<td style='padding: 8px;'><strong>" . $available_car['car_name'] . "</strong></td>";
        echo "<td style='padding: 8px;'>" . $available_car['car_nameplate'] . "</td>";
        echo "<td style='padding: 8px;'>Rs. " . $available_car['ac_price'] . "</td>";
        echo "<td style='padding: 8px;'>Rs. " . $available_car['non_ac_price'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

// Step 6: Test booking query
echo "<h3>Step 6: Test Booking Query</h3>";

if ($available_cars_count > 0) {
    // Get first available car for testing
    $test_car_sql = "SELECT car_id FROM cars WHERE car_availability = 'yes' LIMIT 1";
    $test_car_result = $conn->query($test_car_sql);
    $test_car = mysqli_fetch_assoc($test_car_result);
    $test_car_id = $test_car['car_id'];
    
    // Test the exact query from bookingconfirm.php
    $test_query = "SELECT * FROM cars WHERE car_id = '$test_car_id' AND car_availability = 'yes'";
    $test_result = $conn->query($test_query);
    
    if ($test_result && mysqli_num_rows($test_result) > 0) {
        echo "<p style='color: green;'>✅ Test booking query works for car ID: $test_car_id</p>";
        echo "<p style='color: green;'>The booking error should now be resolved!</p>";
    } else {
        echo "<p style='color: red;'>❌ Test booking query still fails</p>";
        echo "<p>Query: $test_query</p>";
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

// Step 7: Instructions for customers
echo "<h3>Step 7: Customer Booking Instructions</h3>";

echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4 style='color: #0c5460;'>📋 How to Book Cars Now:</h4>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h5>For Customers:</h5>";
echo "<ol>";
echo "<li>Login at <a href='customerlogin.php' target='_blank'>customerlogin.php</a></li>";
echo "<li>Go to <a href='index.php' target='_blank'>index.php</a></li>";
echo "<li>Select any available car</li>";
echo "<li>Fill the booking form completely</li>";
echo "<li>Submit the booking</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h5>For Testing:</h5>";
echo "<p>You can test with any of the available car IDs shown above.</p>";
echo "<p>The error 'Selected car is not available or does not exist' should no longer appear.</p>";
echo "</div>";

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
h2, h3, h4, h5 { 
    color: #2c3e50; 
}
table {
    font-size: 14px;
}
th {
    background-color: #6c757d !important;
    color: white !important;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>