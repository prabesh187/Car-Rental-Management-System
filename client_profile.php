<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_client'])){
    header("location: clientlogin.php");
    exit();
}

$conn = Connect();
$client_username = $_SESSION['login_client'];

// Get client details
$client_sql = "SELECT * FROM clients WHERE client_username = ?";
$client_stmt = $conn->prepare($client_sql);
$client_stmt->bind_param("s", $client_username);
$client_stmt->execute();
$client = $client_stmt->get_result()->fetch_assoc();

// Get fleet statistics
$fleet_stats_sql = "SELECT 
    COUNT(DISTINCT cc.car_id) as total_cars,
    COUNT(DISTINCT CASE WHEN c.car_availability = 'yes' THEN cc.car_id END) as available_cars,
    COUNT(DISTINCT d.driver_id) as total_drivers,
    COUNT(DISTINCT CASE WHEN d.driver_availability = 'yes' THEN d.driver_id END) as available_drivers
    FROM clientcars cc
    LEFT JOIN cars c ON cc.car_id = c.car_id
    LEFT JOIN driver d ON d.client_username = cc.client_username
    WHERE cc.client_username = ?";
$fleet_stmt = $conn->prepare($fleet_stats_sql);
$fleet_stmt->bind_param("s", $client_username);
$fleet_stmt->execute();
$fleet_stats = $fleet_stmt->get_result()->fetch_assoc();

// Get revenue statistics
$revenue_sql = "SELECT 
    COUNT(rc.id) as total_bookings,
    COUNT(CASE WHEN rc.return_status = 'R' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN rc.return_status = 'NR' THEN 1 END) as active_bookings,
    SUM(CASE WHEN rc.return_status = 'R' THEN rc.total_amount ELSE 0 END) as total_revenue
    FROM rentedcars rc
    JOIN clientcars cc ON rc.car_id = cc.car_id
    WHERE cc.client_username = ?";
$revenue_stmt = $conn->prepare($revenue_sql);
$revenue_stmt->bind_param("s", $client_username);
$revenue_stmt->execute();
$revenue_stats = $revenue_stmt->get_result()->fetch_assoc();

// Get recent bookings for client's cars
$recent_bookings_sql = "SELECT rc.*, c.car_name, cu.customer_name, d.driver_name 
    FROM rentedcars rc
    JOIN clientcars cc ON rc.car_id = cc.car_id
    JOIN cars c ON rc.car_id = c.car_id
    JOIN customers cu ON rc.customer_username = cu.customer_username
    JOIN driver d ON rc.driver_id = d.driver_id
    WHERE cc.client_username = ?
    ORDER BY rc.booking_date DESC
    LIMIT 5";
$recent_stmt = $conn->prepare($recent_bookings_sql);
$recent_stmt->bind_param("s", $client_username);
$recent_stmt->execute();
$recent_bookings = $recent_stmt->get_result();

// Handle profile update
$message = '';
if ($_POST && isset($_POST['update_profile'])) {
    $client_name = $conn->real_escape_string($_POST['client_name']);
    $client_phone = $conn->real_escape_string($_POST['client_phone']);
    $client_email = $conn->real_escape_string($_POST['client_email']);
    $client_address = $conn->real_escape_string($_POST['client_address']);
    $new_password = $_POST['new_password'];
    
    try {
        // Check for duplicate email (excluding current client)
        $check_sql = "SELECT client_username FROM clients WHERE client_email = ? AND client_username != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $client_email, $client_username);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            throw new Exception("Email address already exists! Please use a different email.");
        }
        
        // Update profile
        if (!empty($new_password)) {
            $update_sql = "UPDATE clients SET client_name=?, client_phone=?, client_email=?, client_address=?, client_password=? WHERE client_username=?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssss", $client_name, $client_phone, $client_email, $client_address, $new_password, $client_username);
        } else {
            $update_sql = "UPDATE clients SET client_name=?, client_phone=?, client_email=?, client_address=? WHERE client_username=?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssss", $client_name, $client_phone, $client_email, $client_address, $client_username);
        }
        
        if ($update_stmt->execute()) {
            $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Profile updated successfully!</div>';
            // Refresh client data
            $client_stmt->execute();
            $client = $client_stmt->get_result()->fetch_assoc();
        } else {
            throw new Exception("Error updating profile: " . $conn->error);
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile | Car Rental System</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/P.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern-customer.css">
    
    <!-- JavaScript Libraries -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/modern-customer.js"></script>
</head>

<body>
    <!-- Theme Toggle Button -->
    <div class="theme-toggle">
        <i class="fa fa-moon-o" id="theme-icon"></i>
        <span id="theme-text">Dark</span>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand page-scroll" href="clientview.php">
                    <i class="fa fa-briefcase"></i> Client Portal
                </a>
            </div>

            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="clientview.php">Dashboard</a></li>
                    <li><a href="entercar.php">Add Car</a></li>
                    <li><a href="enterdriver.php">Add Driver</a></li>
                    <li><a href="client_profile.php" class="active">My Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Section -->
    <section class="section-modern" style="margin-top: 80px;">
        <div class="container">
            <?php echo $message; ?>
            
            <div class="row">
                <!-- Profile Overview -->
                <div class="col-md-4">
                    <div class="modern-card text-center">
                        <div class="profile-avatar">
                            <i class="fa fa-briefcase fa-5x text-primary"></i>
                        </div>
                        <h3 class="mt-3"><?php echo htmlspecialchars($client['client_name']); ?></h3>
                        <p class="text-muted">@<?php echo htmlspecialchars($client['client_username']); ?></p>
                        <p class="text-muted">
                            <i class="fa fa-building"></i> Fleet Owner
                        </p>
                        
                        <div class="profile-stats mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h4 class="text-primary"><?php echo $fleet_stats['total_cars']; ?></h4>
                                        <small>Total Cars</small>
                                        <div class="text-success" style="font-size: 10px;">
                                            <?php echo $fleet_stats['available_cars']; ?> Available
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h4 class="text-warning"><?php echo $fleet_stats['total_drivers']; ?></h4>
                                        <small>Total Drivers</small>
                                        <div class="text-success" style="font-size: 10px;">
                                            <?php echo $fleet_stats['available_drivers']; ?> Available
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h4 class="text-info"><?php echo $revenue_stats['total_bookings']; ?></h4>
                                        <small>Total Bookings</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h4 class="text-success">Rs. <?php echo number_format($revenue_stats['total_revenue']); ?></h4>
                                        <small>Total Revenue</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Details & Edit Form -->
                <div class="col-md-8">
                    <div class="modern-card">
                        <h4><i class="fa fa-edit"></i> Edit Profile</h4>
                        <p class="text-muted">Update your business information</p>
                        
                        <form method="POST" class="mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern"><i class="fa fa-building"></i> Business Name</label>
                                        <input type="text" class="form-control-modern" name="client_name" 
                                               value="<?php echo htmlspecialchars($client['client_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern"><i class="fa fa-phone"></i> Phone Number</label>
                                        <input type="tel" class="form-control-modern" name="client_phone" 
                                               value="<?php echo htmlspecialchars($client['client_phone']); ?>" required
                                               pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" placeholder="10-digit phone number">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern"><i class="fa fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control-modern" name="client_email" 
                                       value="<?php echo htmlspecialchars($client['client_email']); ?>" required>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern"><i class="fa fa-map-marker"></i> Business Address</label>
                                <textarea class="form-control-modern" name="client_address" rows="3" required><?php echo htmlspecialchars($client['client_address']); ?></textarea>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern"><i class="fa fa-lock"></i> New Password</label>
                                <input type="password" class="form-control-modern" name="new_password" 
                                       placeholder="Leave empty to keep current password" minlength="6">
                                <small class="text-muted">Only fill this if you want to change your password</small>
                            </div>
                            
                            <div class="form-group-modern">
                                <button type="submit" name="update_profile" class="btn-modern btn-lg">
                                    <i class="fa fa-save"></i> Update Profile
                                </button>
                                <a href="clientview.php" class="btn-modern btn-success-modern btn-lg" style="margin-left: 10px;">
                                    <i class="fa fa-dashboard"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <?php if ($recent_bookings->num_rows > 0): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="modern-card">
                        <h4><i class="fa fa-history"></i> Recent Bookings</h4>
                        <p class="text-muted">Latest bookings for your fleet</p>
                        
                        <div class="table-responsive mt-3">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Customer</th>
                                        <th>Driver</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($booking['car_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['driver_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                        <td>
                                            <?php if ($booking['return_status'] == 'R'): ?>
                                                <span class="label label-success">Completed</span>
                                            <?php else: ?>
                                                <span class="label label-warning">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong>Rs. <?php echo number_format($booking['total_amount'] ?? 0); ?></strong></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="clientview.php" class="btn-modern">
                                <i class="fa fa-eye"></i> View Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <style>
        .profile-avatar {
            margin-bottom: 20px;
        }
        
        .profile-stats .stat-item {
            text-align: center;
            padding: 10px 0;
        }
        
        .profile-stats h4 {
            margin: 0;
            font-weight: 700;
        }
        
        .profile-stats small {
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .label {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .label-success {
            background: var(--success-color);
            color: white;
        }
        
        .label-warning {
            background: var(--warning-color);
            color: white;
        }
    </style>
</body>
</html>