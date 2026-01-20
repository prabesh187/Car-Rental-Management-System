<?php
/**
 * Make Cars and Drivers Available for Booking
 * Quick fix to ensure customers can book cars
 */

require 'connection.php';
$conn = Connect();

echo "<h2>🔧 Making Cars and Drivers Available</h2>";

// Step 1: Make all non-rented cars available
$fix_cars_sql = "UPDATE cars c 
                 LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                 SET c.car_availability = 'yes' 
                 WHERE rc.car_id IS NULL";

if ($conn->query($fix_cars_sql)) {
    $affected_cars = $conn->affected_rows;
    echo "<p style='color: green;'>✅ Made $affected_cars cars available</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating cars: " . $conn->error . "</p>";
}

// Step 2: Make all non-assigned drivers available
$fix_drivers_sql = "UPDATE driver d 
                    LEFT JOIN rentedcars rc ON d.driver_id = rc.driver_id AND rc.return_status = 'NR'
                    SET d.driver_availability = 'yes' 
                    WHERE rc.driver_id IS NULL";

if ($conn->query($fix_drivers_sql)) {
    $affected_drivers = $conn->affected_rows;
    echo "<p style='color: green;'>✅ Made $affected_drivers drivers available</p>";
} else {
    echo "<p style='color: red;'>❌ Error updating drivers: " . $conn->error . "</p>";
}

// Step 3: Check current availability
$available_cars = $conn->query("SELECT COUNT(*) as count FROM cars WHERE car_availability = 'yes'")->fetch_assoc()['count'];
$available_drivers = $conn->query("SELECT COUNT(*) as count FROM driver WHERE driver_availability = 'yes'")->fetch_assoc()['count'];

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3 style='color: #155724;'>📊 Current Status:</h3>";
echo "<p style='color: #155724;'><strong>Available Cars:</strong> $available_cars</p>";
echo "<p style='color: #155724;'><strong>Available Drivers:</strong> $available_drivers</p>";

if ($available_cars > 0 && $available_drivers > 0) {
    echo "<h3 style='color: #155724;'>🎉 SUCCESS!</h3>";
    echo "<p style='color: #155724;'>Customers can now book cars! The booking system is ready.</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Customer logs in at <a href='customerlogin.php' target='_blank'>customerlogin.php</a></li>";
    echo "<li>Goes to <a href='index.php' target='_blank'>index.php</a> to browse cars</li>";
    echo "<li>Selects a car and fills the booking form</li>";
    echo "<li>Completes the booking process</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<h3 style='color: #721c24;'>⚠️ Still Issues</h3>";
    echo "<p style='color: #721c24;'>Need both cars and drivers available for bookings to work.</p>";
}
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
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>