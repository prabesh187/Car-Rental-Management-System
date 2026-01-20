<?php
/**
 * Debug Admin CRUD Issues
 * Investigate why admin can't perform CRUD operations on manage car menu
 */

require 'connection.php';

echo "<h2>🔍 Debug Admin CRUD Issues</h2>";
echo "<p>Investigating why admin can't perform CRUD operations on manage car menu...</p>";

$conn = Connect();

// Test 1: Check admin login status
echo "<h3>Test 1: Admin Authentication Status</h3>";
session_start();

if (isset($_SESSION['login_admin'])) {
    echo "<p style='color: green;'>✅ Admin is logged in: " . $_SESSION['login_admin'] . "</p>";
    $admin_logged_in = true;
} else {
    echo "<p style='color: red;'>❌ Admin is NOT logged in - This is the main issue!</p>";
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h4 style='color: #721c24;'>🚨 SOLUTION: Login Required</h4>";
    echo "<p>Admin must login first before accessing car management.</p>";
    echo "<p><strong>Login at:</strong> <a href='admin_login.php' target='_blank'>admin_login.php</a></p>";
    echo "<p><strong>Credentials:</strong> admin / admin123</p>";
    echo "</div>";
    $admin_logged_in = false;
}

// Test 2: Check admin_cars.php file accessibility
echo "<h3>Test 2: Admin Cars File Check</h3>";

if (file_exists('admin_cars.php')) {
    echo "<p style='color: green;'>✅ admin_cars.php file exists</p>";
    
    // Check if file has CRUD functionality
    $file_content = file_get_contents('admin_cars.php');
    
    $crud_checks = [
        'add_car' => 'Add Car functionality',
        'update_car' => 'Update Car functionality', 
        'delete' => 'Delete Car functionality',
        'action=edit' => 'Edit Car functionality',
        'INSERT INTO cars' => 'Database INSERT operation',
        'UPDATE cars' => 'Database UPDATE operation',
        'DELETE FROM cars' => 'Database DELETE operation'
    ];
    
    foreach ($crud_checks as $check => $description) {
        if (strpos($file_content, $check) !== false) {
            echo "<p style='color: green;'>✅ $description - Found</p>";
        } else {
            echo "<p style='color: red;'>❌ $description - Missing</p>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ admin_cars.php file missing</p>";
}

// Test 3: Database connectivity and tables
echo "<h3>Test 3: Database and Tables Check</h3>";

try {
    // Check cars table
    $cars_check = $conn->query("DESCRIBE cars");
    if ($cars_check) {
        echo "<p style='color: green;'>✅ Cars table exists and accessible</p>";
        
        // Check if we can perform basic operations
        $count_result = $conn->query("SELECT COUNT(*) as count FROM cars");
        $count = $count_result->fetch_assoc()['count'];
        echo "<p style='color: blue;'>ℹ️ Current cars in database: $count</p>";
    } else {
        echo "<p style='color: red;'>❌ Cars table not accessible</p>";
    }
    
    // Test INSERT capability
    $test_insert = $conn->prepare("SELECT 1");
    if ($test_insert) {
        echo "<p style='color: green;'>✅ Database prepared statements working</p>";
    } else {
        echo "<p style='color: red;'>❌ Database prepared statements not working</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 4: Check for specific CRUD errors
echo "<h3>Test 4: CRUD Operations Test</h3>";

if ($admin_logged_in) {
    // Test CREATE (Add)
    echo "<h4>CREATE (Add Car) Test:</h4>";
    try {
        $test_name = "Test Car " . time();
        $test_plate = "TEST" . rand(100, 999);
        
        $sql = "INSERT INTO cars (car_name, car_nameplate, car_img, ac_price, non_ac_price, ac_price_per_day, non_ac_price_per_day, car_availability) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdddds", $test_name, $test_plate, "default.jpg", 15.0, 12.0, 800.0, 600.0, "yes");
        
        if ($stmt->execute()) {
            $test_car_id = $conn->insert_id;
            echo "<p style='color: green;'>✅ CREATE operation working</p>";
            
            // Test READ
            echo "<h4>READ (View Car) Test:</h4>";
            $read_sql = "SELECT * FROM cars WHERE car_id = ?";
            $read_stmt = $conn->prepare($read_sql);
            $read_stmt->bind_param("i", $test_car_id);
            $read_stmt->execute();
            $result = $read_stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo "<p style='color: green;'>✅ READ operation working</p>";
                
                // Test UPDATE
                echo "<h4>UPDATE (Edit Car) Test:</h4>";
                $update_sql = "UPDATE cars SET car_name = ? WHERE car_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $updated_name = $test_name . " (Updated)";
                $update_stmt->bind_param("si", $updated_name, $test_car_id);
                
                if ($update_stmt->execute()) {
                    echo "<p style='color: green;'>✅ UPDATE operation working</p>";
                } else {
                    echo "<p style='color: red;'>❌ UPDATE operation failed</p>";
                }
                
                // Test DELETE
                echo "<h4>DELETE (Remove Car) Test:</h4>";
                $delete_sql = "DELETE FROM cars WHERE car_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $test_car_id);
                
                if ($delete_stmt->execute()) {
                    echo "<p style='color: green;'>✅ DELETE operation working</p>";
                } else {
                    echo "<p style='color: red;'>❌ DELETE operation failed</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ READ operation failed</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ CREATE operation failed: " . $conn->error . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ CRUD test error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Skipping CRUD tests - Admin not logged in</p>";
}

// Test 5: Check admin panel navigation
echo "<h3>Test 5: Admin Panel Navigation Check</h3>";

if (file_exists('admin_dashboard.php')) {
    $dashboard_content = file_get_contents('admin_dashboard.php');
    
    if (strpos($dashboard_content, 'admin_cars.php') !== false) {
        echo "<p style='color: green;'>✅ Car management link exists in dashboard</p>";
    } else {
        echo "<p style='color: red;'>❌ Car management link missing from dashboard</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Admin dashboard missing</p>";
}

// Test 6: Common issues and solutions
echo "<h3>Test 6: Common Issues & Solutions</h3>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h4>🔍 Most Common CRUD Issues:</h4>";
echo "<ol>";
echo "<li><strong>Not Logged In:</strong> Admin must login first</li>";
echo "<li><strong>Session Expired:</strong> Login session timed out</li>";
echo "<li><strong>File Permissions:</strong> Web server can't write to files</li>";
echo "<li><strong>Database Permissions:</strong> Database user lacks CRUD permissions</li>";
echo "<li><strong>PHP Errors:</strong> Syntax errors preventing execution</li>";
echo "<li><strong>Form Issues:</strong> HTML form not submitting correctly</li>";
echo "</ol>";
echo "</div>";

// Test 7: Provide immediate solutions
echo "<h3>Test 7: Immediate Solutions</h3>";

if (!$admin_logged_in) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #dc3545;'>";
    echo "<h4 style='color: #721c24;'>🚨 PRIMARY ISSUE: Admin Not Logged In</h4>";
    echo "<p style='color: #721c24;'>This is likely why CRUD operations aren't working.</p>";
    
    echo "<div style='background: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h5>🔑 SOLUTION: Login First</h5>";
    echo "<ol>";
    echo "<li>Go to <a href='admin_login.php' target='_blank'>admin_login.php</a></li>";
    echo "<li>Login with: <strong>admin</strong> / <strong>admin123</strong></li>";
    echo "<li>Then try <a href='admin_cars.php' target='_blank'>admin_cars.php</a></li>";
    echo "</ol>";
    echo "</div>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h4 style='color: #155724;'>✅ Admin Logged In - Try These Links</h4>";
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 15px 0;'>";
    
    echo "<a href='admin_cars.php' target='_blank' style='background: #007bff; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
    echo "🚗 Manage Cars";
    echo "</a>";
    
    echo "<a href='admin_cars.php?action=add' target='_blank' style='background: #28a745; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
    echo "➕ Add Car";
    echo "</a>";
    
    echo "<a href='admin_dashboard.php' target='_blank' style='background: #6c757d; color: white; padding: 15px; text-decoration: none; border-radius: 5px; text-align: center; display: block;'>";
    echo "📊 Dashboard";
    echo "</a>";
    
    echo "</div>";
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