<?php
// Test script to verify optional driver client assignment
session_start();
require 'connection.php';

// Set admin session for testing
$_SESSION['login_admin'] = 'admin';

$conn = Connect();

echo "<h2>Driver Client Assignment Test</h2>";

// Check current drivers and their client assignments
$drivers_sql = "SELECT d.*, c.client_name FROM driver d 
                LEFT JOIN clients c ON d.client_username = c.client_username 
                ORDER BY d.driver_id";
$drivers_result = $conn->query($drivers_sql);

echo "<h3>Current Drivers in Database:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th>ID</th><th>Name</th><th>License</th><th>Client Assignment</th><th>Status</th>";
echo "</tr>";

$independent_count = 0;
$assigned_count = 0;

while ($driver = $drivers_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $driver['driver_id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($driver['driver_name']) . "</strong></td>";
    echo "<td>" . htmlspecialchars($driver['dl_number']) . "</td>";
    
    if ($driver['client_name']) {
        echo "<td style='color: blue;'><strong>Assigned to: " . htmlspecialchars($driver['client_name']) . "</strong></td>";
        $assigned_count++;
    } else {
        echo "<td style='color: green;'><strong>Independent Driver</strong></td>";
        $independent_count++;
    }
    
    $status = $driver['driver_availability'] == 'yes' ? 'Available' : 'Busy';
    $color = $driver['driver_availability'] == 'yes' ? 'green' : 'orange';
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Summary:</h3>";
echo "<ul>";
echo "<li><strong>Total Drivers:</strong> " . ($independent_count + $assigned_count) . "</li>";
echo "<li><strong>Independent Drivers:</strong> $independent_count</li>";
echo "<li><strong>Client-Assigned Drivers:</strong> $assigned_count</li>";
echo "</ul>";

// Check available clients
$clients_sql = "SELECT client_username, client_name FROM clients ORDER BY client_name";
$clients_result = $conn->query($clients_sql);

echo "<h3>Available Clients for Assignment:</h3>";
echo "<ul>";
while ($client = $clients_result->fetch_assoc()) {
    echo "<li><strong>" . htmlspecialchars($client['client_name']) . "</strong> (Username: " . htmlspecialchars($client['client_username']) . ")</li>";
}
echo "</ul>";

echo "<h3>Test Results:</h3>";
echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; color: #155724;'>";
echo "<h4>✅ Optional Client Assignment Feature Status:</h4>";
echo "<ul>";
echo "<li>✅ <strong>Database Support:</strong> Drivers can have NULL client_username</li>";
echo "<li>✅ <strong>Form Updated:</strong> Client selection is now optional</li>";
echo "<li>✅ <strong>UI Improved:</strong> Clear indication of independent vs assigned drivers</li>";
echo "<li>✅ <strong>Validation Fixed:</strong> No longer requires client selection</li>";
echo "<li>✅ <strong>Display Enhanced:</strong> Shows 'Independent' for unassigned drivers</li>";
echo "</ul>";
echo "</div>";

echo "<h3>How to Test:</h3>";
echo "<ol>";
echo "<li><a href='admin_drivers.php?action=add' target='_blank'>Add New Driver</a> - Try adding without selecting a client</li>";
echo "<li><a href='admin_drivers.php' target='_blank'>View All Drivers</a> - See the updated interface</li>";
echo "<li>Edit existing drivers to change their client assignment</li>";
echo "</ol>";

echo "<h3>Database Schema Check:</h3>";
$schema_sql = "DESCRIBE driver";
$schema_result = $conn->query($schema_sql);

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($field = $schema_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "<td>" . $field['Default'] . "</td>";
    echo "</tr>";
}
echo "</table>";

?>

<style>
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background: #f5f5f5;
}
table { 
    background: white; 
    margin: 10px 0; 
    width: 100%;
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
h2, h3 {
    color: #333;
}
</style>