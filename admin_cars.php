<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_admin'])){
    header("location: admin_login.php");
    exit();
}

$conn = Connect();
$message = '';
$action = $_GET['action'] ?? 'list';

// Check for session messages
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}

// Auto-fix: Make sure all non-rented cars are available
$auto_fix_sql = "UPDATE cars c 
                 LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                 SET c.car_availability = 'yes' 
                 WHERE rc.car_id IS NULL";
$conn->query($auto_fix_sql);

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_car'])) {
        $car_name = $conn->real_escape_string($_POST['car_name']);
        $car_nameplate = $conn->real_escape_string($_POST['car_nameplate']);
        $ac_price = floatval($_POST['ac_price']);
        $non_ac_price = floatval($_POST['non_ac_price']);
        $ac_price_per_day = floatval($_POST['ac_price_per_day']);
        $non_ac_price_per_day = floatval($_POST['non_ac_price_per_day']);
        $car_availability = $_POST['car_availability'] ?? 'yes'; // Default to available
        $assigned_client = $_POST['assigned_client'] ?? null;
        
        $car_img = 'assets/img/cars/default.jpg'; // Default image
        
        // Handle image upload
        if (!empty($_FILES["car_image"]["name"])) {
            $target_dir = "assets/img/cars/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Generate unique filename to avoid conflicts
            $file_extension = pathinfo($_FILES["car_image"]["name"], PATHINFO_EXTENSION);
            $unique_filename = "car_admin_" . time() . "." . $file_extension;
            $target_file = $target_dir . $unique_filename;
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($file_extension), $allowed_types)) {
                if (move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_file)) {
                    $car_img = $target_file;
                }
            }
        }
        
        // Start transaction for atomic operation
        $conn->begin_transaction();
        
        try {
            // Insert car
            $sql = "INSERT INTO cars (car_name, car_nameplate, car_img, ac_price, non_ac_price, ac_price_per_day, non_ac_price_per_day, car_availability) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssdddds", $car_name, $car_nameplate, $car_img, $ac_price, $non_ac_price, $ac_price_per_day, $non_ac_price_per_day, $car_availability);
            
            if ($stmt->execute()) {
                $car_id = $conn->insert_id;
                
                // If client is assigned, add to clientcars table
                if (!empty($assigned_client)) {
                    $clientcar_sql = "INSERT INTO clientcars (car_id, client_username) VALUES (?, ?)";
                    $clientcar_stmt = $conn->prepare($clientcar_sql);
                    $clientcar_stmt->bind_param("is", $car_id, $assigned_client);
                    
                    if (!$clientcar_stmt->execute()) {
                        throw new Exception("Failed to assign car to client: " . $conn->error);
                    }
                }
                
                $conn->commit();
                $client_msg = $assigned_client ? " and assigned to client: $assigned_client" : " (available to all clients)";
                $message = '<div class="alert alert-success">Car added successfully' . $client_msg . '!</div>';
                $action = 'list';
            } else {
                throw new Exception("Failed to add car: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = '<div class="alert alert-danger">Error adding car: ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['update_car'])) {
        // Validate required fields
        if (empty($_POST['car_id']) || empty($_POST['car_name']) || empty($_POST['car_nameplate']) || 
            empty($_POST['ac_price']) || empty($_POST['non_ac_price']) || 
            empty($_POST['ac_price_per_day']) || empty($_POST['non_ac_price_per_day'])) {
            $message = '<div class="alert alert-danger">All fields are required for car update!</div>';
        } else {
            $car_id = intval($_POST['car_id']);
            $car_name = $conn->real_escape_string(trim($_POST['car_name']));
            $car_nameplate = $conn->real_escape_string(trim($_POST['car_nameplate']));
            $ac_price = floatval($_POST['ac_price']);
            $non_ac_price = floatval($_POST['non_ac_price']);
            $ac_price_per_day = floatval($_POST['ac_price_per_day']);
            $non_ac_price_per_day = floatval($_POST['non_ac_price_per_day']);
            $car_availability = $_POST['car_availability'];
            $assigned_client = $_POST['assigned_client'] ?? null;
            
            // Validate prices
            if ($ac_price <= 0 || $non_ac_price <= 0 || $ac_price_per_day <= 0 || $non_ac_price_per_day <= 0) {
                $message = '<div class="alert alert-danger">All prices must be greater than 0!</div>';
            } else {
                
                // Start transaction for atomic operation
                $conn->begin_transaction();
                
                try {
        
        // Get current car image
        $current_img_sql = "SELECT car_img FROM cars WHERE car_id = ?";
        $current_stmt = $conn->prepare($current_img_sql);
        $current_stmt->bind_param("i", $car_id);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();
        $current_car = $current_result->fetch_assoc();
        $car_img = $current_car['car_img']; // Keep current image by default
        
        // Handle image upload if new image is provided
        if (!empty($_FILES["car_image"]["name"])) {
            $target_dir = "assets/img/cars/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Generate unique filename to avoid conflicts
            $file_extension = pathinfo($_FILES["car_image"]["name"], PATHINFO_EXTENSION);
            $unique_filename = "car_" . $car_id . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $unique_filename;
            
            // Validate file type
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($file_extension), $allowed_types)) {
                if (move_uploaded_file($_FILES["car_image"]["tmp_name"], $target_file)) {
                    // Delete old image if it's not the default
                    if ($current_car['car_img'] && $current_car['car_img'] != 'assets/img/cars/default.jpg' && file_exists($current_car['car_img'])) {
                        unlink($current_car['car_img']);
                    }
                    $car_img = $target_file;
                } else {
                    $message = '<div class="alert alert-warning">Car updated but image upload failed.</div>';
                }
            } else {
                $message = '<div class="alert alert-warning">Car updated but invalid image format. Please use JPG, PNG, or GIF.</div>';
            }
        }
        
                    // Update car details
                    $sql = "UPDATE cars SET car_name=?, car_nameplate=?, car_img=?, ac_price=?, non_ac_price=?, ac_price_per_day=?, non_ac_price_per_day=?, car_availability=? WHERE car_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssddddsi", $car_name, $car_nameplate, $car_img, $ac_price, $non_ac_price, $ac_price_per_day, $non_ac_price_per_day, $car_availability, $car_id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update car: " . $conn->error);
                    }
                    
                    // Handle client assignment changes
                    // First, remove existing client assignment
                    $remove_client_sql = "DELETE FROM clientcars WHERE car_id = ?";
                    $remove_stmt = $conn->prepare($remove_client_sql);
                    $remove_stmt->bind_param("i", $car_id);
                    $remove_stmt->execute();
                    
                    // If new client is assigned, add it
                    if (!empty($assigned_client)) {
                        $add_client_sql = "INSERT INTO clientcars (car_id, client_username) VALUES (?, ?)";
                        $add_client_stmt = $conn->prepare($add_client_sql);
                        $add_client_stmt->bind_param("is", $car_id, $assigned_client);
                        
                        if (!$add_client_stmt->execute()) {
                            throw new Exception("Failed to assign car to client: " . $conn->error);
                        }
                    }
                    
                    $conn->commit();
                    $client_msg = $assigned_client ? " and assigned to client: $assigned_client" : " (available to all clients)";
                    $message = '<div class="alert alert-success">Car updated successfully' . $client_msg . '!</div>';
                    $action = 'list';
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = '<div class="alert alert-danger">Error updating car: ' . $e->getMessage() . '</div>';
                }
            }
        }
    }
}

// Handle make all cars available
if (isset($_GET['make_all_available'])) {
    $make_all_sql = "UPDATE cars c 
                     LEFT JOIN rentedcars rc ON c.car_id = rc.car_id AND rc.return_status = 'NR'
                     SET c.car_availability = 'yes' 
                     WHERE rc.car_id IS NULL";
    $make_all_result = $conn->query($make_all_sql);
    if ($make_all_result) {
        $affected = $conn->affected_rows;
        $message = '<div class="alert alert-success"><strong>Success!</strong> ' . $affected . ' cars have been made available for booking!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error making cars available: ' . $conn->error . '</div>';
    }
}

// Handle quick availability toggle
if (isset($_GET['quick_available'])) {
    $car_id = intval($_GET['quick_available']);
    $quick_sql = "UPDATE cars SET car_availability = 'yes' WHERE car_id = ?";
    $quick_stmt = $conn->prepare($quick_sql);
    $quick_stmt->bind_param("i", $car_id);
    if ($quick_stmt->execute()) {
        $message = '<div class="alert alert-success">Car marked as available!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating car availability.</div>';
    }
}

if (isset($_GET['quick_unavailable'])) {
    $car_id = intval($_GET['quick_unavailable']);
    $quick_sql = "UPDATE cars SET car_availability = 'no' WHERE car_id = ?";
    $quick_stmt = $conn->prepare($quick_sql);
    $quick_stmt->bind_param("i", $car_id);
    if ($quick_stmt->execute()) {
        $message = '<div class="alert alert-warning">Car marked as unavailable!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating car availability.</div>';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $car_id = $_GET['delete'];
    
    // Check if car has bookings
    $check_sql = "SELECT COUNT(*) as count FROM rentedcars WHERE car_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $car_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = '<div class="alert alert-warning">Cannot delete car with existing bookings. Set to unavailable instead.</div>';
    } else {
        // Start transaction for safe deletion
        $conn->begin_transaction();
        
        try {
            // First delete from clientcars table (foreign key constraint)
            $delete_clientcars_sql = "DELETE FROM clientcars WHERE car_id = ?";
            $stmt1 = $conn->prepare($delete_clientcars_sql);
            $stmt1->bind_param("i", $car_id);
            $stmt1->execute();
            
            // Then delete from cars table
            $delete_car_sql = "DELETE FROM cars WHERE car_id = ?";
            $stmt2 = $conn->prepare($delete_car_sql);
            $stmt2->bind_param("i", $car_id);
            $stmt2->execute();
            
            // Commit transaction
            $conn->commit();
            $message = '<div class="alert alert-success">Car deleted successfully!</div>';
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $message = '<div class="alert alert-danger">Error deleting car: ' . $e->getMessage() . '</div>';
        }
    }
}

// Get car for editing
$edit_car = null;
$current_client = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $car_id = intval($_GET['id']); // Ensure it's an integer
    $sql = "SELECT c.*, cc.client_username as current_client FROM cars c 
            LEFT JOIN clientcars cc ON c.car_id = cc.car_id 
            WHERE c.car_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_car = $result->fetch_assoc();
        
        if ($edit_car) {
            $current_client = $edit_car['current_client'];
        } else {
            $message = '<div class="alert alert-danger">Car with ID ' . htmlspecialchars($car_id) . ' not found! Please check if the car exists.</div>';
            $action = 'list';
        }
    } else {
        $message = '<div class="alert alert-danger">Database error: Could not prepare statement for car retrieval.</div>';
        $action = 'list';
    }
}

// Get all cars with client information and booking count
$cars_sql = "SELECT c.*, cc.client_username, cl.client_name, COUNT(rc.id) as booking_count,
             CASE WHEN rc_active.car_id IS NOT NULL THEN 'Currently Rented' ELSE 'Available' END as rental_status
             FROM cars c 
             LEFT JOIN clientcars cc ON c.car_id = cc.car_id
             LEFT JOIN clients cl ON cc.client_username = cl.client_username
             LEFT JOIN rentedcars rc ON c.car_id = rc.car_id 
             LEFT JOIN rentedcars rc_active ON c.car_id = rc_active.car_id AND rc_active.return_status = 'NR'
             GROUP BY c.car_id 
             ORDER BY c.car_id DESC";
$cars_result = $conn->query($cars_sql);

// Get all clients for dropdown
$clients_sql = "SELECT client_username, client_name FROM clients ORDER BY client_name";
$clients_result = $conn->query($clients_sql);
$clients = [];
if ($clients_result) {
    while ($client = $clients_result->fetch_assoc()) {
        $clients[] = $client;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Cars | Admin Panel</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/P.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <style>
        .admin-sidebar {
            background: #2c3e50;
            min-height: 100vh;
            padding: 0;
        }
        .admin-content {
            padding: 20px;
            background: #ecf0f1;
            min-height: 100vh;
        }
        .sidebar-header {
            background: #34495e;
            padding: 20px;
            color: white;
            text-align: center;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu li {
            border-bottom: 1px solid #34495e;
        }
        .sidebar-menu li a {
            display: block;
            padding: 15px 20px;
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu li a:hover {
            background: #3498db;
            color: white;
            text-decoration: none;
        }
        .sidebar-menu li a.active {
            background: #3498db;
            color: white;
        }
        .admin-header {
            background: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 admin-sidebar">
            <div class="sidebar-header">
                <h4><i class="fa fa-shield"></i> Admin Panel</h4>
                <small>Car Rental System</small>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="admin_cars.php" class="active"><i class="fa fa-car"></i> Manage Cars</a></li>
                <li><a href="admin_customers.php"><i class="fa fa-users"></i> Manage Customers</a></li>
                <li><a href="admin_drivers.php"><i class="fa fa-id-card"></i> Manage Drivers</a></li>
                <li><a href="admin_bookings.php"><i class="fa fa-calendar"></i> Manage Bookings</a></li>
                <li><a href="admin_clients.php"><i class="fa fa-briefcase"></i> Manage Clients</a></li>
                <li><a href="admin_reports.php"><i class="fa fa-chart-bar"></i> Reports</a></li>

                <li><a href="admin_settings.php"><i class="fa fa-cog"></i> Settings</a></li>
                <li><a href="admin_logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10 admin-content">
            <!-- Header -->
            <div class="admin-header">
                <div class="row">
                    <div class="col-md-6">
                        <h2><i class="fa fa-car"></i> Manage Cars</h2>
                        <p class="text-muted">Complete CRUD operations for car management</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <?php if ($action == 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Car
                        </a>
                        <?php else: ?>
                        <a href="admin_cars.php" class="btn btn-default">
                            <i class="fa fa-list"></i> Back to List
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php echo $message; ?>
            
            <?php if ($action == 'list'): ?>
            <!-- Car Statistics Summary -->
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h4><i class="fa fa-dashboard"></i> Car Availability Dashboard</h4>
                        </div>
                        <div class="panel-body">
                            <?php
                            // Get car statistics
                            $stats_sql = "SELECT 
                                            COUNT(*) as total_cars,
                                            SUM(CASE WHEN car_availability = 'yes' THEN 1 ELSE 0 END) as available_cars,
                                            SUM(CASE WHEN car_availability = 'no' THEN 1 ELSE 0 END) as unavailable_cars,
                                            COUNT(DISTINCT cc.client_username) as clients_with_cars,
                                            SUM(CASE WHEN cc.client_username IS NULL THEN 1 ELSE 0 END) as unassigned_cars
                                          FROM cars c 
                                          LEFT JOIN clientcars cc ON c.car_id = cc.car_id";
                            $stats_result = $conn->query($stats_sql);
                            $stats = $stats_result->fetch_assoc();
                            
                            // Get currently rented cars
                            $rented_sql = "SELECT COUNT(*) as rented_cars FROM cars c 
                                          JOIN rentedcars rc ON c.car_id = rc.car_id 
                                          WHERE rc.return_status = 'NR'";
                            $rented_result = $conn->query($rented_sql);
                            $rented_stats = $rented_result->fetch_assoc();
                            ?>
                            
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h3 class="text-primary"><?php echo $stats['total_cars']; ?></h3>
                                        <p>Total Cars</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h3 class="text-success"><?php echo $stats['available_cars']; ?></h3>
                                        <p>Available Cars</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h3 class="text-danger"><?php echo $stats['unavailable_cars']; ?></h3>
                                        <p>Unavailable Cars</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h3 class="text-warning"><?php echo $rented_stats['rented_cars']; ?></h3>
                                        <p>Currently Rented</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h3 class="text-info"><?php echo $stats['clients_with_cars']; ?></h3>
                                        <p>Active Clients</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <h3 class="text-muted"><?php echo $stats['unassigned_cars']; ?></h3>
                                        <p>Unassigned Cars</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row" style="margin-top: 15px;">
                                <div class="col-md-12">
                                    <div class="btn-group">
                                        <a href="?make_all_available=1" class="btn btn-success" 
                                           onclick="return confirm('Make all cars available for booking?')">
                                            <i class="fa fa-check-circle"></i> Make All Cars Available
                                        </a>
                                        <a href="?action=add" class="btn btn-primary">
                                            <i class="fa fa-plus"></i> Add New Car
                                        </a>
                                        <a href="admin_dashboard.php" class="btn btn-default">
                                            <i class="fa fa-dashboard"></i> Back to Dashboard
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Car Form -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><?php echo $action == 'add' ? 'Add New Car' : 'Edit Car'; ?></h4>
                </div>
                <div class="panel-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="car_id" value="<?php echo $edit_car['car_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Car Name *</label>
                                    <input type="text" class="form-control" name="car_name" 
                                           value="<?php echo $edit_car['car_name'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Number Plate *</label>
                                    <input type="text" class="form-control" name="car_nameplate" 
                                           value="<?php echo $edit_car['car_nameplate'] ?? ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>AC Price (per km) *</label>
                                    <input type="number" step="0.01" class="form-control" name="ac_price" 
                                           value="<?php echo $edit_car['ac_price'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Non-AC Price (per km) *</label>
                                    <input type="number" step="0.01" class="form-control" name="non_ac_price" 
                                           value="<?php echo $edit_car['non_ac_price'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>AC Price (per day) *</label>
                                    <input type="number" step="0.01" class="form-control" name="ac_price_per_day" 
                                           value="<?php echo $edit_car['ac_price_per_day'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Non-AC Price (per day) *</label>
                                    <input type="number" step="0.01" class="form-control" name="non_ac_price_per_day" 
                                           value="<?php echo $edit_car['non_ac_price_per_day'] ?? ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Availability Status</label>
                                    <select class="form-control" name="car_availability">
                                        <option value="yes" <?php echo ($edit_car['car_availability'] ?? 'yes') == 'yes' ? 'selected' : ''; ?>>Available</option>
                                        <option value="no" <?php echo ($edit_car['car_availability'] ?? '') == 'no' ? 'selected' : ''; ?>>Not Available</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Assign to Client (Optional)</label>
                                    <select class="form-control" name="assigned_client">
                                        <option value="" <?php echo (empty($current_client)) ? 'selected' : ''; ?>>No Client (Available to All)</option>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo htmlspecialchars($client['client_username']); ?>"
                                                <?php echo ($current_client == $client['client_username']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['client_name']) . ' (' . $client['client_username'] . ')'; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Leave empty to make car available to all clients. Current: 
                                    <?php echo $current_client ? htmlspecialchars($current_client) : 'No Client (Available to All)'; ?></small> to all clients</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Car Image</label>
                                    <input type="file" class="form-control" name="car_image" accept="image/*" id="car_image" onchange="previewImage(this)">
                                    <small class="text-muted">Supported formats: JPG, PNG, GIF (Max 5MB)</small>
                                    
                                    <?php if ($action == 'edit' && $edit_car['car_img']): ?>
                                    <div style="margin-top: 10px;">
                                        <small class="text-muted">Current image:</small><br>
                                        <img src="<?php echo $edit_car['car_img']; ?>" alt="Current car image" 
                                             id="current_image"
                                             style="width: 120px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                                        <br><small class="text-info">Upload a new image to replace the current one</small>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Image Preview -->
                                    <div id="image_preview" style="margin-top: 10px; display: none;">
                                        <small class="text-success">New image preview:</small><br>
                                        <img id="preview_img" style="width: 120px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="<?php echo $action == 'add' ? 'add_car' : 'update_car'; ?>" 
                                    class="btn btn-success">
                                <i class="fa fa-save"></i> <?php echo $action == 'add' ? 'Add Car' : 'Update Car'; ?>
                            </button>
                            <a href="admin_cars.php" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Cars List -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>All Cars</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="4%">S.No</th>
                                    <th width="6%">Image</th>
                                    <th width="12%">Car Name</th>
                                    <th width="10%">Number Plate</th>
                                    <th width="12%">Assigned Client</th>
                                    <th width="8%">AC Price/km</th>
                                    <th width="8%">Non-AC Price/km</th>
                                    <th width="8%">AC Price/day</th>
                                    <th width="8%">Non-AC Price/day</th>
                                    <th width="8%">Availability</th>
                                    <th width="8%">Rental Status</th>
                                    <th width="6%">Bookings</th>
                                    <th width="12%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial_number = 1;
                                if ($cars_result && $cars_result->num_rows > 0):
                                while ($car = $cars_result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><strong><?php echo $serial_number++; ?></strong></td>
                                    <td>
                                        <img src="<?php echo $car['car_img']; ?>" alt="Car" class="car-image">
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($car['car_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($car['car_nameplate']); ?></td>
                                    <td>
                                        <?php if ($car['client_name']): ?>
                                            <strong><?php echo htmlspecialchars($car['client_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($car['client_username']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No Client</span>
                                            <br><small class="text-info">Available to All</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rs. <?php echo number_format($car['ac_price'], 2); ?></td>
                                    <td>Rs. <?php echo number_format($car['non_ac_price'], 2); ?></td>
                                    <td>Rs. <?php echo number_format($car['ac_price_per_day'], 2); ?></td>
                                    <td>Rs. <?php echo number_format($car['non_ac_price_per_day'], 2); ?></td>
                                    <td>
                                        <?php if ($car['car_availability'] == 'yes'): ?>
                                            <span class="label label-success">✅ Available</span>
                                        <?php else: ?>
                                            <span class="label label-danger">❌ Not Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($car['rental_status'] == 'Currently Rented'): ?>
                                            <span class="label label-warning">🚗 Rented</span>
                                        <?php else: ?>
                                            <span class="label label-info">🆓 Free</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $car['booking_count']; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $car['car_id']; ?>" 
                                               class="btn btn-primary btn-xs" title="Edit Car">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <?php if ($car['booking_count'] == 0): ?>
                                            <a href="?delete=<?php echo $car['car_id']; ?>" 
                                               class="btn btn-danger btn-xs" title="Delete Car"
                                               onclick="return confirm('Are you sure you want to permanently delete this car: <?php echo htmlspecialchars($car['car_name']); ?> (<?php echo htmlspecialchars($car['car_nameplate']); ?>)?\n\nThis action cannot be undone!')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <button class="btn btn-warning btn-xs" title="Cannot delete - has bookings" disabled>
                                                <i class="fa fa-lock"></i>
                                            </button>
                                            <?php endif; ?>
                                            <!-- Quick availability toggle -->
                                            <?php if ($car['car_availability'] == 'yes'): ?>
                                            <a href="?quick_unavailable=<?php echo $car['car_id']; ?>" 
                                               class="btn btn-warning btn-xs" title="Make Unavailable">
                                                <i class="fa fa-pause"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="?quick_available=<?php echo $car['car_id']; ?>" 
                                               class="btn btn-success btn-xs" title="Make Available">
                                                <i class="fa fa-play"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="13" class="text-center">
                                        <div class="alert alert-info">
                                            <h4>No Cars Found</h4>
                                            <p>No cars are currently in the system. <a href="?action=add" class="btn btn-primary btn-sm">Add the first car</a></p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('preview_img').src = e.target.result;
            document.getElementById('image_preview').style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
        
        // Validate file size (5MB limit)
        if (input.files[0].size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            input.value = '';
            document.getElementById('image_preview').style.display = 'none';
            return false;
        }
        
        // Validate file type
        var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(input.files[0].type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
            input.value = '';
            document.getElementById('image_preview').style.display = 'none';
            return false;
        }
    }
}

// Form validation
$(document).ready(function() {
    $('form').on('submit', function(e) {
        var carName = $('input[name="car_name"]').val().trim();
        var carPlate = $('input[name="car_nameplate"]').val().trim();
        var acPrice = parseFloat($('input[name="ac_price"]').val());
        var nonAcPrice = parseFloat($('input[name="non_ac_price"]').val());
        var acPriceDay = parseFloat($('input[name="ac_price_per_day"]').val());
        var nonAcPriceDay = parseFloat($('input[name="non_ac_price_per_day"]').val());
        
        // Check required fields
        if (!carName || !carPlate) {
            alert('Please fill in Car Name and Number Plate fields');
            e.preventDefault();
            return false;
        }
        
        // Check if prices are valid numbers
        if (isNaN(acPrice) || isNaN(nonAcPrice) || isNaN(acPriceDay) || isNaN(nonAcPriceDay)) {
            alert('Please enter valid numbers for all price fields');
            e.preventDefault();
            return false;
        }
        
        // Check if prices are positive
        if (acPrice <= 0 || nonAcPrice <= 0 || acPriceDay <= 0 || nonAcPriceDay <= 0) {
            alert('All prices must be greater than 0');
            e.preventDefault();
            return false;
        }
        
        // Show loading message
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        // Re-enable button after 10 seconds as fallback
        setTimeout(function() {
            submitBtn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Car');
        }, 10000);
    });
});
</script>

</body>
</html>