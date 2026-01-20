<?php
/**
 * Admin Access Test - Simple Diagnostic
 * Quick test to identify admin car management access issues
 */

echo "<h2>🔧 Admin Car Management Access Test</h2>";
echo "<p>Let's identify exactly why admin can't manage cars...</p>";

// Step 1: Check admin login status
echo "<h3>Step 1: Admin Login Status</h3>";
session_start();

if (isset($_SESSION['login_admin'])) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p style='color: #155724;'>✅ <strong>Admin is logged in:</strong> " . $_SESSION['login_admin'] . "</p>";
    echo "</div>";
    
    $admin_logged_in = true;
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p style='color: #721c24;'>❌ <strong>Admin is NOT logged in</strong> - This is likely the issue!</p>";
    echo "<p><strong>Solution:</strong> <a href='admin_login.php' style='color: #007bff;'>Click here to login as admin</a></p>";
    echo "</div>";
    
    $admin_logged_in = false;
}

// Step 2: Test file accessibility
echo "<h3>Step 2: File Accessibility</h3>";

$files_to_check = [
    'admin_login.php' => 'Admin Login',
    'admin_dashboard.php' => 'Admin Dashboard', 
    'admin_cars.php' => 'Car Management'
];

$all_files_exist = true;
foreach ($files_to_check as $file => $name) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $name ($file) - Available</p>";
    } else {
        echo "<p style='color: red;'>❌ $name ($file) - Missing</p>";
        $all_files_exist = false;
    }
}

// Step 3: Database connectivity test
echo "<h3>Step 3: Database Connection</h3>";

if (file_exists('connection.php')) {
    try {
        require_once 'connection.php';
        $conn = Connect();
        
        if ($conn) {
            echo "<p style='color: green;'>✅ Database connection successful</p>";
            
            // Check cars table
            $result = $conn->query("SELECT COUNT(*) as count FROM cars");
            if ($result) {
                $car_count = $result->fetch_assoc()['count'];
                echo "<p style='color: blue;'>ℹ️ Cars in database: $car_count</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Database connection failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ connection.php file missing</p>";
}

// Step 4: Provide solutions based on findings
echo "<h3>Step 4: Solutions</h3>";

if (!$admin_logged_in) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='color: #856404;'>🔑 SOLUTION: Admin Needs to Login First</h4>";
    echo "<p style='color: #856404;'>The admin must login before accessing car management.</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h5>Default Admin Credentials:</h5>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> admin</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p><a href='admin_login.php' style='background: #007bff; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>LOGIN AS ADMIN NOW</a></p>";
    echo "</div>";
    
} else if ($all_files_exist) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='color: #155724;'>✅ Everything Looks Good!</h4>";
    echo "<p style='color: #155724;'>Admin is logged in and all files exist. Car management should be accessible.</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h5>Try These Links:</h5>";
    echo "<ul>";
    echo "<li><a href='admin_dashboard.php' target='_blank' style='color: #007bff;'>Admin Dashboard</a> - Look for 'Manage Cars' in the menu</li>";
    echo "<li><a href='admin_cars.php' target='_blank' style='color: #007bff;'>Car Management (Direct)</a> - Direct access to car management</li>";
    echo "<li><a href='admin_cars.php?action=add' target='_blank' style='color: #007bff;'>Add New Car</a> - Test adding a car</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h4 style='color: #721c24;'>❌ Missing Files</h4>";
    echo "<p style='color: #721c24;'>Some required files are missing. This needs to be fixed first.</p>";
    echo "</div>";
}

// Step 5: Quick access panel
echo "<h3>Step 5: Quick Access Panel</h3>";

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🚀 Quick Access Links</h4>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 15px 0;'>";

echo "<a href='admin_login.php' target='_blank' style='background: #dc3545; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "<i class='fa fa-sign-in'></i><br>Admin Login";
echo "</a>";

echo "<a href='admin_dashboard.php' target='_blank' style='background: #28a745; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "<i class='fa fa-dashboard'></i><br>Dashboard";
echo "</a>";

echo "<a href='admin_cars.php' target='_blank' style='background: #007bff; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "<i class='fa fa-car'></i><br>Manage Cars";
echo "</a>";

echo "<a href='admin_cars.php?action=add' target='_blank' style='background: #17a2b8; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
echo "<i class='fa fa-plus'></i><br>Add Car";
echo "</a>";

echo "</div>";
echo "</div>";

// Step 6: Troubleshooting guide
echo "<h3>Step 6: Troubleshooting Guide</h3>";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>🔍 If Car Management Still Not Working:</h4>";
echo "<ol>";
echo "<li><strong>Clear Browser Cache:</strong> Refresh the page (Ctrl+F5)</li>";
echo "<li><strong>Check Browser Console:</strong> Press F12 and look for JavaScript errors</li>";
echo "<li><strong>Try Different Browser:</strong> Test in Chrome, Firefox, or Edge</li>";
echo "<li><strong>Check URL:</strong> Make sure you're accessing the correct admin_cars.php</li>";
echo "<li><strong>File Permissions:</strong> Ensure web server can read the PHP files</li>";
echo "</ol>";
echo "</div>";

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
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>