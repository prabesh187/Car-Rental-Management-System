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
    if (isset($_POST['add_client'])) {
        $client_username = $conn->real_escape_string($_POST['client_username']);
        $client_name = $conn->real_escape_string($_POST['client_name']);
        $client_phone = $conn->real_escape_string($_POST['client_phone']);
        $client_email = $conn->real_escape_string($_POST['client_email']);
        $client_address = $conn->real_escape_string($_POST['client_address']);
        $client_password = $_POST['client_password']; // Don't escape before hashing
        
        // Check for duplicate username or email
        $check_sql = "SELECT client_username, client_email FROM clients WHERE client_username = ? OR client_email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $client_username, $client_email);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            if ($existing['client_username'] === $client_username) {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Username already exists! Please choose a different username.</div>';
            } else {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Email address already exists! Please use a different email.</div>';
            }
        } else {
            $sql = "INSERT INTO clients (client_username, client_name, client_phone, client_email, client_address, client_password) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $client_username, $client_name, $client_phone, $client_email, $client_address, $client_password);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Client added successfully!</div>';
                $action = 'list';
            } else {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error adding client: ' . $conn->error . '</div>';
            }
        }
    }
    
    if (isset($_POST['update_client'])) {
        $original_username = $_POST['original_username'];
        $client_username = $conn->real_escape_string($_POST['client_username']);
        $client_name = $conn->real_escape_string($_POST['client_name']);
        $client_phone = $conn->real_escape_string($_POST['client_phone']);
        $client_email = $conn->real_escape_string($_POST['client_email']);
        $client_address = $conn->real_escape_string($_POST['client_address']);
        $client_password = $_POST['client_password']; // Don't escape before hashing
        
        // Check for duplicate username or email (excluding current client)
        $check_sql = "SELECT client_username, client_email FROM clients WHERE (client_username = ? OR client_email = ?) AND client_username != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sss", $client_username, $client_email, $original_username);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            if ($existing['client_username'] === $client_username) {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Username already exists! Please choose a different username.</div>';
            } else {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Email address already exists! Please use a different email.</div>';
            }
        } else {
            // Handle password update - only update if password is provided
            $update_password = !empty($client_password);
            
            if ($update_password) {
                $sql = "UPDATE clients SET client_username=?, client_name=?, client_phone=?, client_email=?, client_address=?, client_password=? WHERE client_username=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", $client_username, $client_name, $client_phone, $client_email, $client_address, $client_password, $original_username);
            } else {
                // Update without changing password
                $sql = "UPDATE clients SET client_username=?, client_name=?, client_phone=?, client_email=?, client_address=? WHERE client_username=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $client_username, $client_name, $client_phone, $client_email, $client_address, $original_username);
            }
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Client updated successfully!</div>';
                $action = 'list';
            } else {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error updating client: ' . $conn->error . '</div>';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $client_username = $_GET['delete'];
    
    // Check if client has cars or drivers
    $check_cars_sql = "SELECT COUNT(*) as count FROM clientcars WHERE client_username = ?";
    $check_drivers_sql = "SELECT COUNT(*) as count FROM driver WHERE client_username = ?";
    
    $check_stmt1 = $conn->prepare($check_cars_sql);
    $check_stmt1->bind_param("s", $client_username);
    $check_stmt1->execute();
    $cars_count = $check_stmt1->get_result()->fetch_assoc()['count'];
    
    $check_stmt2 = $conn->prepare($check_drivers_sql);
    $check_stmt2->bind_param("s", $client_username);
    $check_stmt2->execute();
    $drivers_count = $check_stmt2->get_result()->fetch_assoc()['count'];
    
    if ($cars_count > 0 || $drivers_count > 0) {
        $message = '<div class="alert alert-warning">Cannot delete client with assigned cars or drivers.</div>';
    } else {
        $sql = "DELETE FROM clients WHERE client_username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $client_username);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Client deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting client: ' . $conn->error . '</div>';
        }
    }
}

// Get client for editing
$edit_client = null;
if ($action == 'edit' && isset($_GET['username'])) {
    $client_username = $_GET['username'];
    $sql = "SELECT * FROM clients WHERE client_username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $client_username);
    $stmt->execute();
    $edit_client = $stmt->get_result()->fetch_assoc();
}

// Get all clients with stats
$clients_sql = "SELECT c.*, 
                COUNT(DISTINCT cc.car_id) as car_count,
                COUNT(DISTINCT d.driver_id) as driver_count,
                COUNT(DISTINCT rc.id) as total_bookings
                FROM clients c 
                LEFT JOIN clientcars cc ON c.client_username = cc.client_username
                LEFT JOIN driver d ON c.client_username = d.client_username
                LEFT JOIN rentedcars rc ON cc.car_id = rc.car_id
                GROUP BY c.client_username 
                ORDER BY c.client_name";
$clients_result = $conn->query($clients_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Clients | Admin Panel</title>
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
                <li><a href="admin_drivers.php"><i class="fa fa-id-card"></i> Manage Drivers</a></li>
                <li><a href="admin_bookings.php"><i class="fa fa-calendar"></i> Manage Bookings</a></li>
                <li><a href="admin_clients.php" class="active"><i class="fa fa-briefcase"></i> Manage Clients</a></li>
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
                        <h2><i class="fa fa-briefcase"></i> Manage Clients</h2>
                        <p class="text-muted">Complete CRUD operations for client/employee management</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <?php if ($action == 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Client
                        </a>
                        <?php else: ?>
                        <a href="admin_clients.php" class="btn btn-default">
                            <i class="fa fa-list"></i> Back to List
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php echo $message; ?>
            
            <?php if ($action == 'add' || $action == 'edit'): ?>
            <!-- Add/Edit Client Form -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><?php echo $action == 'add' ? 'Add New Client' : 'Edit Client'; ?></h4>
                </div>
                <div class="panel-body">
                    <form method="POST">
                        <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="original_username" value="<?php echo $edit_client['client_username']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Username *</label>
                                    <input type="text" class="form-control" name="client_username" 
                                           value="<?php echo $edit_client['client_username'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Full Name *</label>
                                    <input type="text" class="form-control" name="client_name" 
                                           value="<?php echo $edit_client['client_name'] ?? ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone Number *</label>
                                    <input type="tel" class="form-control" name="client_phone" 
                                           value="<?php echo $edit_client['client_phone'] ?? ''; ?>" required 
                                           pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" placeholder="10-digit phone number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Address *</label>
                                    <input type="email" class="form-control" name="client_email" 
                                           value="<?php echo $edit_client['client_email'] ?? ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Address *</label>
                                    <textarea class="form-control" name="client_address" rows="3" required><?php echo $edit_client['client_address'] ?? ''; ?></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Password <?php echo $action == 'add' ? '*' : ''; ?></label>
                                    <input type="password" class="form-control" name="client_password" 
                                           value="" <?php echo $action == 'add' ? 'required' : ''; ?> 
                                           placeholder="<?php echo $action == 'edit' ? 'Leave empty to keep current password' : 'Enter password'; ?>">
                                    <?php if ($action == 'edit'): ?>
                                    <small class="text-muted">Leave empty to keep current password, or enter new password to change</small>
                                    <?php else: ?>
                                    <small class="text-muted">Minimum 6 characters required</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="<?php echo $action == 'add' ? 'add_client' : 'update_client'; ?>" 
                                    class="btn btn-success">
                                <i class="fa fa-save"></i> <?php echo $action == 'add' ? 'Add Client' : 'Update Client'; ?>
                            </button>
                            <a href="admin_clients.php" class="btn btn-default">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Clients List -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>All Clients/Employees</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                    <th>Cars</th>
                                    <th>Drivers</th>
                                    <th>Bookings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial_number = 1;
                                while ($client = $clients_result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><strong><?php echo $serial_number++; ?></strong></td>
                                    <td><strong><?php echo $client['client_username']; ?></strong></td>
                                    <td><?php echo $client['client_name']; ?></td>
                                    <td><?php echo $client['client_phone']; ?></td>
                                    <td><?php echo $client['client_email']; ?></td>
                                    <td><?php echo substr($client['client_address'], 0, 30) . '...'; ?></td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $client['car_count']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info"><?php echo $client['driver_count']; ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success"><?php echo $client['total_bookings']; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?action=edit&username=<?php echo $client['client_username']; ?>" 
                                               class="btn btn-primary" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <?php if ($client['car_count'] == 0 && $client['driver_count'] == 0): ?>
                                            <a href="?delete=<?php echo $client['client_username']; ?>" 
                                               class="btn btn-danger" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this client?')">
                                                <i class="fa fa-trash"></i>
                                            </a>
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

</body>
</html>