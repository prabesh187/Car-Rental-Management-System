<?php
/**
 * Make All Cars Available Script
 * Sets all cars in the system to available status
 */

require 'connection.php';

echo "<h2>Make All Cars Available</h2>";
echo "<p>This script will set all cars in the system to available status.</p>";

$conn = Connect();

try {
    // First, let's check current car availability status
    echo "<h3>Current Car Availability Status</h3>";
    
    $status_sql = "SELECT 
                    COUNT(*) as total_cars,
                    SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available_cars,
                    SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable_cars
                   FROM cars";
    
    $status_result = $conn->query($status_sql);
    $status = $status_result->fetch_assoc();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Before Update:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Cars:</strong> {$status['total_cars']}</li>";
    echo "<li><strong>Available Cars:</strong> {$status['available_cars']}</li>";
    echo "<li><strong>Unavailable Cars:</strong> {$status['unavailable_cars']}</li>";
    echo "</ul>";
    echo "</div>";
    
    // Show cars that are currently unavailable
    if ($status['unavailable_cars'] > 0) {
        echo "<h4>Cars Currently Unavailable:</h4>";
        $unavailable_sql = "SELECT car_id, car_name, car_nameplate, car_availability FROM cars WHERE car_availability = 'no'";
        $unavailable_result = $conn->query($unavailable_sql);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>Car ID</th><th>Car Name</th><th>Number Plate</th><th>Current Status</th></tr>";
        
        while ($car = $unavailable_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$car['car_id']}</td>";
            echo "<td>{$car['car_name']}</td>";
            echo "<td>{$car['car_nameplate']}</td>";
            echo "<td style='color: red;'>{$car['car_availability']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check if any cars are currently rented (should not be made available)
    echo "<h3>Checking for Currently Rented Cars</h3>";
    $rented_sql = "SELECT c.car_id, c.car_name, c.car_nameplate, rc.customer_username, rc.rent_start_date, rc.rent_end_date
                   FROM cars c 
                   JOIN rentedcars rc ON c.car_id = rc.car_id 
                   WHERE rc.return_status = 'NR'";
    
    $rented_result = $conn->query($rented_sql);
    
    if ($rented_result->num_rows > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
        echo "<h4 style='color: #856404;'>⚠️ Warning: Currently Rented Cars Found</h4>";
        echo "<p>The following cars are currently rented and should NOT be made available:</p>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Car ID</th><th>Car Name</th><th>Number Plate</th><th>Customer</th><th>Rental Period</th></tr>";
        
        $rented_car_ids = [];
        while ($car = $rented_result->fetch_assoc()) {
            $rented_car_ids[] = $car['car_id'];
            echo "<tr>";
            echo "<td>{$car['car_id']}</td>";
            echo "<td>{$car['car_name']}</td>";
            echo "<td>{$car['car_nameplate']}</td>";
            echo "<td>{$car['customer_username']}</td>";
            echo "<td>{$car['rent_start_date']} to {$car['rent_end_date']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ No cars are currently rented - safe to make all cars available</p>";
        $rented_car_ids = [];
    }
    
    // Now update all cars to available (except currently rented ones)
    echo "<h3>Making Cars Available</h3>";
    
    if (!empty($rented_car_ids)) {
        // Update only cars that are not currently rented
        $rented_ids_str = implode(',', $rented_car_ids);
        $update_sql = "UPDATE cars SET car_availability = 'yes' WHERE car_id NOT IN ($rented_ids_str)";
        echo "<p>Updating cars (excluding currently rented cars with IDs: " . implode(', ', $rented_car_ids) . ")</p>";
    } else {
        // Update all cars
        $update_sql = "UPDATE cars SET car_availability = 'yes'";
        echo "<p>Updating all cars to available status...</p>";
    }
    
    $update_result = $conn->query($update_sql);
    
    if ($update_result) {
        $affected_rows = $conn->affected_rows;
        echo "<p style='color: green;'>✅ Successfully updated {$affected_rows} cars to available status!</p>";
    } else {
        throw new Exception("Failed to update cars: " . $conn->error);
    }
    
    // Check final status
    echo "<h3>Final Car Availability Status</h3>";
    
    $final_status_result = $conn->query($status_sql);
    $final_status = $final_status_result->fetch_assoc();
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724;'>After Update:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Cars:</strong> {$final_status['total_cars']}</li>";
    echo "<li><strong>Available Cars:</strong> {$final_status['available_cars']}</li>";
    echo "<li><strong>Unavailable Cars:</strong> {$final_status['unavailable_cars']}</li>";
    echo "</ul>";
    
    if ($final_status['unavailable_cars'] == 0) {
        echo "<p style='color: #155724;'><strong>🎉 All cars are now available for booking!</strong></p>";
    } else {
        echo "<p style='color: #856404;'>⚠️ {$final_status['unavailable_cars']} cars remain unavailable (likely currently rented)</p>";
    }
    echo "</div>";
    
    // Show all cars with their current status
    echo "<h3>Complete Car List</h3>";
    $all_cars_sql = "SELECT c.car_id, c.car_name, c.car_nameplate, c.car_availability,
                      CASE WHEN rc.car_id IS NOT NULL THEN 'Currently Rented' ELSE 'Not Rented' END as rental_status
                      FROM cars c 
                      LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                      ORDER BY c.car_id";
    
    $all_cars_result = $conn->query($all_cars_sql);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Car ID</th><th>Car Name</th><th>Number Plate</th><th>Availability</th><th>Rental Status</th></tr>";
    
    while ($car = $all_cars_result->fetch_assoc()) {
        $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
        $rental_color = $car['rental_status'] == 'Currently Rented' ? 'orange' : 'blue';
        
        echo "<tr>";
        echo "<td>{$car['car_id']}</td>";
        echo "<td>{$car['car_name']}</td>";
        echo "<td>{$car['car_nameplate']}</td>";
        echo "<td style='color: {$availability_color}; font-weight: bold;'>{$car['car_availability']}</td>";
        echo "<td style='color: {$rental_color};'>{$car['rental_status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Additional actions
    echo "<h3>Additional Actions</h3>";
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
    echo "<h4>What you can do now:</h4>";
    echo "<ul>";
    echo "<li><a href='index.php' target='_blank'>View Available Cars on Homepage</a></li>";
    echo "<li><a href='admin_cars.php' target='_blank'>Manage Cars in Admin Panel</a></li>";
    echo "<li><a href='booking.php' target='_blank'>Test Car Booking System</a></li>";
    echo "<li><a href='enhanced_booking.php' target='_blank'>Try Enhanced Booking</a></li>";
    echo "</ul>";
    
    echo "<h4>To make specific cars unavailable:</h4>";
    echo "<ol>";
    echo "<li>Go to Admin Panel → Manage Cars</li>";
    echo "<li>Edit the specific car</li>";
    echo "<li>Change availability status to 'Not Available'</li>";
    echo "<li>Save changes</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Failed to update car availability: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
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
p { 
    margin: 5px 0; 
}
ul, ol { 
    margin: 10px 0; 
}
li { 
    margin: 5px 0; 
}
table {
    background: white;
    margin: 10px 0;
}
th, td {
    text-align: left;
    padding: 8px;
}
th {
    background: #e9ecef;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>