<?php
/**
 * Debug Admin Car Access Issues
 * Check why admin can't manage cars from their end
 */

echo "<h2>🔍 Debug Admin Car Management Access</h2>";
echo "<p>Investigating why admin can't manage cars from their end...</p>";

// Test 1: Check if admin_cars.php file exists and is accessible
echo "<h3>Test 1: File Accessibility Check</h3>";

$admin_files = [
    'admin_cars.php' => 'Car Management',
    'admin_dashboard.php' => 'Admin Dashboard',
    'admin_login.php' => 'Admin Login',
    'admin_customers.php' => 'Customer Management',
    'admin_clients.php' => 'Client Management'
];

foreach ($admin_files as $file => $description) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p style='color: green;'>✅ $description ($file) - Exists ({$size} bytes)</p>";
    } else {
        echo "<p style='color: red;'>❌ $description ($file) - Missing</p>";
    }
}

// Test 2: Check admin session and authentication
echo "<h3>Test 2: Admin Authentication Check</h3>";

session_start();
if (isset($_SESSION['login_admin'])) {
    echo "<p style='color: green;'>✅ Admin is logged in: " . $_SESSION['login_admin'] . "</p>";
    
    // Test database connection
    if (file_exists('connection.php')) {
        require 'connection.php';
        $conn = Connect();
        
        if ($conn) {
            echo "<p style='color: green;'>✅ Database connection successful</p>";
            
            // Check if cars table exists
            $tables_check = $conn->query("SHOW TABLES LIKE 'cars'");
            if ($tables_check && $tables_check->num_rows > 0) {
                echo "<p style='color: green;'>✅ Cars table exists</p>";
                
                // Get car count
                $car_count_result = $conn->query("SELECT COUNT(*) as count FROM cars");
                $car_count = $car_count_result->fetch_assoc()['count'];
                echo "<p style='color: blue;'>ℹ️ Total cars in database: $car_count</p>";
            } else {
                echo "<p style='color: red;'>❌ Cars table does not exist</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Database connection failed</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ connection.php file missing</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Admin not logged in - this might be the issue</p>";
    echo "<p><a href='admin_login.php' style='color: #007bff;'>Click here to login as admin</a></p>";
}

// Test 3: Check admin dashboard navigation
echo "<h3>Test 3: Admin Dashboard Navigation Check</h3>";

if (file_exists('admin_dashboard.php')) {
    $dashboard_content = file_get_contents('admin_dashboard.php');
    
    if (strpos($dashboard_content, 'admin_cars.php') !== false) {
        echo "<p style='color: green;'>✅ Car management link exists in dashboard</p>";
    } else {
        echo "<p style='color: red;'>❌ Car management link missing from dashboard</p>";
    }
    
    if (strpos($dashboard_content, 'Manage Cars') !== false || strpos($dashboard_content, 'Cars') !== false) {
        echo "<p style='color: green;'>✅ Car management menu item found</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Car management menu item might be missing</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Admin dashboard file missing</p>";
}

// Test 4: Check for PHP errors in admin_cars.php
echo "<h3>Test 4: PHP Syntax Check</h3>";

if (file_exists('admin_cars.php')) {
    // Check for basic PHP syntax issues
    $admin_cars_content = file_get_contents('admin_cars.php');
    
    if (strpos($admin_cars_content, '<?php') === 0) {
        echo "<p style='color: green;'>✅ PHP opening tag found</p>";
    } else {
        echo "<p style='color: red;'>❌ PHP opening tag missing or incorrect</p>";
    }
    
    if (strpos($admin_cars_content, 'session_start()') !== false) {
        echo "<p style='color: green;'>✅ Session management found</p>";
    } else {
        echo "<p style='color: red;'>❌ Session management missing</p>";
    }
    
    if (strpos($admin_cars_content, 'login_admin') !== false) {
        echo "<p style='color: green;'>✅ Admin authentication check found</p>";
    } else {
        echo "<p style='color: red;'>❌ Admin authentication check missing</p>";
    }
} else {
    echo "<p style='color: red;'>❌ admin_cars.php file not found</p>";
}

// Test 5: Create direct access links
echo "<h3>Test 5: Direct Access Links</h3>";

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔗 Try These Direct Links:</h4>";
echo "<ul>";
echo "<li><a href='admin_login.php' target='_blank' style='color: #007bff;'>Admin Login Page</a></li>";
echo "<li><a href='admin_dashboard.php' target='_blank' style='color: #007bff;'>Admin Dashboard</a></li>";
echo "<li><a href='admin_cars.php' target='_blank' style='color: #007bff;'>Car Management (Direct)</a></li>";
echo "<li><a href='admin_cars.php?action=add' target='_blank' style='color: #007bff;'>Add New Car (Direct)</a></li>";
echo "</ul>";
echo "</div>";

// Test 6: Common issues and solutions
echo "<h3>Test 6: Common Issues & Solutions</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔍 Possible Issues:</h4>";
echo "<ol>";
echo "<li><strong>Not Logged In:</strong> Admin needs to login first</li>";
echo "<li><strong>Session Expired:</strong> Admin session might have expired</li>";
echo "<li><strong>Missing Navigation:</strong> Car management link not in dashboard menu</li>";
echo "<li><strong>File Permissions:</strong> Web server can't access admin_cars.php</li>";
echo "<li><strong>PHP Errors:</strong> Syntax errors preventing page from loading</li>";
echo "<li><strong>Database Issues:</strong> Connection or table problems</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>💡 Solutions:</h4>";
echo "<ol>";
echo "<li><strong>Login First:</strong> Go to admin_login.php and login</li>";
echo "<li><strong>Check Dashboard:</strong> Look for 'Manage Cars' in the menu</li>";
echo "<li><strong>Direct Access:</strong> Try admin_cars.php directly</li>";
echo "<li><strong>Clear Cache:</strong> Refresh browser and clear cache</li>";
echo "<li><strong>Check Errors:</strong> Look for PHP error messages</li>";
echo "</ol>";
echo "</div>";

// Test 7: Create emergency admin car access
echo "<h3>Test 7: Emergency Admin Car Access</h3>";

echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🚨 Emergency Access Method:</h4>";
echo "<p>If the normal admin panel isn't working, you can:</p>";
echo "<ol>";
echo "<li><strong>Login as Admin:</strong> Use admin_login.php</li>";
echo "<li><strong>Direct URL:</strong> Go directly to admin_cars.php</li>";
echo "<li><strong>Check Browser Console:</strong> Look for JavaScript errors</li>";
echo "<li><strong>Check PHP Logs:</strong> Look for server-side errors</li>";
echo "</ol>";
echo "</div>";

// Test 8: Quick admin login test
echo "<h3>Test 8: Quick Admin Login Test</h3>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔑 Admin Login Credentials:</h4>";
echo "<p><strong>Default Admin Login:</strong></p>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> admin123</li>";
echo "</ul>";
echo "<p><a href='admin_login.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Login as Admin Now</a></p>";
echo "</div>";

// Final recommendations
echo "<h3>🎯 Recommended Next Steps</h3>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
echo "<h4 style='color: #155724;'>✅ Step-by-Step Solution:</h4>";
echo "<ol style='color: #155724;'>";
echo "<li><strong>Login:</strong> Go to admin_login.php and login with admin/admin123</li>";
echo "<li><strong>Dashboard:</strong> Check if 'Manage Cars' appears in the admin dashboard menu</li>";
echo "<li><strong>Direct Access:</strong> If menu missing, go directly to admin_cars.php</li>";
echo "<li><strong>Test Functions:</strong> Try adding a test car to verify functionality</li>";
echo "<li><strong>Report Issues:</strong> If still not working, check browser console for errors</li>";
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