<?php
/**
 * Debug Booking Issues
 * Investigate why cars are not being booked by customers
 */

require 'connection.php';
$conn = Connect();

echo "<h2>🔍 Debug Car Booking Issues</h2>";
echo "<p>Investigating why customers cannot book cars...</p>";

// Test 1: Check database connection
echo "<h3>Test 1: Database Connection</h3>";
if ($conn) {
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit();
}

// Test 2: Check available cars
echo "<h3>Test 2: Available Cars Check</h3>";
$cars_sql = "SELECT * FROM cars WHERE car_availability = 'yes'";
$cars_result = $conn->query($cars_sql);

if ($cars_result && mysqli_num_rows($cars_result) > 0) {
    echo "<p style='color: green;'>✅ Found " . mysqli_num_rows($cars_result) . " available cars</p>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Available Cars:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px;'>Car ID</th>";
    echo "<th style='padding: 8px;'>Car Name</th>";
    echo "<th style='padding: 8px;'>Number Plate</th>";
    echo "<th style='padding: 8px;'>Availability</th>";
    echo "<th style='padding: 8px;'>AC Price/km</th>";
    echo "<th style='padding: 8px;'>Non-AC Price/km</th>";
    echo "</tr>";
    
    while ($car = mysqli_fetch_assoc($cars_result)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $car['car_id'] . "</td>";
        echo "<td style='padding: 8px;'><strong>" . $car['car_name'] . "</strong></td>";
        echo "<td style='padding: 8px;'>" . $car['car_nameplate'] . "</td>";
        echo "<td style='padding: 8px;'>" . $car['car_availability'] . "</td>";
        echo "<td style='padding: 8px;'>Rs. " . $car['ac_price'] . "</td>";
        echo "<td style='padding: 8px;'>Rs. " . $car['non_ac_price'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ No available cars found!</p>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #721c24;'>🚨 CRITICAL ISSUE: No Available Cars</h4>";
    echo "<p>This is likely why customers cannot book cars.</p>";
    echo "</div>";
}

// Test 3: Check available drivers
echo "<h3>Test 3: Available Drivers Check</h3>";
$drivers_sql = "SELECT * FROM driver WHERE driver_availability = 'yes'";
$drivers_result = $conn->query($drivers_sql);

if ($drivers_result && mysqli_num_rows($drivers_result) > 0) {
    echo "<p style='color: green;'>✅ Found " . mysqli_num_rows($drivers_result) . " available drivers</p>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Available Drivers:</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='padding: 8px;'>Driver ID</th>";
    echo "<th style='padding: 8px;'>Driver Name</th>";
    echo "<th style='padding: 8px;'>Phone</th>";
    echo "<th style='padding: 8px;'>Availability</th>";
    echo "</tr>";
    
    while ($driver = mysqli_fetch_assoc($drivers_result)) {
        echo "<tr>";
        echo "<td style='padding: 8px;'>" . $driver['driver_id'] . "</td>";
        echo "<td style='padding: 8px;'><strong>" . $driver['driver_name'] . "</strong></td>";
        echo "<td style='padding: 8px;'>" . $driver['driver_phone'] . "</td>";
        echo "<td style='padding: 8px;'>" . $driver['driver_availability'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: red;'>❌ No available drivers found!</p>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #721c24;'>🚨 CRITICAL ISSUE: No Available Drivers</h4>";
    echo "<p>This is likely why customers cannot complete bookings.</p>";
    echo "</div>";
}

// Test 4: Check customer accounts
echo "<h3>Test 4: Customer Accounts Check</h3>";
$customers_sql = "SELECT COUNT(*) as count FROM customers";
$customers_result = $conn->query($customers_sql);
$customer_count = mysqli_fetch_assoc($customers_result)['count'];

echo "<p style='color: blue;'>ℹ️ Total customers in system: $customer_count</p>";

// Test 5: Check recent booking attempts
echo "<h3>Test 5: Recent Booking History</h3>";
$bookings_sql = "SELECT COUNT(*) as count FROM rentedcars WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$bookings_result = $conn->query($bookings_sql);
$recent_bookings = mysqli_fetch_assoc($bookings_result)['count'];

echo "<p style='color: blue;'>ℹ️ Bookings in last 7 days: $recent_bookings</p>";

if ($recent_bookings == 0) {
    echo "<p style='color: orange;'>⚠️ No recent bookings - this confirms the booking issue</p>";
}

// Test 6: Check for common booking errors
echo "<h3>Test 6: Common Booking Issues Check</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔍 Common Booking Problems:</h4>";
echo "<ol>";
echo "<li><strong>No Available Cars:</strong> All cars marked as unavailable</li>";
echo "<li><strong>No Available Drivers:</strong> All drivers marked as unavailable</li>";
echo "<li><strong>Session Issues:</strong> Customer not properly logged in</li>";
echo "<li><strong>Form Validation:</strong> Required fields not being submitted</li>";
echo "<li><strong>Database Errors:</strong> SQL queries failing</li>";
echo "<li><strong>Date Issues:</strong> Invalid date selections</li>";
echo "</ol>";
echo "</div>";

// Test 7: Simulate a booking process
echo "<h3>Test 7: Simulate Booking Process</h3>";

// Get first available car and driver
$test_car_sql = "SELECT * FROM cars WHERE car_availability = 'yes' LIMIT 1";
$test_car_result = $conn->query($test_car_sql);

$test_driver_sql = "SELECT * FROM driver WHERE driver_availability = 'yes' LIMIT 1";
$test_driver_result = $conn->query($test_driver_sql);

if ($test_car_result && mysqli_num_rows($test_car_result) > 0 && 
    $test_driver_result && mysqli_num_rows($test_driver_result) > 0) {
    
    $test_car = mysqli_fetch_assoc($test_car_result);
    $test_driver = mysqli_fetch_assoc($test_driver_result);
    
    echo "<p style='color: green;'>✅ Test booking possible with:</p>";
    echo "<p><strong>Car:</strong> " . $test_car['car_name'] . " (ID: " . $test_car['car_id'] . ")</p>";
    echo "<p><strong>Driver:</strong> " . $test_driver['driver_name'] . " (ID: " . $test_driver['driver_id'] . ")</p>";
    
    // Test the booking query
    $test_customer = 'test_customer';
    $test_start_date = date('Y-m-d');
    $test_end_date = date('Y-m-d', strtotime('+1 day'));
    $test_fare = $test_car['ac_price'];
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🧪 Test Booking Parameters:</h4>";
    echo "<p><strong>Customer:</strong> $test_customer</p>";
    echo "<p><strong>Car ID:</strong> " . $test_car['car_id'] . "</p>";
    echo "<p><strong>Driver ID:</strong> " . $test_driver['driver_id'] . "</p>";
    echo "<p><strong>Start Date:</strong> $test_start_date</p>";
    echo "<p><strong>End Date:</strong> $test_end_date</p>";
    echo "<p><strong>Fare:</strong> Rs. $test_fare</p>";
    echo "</div>";
    
} else {
    echo "<p style='color: red;'>❌ Cannot simulate booking - no available cars or drivers</p>";
}

// Test 8: Solutions
echo "<h3>Test 8: Immediate Solutions</h3>";

echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #0c5460;'>";
echo "<h4 style='color: #0c5460;'>🔧 SOLUTIONS TO FIX BOOKING ISSUES:</h4>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h5>1. Make Cars Available</h5>";
echo "<p>If no cars are available, run this query:</p>";
echo "<code style='background: #f8f9fa; padding: 5px; border-radius: 3px;'>UPDATE cars SET car_availability = 'yes' WHERE car_availability = 'no'</code>";
echo "</div>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h5>2. Make Drivers Available</h5>";
echo "<p>If no drivers are available, run this query:</p>";
echo "<code style='background: #f8f9fa; padding: 5px; border-radius: 3px;'>UPDATE driver SET driver_availability = 'yes' WHERE driver_availability = 'no'</code>";
echo "</div>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h5>3. Check Customer Login</h5>";
echo "<p>Ensure customer is properly logged in before accessing booking page.</p>";
echo "</div>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h5>4. Test Booking Flow</h5>";
echo "<ol>";
echo "<li>Login as customer</li>";
echo "<li>Go to index.php</li>";
echo "<li>Select a car</li>";
echo "<li>Fill booking form completely</li>";
echo "<li>Submit booking</li>";
echo "</ol>";
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
code {
    font-family: 'Courier New', monospace;
    font-size: 14px;
}
</style>