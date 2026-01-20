<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_admin'])){
    header("location: admin_login.php");
    exit();
}

$conn = Connect();

// Get date range for reports
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Revenue Report
$revenue_sql = "SELECT 
    DATE(car_return_date) as return_date,
    COUNT(*) as bookings,
    SUM(total_amount) as daily_revenue
    FROM rentedcars 
    WHERE return_status = 'R' 
    AND car_return_date BETWEEN ? AND ?
    GROUP BY DATE(car_return_date)
    ORDER BY return_date DESC";
$revenue_stmt = $conn->prepare($revenue_sql);
$revenue_stmt->bind_param("ss", $start_date, $end_date);
$revenue_stmt->execute();
$revenue_data = $revenue_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Total statistics
$stats_sql = "SELECT 
    COUNT(*) as total_bookings,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_booking_value,
    COUNT(DISTINCT customer_username) as unique_customers
    FROM rentedcars 
    WHERE return_status = 'R' 
    AND car_return_date BETWEEN ? AND ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("ss", $start_date, $end_date);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Top performing cars
$top_cars_sql = "SELECT 
    c.car_name, c.car_nameplate,
    COUNT(rc.id) as bookings,
    SUM(rc.total_amount) as revenue,
    AVG(rc.total_amount) as avg_revenue
    FROM rentedcars rc
    JOIN cars c ON rc.car_id = c.car_id
    WHERE rc.return_status = 'R' 
    AND rc.car_return_date BETWEEN ? AND ?
    GROUP BY c.car_id
    ORDER BY revenue DESC
    LIMIT 5";
$top_cars_stmt = $conn->prepare($top_cars_sql);
$top_cars_stmt->bind_param("ss", $start_date, $end_date);
$top_cars_stmt->execute();
$top_cars = $top_cars_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Top customers
$top_customers_sql = "SELECT 
    cu.customer_name, cu.customer_phone,
    COUNT(rc.id) as bookings,
    SUM(rc.total_amount) as total_spent
    FROM rentedcars rc
    JOIN customers cu ON rc.customer_username = cu.customer_username
    WHERE rc.return_status = 'R' 
    AND rc.car_return_date BETWEEN ? AND ?
    GROUP BY cu.customer_username
    ORDER BY total_spent DESC
    LIMIT 5";
$top_customers_stmt = $conn->prepare($top_customers_sql);
$top_customers_stmt->bind_param("ss", $start_date, $end_date);
$top_customers_stmt->execute();
$top_customers = $top_customers_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports | Admin Panel</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/P.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .report-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-box {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .stat-box h3 {
            margin: 0;
            font-size: 2.5em;
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
                <li><a href="admin_clients.php"><i class="fa fa-briefcase"></i> Manage Clients</a></li>
                <li><a href="admin_reports.php" class="active"><i class="fa fa-chart-bar"></i> Reports</a></li>

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
                        <h2><i class="fa fa-chart-bar"></i> Business Reports</h2>
                        <p class="text-muted">Analytics and performance insights</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <!-- Date Range Filter -->
                        <form method="GET" class="form-inline">
                            <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo $start_date; ?>">
                            <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo $end_date; ?>">
                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Summary Statistics -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <h3>Rs. <?php echo number_format($stats['total_revenue'] ?? 0); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                        <h3>Rs. <?php echo number_format($stats['avg_booking_value'] ?? 0); ?></h3>
                        <p>Avg Booking Value</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                        <h3><?php echo $stats['unique_customers'] ?? 0; ?></h3>
                        <p>Unique Customers</p>
                    </div>
                </div>
            </div>
            
            <!-- Revenue Chart -->
            <div class="report-card">
                <h4><i class="fa fa-line-chart"></i> Daily Revenue Trend</h4>
                <canvas id="revenueChart" width="400" height="100"></canvas>
            </div>
            
            <!-- Top Performers -->
            <div class="row">
                <div class="col-md-6">
                    <div class="report-card">
                        <h4><i class="fa fa-car"></i> Top Performing Cars</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Car</th>
                                        <th>Bookings</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_cars as $car): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $car['car_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $car['car_nameplate']; ?></small>
                                        </td>
                                        <td><?php echo $car['bookings']; ?></td>
                                        <td>Rs. <?php echo number_format($car['revenue']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="report-card">
                        <h4><i class="fa fa-users"></i> Top Customers</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Bookings</th>
                                        <th>Total Spent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $customer['customer_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $customer['customer_phone']; ?></small>
                                        </td>
                                        <td><?php echo $customer['bookings']; ?></td>
                                        <td>Rs. <?php echo number_format($customer['total_spent']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daily Revenue Table -->
            <div class="report-card">
                <h4><i class="fa fa-table"></i> Daily Revenue Breakdown</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Bookings</th>
                                <th>Revenue</th>
                                <th>Avg per Booking</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenue_data as $day): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($day['return_date'])); ?></td>
                                <td><?php echo $day['bookings']; ?></td>
                                <td>Rs. <?php echo number_format($day['daily_revenue']); ?></td>
                                <td>Rs. <?php echo number_format($day['daily_revenue'] / $day['bookings']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [
            <?php 
            foreach (array_reverse($revenue_data) as $day) {
                echo "'" . date('M j', strtotime($day['return_date'])) . "',";
            }
            ?>
        ],
        datasets: [{
            label: 'Daily Revenue',
            data: [
                <?php 
                foreach (array_reverse($revenue_data) as $day) {
                    echo $day['daily_revenue'] . ",";
                }
                ?>
            ],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: Rs. ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>