<?php
session_start();
require 'connection.php';

// For demo purposes, we'll use a simple driver login system
// In a real application, you'd have a proper driver authentication system
if(!isset($_GET['driver_id']) && !isset($_SESSION['driver_id'])){
    // Redirect to a driver login page or show error
    echo "<script>alert('Please access this page through proper driver login.'); window.location.href='index.php';</script>";
    exit();
}

$conn = Connect();
$driver_id = $_GET['driver_id'] ?? $_SESSION['driver_id'];

// Get driver details
$driver_sql = "SELECT d.*, c.client_name FROM driver d 
               JOIN clients c ON d.client_username = c.client_username 
               WHERE d.driver_id = ?";
$driver_stmt = $conn->prepare($driver_sql);
$driver_stmt->bind_param("i", $driver_id);
$driver_stmt->execute();
$driver = $driver_stmt->get_result()->fetch_assoc();

if (!$driver) {
    echo "<script>alert('Driver not found!'); window.location.href='index.php';</script>";
    exit();
}

// Get driver statistics
$stats_sql = "SELECT 
    COUNT(*) as total_trips,
    COUNT(CASE WHEN return_status = 'R' THEN 1 END) as completed_trips,
    COUNT(CASE WHEN return_status = 'NR' THEN 1 END) as active_trips,
    SUM(CASE WHEN return_status = 'R' THEN total_amount ELSE 0 END) as total_revenue,
    AVG(CASE WHEN return_status = 'R' THEN total_amount ELSE NULL END) as avg_trip_value,
    MAX(booking_date) as last_trip_date
    FROM rentedcars WHERE driver_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $driver_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get recent trips
$recent_trips_sql = "SELECT rc.*, c.car_name, cu.customer_name 
    FROM rentedcars rc
    JOIN cars c ON rc.car_id = c.car_id
    JOIN customers cu ON rc.customer_username = cu.customer_username
    WHERE rc.driver_id = ?
    ORDER BY rc.booking_date DESC
    LIMIT 5";
$recent_stmt = $conn->prepare($recent_trips_sql);
$recent_stmt->bind_param("i", $driver_id);
$recent_stmt->execute();
$recent_trips = $recent_stmt->get_result();

// Calculate driver rating (simple average based on completed trips)
$rating = 0;
if ($stats['completed_trips'] > 0) {
    // Simple rating calculation based on performance
    $rating = min(5, 3 + ($stats['completed_trips'] * 0.1) + (($stats['total_revenue'] / max($stats['completed_trips'], 1)) / 1000));
}

// Handle profile update (basic info only - sensitive data should be updated by admin)
$message = '';
if ($_POST && isset($_POST['update_profile'])) {
    $driver_phone = $conn->real_escape_string($_POST['driver_phone']);
    $driver_address = $conn->real_escape_string($_POST['driver_address']);
    
    try {
        $update_sql = "UPDATE driver SET driver_phone=?, driver_address=? WHERE driver_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $driver_phone, $driver_address, $driver_id);
        
        if ($update_stmt->execute()) {
            $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Profile updated successfully!</div>';
            // Refresh driver data
            $driver_stmt->execute();
            $driver = $driver_stmt->get_result()->fetch_assoc();
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
    <title>Driver Profile | Car Rental System</title>
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
                    <i class="fa fa-id-card"></i> Driver Portal
                </a>
            </div>

            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="driver_profile.php?driver_id=<?php echo $driver_id; ?>" class="active">My Profile</a></li>
                    <li><a href="admin_login.php">Admin Login</a></li>
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
                            <i class="fa fa-id-card fa-5x text-primary"></i>
                        </div>
                        <h3 class="mt-3"><?php echo htmlspecialchars($driver['driver_name']); ?></h3>
                        <p class="text-muted">Professional Driver</p>
                        <p class="text-muted">
                            <i class="fa fa-building"></i> 
                            Works for: <strong><?php echo htmlspecialchars($driver['client_name']); ?></strong>
                        </p>
                        
                        <!-- Driver Rating -->
                        <div class="driver-rating mt-3">
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa fa-star<?php echo $i <= $rating ? '' : '-o'; ?>" style="color: #f39c12;"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted"><?php echo number_format($rating, 1); ?>/5.0 Rating</p>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="status-badge mt-3">
                            <?php if ($driver['driver_availability'] == 'yes'): ?>
                                <span class="label label-success"><i class="fa fa-check-circle"></i> Available</span>
                            <?php else: ?>
                                <span class="label label-warning"><i class="fa fa-clock-o"></i> Busy</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-stats mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h4 class="text-primary"><?php echo $stats['total_trips']; ?></h4>
                                        <small>Total Trips</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="stat-item">
                                        <h4 class="text-success"><?php echo $stats['completed_trips']; ?></h4>
                                        <small>Completed</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="stat-item">
                                        <h4 class="text-info">Rs. <?php echo number_format($stats['total_revenue']); ?></h4>
                                        <small>Total Revenue Generated</small>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($stats['avg_trip_value'] > 0): ?>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="stat-item">
                                        <h5 class="text-warning">Rs. <?php echo number_format($stats['avg_trip_value']); ?></h5>
                                        <small>Average Trip Value</small>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Details & Edit Form -->
                <div class="col-md-8">
                    <div class="modern-card">
                        <h4><i class="fa fa-edit"></i> Driver Information</h4>
                        <p class="text-muted">Update your contact information</p>
                        
                        <!-- Driver Details (Read-only) -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label><i class="fa fa-id-card"></i> Driver License</label>
                                    <p class="info-value"><?php echo htmlspecialchars($driver['dl_number']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label><i class="fa fa-venus-mars"></i> Gender</label>
                                    <p class="info-value"><?php echo htmlspecialchars($driver['driver_gender']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Editable Information -->
                        <form method="POST" class="mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern"><i class="fa fa-phone"></i> Phone Number</label>
                                        <input type="tel" class="form-control-modern" name="driver_phone" 
                                               value="<?php echo htmlspecialchars($driver['driver_phone']); ?>" required
                                               pattern="[0-9]{10}" maxlength="10" title="Please enter exactly 10 digits" placeholder="10-digit phone number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label><i class="fa fa-calendar"></i> Last Trip</label>
                                        <p class="info-value">
                                            <?php echo $stats['last_trip_date'] ? date('M j, Y', strtotime($stats['last_trip_date'])) : 'No trips yet'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern"><i class="fa fa-map-marker"></i> Address</label>
                                <textarea class="form-control-modern" name="driver_address" rows="3" required><?php echo htmlspecialchars($driver['driver_address']); ?></textarea>
                            </div>
                            
                            <div class="form-group-modern">
                                <button type="submit" name="update_profile" class="btn-modern btn-lg">
                                    <i class="fa fa-save"></i> Update Contact Info
                                </button>
                                <a href="admin_drivers.php" class="btn-modern btn-info-modern btn-lg" style="margin-left: 10px;">
                                    <i class="fa fa-users"></i> View All Drivers
                                </a>
                            </div>
                        </form>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fa fa-info-circle"></i>
                            <strong>Note:</strong> To update your name, license number, or other official details, please contact your fleet manager or admin.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Trips -->
            <?php if ($recent_trips->num_rows > 0): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="modern-card">
                        <h4><i class="fa fa-history"></i> Recent Trips</h4>
                        <p class="text-muted">Your latest driving assignments</p>
                        
                        <div class="table-responsive mt-3">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Customer</th>
                                        <th>Trip Date</th>
                                        <th>Status</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($trip = $recent_trips->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($trip['car_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($trip['customer_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($trip['booking_date'])); ?></td>
                                        <td>
                                            <?php if ($trip['return_status'] == 'R'): ?>
                                                <span class="label label-success">Completed</span>
                                            <?php else: ?>
                                                <span class="label label-warning">In Progress</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong>Rs. <?php echo number_format($trip['total_amount'] ?? 0); ?></strong></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="modern-card text-center">
                        <i class="fa fa-car fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No Trips Yet</h4>
                        <p class="text-muted">You haven't been assigned any trips yet. Contact your fleet manager for assignments.</p>
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
        
        .profile-stats h4, .profile-stats h5 {
            margin: 0;
            font-weight: 700;
        }
        
        .profile-stats small {
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-item {
            margin-bottom: 20px;
        }
        
        .info-item label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }
        
        .info-value {
            font-size: 16px;
            color: var(--text-primary);
            margin: 0;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .rating-stars {
            margin-bottom: 10px;
        }
        
        .rating-stars i {
            font-size: 18px;
            margin: 0 2px;
        }
        
        .status-badge {
            margin: 15px 0;
        }
        
        .label {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
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