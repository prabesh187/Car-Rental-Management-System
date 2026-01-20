<?php
/**
 * Simple One-Click Solution: Make All Cars Available
 * Quick fix to set all cars to available status
 */

require 'connection.php';
$conn = Connect();

// Simple update query to make all cars available
$sql = "UPDATE cars SET car_availability = 'yes'";
$result = $conn->query($sql);

if ($result) {
    $affected_rows = $conn->affected_rows;
    echo "✅ SUCCESS: {$affected_rows} cars have been made available!";
    
    // Show current status
    $count_sql = "SELECT COUNT(*) as total FROM cars WHERE car_availability = 'yes'";
    $count_result = $conn->query($count_sql);
    $total_available = $count_result->fetch_assoc()['total'];
    
    echo "<br>📊 Total available cars: {$total_available}";
    echo "<br>🎉 All cars are now available for booking!";
    
    echo "<br><br>🔗 Quick Links:";
    echo "<br>• <a href='index.php'>View Cars on Homepage</a>";
    echo "<br>• <a href='booking.php'>Book a Car</a>";
    echo "<br>• <a href='admin_cars.php'>Admin Car Management</a>";
} else {
    echo "❌ ERROR: Failed to update cars - " . $conn->error;
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>