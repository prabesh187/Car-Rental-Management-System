<?php
/**
 * Test Driver Booking Display
 * Verify that driver information is shown correctly during booking process
 */

require 'connection.php';
$conn = Connect();

echo "<h2>🔍 Test Driver Booking Display</h2>";
echo "<p>Testing if driver information is properly displayed during booking...</p>";

// Test 1: Check available drivers
echo "<h3>Test 1: Available Drivers Check</h3>";
$sql_drivers = "SELECT d.*, cl.client_name 
                FROM driver d 
                LEFT JOIN clients cl ON d.client_username = cl.client_username 
                WHERE d.driver_availability = 'yes' 
                ORDER BY d.driver_name";
$result_drivers = $conn->query($sql_drivers);

if ($result_drivers && mysqli_num_rows($result_drivers) > 0) {
    echo "<p style='color: green;'>✅ Found " . mysqli_num_rows($result_drivers) . " available drivers</p>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Available Drivers:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px;'>Driver ID</th>";
    echo "<th style='padding: 8px;'>Name</th>";
    echo "<th style='padding: 8px;'>Gender</th>";
    echo "<th style='padding: 8px;'>Phone</th>";
    echo "<th style='padding: 8px;'>License</th>";
    echo "<th style='padding: 8px;'>Client Assignment</th>";
    echo "</tr>";
    
    while ($driver = mysqli_fetch_assoc($result_drivers)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $driver['driver_id'] . "</td>";
        echo "<td style='padding: 8px;'><strong>" . $driver['driver_name'] . "</strong></td>";
        echo "<td style='padding: 8px;'>" . $driver['driver_gender'] . "</td>";
        echo "<td style='padding: 8px;'>" . $driver['driver_phone'] . "</td>";
        echo "<td style='padding: 8px;'>" . $driver['dl_number'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($driver['client_name'] ? $driver['client_name'] : 'Independent') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ No available drivers found</p>";
}

// Test 2: Check recent bookings with driver info
echo "<h3>Test 2: Recent Bookings with Driver Information</h3>";
$sql_bookings = "SELECT rc.*, c.car_name, c.car_nameplate, d.driver_name, d.driver_phone, d.driver_gender, d.dl_number,
                         cl.client_name, cl.client_phone
                  FROM rentedcars rc 
                  JOIN cars c ON rc.car_id = c.car_id 
                  JOIN driver d ON rc.driver_id = d.driver_id
                  LEFT JOIN clients cl ON d.client_username = cl.client_username
                  ORDER BY rc.booking_date DESC 
                  LIMIT 5";
$result_bookings = $conn->query($sql_bookings);

if ($result_bookings && mysqli_num_rows($result_bookings) > 0) {
    echo "<p style='color: green;'>✅ Found " . mysqli_num_rows($result_bookings) . " recent bookings with driver info</p>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Recent Bookings:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px;'>Booking ID</th>";
    echo "<th style='padding: 8px;'>Customer</th>";
    echo "<th style='padding: 8px;'>Car</th>";
    echo "<th style='padding: 8px;'>Driver</th>";
    echo "<th style='padding: 8px;'>Driver Phone</th>";
    echo "<th style='padding: 8px;'>Fleet</th>";
    echo "<th style='padding: 8px;'>Status</th>";
    echo "</tr>";
    
    while ($booking = mysqli_fetch_assoc($result_bookings)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $booking['id'] . "</td>";
        echo "<td style='padding: 8px;'>" . $booking['customer_username'] . "</td>";
        echo "<td style='padding: 8px;'>" . $booking['car_name'] . " (" . $booking['car_nameplate'] . ")</td>";
        echo "<td style='padding: 8px;'><strong>" . $booking['driver_name'] . "</strong><br>";
        echo "<small>" . $booking['driver_gender'] . " | DL: " . $booking['dl_number'] . "</small></td>";
        echo "<td style='padding: 8px;'>" . $booking['driver_phone'] . "</td>";
        echo "<td style='padding: 8px;'>" . ($booking['client_name'] ? $booking['client_name'] : 'Independent') . "</td>";
        echo "<td style='padding: 8px;'>" . ($booking['return_status'] == 'NR' ? 'Active' : 'Returned') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: orange;'>⚠️ No recent bookings found</p>";
}

// Test 3: Simulate booking confirmation query
echo "<h3>Test 3: Booking Confirmation Query Test</h3>";

// Get a sample car and driver for testing
$sample_car_sql = "SELECT car_id FROM cars WHERE car_availability = 'yes' LIMIT 1";
$sample_car_result = $conn->query($sample_car_sql);

$sample_driver_sql = "SELECT driver_id FROM driver WHERE driver_availability = 'yes' LIMIT 1";
$sample_driver_result = $conn->query($sample_driver_sql);

if ($sample_car_result && mysqli_num_rows($sample_car_result) > 0 && 
    $sample_driver_result && mysqli_num_rows($sample_driver_result) > 0) {
    
    $sample_car = mysqli_fetch_assoc($sample_car_result);
    $sample_driver = mysqli_fetch_assoc($sample_driver_result);
    
    $car_id = $sample_car['car_id'];
    $driver_id = $sample_driver['driver_id'];
    
    echo "<p style='color: blue;'>ℹ️ Testing with Car ID: $car_id, Driver ID: $driver_id</p>";
    
    // Test the fixed query from bookingconfirm.php
    $test_sql = "SELECT c.*, d.*, cl.client_name, cl.client_phone
                 FROM cars c, driver d
                 LEFT JOIN clients cl ON d.client_username = cl.client_username
                 WHERE c.car_id = '$car_id' AND d.driver_id = '$driver_id'";
    $test_result = $conn->query($test_sql);
    
    if ($test_result && mysqli_num_rows($test_result) > 0) {
        echo "<p style='color: green;'>✅ Booking confirmation query works correctly</p>";
        
        $test_row = mysqli_fetch_assoc($test_result);
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Sample Booking Confirmation Data:</h4>";
        echo "<p><strong>Car:</strong> " . $test_row['car_name'] . " (" . $test_row['car_nameplate'] . ")</p>";
        echo "<p><strong>Driver:</strong> " . $test_row['driver_name'] . " (" . $test_row['driver_gender'] . ")</p>";
        echo "<p><strong>Driver Phone:</strong> " . $test_row['driver_phone'] . "</p>";
        echo "<p><strong>Driver License:</strong> " . $test_row['dl_number'] . "</p>";
        echo "<p><strong>Fleet:</strong> " . ($test_row['client_name'] ? $test_row['client_name'] . " (" . $test_row['client_phone'] . ")" : "Independent Driver") . "</p>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ Booking confirmation query failed</p>";
        echo "<p>Query: " . $test_sql . "</p>";
        echo "<p>Error: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No available cars or drivers for testing</p>";
}

// Test 4: Check if booking.php driver selection works
echo "<h3>Test 4: Driver Selection in Booking Form</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔧 How to Test Driver Display:</h4>";
echo "<ol>";
echo "<li>Login as a customer</li>";
echo "<li>Go to the main page and select a car to book</li>";
echo "<li>In the booking form, check if drivers appear in the dropdown</li>";
echo "<li>Select a driver and verify their details appear below</li>";
echo "<li>Complete the booking and check if driver info shows in confirmation</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🎯 Expected Behavior:</h4>";
echo "<ul>";
echo "<li>✅ Driver dropdown should show all available drivers</li>";
echo "<li>✅ Driver details should appear when selected (name, gender, phone, license)</li>";
echo "<li>✅ Both independent and fleet drivers should be available</li>";
echo "<li>✅ Booking confirmation should show complete driver information</li>";
echo "<li>✅ My Bookings page should display driver details for all bookings</li>";
echo "</ul>";
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
table {
    font-size: 14px;
}
th {
    background-color: #6c757d !important;
    color: white !important;
}
</style>