<?php
// Test file to check booking system functionality
require 'connection.php';
$conn = Connect();

echo "<h2>Car Rental Booking System Test</h2>";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test</h3>";
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
    die();
}

// Test 2: Check if cars table exists and has data
echo "<h3>2. Cars Table Test</h3>";
$sql_cars = "SELECT COUNT(*) as count FROM cars WHERE car_availability = 'yes'";
$result_cars = $conn->query($sql_cars);
if ($result_cars) {
    $row = $result_cars->fetch_assoc();
    echo "✅ Available cars: " . $row['count'] . "<br>";
} else {
    echo "❌ Error checking cars table: " . $conn->error . "<br>";
}

// Test 3: Check if drivers table exists and has data
echo "<h3>3. Drivers Table Test</h3>";
$sql_drivers = "SELECT COUNT(*) as count FROM driver WHERE driver_availability = 'yes'";
$result_drivers = $conn->query($sql_drivers);
if ($result_drivers) {
    $row = $result_drivers->fetch_assoc();
    echo "✅ Available drivers: " . $row['count'] . "<br>";
} else {
    echo "❌ Error checking drivers table: " . $conn->error . "<br>";
}

// Test 4: Check if customers table exists
echo "<h3>4. Customers Table Test</h3>";
$sql_customers = "SELECT COUNT(*) as count FROM customers";
$result_customers = $conn->query($sql_customers);
if ($result_customers) {
    $row = $result_customers->fetch_assoc();
    echo "✅ Total customers: " . $row['count'] . "<br>";
} else {
    echo "❌ Error checking customers table: " . $conn->error . "<br>";
}

// Test 5: Check rentedcars table
echo "<h3>5. Rentedcars Table Test</h3>";
$sql_rented = "SELECT COUNT(*) as count FROM rentedcars";
$result_rented = $conn->query($sql_rented);
if ($result_rented) {
    $row = $result_rented->fetch_assoc();
    echo "✅ Total bookings: " . $row['count'] . "<br>";
} else {
    echo "❌ Error checking rentedcars table: " . $conn->error . "<br>";
}

// Test 6: Check for cars with available drivers
echo "<h3>6. Cars with Available Drivers Test</h3>";
$sql_cars_drivers = "SELECT c.car_id, c.car_name, COUNT(d.driver_id) as driver_count 
                     FROM cars c 
                     LEFT JOIN clientcars cc ON c.car_id = cc.car_id 
                     LEFT JOIN driver d ON cc.client_username = d.client_username AND d.driver_availability = 'yes'
                     WHERE c.car_availability = 'yes'
                     GROUP BY c.car_id, c.car_name";
$result_cars_drivers = $conn->query($sql_cars_drivers);
if ($result_cars_drivers) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Car ID</th><th>Car Name</th><th>Available Drivers</th></tr>";
    while ($row = $result_cars_drivers->fetch_assoc()) {
        $status = $row['driver_count'] > 0 ? "✅" : "❌";
        echo "<tr><td>{$row['car_id']}</td><td>{$row['car_name']}</td><td>{$status} {$row['driver_count']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error checking cars with drivers: " . $conn->error . "<br>";
}

echo "<h3>7. Booking System Status</h3>";
echo "If all tests above show ✅, the booking system should work properly.<br>";
echo "If any test shows ❌, that indicates the issue preventing bookings.<br>";
?>