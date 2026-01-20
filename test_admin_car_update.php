<!DOCTYPE html>
<html>
<head>
    <title>Test Car Update Functionality</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <script src="assets/js/jquery.min.js"></script>
</head>
<body>
<div class="container">
    <h2>Car Update Functionality Test</h2>
    
    <?php
    session_start();
    require 'connection.php';
    
    // Set admin session for testing
    $_SESSION['login_admin'] = 'admin';
    
    $conn = Connect();
    $message = '';
    
    // Get a test car
    $test_car_sql = "SELECT * FROM cars LIMIT 1";
    $test_car_result = $conn->query($test_car_sql);
    
    if ($test_car_result && $test_car_result->num_rows > 0) {
        $test_car = $test_car_result->fetch_assoc();
        
        echo "<div class='alert alert-info'>";
        echo "<h4>Test Car Found:</h4>";
        echo "<p><strong>ID:</strong> " . $test_car['car_id'] . "</p>";
        echo "<p><strong>Name:</strong> " . $test_car['car_name'] . "</p>";
        echo "<p><strong>Nameplate:</strong> " . $test_car['car_nameplate'] . "</p>";
        echo "<p><strong>AC Price:</strong> " . $test_car['ac_price'] . "</p>";
        echo "<p><strong>Non-AC Price:</strong> " . $test_car['non_ac_price'] . "</p>";
        echo "</div>";
        
        // Handle form submission
        if (isset($_POST['test_update'])) {
            $car_id = intval($_POST['car_id']);
            $car_name = $conn->real_escape_string(trim($_POST['car_name']));
            $car_nameplate = $conn->real_escape_string(trim($_POST['car_nameplate']));
            $ac_price = floatval($_POST['ac_price']);
            $non_ac_price = floatval($_POST['non_ac_price']);
            $ac_price_per_day = floatval($_POST['ac_price_per_day']);
            $non_ac_price_per_day = floatval($_POST['non_ac_price_per_day']);
            $car_availability = $_POST['car_availability'];
            $car_img = $test_car['car_img']; // Keep existing image
            
            echo "<div class='alert alert-warning'>";
            echo "<h4>Processing Update:</h4>";
            echo "<p>Car ID: $car_id</p>";
            echo "<p>Car Name: $car_name</p>";
            echo "<p>AC Price: $ac_price</p>";
            echo "</div>";
            
            $sql = "UPDATE cars SET car_name=?, car_nameplate=?, car_img=?, ac_price=?, non_ac_price=?, ac_price_per_day=?, non_ac_price_per_day=?, car_availability=? WHERE car_id=?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("sssddddsi", $car_name, $car_nameplate, $car_img, $ac_price, $non_ac_price, $ac_price_per_day, $non_ac_price_per_day, $car_availability, $car_id);
                
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $message = '<div class="alert alert-success">✓ Car updated successfully! Affected rows: ' . $stmt->affected_rows . '</div>';
                        
                        // Get updated car data
                        $verify_sql = "SELECT * FROM cars WHERE car_id = ?";
                        $verify_stmt = $conn->prepare($verify_sql);
                        $verify_stmt->bind_param("i", $car_id);
                        $verify_stmt->execute();
                        $updated_car = $verify_stmt->get_result()->fetch_assoc();
                        
                        echo "<div class='alert alert-success'>";
                        echo "<h4>Updated Car Data:</h4>";
                        echo "<p><strong>Name:</strong> " . $updated_car['car_name'] . "</p>";
                        echo "<p><strong>Nameplate:</strong> " . $updated_car['car_nameplate'] . "</p>";
                        echo "<p><strong>AC Price:</strong> " . $updated_car['ac_price'] . "</p>";
                        echo "<p><strong>Non-AC Price:</strong> " . $updated_car['non_ac_price'] . "</p>";
                        echo "<p><strong>AC Price/Day:</strong> " . $updated_car['ac_price_per_day'] . "</p>";
                        echo "<p><strong>Non-AC Price/Day:</strong> " . $updated_car['non_ac_price_per_day'] . "</p>";
                        echo "<p><strong>Availability:</strong> " . $updated_car['car_availability'] . "</p>";
                        echo "</div>";
                        
                    } else {
                        $message = '<div class="alert alert-warning">No rows were affected. Car may not exist or no changes were made.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Error executing update: ' . $stmt->error . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Error preparing statement: ' . $conn->error . '</div>';
            }
        }
        
        echo $message;
        ?>
        
        <form method="POST" class="form-horizontal">
            <input type="hidden" name="car_id" value="<?php echo $test_car['car_id']; ?>">
            
            <div class="form-group">
                <label class="col-sm-2 control-label">Car Name:</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="car_name" 
                           value="<?php echo isset($_POST['car_name']) ? $_POST['car_name'] : $test_car['car_name']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-2 control-label">Number Plate:</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="car_nameplate" 
                           value="<?php echo isset($_POST['car_nameplate']) ? $_POST['car_nameplate'] : $test_car['car_nameplate']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-2 control-label">AC Price (per km):</label>
                <div class="col-sm-4">
                    <input type="number" step="0.01" class="form-control" name="ac_price" 
                           value="<?php echo isset($_POST['ac_price']) ? $_POST['ac_price'] : $test_car['ac_price']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-2 control-label">Non-AC Price (per km):</label>
                <div class="col-sm-4">
                    <input type="number" step="0.01" class="form-control" name="non_ac_price" 
                           value="<?php echo isset($_POST['non_ac_price']) ? $_POST['non_ac_price'] : $test_car['non_ac_price']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-2 control-label">AC Price (per day):</label>
                <div class="col-sm-4">
                    <input type="number" step="0.01" class="form-control" name="ac_price_per_day" 
                           value="<?php echo isset($_POST['ac_price_per_day']) ? $_POST['ac_price_per_day'] : $test_car['ac_price_per_day']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-2 control-label">Non-AC Price (per day):</label>
                <div class="col-sm-4">
                    <input type="number" step="0.01" class="form-control" name="non_ac_price_per_day" 
                           value="<?php echo isset($_POST['non_ac_price_per_day']) ? $_POST['non_ac_price_per_day'] : $test_car['non_ac_price_per_day']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="col-sm-2 control-label">Availability:</label>
                <div class="col-sm-4">
                    <select class="form-control" name="car_availability">
                        <option value="yes" <?php echo ($test_car['car_availability'] == 'yes') ? 'selected' : ''; ?>>Available</option>
                        <option value="no" <?php echo ($test_car['car_availability'] == 'no') ? 'selected' : ''; ?>>Not Available</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-4">
                    <button type="submit" name="test_update" class="btn btn-primary">Test Update Car</button>
                    <a href="admin_cars.php" class="btn btn-default">Go to Admin Cars</a>
                </div>
            </div>
        </form>
        
        <?php
    } else {
        echo '<div class="alert alert-danger">No cars found in database to test with.</div>';
    }
    
    $conn->close();
    ?>
    
    <div class="alert alert-info">
        <h4>Troubleshooting Steps:</h4>
        <ol>
            <li>Check if this test page works - if it does, the issue is in admin_cars.php form</li>
            <li>Verify admin session is set when accessing admin_cars.php</li>
            <li>Check browser console for JavaScript errors</li>
            <li>Ensure all form fields are properly filled</li>
            <li>Check if the edit URL has the correct car ID parameter</li>
        </ol>
    </div>
</div>
</body>
</html>