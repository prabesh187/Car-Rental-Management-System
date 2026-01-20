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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_driver'])) {
        $driver_name = $conn->real_escape_string($_POST['driver_name']);
        $dl_number = $conn->real_escape_string($_POST['dl_number']);
        $driver_phone = $conn->real_escape_string($_POST['driver_phone']);
        $driver_address = $conn->real_escape_string($_POST['driver_address']);
        $driver_gender = $_POST['driver_gender'];
        $client_username = !empty($_POST['client_username']) ? $_POST['client_username'] : null;
        $driver_availability = $_POST['driver_availability'];
        
        $sql = "INSERT INTO driver (driver_name, dl_number, driver_phone, driver_address, driver_gender, client_username, driver_availability) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $driver_name, $dl_number, $driver_phone, $driver_address, $driver_gender, $client_username, $driver_availability);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Driver added successfully!</div>';
            $action = 'list';
        } else {
            $message = '<div class="alert alert-danger">Error adding driver: ' . $conn->error . '</div>';
        }
    }
    
    if (isset($_POST['update_driver'])) {
        $driver_id = $_POST['driver_id'];
        $driver_name = $conn->real_escape_string($_POST['driver_name']);
        $dl_number = $conn->real_escape_string($_POST['dl_number']);
        $driver_phone = $conn->real_escape_string($_POST['driver_phone']);
        $driver_address = $conn->real_escape_string($_POST['driver_address']);
        $driver_gender = $_POST['driver_gender'];
        $client_username = !empty($_POST['client_username']) ? $_POST['client_username'] : null;
        $driver_availability = $_POST['driver_availability'];
        
        $sql = "UPDATE driver SET driver_name=?, dl_number=?, driver_phone=?, driver_address=?, driver_gender=?, client_username=?, driver_availability=? WHERE driver_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $driver_name, $dl_number, $driver_phone, $driver_address, $driver_gender, $client_username, $driver_availability, $driver_id);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Driver updated successfully!</div>';
            $action = 'list';
        } else {
            $message = '<div class="alert alert-danger">Error updating driver: ' . $conn->error . '</div>';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $driver_id = $_GET['delete'];
    
    // Check if driver has bookings
    $check_sql = "SELECT COUNT(*) as count FROM rentedcars WHERE driver_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $driver_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = '<div class="alert alert-warning">Cannot delete driver with existing bookings.</div>';
    } else {
        $sql = "DELETE FROM driver WHERE driver_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $driver_id);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Driver deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting driver: ' . $conn->error . '</div>';
        }
    }
}

// Get driver for editing
$edit_driver = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $driver_id = $_GET['id'];
    $sql = "SELECT * FROM driver WHERE driver_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $driver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_driver = $result->fetch_assoc();
    
    if (!$edit_driver) {
        $message = '<div class="alert alert-danger">Driver not found!</div>';
        $action = 'list';
    }
}

// Get all drivers with booking count
$drivers_sql = "SELECT d.*, c.client_name, COUNT(rc.id) as booking_count
                FROM driver d 
                LEFT JOIN clients c ON d.client_username = c.client_username
                LEFT JOIN rentedcars rc ON d.driver_id = rc.driver_id 
                GROUP BY d.driver_id 
                ORDER BY d.driver_name";
$drivers_result = $conn->query($drivers_sql);

// Get all clients for dropdown
$clients_sql = "SELECT client_username, client_name FROM clients ORDER BY client_name";
$clients_result = $conn->query($clients_sql);
$clients = $clients_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers | Admin Panel</title>
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
        .panel {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .panel-heading {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
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
                <li><a href="admin_cars.php"><i class="fa fa-car"></i> Manage Cars</a></li>
                <li><a href="admin_customers.php"><i class="fa fa-users"></i> Manage Customers</a></li>
                <li><a href="admin_drivers.php" class="active"><i class="fa fa-id-card"></i> Manage Drivers</a></li>
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
                    <div class="col-md-8">
                        <h2><i class="fa fa-id-card"></i> Manage Drivers</h2>
                        <p class="text-muted">Complete driver management with optional client assignment</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <?php if ($action == 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Driver
                        </a>
                        <?php else: ?>
                        <a href="admin_drivers.php" class="btn btn-secondary">
                            <i class="fa fa-list"></i> Back to List
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        
        <?php echo $message; ?>
        
            <?php if ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Driver Form -->
            <div class="panel">
                <div class="panel-heading">
                    <h4>
                        <i class="fa fa-<?php echo $action == 'add' ? 'plus' : 'edit'; ?>"></i>
                        <?php echo $action == 'add' ? 'Add New Driver' : 'Edit Driver Details'; ?>
                    </h4>
                </div>
                <div class="panel-body">
                    <form method="POST">
                        <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="driver_id" value="<?php echo htmlspecialchars($edit_driver['driver_id']); ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-user"></i> Driver Name *</label>
                                    <input type="text" class="form-control" name="driver_name" 
                                           value="<?php echo htmlspecialchars($edit_driver['driver_name'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-id-card-o"></i> License Number *</label>
                                    <input type="text" class="form-control" name="dl_number" 
                                           value="<?php echo htmlspecialchars($edit_driver['dl_number'] ?? ''); ?>" 
                                           required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-phone"></i> Phone Number *</label>
                                    <input type="tel" class="form-control" name="driver_phone" 
                                           value="<?php echo htmlspecialchars($edit_driver['driver_phone'] ?? ''); ?>" 
                                           required pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" placeholder="10-digit phone number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fa fa-venus-mars"></i> Gender *</label>
                                    <select class="form-control" name="driver_gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo ($edit_driver['driver_gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($edit_driver['driver_gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label><i class="fa fa-map-marker"></i> Address *</label>
                                    <textarea class="form-control" name="driver_address" rows="3" 
                                              required><?php echo htmlspecialchars($edit_driver['driver_address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fa fa-briefcase"></i> Assigned Client</label>
                                    <select class="form-control" name="client_username">
                                        <option value="">Independent Driver</option>
                                        <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo htmlspecialchars($client['client_username']); ?>" 
                                                <?php echo ($edit_driver['client_username'] ?? '') == $client['client_username'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['client_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fa fa-toggle-on"></i> Availability</label>
                                    <select class="form-control" name="driver_availability">
                                        <option value="yes" <?php echo ($edit_driver['driver_availability'] ?? 'yes') == 'yes' ? 'selected' : ''; ?>>Available</option>
                                        <option value="no" <?php echo ($edit_driver['driver_availability'] ?? '') == 'no' ? 'selected' : ''; ?>>Not Available</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="<?php echo $action == 'add' ? 'add_driver' : 'update_driver'; ?>" 
                                    class="btn btn-success btn-lg">
                                <i class="fa fa-save"></i> <?php echo $action == 'add' ? 'Add Driver' : 'Update Driver'; ?>
                            </button>
                            <a href="admin_drivers.php" class="btn btn-secondary btn-lg">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        
            <?php else: ?>
            <!-- Drivers List -->
            <div class="panel">
                <div class="panel-heading">
                    <h4><i class="fa fa-id-card"></i> All Drivers</h4>
                </div>
                <div class="panel-body">
                    <?php if ($drivers_result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Driver Name</th>
                                    <th>License</th>
                                    <th>Phone</th>
                                    <th>Client</th>
                                    <th>Bookings</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial_number = 1;
                                while ($driver = $drivers_result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><strong><?php echo $serial_number++; ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($driver['driver_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($driver['dl_number']); ?></td>
                                    <td><?php echo htmlspecialchars($driver['driver_phone']); ?></td>
                                    <td>
                                        <?php if ($driver['client_name']): ?>
                                            <span class="label label-info"><?php echo htmlspecialchars($driver['client_name']); ?></span>
                                        <?php else: ?>
                                            <span class="label label-default">Independent</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="label <?php echo $driver['booking_count'] > 0 ? 'label-success' : 'label-default'; ?>">
                                            <?php echo $driver['booking_count']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($driver['driver_availability'] == 'yes'): ?>
                                            <span class="label label-success">Available</span>
                                        <?php else: ?>
                                            <span class="label label-warning">Busy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&id=<?php echo $driver['driver_id']; ?>" 
                                               class="btn btn-primary" title="Edit Driver">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <?php if ($driver['booking_count'] == 0): ?>
                                            <a href="?delete=<?php echo $driver['driver_id']; ?>" 
                                               class="btn btn-danger" title="Delete Driver" 
                                               onclick="return confirm('Are you sure you want to delete this driver?')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                            <?php else: ?>
                                            <button class="btn btn-secondary" disabled title="Cannot delete driver with bookings">
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
                    
                    <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa fa-id-card fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No Drivers Found</h4>
                        <p class="text-muted">Start by adding your first driver to the system.</p>
                        <a href="?action=add" class="btn btn-primary btn-lg">
                            <i class="fa fa-plus"></i> Add First Driver
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>