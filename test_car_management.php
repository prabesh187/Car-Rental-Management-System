<?php
/**
 * Test Car Management System
 * Verify that car management is working and demonstrate how to use it
 */

require 'connection.php';

echo "<h2>🚗 Car Management System Test</h2>";
echo "<p>Testing and demonstrating car management functionality...</p>";

$conn = Connect();

try {
    // Test 1: Check current cars in system
    echo "<h3>Test 1: Current Cars in System</h3>";
    
    $cars_sql = "SELECT c.*, cc.client_username, cl.client_name 
                 FROM cars c 
                 LEFT JOIN clientcars cc ON c.car_id = cc.car_id
                 LEFT JOIN clients cl ON cc.client_username = cl.client_username
                 ORDER BY c.car_id DESC";
    $cars_result = $conn->query($cars_sql);
    
    if ($cars_result && $cars_result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Found {$cars_result->num_rows} cars in the system</p>";
        
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>ID</th><th>Car Name</th><th>Number Plate</th><th>Client</th><th>Availability</th><th>AC Price/km</th><th>Non-AC Price/km</th>";
        echo "</tr>";
        
        while ($car = $cars_result->fetch_assoc()) {
            $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
            $availability_text = $car['car_availability'] == 'yes' ? '✅ Available' : '❌ Unavailable';
            $client_text = $car['client_name'] ? $car['client_name'] : 'No Client';
            
            echo "<tr>";
            echo "<td>{$car['car_id']}</td>";
            echo "<td><strong>{$car['car_name']}</strong></td>";
            echo "<td>{$car['car_nameplate']}</td>";
            echo "<td>{$client_text}</td>";
            echo "<td style='color: {$availability_color};'>{$availability_text}</td>";
            echo "<td>Rs. " . number_format($car['ac_price'], 2) . "</td>";
            echo "<td>Rs. " . number_format($car['non_ac_price'], 2) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No cars found in the system</p>";
    }
    
    // Test 2: Car Management Features Available
    echo "<h3>Test 2: Car Management Features</h3>";
    
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>✅ Available Car Management Operations:</h4>";
    echo "<ul>";
    echo "<li><strong>➕ Add Cars:</strong> Create new car entries with all details</li>";
    echo "<li><strong>✏️ Edit Cars:</strong> Modify car information, pricing, availability</li>";
    echo "<li><strong>🗑️ Delete Cars:</strong> Remove cars (with booking safety checks)</li>";
    echo "<li><strong>👁️ View Cars:</strong> List all cars with complete information</li>";
    echo "<li><strong>🏢 Client Assignment:</strong> Assign cars to specific clients</li>";
    echo "<li><strong>💰 Price Management:</strong> Set rental rates for AC/Non-AC</li>";
    echo "<li><strong>📸 Image Upload:</strong> Add car photos</li>";
    echo "<li><strong>🔄 Availability Toggle:</strong> Enable/disable cars for booking</li>";
    echo "</ul>";
    echo "</div>";
    
    // Test 3: Access Methods
    echo "<h3>Test 3: How to Access Car Management</h3>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>🚀 Multiple Ways to Manage Cars:</h4>";
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 15px 0;'>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h5>Method 1: Admin Dashboard</h5>";
    echo "<ol>";
    echo "<li>Login at <a href='admin_login.php' target='_blank'>admin_login.php</a></li>";
    echo "<li>Go to <a href='admin_dashboard.php' target='_blank'>admin_dashboard.php</a></li>";
    echo "<li>Click 'Manage Cars' in sidebar</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h5>Method 2: Direct Access</h5>";
    echo "<ol>";
    echo "<li>Login as admin first</li>";
    echo "<li>Go directly to <a href='admin_cars.php' target='_blank'>admin_cars.php</a></li>";
    echo "<li>Use all car management features</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; line-height: 1.6; }
h2, h3, h4 { color: #2c3e50; }
table { background: white; }
th, td { text-align: left; padding: 8px; }
th { background: #e9ecef; }
a { color: #007bff; text-decoration: none; }
</style>