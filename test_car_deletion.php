<?php
require 'connection.php';
$conn = Connect();

echo "<h2>Car Deletion Test</h2>";

// Show current cars
echo "<h3>Current Cars:</h3>";
$cars_result = $conn->query("SELECT c.car_id, c.car_name, c.car_nameplate, COUNT(rc.id) as booking_count 
                            FROM cars c 
                            LEFT JOIN rentedcars rc ON c.car_id = rc.car_id 
                            GROUP BY c.car_id");

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Plate</th><th>Bookings</th><th>Can Delete?</th></tr>";

while ($car = $cars_result->fetch_assoc()) {
    $can_delete = $car['booking_count'] == 0 ? "YES" : "NO";
    $style = $car['booking_count'] == 0 ? "color: green;" : "color: red;";
    
    echo "<tr>";
    echo "<td>{$car['car_id']}</td>";
    echo "<td>{$car['car_name']}</td>";
    echo "<td>{$car['car_nameplate']}</td>";
    echo "<td>{$car['booking_count']}</td>";
    echo "<td style='$style'><strong>$can_delete</strong></td>";
    echo "</tr>";
}
echo "</table>";

// Show clientcars relationships
echo "<h3>Client-Car Relationships:</h3>";
$clientcars_result = $conn->query("SELECT cc.car_id, cc.client_username, c.car_name 
                                  FROM clientcars cc 
                                  JOIN cars c ON cc.car_id = c.car_id");

echo "<table border='1'>";
echo "<tr><th>Car ID</th><th>Car Name</th><th>Client</th></tr>";

while ($cc = $clientcars_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$cc['car_id']}</td>";
    echo "<td>{$cc['car_name']}</td>";
    echo "<td>{$cc['client_username']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><a href='admin_cars.php'>← Back to Admin Cars</a>";
?>