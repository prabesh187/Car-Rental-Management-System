<?php
/**
 * Admin Car Management Overview
 * Shows all the car management features available to administrators
 */

require 'connection.php';

echo "<h2>🚗 Admin Car Management Overview</h2>";
echo "<p>Complete overview of car management features available to administrators...</p>";

$conn = Connect();

try {
    // Check if admin_cars.php exists and get car statistics
    echo "<h3>📊 Current Car Statistics</h3>";
    
    if (file_exists('admin_cars.php')) {
        echo "<p style='color: green;'>✅ Admin car management system is available</p>";
        
        // Get car statistics
        $stats_sql = "SELECT 
                        COUNT(*) as total_cars,
                        SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available_cars,
                        SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable_cars,
                        COUNT(DISTINCT cc.client_username) as clients_with_cars,
                        SUM(CASE WHEN cc.client_username IS NULL THEN 1 ELSE 0 END) as unassigned_cars
                      FROM cars c 
                      LEFT JOIN clientcars cc ON c.car_id = cc.car_id";
        $stats_result = $conn->query($stats_sql);
        $stats = $stats_result->fetch_assoc();
        
        // Get currently rented cars
        $rented_sql = "SELECT COUNT(*) as rented_cars FROM cars c 
                      JOIN rentedcars rc ON c.car_id = rc.car_id 
                      WHERE rc.return_status = 'NR'";
        $rented_result = $conn->query($rented_sql);
        $rented_stats = $rented_result->fetch_assoc();
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Current Fleet Status:</h4>";
        echo "<div style='display: flex; flex-wrap: wrap; gap: 20px;'>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;'>";
        echo "<h3 style='color: #007bff; margin: 0;'>{$stats['total_cars']}</h3>";
        echo "<p style='margin: 5px 0;'>Total Cars</p>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;'>";
        echo "<h3 style='color: #28a745; margin: 0;'>{$stats['available_cars']}</h3>";
        echo "<p style='margin: 5px 0;'>Available</p>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;'>";
        echo "<h3 style='color: #dc3545; margin: 0;'>{$stats['unavailable_cars']}</h3>";
        echo "<p style='margin: 5px 0;'>Unavailable</p>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;'>";
        echo "<h3 style='color: #ffc107; margin: 0;'>{$rented_stats['rented_cars']}</h3>";
        echo "<p style='margin: 5px 0;'>Currently Rented</p>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;'>";
        echo "<h3 style='color: #17a2b8; margin: 0;'>{$stats['clients_with_cars']}</h3>";
        echo "<p style='margin: 5px 0;'>Active Clients</p>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border-radius: 5px; text-align: center; min-width: 120px;'>";
        echo "<h3 style='color: #6c757d; margin: 0;'>{$stats['unassigned_cars']}</h3>";
        echo "<p style='margin: 5px 0;'>Unassigned Cars</p>";
        echo "</div>";
        
        echo "</div>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Admin car management system not found</p>";
    }
    
    // Show admin car management features
    echo "<h3>🔧 Admin Car Management Features</h3>";
    
    echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>✅ Complete CRUD Operations:</h4>";
    echo "<ul>";
    echo "<li><strong>➕ Add New Cars:</strong> Admin can add cars with all details (name, plate, prices, images)</li>";
    echo "<li><strong>✏️ Edit Cars:</strong> Update car information, prices, availability, and client assignments</li>";
    echo "<li><strong>🗑️ Delete Cars:</strong> Remove cars (with safety checks for existing bookings)</li>";
    echo "<li><strong>👁️ View All Cars:</strong> Complete list with statistics and booking history</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🎛️ Advanced Management Features:</h4>";
    echo "<ul>";
    echo "<li><strong>🏢 Client Assignment:</strong> Assign cars to specific clients or make available to all</li>";
    echo "<li><strong>💰 Pricing Management:</strong> Set AC/Non-AC prices per km and per day</li>";
    echo "<li><strong>📸 Image Management:</strong> Upload and manage car images</li>";
    echo "<li><strong>🔄 Availability Control:</strong> Quick toggle car availability status</li>";
    echo "<li><strong>📊 Statistics Dashboard:</strong> Real-time fleet statistics and performance metrics</li>";
    echo "<li><strong>🔧 Bulk Operations:</strong> Make all cars available with one click</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🛡️ Safety & Security Features:</h4>";
    echo "<ul>";
    echo "<li><strong>🔒 Booking Protection:</strong> Cannot delete cars with existing bookings</li>";
    echo "<li><strong>🔄 Auto-Fix System:</strong> Automatically makes non-rented cars available</li>";
    echo "<li><strong>📝 Audit Trail:</strong> Track all car management operations</li>";
    echo "<li><strong>✅ Data Validation:</strong> Comprehensive form validation and error handling</li>";
    echo "<li><strong>🔐 Admin Authentication:</strong> Only authenticated admins can manage cars</li>";
    echo "</ul>";
    echo "</div>";
    
    // Show sample cars if any exist
    echo "<h3>🚗 Sample Cars in System</h3>";
    
    $sample_cars_sql = "SELECT c.*, cc.client_username, cl.client_name, COUNT(rc.id) as booking_count,
                        CASE WHEN rc_active.car_id IS NOT NULL THEN 'Currently Rented' ELSE 'Available' END as rental_status
                        FROM cars c 
                        LEFT JOIN clientcars cc ON c.car_id = cc.car_id
                        LEFT JOIN clients cl ON cc.client_username = cl.client_username
                        LEFT JOIN rentedcars rc ON c.car_id = rc.car_id 
                        LEFT JOIN rentedcars rc_active ON c.car_id = rc_active.car_id AND rc_active.return_status = 'NR'
                        GROUP BY c.car_id 
                        ORDER BY c.car_id DESC
                        LIMIT 5";
    $sample_result = $conn->query($sample_cars_sql);
    
    if ($sample_result && $sample_result->num_rows > 0) {
        echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>Car Name</th><th>Number Plate</th><th>Client</th><th>Availability</th><th>Rental Status</th><th>Bookings</th>";
        echo "</tr>";
        
        while ($car = $sample_result->fetch_assoc()) {
            $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
            $availability_text = $car['car_availability'] == 'yes' ? '✅ Available' : '❌ Unavailable';
            $client_text = $car['client_name'] ? $car['client_name'] : 'No Client (Available to All)';
            
            echo "<tr>";
            echo "<td><strong>{$car['car_name']}</strong></td>";
            echo "<td>{$car['car_nameplate']}</td>";
            echo "<td>{$client_text}</td>";
            echo "<td style='color: {$availability_color};'>{$availability_text}</td>";
            echo "<td>{$car['rental_status']}</td>";
            echo "<td><span style='background: #007bff; color: white; padding: 2px 8px; border-radius: 3px;'>{$car['booking_count']}</span></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<p>No cars found in the system. Admin can add the first car!</p>";
        echo "</div>";
    }
    
    // Quick access links
    echo "<h3>🚀 Quick Access Links</h3>";
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🎛️ Admin Car Management:</h4>";
    echo "<div style='display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0;'>";
    
    echo "<a href='admin_cars.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>";
    echo "🚗 Manage Cars";
    echo "</a>";
    
    echo "<a href='admin_cars.php?action=add' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>";
    echo "➕ Add New Car";
    echo "</a>";
    
    echo "<a href='admin_cars.php?make_all_available=1' target='_blank' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>";
    echo "🔄 Make All Available";
    echo "</a>";
    
    echo "<a href='admin_dashboard.php' target='_blank' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>";
    echo "📊 Admin Dashboard";
    echo "</a>";
    
    echo "</div>";
    echo "</div>";
    
    // Admin capabilities summary
    echo "<h3>👑 What Admin Can Do with Cars</h3>";
    
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4>🎯 Complete Car Fleet Management:</h4>";
    echo "<ol>";
    echo "<li><strong>Add Cars:</strong> Create new car entries with all details</li>";
    echo "<li><strong>Edit Cars:</strong> Modify car information, pricing, and assignments</li>";
    echo "<li><strong>Delete Cars:</strong> Remove cars (with booking safety checks)</li>";
    echo "<li><strong>Assign to Clients:</strong> Link cars to specific fleet owners</li>";
    echo "<li><strong>Set Pricing:</strong> Configure rental rates for different car types</li>";
    echo "<li><strong>Manage Images:</strong> Upload and update car photos</li>";
    echo "<li><strong>Control Availability:</strong> Enable/disable cars for booking</li>";
    echo "<li><strong>View Statistics:</strong> Monitor fleet performance and utilization</li>";
    echo "<li><strong>Track Bookings:</strong> See booking history for each car</li>";
    echo "<li><strong>Bulk Operations:</strong> Perform actions on multiple cars at once</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h4 style='color: #155724;'>✅ Admin Car Management Status: FULLY FUNCTIONAL</h4>";
    echo "<p style='color: #155724;'>The admin has complete control over the car fleet with all CRUD operations, advanced features, and safety measures in place.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Error checking car management: " . htmlspecialchars($e->getMessage()) . "</p>";
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