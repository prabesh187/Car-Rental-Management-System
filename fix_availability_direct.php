<?php
/**
 * Direct Fix for Car and Driver Availability
 * Execute SQL queries to make cars and drivers available
 */

require 'connection.php';
$conn = Connect();

echo "<h2>🔧 Direct Fix: Making Cars and Drivers Available</h2>";

// Step 1: Make all cars available (except currently rented ones)
echo "<h3>Step 1: Fixing Car Availability</h3>";

// Get currently rented cars
$rented_cars_query = "SELECT DISTINCT car_id FROM rentedcars WHERE return_status = 'NR'";
$rented_result = $conn->query($rented_cars_query);
$rented_car_ids = [];

if ($rented_result && mysqli_num_rows($rented_result) > 0) {
    while ($row = mysqli_fetch_assoc($rented_result)) {
        $rented_car_ids[] = $row['car_id'];
    }
    echo "<p>Currently rented cars: " . implode(', ', $rented_car_ids) . "</p>";
} else {
    echo "<p>No cars are currently rented.</p>";
}

// Make non-rented cars available
if (count($rented_car_ids) > 0) {
    $rented_ids_str = implode(',', $rented_car_ids);
    $update_cars_sql = "UPDATE cars SET car_availability = 'yes' WHERE car_id NOT IN ($rented_ids_str)";
} else {
    $update_cars_sql = "UPDATE cars SET car_availability = 'yes'";
}

if ($conn->query($update_cars_sql)) {
    echo "<p style='color: green;'>✅ Cars availability updated successfully!</p>";
    echo "<p>Affected rows: " . $conn->affected_rows . "</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating cars: " . $conn->error . "</p>";
}

// Step 2: Make all drivers available (except currently assigned ones)
echo "<h3>Step 2: Fixing Driver Availability</h3>";

// Get currently assigned drivers
$assigned_drivers_query = "SELECT DISTINCT driver_id FROM rentedcars WHERE return_status = 'NR'";
$assigned_result = $conn->query($assigned_drivers_query);
$assigned_driver_ids = [];

if ($assigned_result && mysqli_num_rows($assigned_result) > 0) {
    while ($row = mysqli_fetch_assoc($assigned_result)) {
        $assigned_driver_ids[] = $row['driver_id'];
    }
    echo "<p>Currently assigned drivers: " . implode(', ', $assigned_driver_ids) . "</p>";
} else {
    echo "<p>No drivers are currently assigned.</p>";
}

// Make non-assigned drivers available
if (count($assigned_driver_ids) > 0) {
    $assigned_ids_str = implode(',', $assigned_driver_ids);
    $update_drivers_sql = "UPDATE driver SET driver_availability = 'yes' WHERE driver_id NOT IN ($assigned_ids_str)";
} else {
    $update_drivers_sql = "UPDATE driver SET driver_availability = 'yes'";
}

if ($conn->query($update_drivers_sql)) {
    echo "<p style='color: green;'>✅ Drivers availability updated successfully!</p>";
    echo "<p>Affected rows: " . $conn->affected_rows . "</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating drivers: " . $conn->error . "</p>";
}

// Step 3: Verify the fix
echo "<h3>Step 3: Verification</h3>";

// Count available cars
$available_cars_query = "SELECT COUNT(*) as count FROM cars WHERE car_availability = 'yes'";
$cars_count_result = $conn->query($available_cars_query);
$available_cars = mysqli_fetch_assoc($cars_count_result)['count'];

// Count available drivers
$available_drivers_query = "SELECT COUNT(*) as count FROM driver WHERE driver_availability = 'yes'";
$drivers_count_result = $conn->query($available_drivers_query);
$available_drivers = mysqli_fetch_assoc($drivers_count_result)['count'];

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4 style='color: #155724;'>📊 Current Status:</h4>";
echo "<p style='color: #155724;'><strong>Available Cars:</strong> $available_cars</p>";
echo "<p style='color: #155724;'><strong>Available Drivers:</strong> $available_drivers</p>";

if ($available_cars > 0 && $available_drivers > 0) {
    echo "<h4 style='color: #155724;'>🎉 SUCCESS!</h4>";
    echo "<p style='color: #155724;'>Customers can now book cars! Both cars and drivers are available.</p>";
} else {
    echo "<h4 style='color: #721c24;'>⚠️ Issue Still Exists</h4>";
    echo "<p style='color: #721c24;'>Need both cars and drivers to be available for bookings.</p>";
}
echo "</div>";

// Step 4: Show available cars and drivers
if ($available_cars > 0) {
    echo "<h4>Available Cars:</h4>";
    $show_cars_query = "SELECT car_id, car_name, car_nameplate FROM cars WHERE car_availability = 'yes' LIMIT 5";
    $show_cars_result = $conn->query($show_cars_query);
    
    echo "<ul>";
    while ($car = mysqli_fetch_assoc($show_cars_result)) {
        echo "<li>ID: " . $car['car_id'] . " - " . $car['car_name'] . " (" . $car['car_nameplate'] . ")</li>";
    }
    echo "</ul>";
}

if ($available_drivers > 0) {
    echo "<h4>Available Drivers:</h4>";
    $show_drivers_query = "SELECT driver_id, driver_name, driver_phone FROM driver WHERE driver_availability = 'yes' LIMIT 5";
    $show_drivers_result = $conn->query($show_drivers_query);
    
    echo "<ul>";
    while ($driver = mysqli_fetch_assoc($show_drivers_result)) {
        echo "<li>ID: " . $driver['driver_id'] . " - " . $driver['driver_name'] . " (" . $driver['driver_phone'] . ")</li>";
    }
    echo "</ul>";
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
</style>