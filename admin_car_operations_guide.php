<?php
/**
 * Admin Car Operations Guide
 * Step-by-step guide for adding and editing cars in admin panel
 */

require 'connection.php';

echo "<h2>🚗 Admin Panel Car Operations Guide</h2>";
echo "<p>Complete guide for adding and editing cars through the admin panel...</p>";

$conn = Connect();

// Check admin login status
session_start();
$admin_logged_in = isset($_SESSION['login_admin']);

echo "<h3>Step 1: Admin Login Status</h3>";
if ($admin_logged_in) {
    echo "<p style='color: green;'>✅ Admin is logged in: " . $_SESSION['login_admin'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Admin not logged in</p>";
    echo "<p><strong>First step:</strong> <a href='admin_login.php' target='_blank' style='color: #007bff;'>Login as admin</a></p>";
    echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Default Admin Credentials:</strong><br>";
    echo "Username: admin<br>";
    echo "Password: admin123";
    echo "</div>";
}

// Show current cars for editing
echo "<h3>Step 2: Current Cars Available for Editing</h3>";

$cars_sql = "SELECT c.*, cc.client_username, cl.client_name 
             FROM cars c 
             LEFT JOIN clientcars cc ON c.car_id = cc.car_id
             LEFT JOIN clients cl ON cc.client_username = cl.client_username
             ORDER BY c.car_id DESC";
$cars_result = $conn->query($cars_sql);

if ($cars_result && $cars_result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Found {$cars_result->num_rows} cars available for editing</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>ID</th><th>Car Name</th><th>Number Plate</th><th>Client</th><th>Availability</th><th>Admin Actions</th>";
    echo "</tr>";
    
    while ($car = $cars_result->fetch_assoc()) {
        $availability_color = $car['car_availability'] == 'yes' ? 'green' : 'red';
        $availability_text = $car['car_availability'] == 'yes' ? 'Available' : 'Unavailable';
        $client_text = $car['client_name'] ? $car['client_name'] : 'No Client';
        
        echo "<tr>";
        echo "<td>{$car['car_id']}</td>";
        echo "<td><strong>{$car['car_name']}</strong></td>";
        echo "<td>{$car['car_nameplate']}</td>";
        echo "<td>{$client_text}</td>";
        echo "<td style='color: {$availability_color};'>{$availability_text}</td>";
        echo "<td>";
        echo "<a href='admin_cars.php?action=edit&id={$car['car_id']}' target='_blank' style='background: #007bff; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-right: 5px;'>Edit</a>";
        echo "<a href='admin_cars.php?delete={$car['car_id']}' target='_blank' style='background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;' onclick='return confirm(\"Delete this car?\")'>Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: orange;'>⚠️ No cars found. You can add the first car!</p>";
}

// Admin panel access methods
echo "<h3>Step 3: How to Access Admin Car Management</h3>";

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🎯 Two Ways to Manage Cars in Admin Panel:</h4>";

echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 15px 0;'>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #007bff;'>";
echo "<h5 style='color: #007bff;'>Method 1: Through Dashboard</h5>";
echo "<ol>";
echo "<li>Login at <a href='admin_login.php' target='_blank'>admin_login.php</a></li>";
echo "<li>Go to <a href='admin_dashboard.php' target='_blank'>admin_dashboard.php</a></li>";
echo "<li>Click 'Manage Cars' in sidebar menu</li>";
echo "<li>Use 'Add New Car' button or edit existing cars</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #28a745;'>";
echo "<h5 style='color: #28a745;'>Method 2: Direct Access</h5>";
echo "<ol>";
echo "<li>Login as admin first</li>";
echo "<li>Go directly to <a href='admin_cars.php' target='_blank'>admin_cars.php</a></li>";
echo "<li>Use all car management features</li>";
echo "<li>Add/Edit/Delete cars as needed</li>";
echo "</ol>";
echo "</div>";

echo "</div>";
echo "</div>";

// Quick action buttons
echo "<h3>Step 4: Quick Action Buttons</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🚀 Direct Access Links:</h4>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";

echo "<a href='admin_login.php' target='_blank' style='background: #dc3545; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🔑 Admin Login<br><small>Login First</small>";
echo "</a>";

echo "<a href='admin_cars.php' target='_blank' style='background: #007bff; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🚗 Manage Cars<br><small>View All Cars</small>";
echo "</a>";

echo "<a href='admin_cars.php?action=add' target='_blank' style='background: #28a745; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "➕ Add New Car<br><small>Create Car</small>";
echo "</a>";

echo "<a href='admin_dashboard.php' target='_blank' style='background: #6c757d; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "📊 Dashboard<br><small>Admin Panel</small>";
echo "</a>";

echo "</div>";
echo "</div>";

// Add car form fields explanation
echo "<h3>Step 5: Add Car Form Fields</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>📝 When Adding a New Car, You'll Need:</h4>";
echo "<ul>";
echo "<li><strong>Car Name:</strong> e.g., 'Toyota Camry', 'Honda Civic'</li>";
echo "<li><strong>Number Plate:</strong> e.g., 'ABC-1234', 'XYZ-5678'</li>";
echo "<li><strong>AC Price per km:</strong> e.g., 15.00</li>";
echo "<li><strong>Non-AC Price per km:</strong> e.g., 12.00</li>";
echo "<li><strong>AC Price per day:</strong> e.g., 800.00</li>";
echo "<li><strong>Non-AC Price per day:</strong> e.g., 600.00</li>";
echo "<li><strong>Availability:</strong> Available or Not Available</li>";
echo "<li><strong>Client Assignment:</strong> Assign to specific client or leave for all</li>";
echo "<li><strong>Car Image:</strong> Upload photo (optional)</li>";
echo "</ul>";
echo "</div>";

// Edit car explanation
echo "<h3>Step 6: Edit Car Process</h3>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>✏️ To Edit an Existing Car:</h4>";
echo "<ol>";
echo "<li>Go to admin car management page</li>";
echo "<li>Find the car you want to edit in the list</li>";
echo "<li>Click the 'Edit' button next to the car</li>";
echo "<li>Modify any fields you want to change</li>";
echo "<li>Click 'Update Car' to save changes</li>";
echo "</ol>";
echo "</div>";

// Troubleshooting
echo "<h3>Step 7: Troubleshooting</h3>";

echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔧 If Car Management Not Working:</h4>";
echo "<ol>";
echo "<li><strong>Check Login:</strong> Make sure you're logged in as admin</li>";
echo "<li><strong>Clear Cache:</strong> Refresh browser (Ctrl+F5)</li>";
echo "<li><strong>Check URL:</strong> Ensure you're on the correct admin_cars.php</li>";
echo "<li><strong>Try Direct Link:</strong> Use admin_cars.php?action=add directly</li>";
echo "<li><strong>Check Console:</strong> Press F12 and look for errors</li>";
echo "</ol>";
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
h2, h3, h4, h5 { 
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
small {
    font-size: 12px;
    opacity: 0.8;
}
</style>