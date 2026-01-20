<?php
/**
 * Admin CRUD Quick Test
 * Quick diagnosis of admin CRUD issues
 */

echo "<h2>🚨 Admin CRUD Quick Diagnosis</h2>";
echo "<p>Let's quickly identify why admin can't perform CRUD operations...</p>";

// Check 1: Session status
session_start();
echo "<h3>🔍 Issue #1: Login Status</h3>";

if (isset($_SESSION['login_admin'])) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<p style='color: #155724;'>✅ Admin is logged in: " . $_SESSION['login_admin'] . "</p>";
    echo "</div>";
    $logged_in = true;
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<p style='color: #721c24;'>❌ <strong>MAIN ISSUE: Admin not logged in!</strong></p>";
    echo "<p>This is why CRUD operations don't work.</p>";
    echo "<p><strong>SOLUTION:</strong> <a href='admin_login.php' target='_blank' style='color: #007bff;'>Login as admin first</a></p>";
    echo "</div>";
    $logged_in = false;
}

// Check 2: File accessibility
echo "<h3>🔍 Issue #2: File Access</h3>";

$files = ['admin_cars.php', 'admin_dashboard.php', 'admin_login.php'];
$all_files_ok = true;

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file - Available</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - Missing</p>";
        $all_files_ok = false;
    }
}

// Check 3: Database connection
echo "<h3>🔍 Issue #3: Database Connection</h3>";

try {
    require 'connection.php';
    $conn = Connect();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection working</p>";
        
        // Quick table check
        $result = $conn->query("SELECT COUNT(*) as count FROM cars");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "<p style='color: blue;'>ℹ️ Cars in database: $count</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Provide solutions based on findings
echo "<h3>🎯 Solutions</h3>";

if (!$logged_in) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; border: 2px solid #ffc107;'>";
    echo "<h4 style='color: #856404;'>🔑 STEP 1: Login as Admin</h4>";
    echo "<p style='color: #856404;'>You must login before using CRUD operations.</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h5>Login Instructions:</h5>";
    echo "<ol>";
    echo "<li>Go to <a href='admin_login.php' target='_blank'>admin_login.php</a></li>";
    echo "<li>Enter username: <strong>admin</strong></li>";
    echo "<li>Enter password: <strong>admin123</strong></li>";
    echo "<li>Click login</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<p><a href='admin_login.php' target='_blank' style='background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>LOGIN NOW</a></p>";
    echo "</div>";
    
} else if ($all_files_ok) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; border: 2px solid #28a745;'>";
    echo "<h4 style='color: #155724;'>✅ STEP 2: Try CRUD Operations</h4>";
    echo "<p style='color: #155724;'>Admin is logged in and files exist. CRUD should work now.</p>";
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin: 15px 0;'>";
    
    echo "<a href='admin_cars.php?action=add' target='_blank' style='background: #28a745; color: white; padding: 12px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
    echo "➕ ADD CAR";
    echo "</a>";
    
    echo "<a href='admin_cars.php' target='_blank' style='background: #007bff; color: white; padding: 12px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
    echo "✏️ EDIT CARS";
    echo "</a>";
    
    echo "<a href='admin_cars.php' target='_blank' style='background: #dc3545; color: white; padding: 12px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
    echo "🗑️ DELETE CARS";
    echo "</a>";
    
    echo "<a href='admin_dashboard.php' target='_blank' style='background: #6c757d; color: white; padding: 12px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
    echo "📊 DASHBOARD";
    echo "</a>";
    
    echo "</div>";
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; border: 2px solid #dc3545;'>";
    echo "<h4 style='color: #721c24;'>❌ STEP 2: Fix Missing Files</h4>";
    echo "<p style='color: #721c24;'>Some admin files are missing and need to be restored.</p>";
    echo "</div>";
}

// Quick troubleshooting
echo "<h3>🔧 Quick Troubleshooting</h3>";

echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>";
echo "<h4>If CRUD still doesn't work after login:</h4>";
echo "<ol>";
echo "<li><strong>Clear browser cache</strong> - Press Ctrl+F5</li>";
echo "<li><strong>Check browser console</strong> - Press F12, look for errors</li>";
echo "<li><strong>Try different browser</strong> - Use Chrome, Firefox, or Edge</li>";
echo "<li><strong>Check URL</strong> - Make sure you're on the right admin_cars.php</li>";
echo "<li><strong>Refresh session</strong> - Logout and login again</li>";
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
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>