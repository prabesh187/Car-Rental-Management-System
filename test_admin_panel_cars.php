<?php
/**
 * Test Admin Panel Cars Functionality
 * Verifies that admin panel car management is working properly
 */

require 'connection.php';

echo "<h2>🔧 Admin Panel Cars Test</h2>";
echo "<p>Testing admin panel car management functionality...</p>";

$conn = Connect();

try {
    // Test 1: Check if cars exist
    echo "<h3>Test 1: Car Inventory Check</h3>";
    $cars_sql = "SELECT COUNT(*) as total FROM cars";
    $cars_result = $conn->query($cars_sql);
    $total_cars = $cars_result->fetch_assoc()['total'];
    
    if ($total_cars > 0) {
        echo "<p style='color: green;'>✅ Found {$total_cars} cars in the system</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ No cars found - you need to add cars first</p>";
    }
    
    // Test 2: Check car availability status
    echo "<h3>Test 2: Car Availability Status</h3>";
    $availability_sql = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available,
                            SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable
                         FROM cars";
    $availability_result = $conn->query($availability_sql);
    $availability = $availability_result->fetch_assoc();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Current Availability Status:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Cars:</strong> {$availability['total']}</li>";
    echo "<li><strong>Available Cars:</strong> <span style='color: green;'>{$availability['available']}</span></li>";
    echo "<li><strong>Unavailable Cars:</strong> <span style='color: red;'>{$availability['unavailable']}</span></li>";
    echo "</ul>";
    echo "</div>";
    
    // Test 3: Check admin panel query
    echo "<h3>Test 3: Admin Panel Query Test</h3>";
    $admin_query = "SELECT c.*, cc.client_username, cl.client_name, COUNT(rc.id) as booking_count,
                    CASE WHEN rc_active.car_id IS NOT NULL THEN 'Currently Rented' ELSE 'Available' END as rental_status
                    FROM cars c 
                    LEFT JOIN clientcars cc ON c.car_id = cc.car_id
                    LEFT JOIN clients cl ON cc.client_username = cl.client_username
                    LEFT JOIN rentedcars rc ON c.car_id = rc.car_id 
                    LEFT JOIN rentedcars rc_active ON c.car_id = rc_active.car_id AND rc_active.return_status = 'NR'
                    GROUP BY c.car_id 
                    ORDER BY c.car_id DESC";
    
    $admin_result = $conn->query($admin_query);
    
    if ($admin_result) {
        echo "<p style='color: green;'>✅ Admin panel query executes successfully</p>";
        echo "<p>Query returns {$admin_result->num_rows} cars for admin panel display</p>";
        
        if ($admin_result->num_rows > 0) {
            echo "<h4>Sample Cars (as shown in admin panel):</h4>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>ID</th><th>Car Name</th><th>Number Plate</th><th>Client</th><th>Availability</th><th>Rental Status</th><th>Bookings</th>";
            echo "</tr>";
            
            $count = 0;
            while ($car = $admin_result->fetch_assoc() && $count < 5) {
                $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
                $availability_text = $car['car_availability'] == 'yes' ? '✅ Available' : '❌ Unavailable';
                $client_text = $car['client_name'] ? $car['client_name'] : 'No Client';
                
                echo "<tr>";
                echo "<td>{$car['car_id']}</td>";
                echo "<td>{$car['car_name']}</td>";
                echo "<td>{$car['car_nameplate']}</td>";
                echo "<td>{$client_text}</td>";
                echo "<td style='color: {$availability_color};'>{$availability_text}</td>";
                echo "<td>{$car['rental_status']}</td>";
                echo "<td>{$car['booking_count']}</td>";
                echo "</tr>";
                $count++;
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>❌ Admin panel query failed: " . $conn->error . "</p>";
    }
    
    // Test 4: Check clients for assignment
    echo "<h3>Test 4: Client Assignment Check</h3>";
    $clients_sql = "SELECT COUNT(*) as total FROM clients";
    $clients_result = $conn->query($clients_sql);
    $total_clients = $clients_result->fetch_assoc()['total'];
    
    if ($total_clients > 0) {
        echo "<p style='color: green;'>✅ Found {$total_clients} clients available for car assignment</p>";
        
        // Show sample clients
        $sample_clients_sql = "SELECT client_username, client_name FROM clients LIMIT 3";
        $sample_clients_result = $conn->query($sample_clients_sql);
        
        echo "<p><strong>Sample Clients:</strong></p>";
        echo "<ul>";
        while ($client = $sample_clients_result->fetch_assoc()) {
            echo "<li>{$client['client_name']} ({$client['client_username']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No clients found - cars will be available to all</p>";
    }
    
    // Test 5: Make all cars available
    echo "<h3>Test 5: Make All Cars Available</h3>";
    $make_available_sql = "UPDATE cars c 
                          LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                          SET c.car_availability = 'yes' 
                          WHERE rc.car_id IS NULL";
    
    $make_available_result = $conn->query($make_available_sql);
    
    if ($make_available_result) {
        $affected_rows = $conn->affected_rows;
        echo "<p style='color: green;'>✅ Successfully updated {$affected_rows} cars to available status</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to update car availability: " . $conn->error . "</p>";
    }
    
    // Final status check
    echo "<h3>Final Status Check</h3>";
    $final_availability_result = $conn->query($availability_sql);
    $final_availability = $final_availability_result->fetch_assoc();
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724;'>✅ Final Status:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Cars:</strong> {$final_availability['total']}</li>";
    echo "<li><strong>Available Cars:</strong> <span style='color: green;'>{$final_availability['available']}</span></li>";
    echo "<li><strong>Unavailable Cars:</strong> <span style='color: red;'>{$final_availability['unavailable']}</span></li>";
    echo "</ul>";
    
    if ($final_availability['available'] == $final_availability['total']) {
        echo "<p style='color: #155724;'><strong>🎉 Perfect! All cars are now available in the admin panel!</strong></p>";
    } else {
        echo "<p style='color: #856404;'>⚠️ Some cars are still unavailable (likely currently rented)</p>";
    }
    echo "</div>";
    
    // Quick actions
    echo "<h3>Quick Actions</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>🚀 Admin Panel Actions:</h4>";
    echo "<ul>";
    echo "<li><a href='admin_cars.php' target='_blank' style='color: #007bff;'>✅ Open Admin Car Management</a></li>";
    echo "<li><a href='admin_cars.php?action=add' target='_blank' style='color: #007bff;'>✅ Add New Car</a></li>";
    echo "<li><a href='admin_cars.php?make_all_available=1' target='_blank' style='color: #007bff;'>✅ Make All Cars Available</a></li>";
    echo "<li><a href='admin_dashboard.php' target='_blank' style='color: #007bff;'>✅ Admin Dashboard</a></li>";
    echo "</ul>";
    
    echo "<h4>🔧 If Cars Still Not Showing:</h4>";
    echo "<ol>";
    echo "<li>Clear browser cache and refresh admin_cars.php</li>";
    echo "<li>Check if you're logged in as admin</li>";
    echo "<li>Try adding a new car through the admin panel</li>";
    echo "<li>Verify database connection is working</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Test failed: " . htmlspecialchars($e->getMessage()) . "</p>";
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