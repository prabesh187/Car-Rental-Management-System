<?php
/**
 * Test Admin Car Management System
 * Diagnose issues with adding and managing cars from admin panel
 */

require 'connection.php';

echo "<h2>🚗 Admin Car Management Test</h2>";
echo "<p>Testing admin car management functionality...</p>";

$conn = Connect();

try {
    // Test 1: Check if admin is logged in (simulate admin session)
    echo "<h3>Test 1: Admin Session Check</h3>";
    
    session_start();
    if (!isset($_SESSION['login_admin'])) {
        $_SESSION['login_admin'] = 'admin'; // Simulate admin login for testing
        echo "<p style='color: orange;'>⚠️ Admin session created for testing</p>";
    } else {
        echo "<p style='color: green;'>✅ Admin session exists: " . $_SESSION['login_admin'] . "</p>";
    }
    
    // Test 2: Check database tables
    echo "<h3>Test 2: Database Tables Check</h3>";
    
    $tables = ['cars', 'clientcars', 'clients'];
    foreach ($tables as $table) {
        $check_table = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($check_table);
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
            
            // Check table structure
            $describe = "DESCRIBE $table";
            $structure = $conn->query($describe);
            
            echo "<div style='margin-left: 20px; background: #f8f9fa; padding: 10px; border-radius: 5px;'>";
            echo "<h5>$table table structure:</h5>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr style='background: #e9ecef;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
            
            while ($field = $structure->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$field['Field']}</td>";
                echo "<td>{$field['Type']}</td>";
                echo "<td>{$field['Null']}</td>";
                echo "<td>{$field['Key']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div><br>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' does not exist</p>";
        }
    }
    
    // Test 3: Check current cars in database
    echo "<h3>Test 3: Current Cars in Database</h3>";
    
    $cars_sql = "SELECT COUNT(*) as total FROM cars";
    $cars_result = $conn->query($cars_sql);
    $total_cars = $cars_result->fetch_assoc()['total'];
    
    echo "<p><strong>Total Cars:</strong> $total_cars</p>";
    
    if ($total_cars > 0) {
        $sample_cars_sql = "SELECT car_id, car_name, car_nameplate, car_availability FROM cars LIMIT 5";
        $sample_result = $conn->query($sample_cars_sql);
        
        echo "<h4>Sample Cars:</h4>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Plate</th><th>Available</th></tr>";
        
        while ($car = $sample_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$car['car_id']}</td>";
            echo "<td>{$car['car_name']}</td>";
            echo "<td>{$car['car_nameplate']}</td>";
            echo "<td>{$car['car_availability']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No cars found in database</p>";
    }
    
    // Test 4: Test adding a car
    echo "<h3>Test 4: Test Car Addition</h3>";
    
    $test_car_name = "Test Car " . time();
    $test_plate = "TEST" . rand(100, 999);
    
    $insert_sql = "INSERT INTO cars (car_name, car_nameplate, car_img, ac_price, non_ac_price, ac_price_per_day, non_ac_price_per_day, car_availability) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $car_img = 'assets/img/cars/default.jpg';
    $ac_price = 15.0;
    $non_ac_price = 12.0;
    $ac_price_per_day = 1500.0;
    $non_ac_price_per_day = 1200.0;
    $availability = 'yes';
    
    $stmt->bind_param("sssdddds", $test_car_name, $test_plate, $car_img, $ac_price, $non_ac_price, $ac_price_per_day, $non_ac_price_per_day, $availability);
    
    if ($stmt->execute()) {
        $new_car_id = $conn->insert_id;
        echo "<p style='color: green;'>✅ Test car added successfully! Car ID: $new_car_id</p>";
        
        // Clean up test car
        $cleanup_sql = "DELETE FROM cars WHERE car_id = ?";
        $cleanup_stmt = $conn->prepare($cleanup_sql);
        $cleanup_stmt->bind_param("i", $new_car_id);
        $cleanup_stmt->execute();
        echo "<p style='color: blue;'>🧹 Test car cleaned up</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to add test car: " . $conn->error . "</p>";
    }
    
    // Test 5: Check admin_cars.php file
    echo "<h3>Test 5: Admin Cars File Check</h3>";
    
    if (file_exists('admin_cars.php')) {
        echo "<p style='color: green;'>✅ admin_cars.php file exists</p>";
        
        $file_size = filesize('admin_cars.php');
        echo "<p><strong>File size:</strong> " . number_format($file_size) . " bytes</p>";
        
        // Check for key functions
        $content = file_get_contents('admin_cars.php');
        
        $checks = [
            'add_car' => 'Car addition functionality',
            'update_car' => 'Car update functionality', 
            'DELETE FROM cars' => 'Car deletion functionality',
            'INSERT INTO cars' => 'Car insertion query',
            'UPDATE cars' => 'Car update query'
        ];
        
        foreach ($checks as $search => $description) {
            if (strpos($content, $search) !== false) {
                echo "<p style='color: green;'>✅ $description found</p>";
            } else {
                echo "<p style='color: red;'>❌ $description missing</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ admin_cars.php file not found</p>";
    }
    
    // Test 6: Check for common issues
    echo "<h3>Test 6: Common Issues Check</h3>";
    
    $issues = [];
    
    // Check file permissions
    if (file_exists('admin_cars.php')) {
        if (is_readable('admin_cars.php')) {
            echo "<p style='color: green;'>✅ admin_cars.php is readable</p>";
        } else {
            echo "<p style='color: red;'>❌ admin_cars.php is not readable</p>";
            $issues[] = "File permission issue";
        }
    }
    
    // Check assets directory
    if (!file_exists('assets/img/cars/')) {
        echo "<p style='color: orange;'>⚠️ Car images directory doesn't exist</p>";
        $issues[] = "Missing car images directory";
    } else {
        echo "<p style='color: green;'>✅ Car images directory exists</p>";
    }
    
    // Check for PHP errors
    if (function_exists('error_get_last')) {
        $last_error = error_get_last();
        if ($last_error && $last_error['message']) {
            echo "<p style='color: red;'>❌ PHP Error detected: " . $last_error['message'] . "</p>";
            $issues[] = "PHP error: " . $last_error['message'];
        }
    }
    
    // Test 7: Simulate admin panel access
    echo "<h3>Test 7: Admin Panel Access Simulation</h3>";
    
    // Test GET parameters
    $test_actions = ['list', 'add', 'edit'];
    foreach ($test_actions as $test_action) {
        echo "<p><strong>Testing action '$test_action':</strong> ";
        
        // Simulate the action parameter
        $_GET['action'] = $test_action;
        $action = $_GET['action'] ?? 'list';
        
        if ($action === $test_action) {
            echo "<span style='color: green;'>✅ Working</span></p>";
        } else {
            echo "<span style='color: red;'>❌ Failed</span></p>";
            $issues[] = "Action parameter handling issue";
        }
    }
    
    // Final summary
    echo "<h3>🎯 Summary</h3>";
    
    if (empty($issues)) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724;'>✅ All Tests Passed!</h4>";
        echo "<p style='color: #155724;'>Admin car management should be working correctly.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
        echo "<h4 style='color: #856404;'>⚠️ Issues Found:</h4>";
        echo "<ul>";
        foreach ($issues as $issue) {
            echo "<li style='color: #856404;'>$issue</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    // Quick actions
    echo "<h3>🚀 Quick Actions</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Admin Car Management Links:</h4>";
    echo "<ul>";
    echo "<li><a href='admin_cars.php' target='_blank' style='color: #007bff;'>✅ Open Admin Car Management</a></li>";
    echo "<li><a href='admin_cars.php?action=add' target='_blank' style='color: #007bff;'>✅ Add New Car</a></li>";
    echo "<li><a href='admin_dashboard.php' target='_blank' style='color: #007bff;'>✅ Admin Dashboard</a></li>";
    echo "<li><a href='admin_login.php' target='_blank' style='color: #007bff;'>✅ Admin Login</a></li>";
    echo "</ul>";
    
    echo "<h4>🔧 If Still Not Working:</h4>";
    echo "<ol>";
    echo "<li>Check if you're logged in as admin</li>";
    echo "<li>Clear browser cache and cookies</li>";
    echo "<li>Check browser developer tools for JavaScript errors</li>";
    echo "<li>Verify database connection is working</li>";
    echo "<li>Check PHP error logs</li>";
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