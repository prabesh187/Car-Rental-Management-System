<?php
/**
 * Fix Booking Availability Issues
 * Make cars and drivers available for booking
 */

require 'connection.php';
$conn = Connect();

echo "<h2>🔧 Fix Booking Availability Issues</h2>";
echo "<p>Making cars and drivers available for customer bookings...</p>";

// Fix 1: Make all non-rented cars available
echo "<h3>Fix 1: Making Cars Available</h3>";

// First, check which cars are currently rented (should remain unavailable)
$rented_cars_sql = "SELECT DISTINCT car_id FROM rentedcars WHERE return_status = 'NR'";
$rented_cars_result = $conn->query($rented_cars_sql);
$rented_car_ids = [];

if ($rented_cars_result && mysqli_num_rows($rented_cars_result) > 0) {
    while ($row = mysqli_fetch_assoc($rented_cars_result)) {
        $rented_car_ids[] = $row['car_id'];
    }
}

echo "<p style='color: blue;'>ℹ️ Currently rented cars: " . count($rented_car_ids) . "</p>";

// Make all non-rented cars available
if (count($rented_car_ids) > 0) {
    $rented_ids_str = implode(',', $rented_car_ids);
    $make_cars_available_sql = "UPDATE cars SET car_availability = 'yes' WHERE car_id NOT IN ($rented_ids_str)";
} else {
    $make_cars_available_sql = "UPDATE cars SET car_availability = 'yes'";
}

$cars_result = $conn->query($make_cars_available_sql);

if ($cars_result) {
    $affected_cars = $conn->affected_rows;
    echo "<p style='color: green;'>✅ Made $affected_cars cars available for booking</p>";
} else {
    echo "<p style='color: red;'>❌ Error making cars available: " . $conn->error . "</p>";
}

// Fix 2: Make all non-assigned drivers available
echo "<h3>Fix 2: Making Drivers Available</h3>";

// First, check which drivers are currently assigned to active rentals
$assigned_drivers_sql = "SELECT DISTINCT driver_id FROM rentedcars WHERE return_status = 'NR'";
$assigned_drivers_result = $conn->query($assigned_drivers_sql);
$assigned_driver_ids = [];

if ($assigned_drivers_result && mysqli_num_rows($assigned_drivers_result) > 0) {
    while ($row = mysqli_fetch_assoc($assigned_drivers_result)) {
        $assigned_driver_ids[] = $row['driver_id'];
    }
}

echo "<p style='color: blue;'>ℹ️ Currently assigned drivers: " . count($assigned_driver_ids) . "</p>";

// Make all non-assigned drivers available
if (count($assigned_driver_ids) > 0) {
    $assigned_ids_str = implode(',', $assigned_driver_ids);
    $make_drivers_available_sql = "UPDATE driver SET driver_availability = 'yes' WHERE driver_id NOT IN ($assigned_ids_str)";
} else {
    $make_drivers_available_sql = "UPDATE driver SET driver_availability = 'yes'";
}

$drivers_result = $conn->query($make_drivers_available_sql);

if ($drivers_result) {
    $affected_drivers = $conn->affected_rows;
    echo "<p style='color: green;'>✅ Made $affected_drivers drivers available for booking</p>";
} else {
    echo "<p style='color: red;'>❌ Error making drivers available: " . $conn->error . "</p>";
}

// Fix 3: Verify availability after fixes
echo "<h3>Fix 3: Verification After Fixes</h3>";

// Check available cars
$available_cars_sql = "SELECT COUNT(*) as count FROM cars WHERE car_availability = 'yes'";
$available_cars_result = $conn->query($available_cars_sql);
$available_cars_count = mysqli_fetch_assoc($available_cars_result)['count'];

echo "<p style='color: green;'>✅ Available cars now: $available_cars_count</p>";

// Check available drivers
$available_drivers_sql = "SELECT COUNT(*) as count FROM driver WHERE driver_availability = 'yes'";
$available_drivers_result = $conn->query($available_drivers_sql);
$available_drivers_count = mysqli_fetch_assoc($available_drivers_result)['count'];

echo "<p style='color: green;'>✅ Available drivers now: $available_drivers_count</p>";

// Fix 4: Test booking capability
echo "<h3>Fix 4: Test Booking Capability</h3>";

if ($available_cars_count > 0 && $available_drivers_count > 0) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h4 style='color: #155724;'>🎉 BOOKING SYSTEM READY!</h4>";
    echo "<p style='color: #155724;'>Cars and drivers are now available for customer bookings.</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h5>📋 Next Steps for Customers:</h5>";
    echo "<ol>";
    echo "<li>Login as customer at <a href='customerlogin.php' target='_blank'>customerlogin.php</a></li>";
    echo "<li>Go to main page <a href='index.php' target='_blank'>index.php</a></li>";
    echo "<li>Select a car to book</li>";
    echo "<li>Fill out the booking form</li>";
    echo "<li>Complete the booking process</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #dc3545;'>";
    echo "<h4 style='color: #721c24;'>⚠️ STILL ISSUES REMAINING</h4>";
    echo "<p style='color: #721c24;'>Available cars: $available_cars_count | Available drivers: $available_drivers_count</p>";
    echo "<p>You need both cars and drivers to be available for bookings to work.</p>";
    echo "</div>";
}

// Fix 5: Show current availability status
echo "<h3>Fix 5: Current System Status</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>📊 System Status Summary:</h4>";

// Cars status
$cars_status_sql = "SELECT 
                        COUNT(*) as total_cars,
                        SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available_cars,
                        SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable_cars
                    FROM cars";
$cars_status_result = $conn->query($cars_status_sql);
$cars_status = mysqli_fetch_assoc($cars_status_result);

echo "<p><strong>Cars:</strong> " . $cars_status['total_cars'] . " total, " . 
     $cars_status['available_cars'] . " available, " . 
     $cars_status['unavailable_cars'] . " unavailable</p>";

// Drivers status
$drivers_status_sql = "SELECT 
                          COUNT(*) as total_drivers,
                          SUM(CASE WHEN driver_availability = 'yes' THEN 1 ELSE 0 END) as available_drivers,
                          SUM(CASE WHEN driver_availability = 'no' THEN 1 ELSE 0 END) as unavailable_drivers
                       FROM driver";
$drivers_status_result = $conn->query($drivers_status_sql);
$drivers_status = mysqli_fetch_assoc($drivers_status_result);

echo "<p><strong>Drivers:</strong> " . $drivers_status['total_drivers'] . " total, " . 
     $drivers_status['available_drivers'] . " available, " . 
     $drivers_status['unavailable_drivers'] . " unavailable</p>";

// Active bookings
$active_bookings_sql = "SELECT COUNT(*) as active_bookings FROM rentedcars WHERE return_status = 'NR'";
$active_bookings_result = $conn->query($active_bookings_sql);
$active_bookings = mysqli_fetch_assoc($active_bookings_result)['active_bookings'];

echo "<p><strong>Active Bookings:</strong> $active_bookings currently rented</p>";

echo "</div>";

// Fix 6: Create quick access links
echo "<h3>Fix 6: Quick Access Links</h3>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 15px 0;'>";

echo "<a href='debug_booking_issues.php' target='_blank' style='background: #007bff; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🔍 Debug Booking Issues";
echo "</a>";

echo "<a href='customerlogin.php' target='_blank' style='background: #28a745; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "👤 Customer Login";
echo "</a>";

echo "<a href='index.php' target='_blank' style='background: #17a2b8; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🏠 Main Page";
echo "</a>";

echo "<a href='admin_cars.php' target='_blank' style='background: #6c757d; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🚗 Admin Cars";
echo "</a>";

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
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>