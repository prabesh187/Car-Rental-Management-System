<?php
// Test booking process simulation
session_start();
require 'connection.php';
$conn = Connect();

echo "<h2>Booking Process Simulation Test</h2>";

// Simulate a customer session
$_SESSION['login_customer'] = 'lucas'; // Using existing customer from database

echo "<h3>Testing Booking Process for Customer: " . $_SESSION['login_customer'] . "</h3>";

// Test booking parameters
$test_car_id = 1; // Audi A4
$test_driver_id = 1; // Bruno Den
$test_type = 'ac';
$test_charge_type = 'days';
$test_start_date = date('Y-m-d');
$test_end_date = date('Y-m-d', strtotime('+2 days'));

echo "<h4>Test Parameters:</h4>";
echo "Car ID: $test_car_id<br>";
echo "Driver ID: $test_driver_id<br>";
echo "Type: $test_type<br>";
echo "Charge Type: $test_charge_type<br>";
echo "Start Date: $test_start_date<br>";
echo "End Date: $test_end_date<br><br>";

// Step 1: Check if car is available
echo "<h4>Step 1: Check Car Availability</h4>";
$sql_car_check = "SELECT * FROM cars WHERE car_id = '$test_car_id' AND car_availability = 'yes'";
$result_car_check = $conn->query($sql_car_check);

if (mysqli_num_rows($result_car_check) > 0) {
    echo "✅ Car is available<br>";
    $car_data = $result_car_check->fetch_assoc();
    echo "Car: " . $car_data['car_name'] . " (" . $car_data['car_nameplate'] . ")<br>";
} else {
    echo "❌ Car is not available<br>";
    die();
}

// Step 2: Check if driver is available
echo "<h4>Step 2: Check Driver Availability</h4>";
$sql_driver_check = "SELECT * FROM driver WHERE driver_id = '$test_driver_id' AND driver_availability = 'yes'";
$result_driver_check = $conn->query($sql_driver_check);

if (mysqli_num_rows($result_driver_check) > 0) {
    echo "✅ Driver is available<br>";
    $driver_data = $result_driver_check->fetch_assoc();
    echo "Driver: " . $driver_data['driver_name'] . " (" . $driver_data['driver_phone'] . ")<br>";
} else {
    echo "❌ Driver is not available<br>";
    die();
}

// Step 3: Calculate fare
echo "<h4>Step 3: Calculate Fare</h4>";
if ($test_type == "ac" && $test_charge_type == "days") {
    $fare = $car_data["ac_price_per_day"];
} elseif ($test_type == "ac" && $test_charge_type == "km") {
    $fare = $car_data["ac_price"];
} elseif ($test_type == "non_ac" && $test_charge_type == "days") {
    $fare = $car_data["non_ac_price_per_day"];
} elseif ($test_type == "non_ac" && $test_charge_type == "km") {
    $fare = $car_data["non_ac_price"];
} else {
    $fare = "NA";
}

if ($fare != "NA") {
    echo "✅ Fare calculated: Rs. $fare per $test_charge_type<br>";
} else {
    echo "❌ Could not calculate fare<br>";
    die();
}

// Step 4: Simulate booking insertion (without actually inserting)
echo "<h4>Step 4: Simulate Booking Insertion</h4>";
$customer_username = $_SESSION["login_customer"];
$return_status = "NR";

$sql_insert_test = "INSERT into rentedcars(customer_username,car_id,driver_id,booking_date,rent_start_date,rent_end_date,fare,charge_type,return_status) 
VALUES('$customer_username','$test_car_id','$test_driver_id','" . date("Y-m-d") ."','$test_start_date','$test_end_date','$fare','$test_charge_type','$return_status')";

echo "SQL Query would be:<br>";
echo "<code>$sql_insert_test</code><br><br>";

// Test the query syntax without executing
if ($conn->prepare($sql_insert_test)) {
    echo "✅ SQL query syntax is valid<br>";
} else {
    echo "❌ SQL query syntax error: " . $conn->error . "<br>";
}

echo "<h4>Conclusion:</h4>";
echo "If all steps show ✅, the booking process should work correctly.<br>";
echo "You can now test the actual booking through the web interface.<br>";
?>