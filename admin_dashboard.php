<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_admin'])){
    header("location: admin_login.php");
    exit();
}

$conn = Connect();

// Get statistics for dashboard
$stats = [];

// Total cars
$result = $conn->query("SELECT COUNT(*) as total FROM cars");
$stats['total_cars'] = $result->fetch_assoc()['total'];

// Available cars
$result = $conn->query("SELECT COUNT(*) as total FROM cars WHERE car_availability = 'yes'");
$stats['available_cars'] = $result->fetch_assoc()['total'];

// Total customers
$result = $conn->query("SELECT COUNT(*) as total FROM customers");
$stats['total_customers'] = $result->fetch_assoc()['total'];

// Total drivers
$result = $conn->query("SELECT COUNT(*) as total FROM driver");
$stats['total_drivers'] = $result->fetch_assoc()['total'];

// Total bookings
$result = $conn->query("SELECT COUNT(*) as total FROM rentedcars");
$stats['total_bookings'] = $result->fetch_assoc()['total'];

// Active bookings
$result = $conn->query("SELECT COUNT(*) as total FROM rentedcars WHERE return_status = 'NR'");
$stats['active_bookings'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM rentedcars WHERE return_status = 'R'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Recent bookings
$recent_bookings = $conn->query("
    SELECT rc.*, c.car_name, cu.customer_name, d.driver_name 
    FROM rentedcars rc
    JOIN cars c ON rc.car_id = c.car_id
    JOIN customers cu ON rc.customer_username = cu.customer_username
    JOIN driver d ON rc.driver_id = d.driver_id
    ORDER BY rc.booking_date DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Car Rental System</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/P.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern-admin.css">
    
    <!-- JavaScript Libraries -->
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/modern-admin.js"></script>
</head>
<body>
    <!-- Theme Toggle Button -->
    <div class="theme-toggle">
        <i class="fa fa-moon-o" id="theme-icon"></i>
        <span id="theme-text">Dark</span>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="admin-sidebar slide-in-left">
                <div class="sidebar-header">
                    <h4><i class="fa fa-shield"></i> Admin Panel</h4>
                    <small>Car Rental System</small>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="admin_dashboard.php" class="active"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="admin_cars.php"><i class="fa fa-car"></i> Manage Cars</a></li>
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
            <div class="admin-content">
                <!-- Header -->
                <div class="admin-header fade-in">
                    <div class="row">
                        <div class="col-md-8 col-sm-12">
                            <h2><i class="fa fa-dashboard"></i> Admin Dashboard</h2>
                            <p class="text-muted">Welcome back, <strong><?php echo $_SESSION['login_admin']; ?></strong>! Here's what's happening with your car rental business today.</p>
                        </div>
                        <div class="col-md-4 col-sm-12 text-right">
                            <div class="d-none d-md-block">
                                <p class="text-muted mb-1">
                                    <i class="fa fa-calendar"></i> <?php echo date('l, F j, Y'); ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="fa fa-clock-o"></i> <?php echo date('g:i A'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="stat-card text-center" data-tooltip="Total number of cars in your fleet">
                            <div class="stat-number text-primary"><?php echo $stats['total_cars']; ?></div>
                            <div class="stat-label">Total Cars</div>
                            <small class="text-success">
                                <i class="fa fa-check-circle"></i> <?php echo $stats['available_cars']; ?> Available
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="stat-card text-center" data-tooltip="Registered customers in your system">
                            <div class="stat-number text-success"><?php echo $stats['total_customers']; ?></div>
                            <div class="stat-label">Total Customers</div>
                            <small class="text-info">
                                <i class="fa fa-user-plus"></i> Growing customer base
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="stat-card text-center" data-tooltip="All bookings made through your system">
                            <div class="stat-number text-warning"><?php echo $stats['total_bookings']; ?></div>
                            <div class="stat-label">Total Bookings</div>
                            <small class="text-danger">
                                <i class="fa fa-clock-o"></i> <?php echo $stats['active_bookings']; ?> Active
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="stat-card text-center" data-tooltip="Total revenue generated from completed bookings">
                            <div class="stat-number text-info">Rs. <?php echo number_format($stats['total_revenue']); ?></div>
                            <div class="stat-label">Total Revenue</div>
                            <small class="text-success">
                                <i class="fa fa-arrow-up"></i> Revenue growth
                            </small>
                        </div>
                    </div>
                </div>
            
                <!-- Quick Actions -->
                <div class="quick-actions fade-in">
                    <h4><i class="fa fa-bolt"></i> Quick Actions</h4>
                    <p class="text-muted mb-3">Perform common tasks quickly and efficiently</p>
                    <div class="row">
                        <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                            <a href="admin_cars.php?action=add" class="btn btn-primary btn-block" data-tooltip="Add a new car to your fleet">
                                <i class="fa fa-plus mb-2"></i><br>Add Car
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                            <a href="admin_customers.php?action=add" class="btn btn-success btn-block" data-tooltip="Register a new customer">
                                <i class="fa fa-user-plus mb-2"></i><br>Add Customer
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                            <a href="admin_drivers.php?action=add" class="btn btn-warning btn-block" data-tooltip="Add a new driver to your team">
                                <i class="fa fa-id-card mb-2"></i><br>Add Driver
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                            <a href="admin_bookings.php" class="btn btn-info btn-block" data-tooltip="View and manage all bookings">
                                <i class="fa fa-calendar mb-2"></i><br>View Bookings
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                            <a href="admin_reports.php" class="btn btn-danger btn-block" data-tooltip="Generate detailed reports and analytics">
                                <i class="fa fa-chart-bar mb-2"></i><br>Reports
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                            <a href="admin_settings.php" class="btn btn-secondary btn-block" data-tooltip="Configure system settings">
                                <i class="fa fa-cog mb-2"></i><br>Settings
                            </a>
                        </div>
                    </div>
                </div>
            
                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-lg-8 col-md-12">
                        <div class="panel panel-default fade-in">
                            <div class="panel-heading">
                                <h4><i class="fa fa-clock-o"></i> Recent Bookings</h4>
                                <small class="text-muted">Latest booking activities in your system</small>
                            </div>
                            <div class="panel-body">
                                <?php if (!empty($recent_bookings)): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Customer</th>
                                                <th>Car</th>
                                                <th class="d-none d-md-table-cell">Driver</th>
                                                <th class="d-none d-sm-table-cell">Date</th>
                                                <th>Status</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_bookings as $booking): ?>
                                            <tr>
                                                <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span><?php echo $booking['customer_name']; ?></span>
                                                        <small class="text-muted d-md-none"><?php echo date('M j', strtotime($booking['booking_date'])); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-primary"><?php echo $booking['car_name']; ?></span>
                                                </td>
                                                <td class="d-none d-md-table-cell"><?php echo $booking['driver_name']; ?></td>
                                                <td class="d-none d-sm-table-cell"><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>
                                                    <?php if ($booking['return_status'] == 'R'): ?>
                                                        <span class="label label-success"><i class="fa fa-check"></i> Completed</span>
                                                    <?php else: ?>
                                                        <span class="label label-warning"><i class="fa fa-clock-o"></i> Active</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><strong>Rs. <?php echo number_format($booking['total_amount'] ?? 0); ?></strong></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="admin_bookings.php" class="btn btn-primary">
                                        <i class="fa fa-eye"></i> View All Bookings
                                    </a>
                                </div>
                                <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fa fa-calendar-o fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent bookings found.</p>
                                    <a href="admin_bookings.php" class="btn btn-primary">
                                        <i class="fa fa-plus"></i> Create New Booking
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-12">
                        <div class="panel panel-default fade-in">
                            <div class="panel-heading">
                                <h4><i class="fa fa-pie-chart"></i> System Overview</h4>
                                <small class="text-muted">Visual breakdown of your fleet status</small>
                            </div>
                            <div class="panel-body text-center">
                                <canvas id="overviewChart" width="300" height="200"></canvas>
                                <div class="mt-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="text-success h5"><?php echo round(($stats['available_cars']/$stats['total_cars'])*100); ?>%</div>
                                                <small class="text-muted">Available</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <div class="text-warning h5"><?php echo $stats['active_bookings']; ?></div>
                                                <small class="text-muted">Active Rentals</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>

    <script>
    // Modern Chart Configuration
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('overviewChart').getContext('2d');
        
        // Create gradient colors
        const availableGradient = ctx.createLinearGradient(0, 0, 0, 400);
        availableGradient.addColorStop(0, '#27ae60');
        availableGradient.addColorStop(1, '#2ecc71');
        
        const rentedGradient = ctx.createLinearGradient(0, 0, 0, 400);
        rentedGradient.addColorStop(0, '#e74c3c');
        rentedGradient.addColorStop(1, '#c0392b');
        
        const activeGradient = ctx.createLinearGradient(0, 0, 0, 400);
        activeGradient.addColorStop(0, '#f39c12');
        activeGradient.addColorStop(1, '#e67e22');
        
        const overviewChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Available Cars', 'Rented Cars', 'Active Bookings'],
                datasets: [{
                    data: [
                        <?php echo $stats['available_cars']; ?>,
                        <?php echo $stats['total_cars'] - $stats['available_cars']; ?>,
                        <?php echo $stats['active_bookings']; ?>
                    ],
                    backgroundColor: [
                        availableGradient,
                        rentedGradient,
                        activeGradient
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 3,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12,
                                family: 'Inter'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#3498db',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                },
                cutout: '60%'
            }
        });
        
        // Update chart colors based on theme
        function updateChartTheme() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const textColor = isDark ? '#ffffff' : '#2c3e50';
            
            overviewChart.options.plugins.legend.labels.color = textColor;
            overviewChart.update();
        }
        
        // Listen for theme changes
        const observer = new MutationObserver(updateChartTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme']
        });
        
        // Initial theme update
        updateChartTheme();
    });
    </script>

</body>
</html>