<?php
/**
 * Simple Car Manager - No Login Required
 * Basic car management for testing purposes
 */

require 'connection.php';

echo "<h2>🚗 Simple Car Manager</h2>";
echo "<p>Basic car management interface (no login required for testing)</p>";

$conn = Connect();
$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_car'])) {
        $car_name = $conn->real_escape_string($_POST['car_name']);
        $car_nameplate = $conn->real_escape_string($_POST['car_nameplate']);
        $ac_price = floatval($_POST['ac_price']);
        $non_ac_price = floatval($_POST['non_ac_price']);
        $ac_price_per_day = floatval($_POST['ac_price_per_day']);
        $non_ac_price_per_day = floatval($_POST['non_ac_price_per_day']);
        
        $sql = "INSERT INTO cars (car_name, car_nameplate, car_img, ac_price, non_ac_price, ac_price_per_day, non_ac_price_per_day, car_availability) 
                VALUES (?, ?, 'assets/img/cars/default.jpg', ?, ?, ?, ?, 'yes')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdddd", $car_name, $car_nameplate, $ac_price, $non_ac_price, $ac_price_per_day, $non_ac_price_per_day);
        
        if ($stmt->execute()) {
            $message = "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin: 10px 0;'>✅ Car added successfully!</div>";
        } else {
            $message = "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin: 10px 0;'>❌ Error adding car: " . $conn->error . "</div>";
        }
    }
    
    if (isset($_POST['delete_car'])) {
        $car_id = intval($_POST['car_id']);
        $sql = "DELETE FROM cars WHERE car_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $car_id);
        
        if ($stmt->execute()) {
            $message = "<div style='background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin: 10px 0;'>✅ Car deleted successfully!</div>";
        } else {
            $message = "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin: 10px 0;'>❌ Error deleting car</div>";
        }
    }
}

echo $message;

// Show current cars
echo "<h3>📋 Current Cars</h3>";
$cars_sql = "SELECT * FROM cars ORDER BY car_id DESC";
$cars_result = $conn->query($cars_sql);

if ($cars_result && $cars_result->num_rows > 0) {
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>Car Name</th><th>Number Plate</th><th>AC Price/km</th><th>Non-AC Price/km</th><th>Availability</th><th>Actions</th>";
    echo "</tr>";
    
    while ($car = $cars_result->fetch_assoc()) {
        $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
        $availability_text = $car['car_availability'] == 'yes' ? 'Available' : 'Unavailable';
        
        echo "<tr>";
        echo "<td>{$car['car_id']}</td>";
        echo "<td><strong>{$car['car_name']}</strong></td>";
        echo "<td>{$car['car_nameplate']}</td>";
        echo "<td>Rs. " . number_format($car['ac_price'], 2) . "</td>";
        echo "<td>Rs. " . number_format($car['non_ac_price'], 2) . "</td>";
        echo "<td style='color: {$availability_color};'>{$availability_text}</td>";
        echo "<td>";
        echo "<form method='POST' style='display: inline;'>";
        echo "<input type='hidden' name='car_id' value='{$car['car_id']}'>";
        echo "<button type='submit' name='delete_car' style='background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;' onclick='return confirm(\"Delete this car?\")'>Delete</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: orange;'>No cars found. Add some cars below!</p>";
}

// Add car form
echo "<h3>➕ Add New Car</h3>";
echo "<div style='background: white; padding: 20px; border-radius: 5px; margin: 10px 0;'>";
echo "<form method='POST'>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;'>";

echo "<div>";
echo "<label>Car Name:</label><br>";
echo "<input type='text' name='car_name' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</div>";

echo "<div>";
echo "<label>Number Plate:</label><br>";
echo "<input type='text' name='car_nameplate' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</div>";

echo "<div>";
echo "<label>AC Price per km:</label><br>";
echo "<input type='number' step='0.01' name='ac_price' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</div>";

echo "<div>";
echo "<label>Non-AC Price per km:</label><br>";
echo "<input type='number' step='0.01' name='non_ac_price' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</div>";

echo "<div>";
echo "<label>AC Price per day:</label><br>";
echo "<input type='number' step='0.01' name='ac_price_per_day' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</div>";

echo "<div>";
echo "<label>Non-AC Price per day:</label><br>";
echo "<input type='number' step='0.01' name='non_ac_price_per_day' required style='width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;'>";
echo "</div>";

echo "</div>";

echo "<button type='submit' name='add_car' style='background: #28a745; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>";
echo "➕ Add Car";
echo "</button>";

echo "</form>";
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
h2, h3 { 
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
</style>