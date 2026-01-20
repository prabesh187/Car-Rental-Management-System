<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_customer'])){
    header("location: customerlogin.php");
    exit();
}

$conn = Connect();
$customer_username = $_SESSION['login_customer'];

// Get customer details
$customer_sql = "SELECT * FROM customers WHERE customer_username = ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param("s", $customer_username);
$customer_stmt->execute();
$customer = $customer_stmt->get_result()->fetch_assoc();

// Get booking statistics
$stats_sql = "SELECT 
    COUNT(*) as total_bookings,
    COUNT(CASE WHEN return_status = 'R' THEN 1 END) as completed_bookings,
    COUNT(CASE WHEN return_status = 'NR' THEN 1 END) as active_bookings,
    SUM(CASE WHEN return_status = 'R' THEN total_amount ELSE 0 END) as total_spent,
    MAX(booking_date) as last_booking_date
    FROM rentedcars WHERE customer_username = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("s", $customer_username);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get recent bookings
$recent_bookings_sql = "SELECT rc.*, c.car_name, c.car_img, d.driver_name 
    FROM rentedcars rc
    JOIN cars c ON rc.car_id = c.car_id
    JOIN driver d ON rc.driver_id = d.driver_id
    WHERE rc.customer_username = ?
    ORDER BY rc.booking_date DESC
    LIMIT 5";
$recent_stmt = $conn->prepare($recent_bookings_sql);
$recent_stmt->bind_param("s", $customer_username);
$recent_stmt->execute();
$recent_bookings = $recent_stmt->get_result();

// Handle profile update
$message = '';
if ($_POST && isset($_POST['update_profile'])) {
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $customer_phone = $conn->real_escape_string($_POST['customer_phone']);
    $customer_email = $conn->real_escape_string($_POST['customer_email']);
    $customer_address = $conn->real_escape_string($_POST['customer_address']);
    $new_password = $_POST['new_password'];
    
    try {
        // Check for duplicate email (excluding current customer)
        $check_sql = "SELECT customer_username FROM customers WHERE customer_email = ? AND customer_username != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $customer_email, $customer_username);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            throw new Exception("Email address already exists! Please use a different email.");
        }
        
        // Update profile
        if (!empty($new_password)) {
            $update_sql = "UPDATE customers SET customer_name=?, customer_phone=?, customer_email=?, customer_address=?, customer_password=? WHERE customer_username=?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssss", $customer_name, $customer_phone, $customer_email, $customer_address, $new_password, $customer_username);
        } else {
            $update_sql = "UPDATE customers SET customer_name=?, customer_phone=?, customer_email=?, customer_address=? WHERE customer_username=?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sssss", $customer_name, $customer_phone, $customer_email, $customer_address, $customer_username);
        }
        
        if ($update_stmt->execute()) {
            $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Profile updated successfully!</div>';
            // Refresh customer data
            $customer_stmt->execute();
            $customer = $customer_stmt->get_result()->fetch_assoc();
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
    <title>My Profile | Car Rental System</title>
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
                <a class="navbar-brand page-scroll" href="index.php">
                    <i class="fa fa-car"></i> Car Rentals
                </a>
            </div>

            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="booking.php">Book Car</a></li>
                    <li><a href="mybookings.php">My Bookings</a></li>
                    <li><a href="customer_profile.php" class="active">My Profile</a></li>
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
                            <i class="fa fa-user-circle fa-5x text-primary"></i>
                        </div>
                        <h3 class="mt-3"><?php echo htmlspecialchars($customer['customer_name']); ?></h3>
                        <p class="text-muted">@<?php echo htmlspecialchars($customer['customer_username']); ?></p>
                        <p class="text-muted">
                            <i class="fa fa-calendar"></i> 
                            Member since <?php echo date('M Y', strtotime($stats['last_booking_date'] ?? 'now')); ?>
                        </p>
                        
                        <div class="profile-stats mt-4">
                            <div class="row">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="text-primary"><?php echo $stats['total_bookings']; ?></h4>
                                        <small>Total Bookings</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="text-success"><?php echo $stats['completed_bookings']; ?></h4>
                                        <small>Completed</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h4 class="text-warning"><?php echo $stats['active_bookings']; ?></h4>
                                        <small>Active</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5 class="text-success">Total Spent: Rs. <?php echo number_format($stats['total_spent']); ?></h5>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Details & Edit Form -->
                <div class="col-md-8">
                    <div class="modern-card">
                        <h4><i class="fa fa-edit"></i> Edit Profile</h4>
                        <p class="text-muted">Update your personal information</p>
                        
                        <form method="POST" class="mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern"><i class="fa fa-user"></i> Full Name</label>
                                        <input type="text" class="form-control-modern" name="customer_name" 
                                               value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern"><i class="fa fa-phone"></i> Phone Number</label>
                                        <input type="tel" class="form-control-modern" name="customer_phone" 
                                               value="<?php echo htmlspecialchars($customer['customer_phone']); ?>" required
                                               pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" placeholder="10-digit phone number">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern"><i class="fa fa-envelope"></i> Email Address</label>
                                <input type="email" class="form-control-modern" name="customer_email" 
                                       value="<?php echo htmlspecialchars($customer['customer_email']); ?>" required>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern"><i class="fa fa-map-marker"></i> Address</label>
                                <textarea class="form-control-modern" name="customer_address" rows="3" required><?php echo htmlspecialchars($customer['customer_address']); ?></textarea>
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
                                <a href="mybookings.php" class="btn-modern btn-success-modern btn-lg" style="margin-left: 10px;">
                                    <i class="fa fa-calendar"></i> View My Bookings
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
                        <p class="text-muted">Your latest car rental activities</p>
                        
                        <div class="table-responsive mt-3">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Driver</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $booking['car_img']; ?>" alt="Car" 
                                                     style="width: 50px; height: 30px; object-fit: cover; border-radius: 4px; margin-right: 10px;"
                                                     onerror="this.src='https://via.placeholder.com/50x30/3498db/ffffff?text=Car'">
                                                <span><?php echo htmlspecialchars($booking['car_name']); ?></span>
                                            </div>
                                        </td>
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
                            <a href="mybookings.php" class="btn-modern">
                                <i class="fa fa-eye"></i> View All Bookings
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