<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_admin'])){
    header("location: admin_login.php");
    exit();
}

$conn = Connect();
$message = '';

// Handle booking status updates
if ($_POST) {
    if (isset($_POST['update_booking'])) {
        $booking_id = $_POST['booking_id'];
        $return_status = $_POST['return_status'];
        $distance = $_POST['distance'] ?? null;
        $no_of_days = $_POST['no_of_days'] ?? null;
        $total_amount = $_POST['total_amount'] ?? null;
        
        $sql = "UPDATE rentedcars SET return_status=?, distance=?, no_of_days=?, total_amount=?, car_return_date=NOW() WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdddi", $return_status, $distance, $no_of_days, $total_amount, $booking_id);
        
        if ($stmt->execute()) {
            // Update car and driver availability if returned
            if ($return_status == 'R') {
                $booking_sql = "SELECT car_id, driver_id FROM rentedcars WHERE id = ?";
                $booking_stmt = $conn->prepare($booking_sql);
                $booking_stmt->bind_param("i", $booking_id);
                $booking_stmt->execute();
                $booking_data = $booking_stmt->get_result()->fetch_assoc();
                
                // Make car and driver available
                $conn->query("UPDATE cars SET car_availability = 'yes' WHERE car_id = " . $booking_data['car_id']);
                $conn->query("UPDATE driver SET driver_availability = 'yes' WHERE driver_id = " . $booking_data['driver_id']);
            }
            
            $message = '<div class="alert alert-success">Booking updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating booking: ' . $conn->error . '</div>';
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];
$param_types = "";

if ($status_filter != 'all') {
    $where_conditions[] = "rc.return_status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if (!empty($date_filter)) {
    $where_conditions[] = "DATE(rc.booking_date) = ?";
    $params[] = $date_filter;
    $param_types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get all bookings with related data
$bookings_sql = "SELECT rc.*, c.car_name, c.car_nameplate, cu.customer_name, cu.customer_phone, 
                 d.driver_name, d.driver_phone, cl.client_name
                 FROM rentedcars rc
                 JOIN cars c ON rc.car_id = c.car_id
                 JOIN customers cu ON rc.customer_username = cu.customer_username
                 JOIN driver d ON rc.driver_id = d.driver_id
                 JOIN clientcars cc ON c.car_id = cc.car_id
                 JOIN clients cl ON cc.client_username = cl.client_username
                 $where_clause
                 ORDER BY rc.booking_date DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($bookings_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $bookings_result = $stmt->get_result();
} else {
    $bookings_result = $conn->query($bookings_sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Bookings | Admin Panel</title>
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
        .booking-details {
            font-size: 12px;
        }
        .status-badge {
            font-size: 11px;
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
                <li><a href="admin_bookings.php" class="active"><i class="fa fa-calendar"></i> Manage Bookings</a></li>
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
                        <h2><i class="fa fa-calendar"></i> Manage Bookings</h2>
                        <p class="text-muted">View and manage all car rental bookings</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <!-- Filters -->
                        <form method="GET" class="form-inline">
                            <select name="status" class="form-control form-control-sm">
                                <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="NR" <?php echo $status_filter == 'NR' ? 'selected' : ''; ?>>Active</option>
                                <option value="R" <?php echo $status_filter == 'R' ? 'selected' : ''; ?>>Returned</option>
                            </select>
                            <input type="date" name="date" class="form-control form-control-sm" value="<?php echo $date_filter; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            <a href="admin_bookings.php" class="btn btn-sm btn-default">Clear</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php echo $message; ?>
            
            <!-- Bookings List -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>All Bookings</h4>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Car Details</th>
                                    <th>Driver</th>
                                    <th>Booking Date</th>
                                    <th>Rental Period</th>
                                    <th>Fare</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $serial_number = 1;
                                while ($booking = $bookings_result->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><strong><?php echo $serial_number++; ?></strong></td>
                                    <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                    <td>
                                        <div class="booking-details">
                                            <strong><?php echo $booking['customer_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $booking['customer_phone']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="booking-details">
                                            <strong><?php echo $booking['car_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $booking['car_nameplate']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="booking-details">
                                            <strong><?php echo $booking['driver_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $booking['driver_phone']; ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <div class="booking-details">
                                            <strong>Start:</strong> <?php echo date('M j', strtotime($booking['rent_start_date'])); ?><br>
                                            <strong>End:</strong> <?php echo date('M j', strtotime($booking['rent_end_date'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        Rs. <?php echo $booking['fare']; ?><br>
                                        <small class="text-muted"><?php echo $booking['charge_type']; ?></small>
                                    </td>
                                    <td>
                                        <?php if ($booking['return_status'] == 'R'): ?>
                                            <span class="label label-success status-badge">Returned</span>
                                        <?php else: ?>
                                            <span class="label label-warning status-badge">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['total_amount']): ?>
                                            <strong>Rs. <?php echo number_format($booking['total_amount']); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['return_status'] == 'NR'): ?>
                                        <button class="btn btn-xs btn-primary" data-toggle="modal" 
                                                data-target="#returnModal<?php echo $booking['id']; ?>">
                                            <i class="fa fa-check"></i> Return
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-xs btn-info" data-toggle="modal" 
                                                data-target="#viewModal<?php echo $booking['id']; ?>">
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <!-- Return Modal -->
                                <div class="modal fade" id="returnModal<?php echo $booking['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Return Car - Booking #<?php echo $booking['id']; ?></h4>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="return_status" value="R">
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Distance (km)</label>
                                                                <input type="number" step="0.01" class="form-control" 
                                                                       name="distance" <?php echo $booking['charge_type'] == 'km' ? 'required' : ''; ?>>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Number of Days</label>
                                                                <input type="number" class="form-control" name="no_of_days" 
                                                                       value="<?php echo ceil((strtotime($booking['rent_end_date']) - strtotime($booking['rent_start_date'])) / 86400); ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Total Amount</label>
                                                        <input type="number" step="0.01" class="form-control" 
                                                               name="total_amount" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_booking" class="btn btn-success">
                                                        <i class="fa fa-check"></i> Complete Return
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo $booking['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                <h4 class="modal-title">Booking Details - #<?php echo $booking['id']; ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h5>Customer Information</h5>
                                                        <p><strong>Name:</strong> <?php echo $booking['customer_name']; ?></p>
                                                        <p><strong>Phone:</strong> <?php echo $booking['customer_phone']; ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h5>Car Information</h5>
                                                        <p><strong>Car:</strong> <?php echo $booking['car_name']; ?></p>
                                                        <p><strong>Plate:</strong> <?php echo $booking['car_nameplate']; ?></p>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h5>Driver Information</h5>
                                                        <p><strong>Name:</strong> <?php echo $booking['driver_name']; ?></p>
                                                        <p><strong>Phone:</strong> <?php echo $booking['driver_phone']; ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h5>Booking Details</h5>
                                                        <p><strong>Fare:</strong> Rs. <?php echo $booking['fare']; ?> per <?php echo $booking['charge_type']; ?></p>
                                                        <p><strong>Distance:</strong> <?php echo $booking['distance'] ?? 'N/A'; ?> km</p>
                                                        <p><strong>Days:</strong> <?php echo $booking['no_of_days'] ?? 'N/A'; ?></p>
                                                        <p><strong>Total:</strong> Rs. <?php echo number_format($booking['total_amount'] ?? 0); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>