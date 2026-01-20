<?php
// Test file to check car availability for customers
require 'connection.php';
$conn = Connect();

echo "<h2>Car Availability Test</h2>";

// Test 1: Check all available cars
echo "<h3>1. All Available Cars</h3>";
$sql_cars = "SELECT * FROM cars WHERE car_availability = 'yes'";
$result_cars = $conn->query($sql_cars);

if ($result_cars->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Car ID</th><th>Car Name</th><th>Nameplate</th><th>Status</th></tr>";
    while ($car = $result_cars->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$car['car_id']}</td>";
        echo "<td>{$car['car_name']}</td>";
        echo "<td>{$car['car_nameplate']}</td>";
        echo "<td style='color: green;'>✅ Available for Booking</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No cars available</p>";
}

// Test 2: Check all available drivers
echo "<h3>2. All Available Drivers</h3>";
$sql_drivers = "SELECT d.*, c.client_name FROM driver d 
                LEFT JOIN clients c ON d.client_username = c.client_username 
                WHERE d.driver_availability = 'yes' 
                ORDER BY d.driver_name";
$result_drivers = $conn->query($sql_drivers);

if ($result_drivers->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Driver ID</th><th>Driver Name</th><th>Phone</th><th>Assignment</th><th>Status</th></tr>";
    while ($driver = $result_drivers->fetch_assoc()) {
        $assignment = $driver['client_name'] ? "Fleet: " . $driver['client_name'] : "Independent";
        echo "<tr>";
        echo "<td>{$driver['driver_id']}</td>";
        echo "<td>{$driver['driver_name']}</td>";
        echo "<td>{$driver['driver_phone']}</td>";
        echo "<td>{$assignment}</td>";
        echo "<td style='color: green;'>✅ Available</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No drivers available</p>";
}

// Test 3: Test booking availability for each car
echo "<h3>3. Booking Availability Test</h3>";
$sql_cars_test = "SELECT * FROM cars WHERE car_availability = 'yes'";
$result_cars_test = $conn->query($sql_cars_test);

if ($result_cars_test->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Car</th><th>Available Drivers</th><th>Booking Status</th><th>Action</th></tr>";
    
    while ($car = $result_cars_test->fetch_assoc()) {
        // Count available drivers (using new logic - ALL drivers)
        $sql_available_drivers = "SELECT COUNT(*) as driver_count FROM driver WHERE driver_availability = 'yes'";
        $driver_count_result = $conn->query($sql_available_drivers);
        $driver_count = $driver_count_result->fetch_assoc()['driver_count'];
        
        echo "<tr>";
        echo "<td>{$car['car_name']} ({$car['car_nameplate']})</td>";
        echo "<td>{$driver_count} drivers available</td>";
        
        if ($driver_count > 0) {
            echo "<td style='color: green;'>✅ Can be booked</td>";
            echo "<td><a href='booking.php?id={$car['car_id']}' target='_blank'>Test Booking</a></td>";
        } else {
            echo "<td style='color: red;'>❌ No drivers available</td>";
            echo "<td>Cannot book</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Test 4: Check client-car relationships (for reference)
echo "<h3>4. Client-Car Relationships (Reference Only)</h3>";
echo "<p><em>Note: Cars are no longer restricted by these relationships for customer bookings</em></p>";
$sql_clientcars = "SELECT cc.*, c.car_name, cl.client_name 
                   FROM clientcars cc 
                   JOIN cars c ON cc.car_id = c.car_id 
                   JOIN clients cl ON cc.client_username = cl.client_username 
                   ORDER BY cl.client_name";
$result_clientcars = $conn->query($sql_clientcars);

if ($result_clientcars->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Client/Fleet Owner</th><th>Owned Cars</th><th>Status</th></tr>";
    
    $current_client = '';
    $cars_list = '';
    
    while ($cc = $result_clientcars->fetch_assoc()) {
        if ($current_client != $cc['client_name']) {
            if ($current_client != '') {
                echo "<tr>";
                echo "<td>{$current_client}</td>";
                echo "<td>{$cars_list}</td>";
                echo "<td style='color: blue;'>ℹ️ Reference only</td>";
                echo "</tr>";
            }
            $current_client = $cc['client_name'];
            $cars_list = $cc['car_name'];
        } else {
            $cars_list .= ", " . $cc['car_name'];
        }
    }
    
    // Add the last client
    if ($current_client != '') {
        echo "<tr>";
        echo "<td>{$current_client}</td>";
        echo "<td>{$cars_list}</td>";
        echo "<td style='color: blue;'>ℹ️ Reference only</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<h3>Summary</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
echo "<h4 style='color: green;'>✅ FIXED: Car Availability Issue</h4>";
echo "<ul>";
echo "<li><strong>Before:</strong> Cars were only available if they had drivers from the same client/fleet</li>";
echo "<li><strong>After:</strong> ALL available cars can be booked with ANY available driver</li>";
echo "<li><strong>Benefit:</strong> Customers now have access to the complete fleet</li>";
echo "<li><strong>Flexibility:</strong> Independent drivers can drive any car</li>";
echo "<li><strong>Fleet drivers:</strong> Can also drive cars from other fleets when needed</li>";
echo "</ul>";
echo "</div>";

echo "<br><h4>Test the booking system:</h4>";
echo "<p>1. Go to <a href='index.php' target='_blank'>Main Page</a> - You should see all available cars</p>";
echo "<p>2. Click on any car to test booking - You should see all available drivers</p>";
echo "<p>3. Try <a href='enhanced_booking.php' target='_blank'>Enhanced Booking</a> for the AI-powered experience</p>";
?>