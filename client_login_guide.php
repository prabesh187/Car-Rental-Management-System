<?php
/**
 * Client Login Guide
 * Shows where and how clients can log in to the system
 */

require 'connection.php';

echo "<h2>🔑 Client Login Guide</h2>";
echo "<p>Complete guide to client login locations and functionality...</p>";

$conn = Connect();

// Check if client login files exist
echo "<h3>📍 Client Login Locations</h3>";

$login_files = [
    'clientlogin.php' => 'Main Client Login Page',
    'login_client.php' => 'Client Login Handler',
    'clientsignup.php' => 'Client Registration Page',
    'client_registered_success.php' => 'Registration Success Handler'
];

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔗 Client Login Access Points:</h4>";

foreach ($login_files as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ <strong>$description</strong>: <a href='$file' target='_blank' style='color: #007bff;'>$file</a></p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>$description</strong>: $file (Missing)</p>";
    }
}
echo "</div>";

// Check navigation links
echo "<h3>🧭 Navigation to Client Login</h3>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>✅ Ways Clients Can Access Login:</h4>";
echo "<ol>";
echo "<li><strong>Main Website:</strong> From index.php → 'Employee' or 'Client' menu</li>";
echo "<li><strong>Direct URL:</strong> Go directly to clientlogin.php</li>";
echo "<li><strong>Registration:</strong> After signing up at clientsignup.php</li>";
echo "<li><strong>Customer Login:</strong> Link from customerlogin.php</li>";
echo "</ol>";
echo "</div>";

// Check current clients in system
echo "<h3>👥 Current Clients in System</h3>";

$clients_sql = "SELECT client_username, client_name, client_email FROM clients ORDER BY client_username";
$clients_result = $conn->query($clients_sql);

if ($clients_result && $clients_result->num_rows > 0) {
    echo "<p style='color: green;'>✅ Found {$clients_result->num_rows} clients in the system</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th>Username</th><th>Name</th><th>Email</th><th>Login Status</th>";
    echo "</tr>";
    
    while ($client = $clients_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>{$client['client_username']}</strong></td>";
        echo "<td>{$client['client_name']}</td>";
        echo "<td>{$client['client_email']}</td>";
        echo "<td><a href='clientlogin.php' target='_blank' style='color: #007bff;'>Can Login</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "<p style='color: orange;'>⚠️ No clients found in the system</p>";
    echo "<p><a href='clientsignup.php' target='_blank' style='color: #007bff;'>Create first client account</a></p>";
}

// Test client login functionality
echo "<h3>🧪 Test Client Login</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔑 Test Client Login Credentials:</h4>";
echo "<p>If you've reset passwords using our tools, all clients can login with:</p>";
echo "<ul>";
echo "<li><strong>Username:</strong> Any existing client username</li>";
echo "<li><strong>Password:</strong> password123</li>";
echo "</ul>";
echo "<p><a href='clientlogin.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Client Login Now</a></p>";
echo "</div>";

// Show client login interface
echo "<h3>🎨 Client Login Interface</h3>";

if (file_exists('clientlogin.php')) {
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4>Client Login Page Features:</h4>";
    
    $clientlogin_content = file_get_contents('clientlogin.php');
    
    if (strpos($clientlogin_content, 'client_username') !== false) {
        echo "<p style='color: green;'>✅ Username field available</p>";
    }
    
    if (strpos($clientlogin_content, 'client_password') !== false) {
        echo "<p style='color: green;'>✅ Password field available</p>";
    }
    
    if (strpos($clientlogin_content, 'clientsignup.php') !== false) {
        echo "<p style='color: green;'>✅ Registration link available</p>";
    }
    
    if (strpos($clientlogin_content, 'Employee') !== false || strpos($clientlogin_content, 'Client') !== false) {
        echo "<p style='color: green;'>✅ Proper client/employee branding</p>";
    }
    
    echo "</div>";
}

// Quick access panel
echo "<h3>🚀 Quick Access Panel</h3>";

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🔗 Client Login & Registration Links:</h4>";

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 15px 0;'>";

echo "<a href='clientlogin.php' target='_blank' style='background: #007bff; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🔑 Client Login";
echo "</a>";

echo "<a href='clientsignup.php' target='_blank' style='background: #28a745; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "📝 Client Registration";
echo "</a>";

echo "<a href='index.php' target='_blank' style='background: #6c757d; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "🏠 Main Website";
echo "</a>";

echo "<a href='customerlogin.php' target='_blank' style='background: #17a2b8; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "👤 Customer Login";
echo "</a>";

echo "</div>";
echo "</div>";

// Client capabilities after login
echo "<h3>🎛️ What Clients Can Do After Login</h3>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>✅ Client Dashboard Features:</h4>";
echo "<ul>";
echo "<li><strong>🚗 Add Cars:</strong> Add cars to their fleet</li>";
echo "<li><strong>👨‍💼 Add Drivers:</strong> Hire drivers for their cars</li>";
echo "<li><strong>📊 View Dashboard:</strong> See their business statistics</li>";
echo "<li><strong>📋 Manage Fleet:</strong> Control their cars and drivers</li>";
echo "<li><strong>💰 Track Revenue:</strong> Monitor earnings from rentals</li>";
echo "<li><strong>📈 View Reports:</strong> Business performance analytics</li>";
echo "</ul>";
echo "</div>";

// Navigation from main site
echo "<h3>🧭 How to Find Client Login from Main Site</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>📍 Navigation Path:</h4>";
echo "<ol>";
echo "<li>Go to <a href='index.php' target='_blank' style='color: #007bff;'>main website (index.php)</a></li>";
echo "<li>Look for navigation menu at the top</li>";
echo "<li>Click on <strong>'Employee'</strong> or <strong>'Client'</strong> link</li>";
echo "<li>This will take you to the client login page</li>";
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