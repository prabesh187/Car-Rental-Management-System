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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_car'])) {
        $car_name = $conn->real_escape_string($_POST['car_name']);
        $car_nameplate = $conn->real_escape_string($_POST['car_nameplate']);
        $ac_price = $conn->real_escape_string($_POST['ac_price']);
        $non_ac_price = $conn->real_escape_string($_POST['non_ac_price']);
        $ac_price_per_day = $conn->real_escape_string($_POST['ac_price_per_day']);
        $non_ac_price_per_day = $conn->real_escape_string($_POST['non_ac_price_per_day']);
        $car_availability = $_POST['car_availability'];
        $assigned_client = $_POST['assigned_client'] ?? null; // Optional client assignment
        
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
            // Insert car into cars table
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
                $message = '<div class="alert alert-success">Car added successfully!' . 
                          ($assigned_client ? " Assigned to client: $assigned_client" : " (No client assigned - available to all)") . '</div>';
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
                
                // Start transaction
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
                            }
                        }
                    }
                    
                    // Update car details
                    $sql = "UPDATE cars SET car_name=?, car_nameplate=?, car_img=?, ac_price=?, non_ac_price=?, ac_price_per_day=?, non_ac_price_per_day=?, car_availability=? WHERE car_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssddddsi", $car_name, $car_nameplate, $car_img, $ac_price, $non_ac_price, $ac_price_per_day, $non_ac_price_per_day, $car_availability, $car_id);
                    
                    if ($stmt->execute()) {
                        // Handle client assignment changes
                        $current_client_sql = "SELECT client_username FROM clientcars WHERE car_id = ?";
                        $current_client_stmt = $conn->prepare($current_client_sql);
                        $current_client_stmt->bind_param("i", $car_id);
                        $current_client_stmt->execute();
                        $current_client_result = $current_client_stmt->get_result();
                        $current_client = $current_client_result->fetch_assoc();
                        
                        if ($assigned_client && !$current_client) {
                            // Assign to new client
                            $assign_sql = "INSERT INTO clientcars (car_id, client_username) VALUES (?, ?)";
                            $assign_stmt = $conn->prepare($assign_sql);
                            $assign_stmt->bind_param("is", $car_id, $assigned_client);
                            $assign_stmt->execute();
                        } elseif ($assigned_client && $current_client && $current_client['client_username'] != $assigned_client) {
                            // Change client assignment
                            $update_client_sql = "UPDATE clientcars SET client_username = ? WHERE car_id = ?";
                            $update_client_stmt = $conn->prepare($update_client_sql);
                            $update_client_stmt->bind_param("si", $assigned_client, $car_id);
                            $update_client_stmt->execute();
                        } elseif (!$assigned_client && $current_client) {
                            // Remove client assignment
                            $remove_client_sql = "DELETE FROM clientcars WHERE car_id = ?";
                            $remove_client_stmt = $conn->prepare($remove_client_sql);
                            $remove_client_stmt->bind_param("i", $car_id);
                            $remove_client_stmt->execute();
                        }
                        
                        $conn->commit();
                        $message = '<div class="alert alert-success">Car updated successfully!</div>';
                        $action = 'list';
                    } else {
                        throw new Exception("Failed to update car: " . $conn->error);
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $message = '<div class="alert alert-danger">Error updating car: ' . $e->getMessage() . '</div>';
                }
            }
        }
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
    $car_id = intval($_GET['id']);
    $sql = "SELECT * FROM cars WHERE car_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_car = $result->fetch_assoc();
        
        if ($edit_car) {
            // Get current client assignment
            $client_sql = "SELECT client_username FROM clientcars WHERE car_id = ?";
            $client_stmt = $conn->prepare($client_sql);
            $client_stmt->bind_param("i", $car_id);
            $client_stmt->execute();
            $client_result = $client_stmt->get_result();
            $client_data = $client_result->fetch_assoc();
            $current_client = $client_data['client_username'] ?? null;
        } else {
            $message = '<div class="alert alert-danger">Car with ID ' . htmlspecialchars($car_id) . ' not found!</div>';
            $action = 'list';
        }
    } else {
        $message = '<div class="alert alert-danger">Database error: Could not prepare statement for car retrieval.</div>';
        $action = 'list';
    }
}

// Get all cars with client information
$cars_sql = "SELECT c.*, cc.client_username, cl.client_name, COUNT(rc.id) as booking_count 
             FROM cars c 
             LEFT JOIN clientcars cc ON c.car_id = cc.car_id
             LEFT JOIN clients cl ON cc.client_username = cl.client_username
             LEFT JOIN rentedcars rc ON c.car_id = rc.car_id 
             GROUP BY c.car_id 
             ORDER BY c.car_id DESC";
$cars_result = $conn->query($cars_sql);

// Get all clients for dropdown
$clients_sql = "SELECT client_username, client_name FROM clients ORDER BY client_name";
$clients_result = $conn->query($clients_sql);
$clients = [];
while ($client = $clients_result->fetch_assoc()) {
    $clients[] = $client;
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
        .client-info {
            font-size: 0.9em;
            color: #666;
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
                <li><a href="admin_cars_fixed.php" class="active"><i class="fa fa-car"></i> Manage Cars</a></li>
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
                        <h2><i class="fa fa-car"></i> Manage Cars (Fixed Version)</h2>
                        <p class="text-muted">Complete CRUD operations with proper client relationships</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <?php if ($action == 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Car
                        </a>
                        <a href="admin_cars.php" class="btn btn-warning">
                            <i class="fa fa-arrow-left"></i> Back to Original
                        </a>
                        <?php else: ?>
                        <a href="admin_cars_fixed.php" class="btn btn-default">
                            <i class="fa fa-list"></i> Back to List
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php echo $message; ?>
            
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
                                        <option value="yes" <?php echo ($edit_car['car_availability'] ?? '') == 'yes' ? 'selected' : ''; ?>>Available</option>
                                        <option value="no" <?php echo ($edit_car['car_availability'] ?? '') == 'no' ? 'selected' : ''; ?>>Not Available</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Assign to Client (Optional)</label>
                                    <select class="form-control" name="assigned_client">
                                        <option value="">No Client (Available to All)</option>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo $client['client_username']; ?>" 
                                                <?php echo ($current_client == $client['client_username']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['client_name']) . ' (' . $client['client_username'] . ')'; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Leave empty to make car available to all clients</small>
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
                            <a href="admin_cars_fixed.php" class="btn btn-default">
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
                    <h4>All Cars with Client Assignments</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">S.No</th>
                                    <th width="8%">Image</th>
                                    <th width="15%">Car Name</th>
                                    <th width="12%">Number Plate</th>
                                    <th width="15%">Assigned Client</th>
                                    <th width="10%">AC Price/km</th>
                                    <th width="10%">Non-AC Price/km</th>
                                    <th width="8%">Status</th>
                                    <th width="7%">Bookings</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial_number = 1;
                                while ($car = $cars_result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><strong><?php echo $serial_number++; ?></strong></td>
                                    <td>
                                        <img src="<?php echo $car['car_img']; ?>" alt="Car" class="car-image">
                                    </td>
                                    <td><strong><?php echo $car['car_name']; ?></strong></td>
                                    <td><?php echo $car['car_nameplate']; ?></td>
                                    <td>
                                        <?php if ($car['client_name']): ?>
                                            <strong><?php echo htmlspecialchars($car['client_name']); ?></strong>
                                            <div class="client-info">(<?php echo htmlspecialchars($car['client_username']); ?>)</div>
                                        <?php else: ?>
                                            <span class="text-muted">No Client Assigned</span>
                                            <div class="client-info">(Available to All)</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rs. <?php echo $car['ac_price']; ?></td>
                                    <td>Rs. <?php echo $car['non_ac_price']; ?></td>
                                    <td>
                                        <?php if ($car['car_availability'] == 'yes'): ?>
                                            <span class="label label-success">Available</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Not Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge"><?php echo $car['booking_count']; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $car['car_id']; ?>" 
                                               class="btn btn-primary" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <?php if ($car['booking_count'] == 0): ?>
                                            <a href="?delete=<?php echo $car['car_id']; ?>" 
                                               class="btn btn-danger" title="Delete"
                                               onclick="return confirm('Are you sure you want to permanently delete this car: <?php echo htmlspecialchars($car['car_name']); ?> (<?php echo htmlspecialchars($car['car_nameplate']); ?>)?\n\nThis action cannot be undone!')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <button class="btn btn-warning btn-xs" title="Cannot delete - has bookings" disabled>
                                                <i class="fa fa-lock"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
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