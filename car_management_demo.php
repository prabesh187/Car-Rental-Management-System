<?php
/**
 * Car Management Demo
 * Demonstrate how to manage cars in the system
 */

session_start();
require 'connection.php';

echo "<h2>🚗 Car Management Demo</h2>";
echo "<p>Live demonstration of car management capabilities...</p>";

$conn = Connect();
$message = '';

// Handle demo actions
if (isset($_POST['demo_action'])) {
    $action = $_POST['demo_action'];
    
    if ($action == 'add_demo_car') {
        // Add a demo car
        $demo_car_name = "Demo Car " . time();
        $demo_plate = "DEMO" . rand(1000, 9999);
        $demo_ac_price = 15.00;
        $demo_non_ac_price = 12.00;
        $demo_ac_day = 800.00;
        $demo_non_ac_day = 600.00;
        
        $sql = "INSERT INTO cars (car_name, car_nameplate, car_img, ac_price, non_ac_price, ac_price_per_day, non_ac_price_per_day, car_availability) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'yes')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdddd", $demo_car_name, $demo_plate, "assets/img/cars/default.jpg", $demo_ac_price, $demo_non_ac_price, $demo_ac_day, $demo_non_ac_day);
        
        if ($stmt->execute()) {
            $message = "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724;'>✅ Demo car '$demo_car_name' added successfully!</div>";
        } else {
            $message = "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24;'>❌ Failed to add demo car</div>";
        }
    }
    
    if ($action == 'make_all_available') {
        $sql = "UPDATE cars SET car_availability = 'yes'";
        if ($conn->query($sql)) {
            $affected = $conn->affected_rows;
            $message = "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724;'>✅ Made $affected cars available!</div>";
        }
    }
}

echo $message;

// Show current cars
$cars_sql = "SELECT * FROM cars ORDER BY car_id DESC LIMIT 10";
$cars_result = $conn->query($cars_sql);

echo "<h3>📊 Current Cars in System</h3>";

if ($cars_result && $cars_result->num_rows > 0) {
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>Car Name</th><th>Number Plate</th><th>AC Price/km</th><th>Availability</th><th>Actions</th>";
    echo "</tr>";
    
    while ($car = $cars_result->fetch_assoc()) {
        $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
        $availability_text = $car['car_availability'] == 'yes' ? 'Available' : 'Unavailable';
        
        echo "<tr>";
        echo "<td>{$car['car_id']}</td>";
        echo "<td><strong>{$car['car_name']}</strong></td>";
        echo "<td>{$car['car_nameplate']}</td>";
        echo "<td>Rs. " . number_format($car['ac_price'], 2) . "</td>";
        echo "<td style='color: {$availability_color};'>{$availability_text}</td>";
        echo "<td>";
        echo "<a href='admin_cars.php?action=edit&id={$car['car_id']}' target='_blank' style='color: #007bff; margin-right: 10px;'>Edit</a>";
        echo "<a href='admin_cars.php?delete={$car['car_id']}' target='_blank' style='color: #dc3545;'>Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: orange;'>No cars found. Add some cars to get started!</p>";
}

// Demo actions
echo "<h3>🎮 Try Car Management Actions</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Demo Actions:</h4>";

echo "<form method='POST' style='margin: 10px 0;'>";
echo "<input type='hidden' name='demo_action' value='add_demo_car'>";
echo "<button type='submit' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "➕ Add Demo Car";
echo "</button>";
echo "</form>";

echo "<form method='POST' style='margin: 10px 0;'>";
echo "<input type='hidden' name='demo_action' value='make_all_available'>";
echo "<button type='submit' style='background: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
echo "🔄 Make All Cars Available";
echo "</button>";
echo "</form>";

echo "</div>";

// Access links
echo "<h3>🔗 Car Management Access Links</h3>";

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Direct Access to Car Management:</h4>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 15px 0;'>";

echo "<a href='admin_login.php' target='_blank' style='background: #dc3545; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🔑 Admin Login";
echo "</a>";

echo "<a href='admin_cars.php' target='_blank' style='background: #007bff; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🚗 Manage Cars";
echo "</a>";

echo "<a href='admin_cars.php?action=add' target='_blank' style='background: #28a745; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "➕ Add New Car";
echo "</a>";

echo "<a href='admin_dashboard.php' target='_blank' style='background: #6c757d; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "📊 Dashboard";
echo "</a>";

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
h2, h3, h4 { 
    color: #2c3e50; 
}
table {
    background: white;
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