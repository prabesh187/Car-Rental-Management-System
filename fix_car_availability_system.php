<?php
/**
 * Fix Car Availability System
 * Comprehensive solution to ensure all cars are properly available
 */

require 'connection.php';

echo "<h2>🚗 Fix Car Availability System</h2>";
echo "<p>Comprehensive solution to make all cars available and fix availability issues.</p>";

$conn = Connect();

try {
    // Step 1: Analyze current situation
    echo "<h3>Step 1: Current System Analysis</h3>";
    
    $analysis_sql = "SELECT 
                        COUNT(*) as total_cars,
                        SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available_cars,
                        SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable_cars
                     FROM cars";
    
    $analysis_result = $conn->query($analysis_sql);
    $stats = $analysis_result->fetch_assoc();
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Current Status:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Cars:</strong> {$stats['total_cars']}</li>";
    echo "<li><strong>Available Cars:</strong> {$stats['available_cars']}</li>";
    echo "<li><strong>Unavailable Cars:</strong> {$stats['unavailable_cars']}</li>";
    echo "</ul>";
    echo "</div>";
    
    // Step 2: Check for booking issues
    echo "<h3>Step 2: Booking System Analysis</h3>";
    
    // Check for cars marked unavailable but not actually rented
    $stuck_cars_sql = "SELECT c.car_id, c.car_name, c.car_nameplate, c.car_availability
                       FROM cars c
                       LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                       WHERE c.car_availability = 'no' AND rc.car_id IS NULL";
    
    $stuck_cars_result = $conn->query($stuck_cars_sql);
    
    if ($stuck_cars_result->num_rows > 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
        echo "<h4 style='color: #856404;'>⚠️ Found Stuck Cars (Marked Unavailable but Not Rented)</h4>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Car ID</th><th>Car Name</th><th>Number Plate</th><th>Issue</th></tr>";
        
        while ($car = $stuck_cars_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$car['car_id']}</td>";
            echo "<td>{$car['car_name']}</td>";
            echo "<td>{$car['car_nameplate']}</td>";
            echo "<td style='color: red;'>Marked unavailable but not rented</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ No stuck cars found</p>";
    }
    
    // Check for currently rented cars
    $rented_cars_sql = "SELECT c.car_id, c.car_name, c.car_nameplate, c.car_availability,
                               rc.customer_username, rc.rent_start_date, rc.rent_end_date
                        FROM cars c
                        JOIN rentedcars rc ON c.car_id = rc.car_id
                        WHERE rc.return_status = 'NR'";
    
    $rented_cars_result = $conn->query($rented_cars_sql);
    
    if ($rented_cars_result->num_rows > 0) {
        echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #bee5eb;'>";
        echo "<h4 style='color: #0c5460;'>ℹ️ Currently Rented Cars (Should Remain Unavailable)</h4>";
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>Car ID</th><th>Car Name</th><th>Customer</th><th>Rental Period</th><th>Current Status</th></tr>";
        
        $rented_car_ids = [];
        while ($car = $rented_cars_result->fetch_assoc()) {
            $rented_car_ids[] = $car['car_id'];
            $status_color = $car['car_availability'] == 'no' ? 'green' : 'red';
            $status_text = $car['car_availability'] == 'no' ? 'Correctly Unavailable' : 'ERROR: Should be Unavailable';
            
            echo "<tr>";
            echo "<td>{$car['car_id']}</td>";
            echo "<td>{$car['car_name']}</td>";
            echo "<td>{$car['customer_username']}</td>";
            echo "<td>{$car['rent_start_date']} to {$car['rent_end_date']}</td>";
            echo "<td style='color: {$status_color};'>{$status_text}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p style='color: green;'>✅ No cars are currently rented</p>";
        $rented_car_ids = [];
    }
    
    // Step 3: Fix the availability issues
    echo "<h3>Step 3: Fixing Car Availability</h3>";
    
    $conn->begin_transaction();
    
    try {
        // Fix 1: Make all non-rented cars available
        if (!empty($rented_car_ids)) {
            $rented_ids_str = implode(',', $rented_car_ids);
            $fix_sql = "UPDATE cars SET car_availability = 'yes' WHERE car_id NOT IN ($rented_ids_str)";
            echo "<p>Making all cars available except currently rented ones (IDs: " . implode(', ', $rented_car_ids) . ")</p>";
        } else {
            $fix_sql = "UPDATE cars SET car_availability = 'yes'";
            echo "<p>Making ALL cars available (no cars are currently rented)</p>";
        }
        
        $fix_result = $conn->query($fix_sql);
        $affected_rows = $conn->affected_rows;
        
        // Fix 2: Ensure rented cars are marked unavailable
        if (!empty($rented_car_ids)) {
            $rented_ids_str = implode(',', $rented_car_ids);
            $fix_rented_sql = "UPDATE cars SET car_availability = 'no' WHERE car_id IN ($rented_ids_str)";
            $conn->query($fix_rented_sql);
            echo "<p>Ensured rented cars are marked as unavailable</p>";
        }
        
        $conn->commit();
        
        echo "<p style='color: green;'>✅ Successfully updated {$affected_rows} cars!</p>";
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
    // Step 4: Verify the fix
    echo "<h3>Step 4: Verification</h3>";
    
    $final_analysis_result = $conn->query($analysis_sql);
    $final_stats = $final_analysis_result->fetch_assoc();
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724;'>✅ After Fix:</h4>";
    echo "<ul>";
    echo "<li><strong>Total Cars:</strong> {$final_stats['total_cars']}</li>";
    echo "<li><strong>Available Cars:</strong> {$final_stats['available_cars']}</li>";
    echo "<li><strong>Unavailable Cars:</strong> {$final_stats['unavailable_cars']}</li>";
    echo "</ul>";
    
    $improvement = $final_stats['available_cars'] - $stats['available_cars'];
    if ($improvement > 0) {
        echo "<p style='color: #155724;'><strong>🎉 Improvement: {$improvement} more cars are now available!</strong></p>";
    } elseif ($final_stats['available_cars'] == $final_stats['total_cars']) {
        echo "<p style='color: #155724;'><strong>🎉 Perfect! All cars are now available for booking!</strong></p>";
    }
    echo "</div>";
    
    // Step 5: Show complete car status
    echo "<h3>Step 5: Complete Car Status</h3>";
    
    $complete_status_sql = "SELECT c.car_id, c.car_name, c.car_nameplate, c.car_availability,
                                   CASE 
                                       WHEN rc.car_id IS NOT NULL THEN CONCAT('Rented by ', rc.customer_username, ' until ', rc.rent_end_date)
                                       ELSE 'Available for Booking'
                                   END as status_detail
                            FROM cars c
                            LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                            ORDER BY c.car_id";
    
    $complete_result = $conn->query($complete_status_sql);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Car ID</th><th>Car Name</th><th>Number Plate</th><th>Availability</th><th>Status Detail</th></tr>";
    
    while ($car = $complete_result->fetch_assoc()) {
        $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
        $availability_text = $car['car_availability'] == 'yes' ? 'Available' : 'Unavailable';
        
        echo "<tr>";
        echo "<td>{$car['car_id']}</td>";
        echo "<td>{$car['car_name']}</td>";
        echo "<td>{$car['car_nameplate']}</td>";
        echo "<td style='color: {$availability_color}; font-weight: bold;'>{$availability_text}</td>";
        echo "<td>{$car['status_detail']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Step 6: System improvements and recommendations
    echo "<h3>Step 6: System Improvements</h3>";
    
    echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
    echo "<h4>🔧 Recommended System Improvements:</h4>";
    echo "<ol>";
    echo "<li><strong>Automatic Availability Management:</strong> Cars should automatically become available when returned</li>";
    echo "<li><strong>Booking Validation:</strong> Prevent booking unavailable cars</li>";
    echo "<li><strong>Return Process Fix:</strong> Ensure cars are marked available after return</li>";
    echo "<li><strong>Admin Override:</strong> Allow admins to manually set car availability</li>";
    echo "</ol>";
    
    echo "<h4>📋 Files That Need Updates:</h4>";
    echo "<ul>";
    echo "<li><strong>bookingconfirm.php:</strong> Sets cars to unavailable when booked</li>";
    echo "<li><strong>printbill.php:</strong> Should set cars to available when returned</li>";
    echo "<li><strong>admin_cars.php:</strong> Admin can manually control availability</li>";
    echo "<li><strong>prereturncar.php:</strong> Return process should update availability</li>";
    echo "</ul>";
    echo "</div>";
    
    // Step 7: Quick actions
    echo "<h3>Step 7: Quick Actions</h3>";
    
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>🚀 What You Can Do Now:</h4>";
    echo "<ul>";
    echo "<li><a href='index.php' target='_blank' style='color: #007bff;'>✅ View Available Cars on Homepage</a></li>";
    echo "<li><a href='booking.php' target='_blank' style='color: #007bff;'>✅ Test Car Booking System</a></li>";
    echo "<li><a href='admin_cars.php' target='_blank' style='color: #007bff;'>✅ Manage Cars in Admin Panel</a></li>";
    echo "<li><a href='enhanced_booking.php' target='_blank' style='color: #007bff;'>✅ Try Enhanced Booking</a></li>";
    echo "</ul>";
    
    echo "<h4>🔄 To Run This Fix Again:</h4>";
    echo "<p>Simply refresh this page or run: <code>php fix_car_availability_system.php</code></p>";
    
    echo "<h4>⚙️ Manual Control:</h4>";
    echo "<p>To manually control car availability:</p>";
    echo "<ol>";
    echo "<li>Go to <a href='admin_cars.php' target='_blank'>Admin Panel → Manage Cars</a></li>";
    echo "<li>Click 'Edit' on any car</li>";
    echo "<li>Change 'Availability Status' to 'Available' or 'Not Available'</li>";
    echo "<li>Save changes</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb;'>";
    echo "<h4 style='color: #721c24;'>❌ Error</h4>";
    echo "<p>Failed to fix car availability: " . htmlspecialchars($e->getMessage()) . "</p>";
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
code {
    background: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style>