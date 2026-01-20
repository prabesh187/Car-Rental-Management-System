<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_admin'])){
    header("location: admin_login.php");
    exit();
}

$conn = Connect();
$message = '';

// Handle settings updates
if ($_POST) {
    if (isset($_POST['update_admin_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        try {
            // Validate input
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception("All password fields are required!");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match!");
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception("New password must be at least 6 characters long!");
            }
            
            // Check if admin_users table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
            
            if ($table_check->num_rows > 0) {
                // Use database authentication
                $admin_username = $_SESSION['login_admin'];
                
                // Get current admin data
                $sql = "SELECT admin_id, admin_password FROM admin_users WHERE admin_username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $admin_username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    
                    // Verify current password
                    if (password_verify($current_password, $admin['admin_password'])) {
                        // Update password
                        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_sql = "UPDATE admin_users SET admin_password = ?, updated_at = NOW() WHERE admin_id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $new_password_hash, $admin['admin_id']);
                        
                        if ($update_stmt->execute()) {
                            $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Password updated successfully! Please login again with your new password.</div>';
                        } else {
                            throw new Exception("Error updating password in database!");
                        }
                    } else {
                        throw new Exception("Current password is incorrect!");
                    }
                } else {
                    throw new Exception("Admin user not found!");
                }
            } else {
                // Fallback for systems without admin table
                if ($current_password === 'admin123') {
                    $message = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Password validation successful, but database table not found. Please run <a href="setup_admin.php">setup_admin.php</a> to enable password updates.</div>';
                } else {
                    throw new Exception("Current password is incorrect!");
                }
            }
            
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' . $e->getMessage() . '</div>';
        }
    }
    
    if (isset($_POST['backup_database'])) {
        // Simple backup simulation
        $backup_file = "backup_" . date('Y-m-d_H-i-s') . ".sql";
        $message = '<div class="alert alert-info">Database backup initiated: ' . $backup_file . ' (This is a demo)</div>';
    }
}

// Get system statistics
$stats = [];

// Database size (approximate)
$result = $conn->query("SELECT 
    SUM(data_length + index_length) / 1024 / 1024 AS 'db_size_mb'
    FROM information_schema.tables 
    WHERE table_schema = 'carrentalp'");
$stats['db_size'] = $result ? round($result->fetch_assoc()['db_size_mb'], 2) : 0;

// Total records
$stats['total_cars'] = $conn->query("SELECT COUNT(*) as count FROM cars")->fetch_assoc()['count'];
$stats['total_customers'] = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
$stats['total_drivers'] = $conn->query("SELECT COUNT(*) as count FROM driver")->fetch_assoc()['count'];
$stats['total_bookings'] = $conn->query("SELECT COUNT(*) as count FROM rentedcars")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings | Admin Panel</title>
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
                    <li><a href="admin_dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li><a href="admin_cars.php"><i class="fa fa-car"></i> Manage Cars</a></li>
                    <li><a href="admin_customers.php"><i class="fa fa-users"></i> Manage Customers</a></li>
                    <li><a href="admin_drivers.php"><i class="fa fa-id-card"></i> Manage Drivers</a></li>
                    <li><a href="admin_bookings.php"><i class="fa fa-calendar"></i> Manage Bookings</a></li>
                    <li><a href="admin_clients.php"><i class="fa fa-briefcase"></i> Manage Clients</a></li>
                    <li><a href="admin_reports.php"><i class="fa fa-chart-bar"></i> Reports</a></li>
                    <li><a href="admin_settings.php" class="active"><i class="fa fa-cog"></i> Settings</a></li>
                    <li><a href="admin_logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <div class="row">
                    <div class="col-md-6">
                        <h2><i class="fa fa-cog"></i> Admin Settings</h2>
                        <p class="text-muted">System configuration and maintenance</p>
                    </div>
                    <div class="col-md-6 text-right">
                        <span class="text-muted">
                            <i class="fa fa-user"></i> Logged in as: <strong><?php echo $_SESSION['login_admin']; ?></strong>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php echo $message; ?>
            
            <!-- Admin Profile Information -->
            <div class="settings-card fade-in">
                <h4><i class="fa fa-user-circle"></i> Admin Profile</h4>
                <p class="text-muted">Current admin account information</p>
                
                <?php
                // Get admin profile information
                $admin_info = null;
                $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
                
                if ($table_check->num_rows > 0) {
                    $admin_sql = "SELECT admin_username, admin_name, admin_email, created_at, last_login FROM admin_users WHERE admin_username = ?";
                    $admin_stmt = $conn->prepare($admin_sql);
                    $admin_username = $_SESSION['login_admin'];
                    $admin_stmt->bind_param("s", $admin_username);
                    $admin_stmt->execute();
                    $admin_result = $admin_stmt->get_result();
                    $admin_info = $admin_result->fetch_assoc();
                }
                ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-info">
                            <h5><i class="fa fa-user"></i> Username</h5>
                            <p class="info-value"><?php echo htmlspecialchars($_SESSION['login_admin']); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-info">
                            <h5><i class="fa fa-id-card"></i> Display Name</h5>
                            <p class="info-value"><?php echo htmlspecialchars($admin_info['admin_name'] ?? $_SESSION['admin_name'] ?? 'Administrator'); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if ($admin_info): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-info">
                            <h5><i class="fa fa-envelope"></i> Email</h5>
                            <p class="info-value"><?php echo htmlspecialchars($admin_info['admin_email'] ?? 'Not set'); ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-info">
                            <h5><i class="fa fa-calendar"></i> Account Created</h5>
                            <p class="info-value"><?php echo $admin_info['created_at'] ? date('M j, Y g:i A', strtotime($admin_info['created_at'])) : 'Unknown'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="profile-info">
                            <h5><i class="fa fa-clock-o"></i> Last Login</h5>
                            <p class="info-value"><?php echo $admin_info['last_login'] ? date('M j, Y g:i A', strtotime($admin_info['last_login'])) : 'Current session'; ?></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-info">
                            <h5><i class="fa fa-shield"></i> Account Status</h5>
                            <p class="info-value"><span class="label label-success">Active</span></p>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Legacy Admin Account:</strong> You're using the legacy admin system. 
                    <a href="setup_admin.php" target="_blank">Run setup</a> to enable advanced admin features.
                </div>
                <?php endif; ?>
            </div>
            
            <!-- System Information -->
            <div class="settings-card fade-in">
                <h4><i class="fa fa-info-circle"></i> System Information</h4>
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-primary"><?php echo $stats['total_cars']; ?></h3>
                            <p>Total Cars</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-success"><?php echo $stats['total_customers']; ?></h3>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-warning"><?php echo $stats['total_drivers']; ?></h3>
                            <p>Total Drivers</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h3 class="text-info"><?php echo $stats['total_bookings']; ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Database Size:</strong> <?php echo $stats['db_size']; ?> MB</p>
                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                        <p><strong>System Status:</strong> <span class="text-success">Online</span></p>
                    </div>
                </div>
            </div>
            
            <!-- Admin Password Change -->
            <div class="settings-card fade-in">
                <h4><i class="fa fa-lock"></i> Change Admin Password</h4>
                <p class="text-muted">Update your admin password for enhanced security</p>
                
                <form method="POST" class="form-horizontal" id="passwordForm">
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><i class="fa fa-key"></i> Current Password:</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" name="current_password" 
                                   required minlength="1" placeholder="Enter your current password">
                            <small class="text-muted">Enter your existing admin password</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><i class="fa fa-lock"></i> New Password:</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" name="new_password" 
                                   required minlength="6" placeholder="Enter new password">
                            <small class="text-muted">Minimum 6 characters required</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><i class="fa fa-check"></i> Confirm Password:</label>
                        <div class="col-sm-6">
                            <input type="password" class="form-control" name="confirm_password" 
                                   required minlength="6" placeholder="Confirm new password">
                            <small class="text-muted">Re-enter the new password</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                            <button type="submit" name="update_admin_password" class="btn btn-primary btn-lg">
                                <i class="fa fa-save"></i> Update Password
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('passwordForm').reset();">
                                <i class="fa fa-refresh"></i> Reset Form
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="alert alert-info mt-3">
                    <i class="fa fa-info-circle"></i>
                    <strong>Security Tips:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Use a strong password with letters, numbers, and symbols</li>
                        <li>Don't use the same password for multiple accounts</li>
                        <li>Change your password regularly for better security</li>
                        <li>Keep your password confidential and secure</li>
                    </ul>
                </div>
            </div>
            
            <!-- Database Management -->
            <div class="settings-card">
                <h4><i class="fa fa-database"></i> Database Management</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Backup Database</h5>
                        <p class="text-muted">Create a backup of the current database</p>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="backup_database" class="btn btn-success" 
                                    onclick="return confirm('Create database backup?')">
                                <i class="fa fa-download"></i> Create Backup
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h5>System Maintenance</h5>
                        <p class="text-muted">Perform system maintenance tasks</p>
                        <button class="btn btn-warning" onclick="alert('Maintenance mode activated (Demo)')">
                            <i class="fa fa-wrench"></i> Maintenance Mode
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="settings-card">
                <h4><i class="fa fa-bolt"></i> Quick Actions</h4>
                <div class="row">
                    <div class="col-md-3">
                        <a href="admin_dashboard.php" class="btn btn-primary btn-block">
                            <i class="fa fa-dashboard"></i><br>Dashboard
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="admin_reports.php" class="btn btn-info btn-block">
                            <i class="fa fa-chart-bar"></i><br>View Reports
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="admin_dashboard.php" class="btn btn-success btn-block">
                            <i class="fa fa-dashboard"></i><br>Dashboard
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="check_assets.php" class="btn btn-warning btn-block">
                            <i class="fa fa-folder"></i><br>Check Assets
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- System Logs (Demo) -->
            <div class="settings-card">
                <h4><i class="fa fa-list"></i> Recent System Activity</h4>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Action</th>
                                <th>User</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo date('H:i:s'); ?></td>
                                <td>Admin Login</td>
                                <td><?php echo $_SESSION['login_admin']; ?></td>
                                <td><span class="label label-success">Success</span></td>
                            </tr>
                            <tr>
                                <td><?php echo date('H:i:s', strtotime('-5 minutes')); ?></td>
                                <td>Database Query</td>
                                <td>System</td>
                                <td><span class="label label-info">Info</span></td>
                            </tr>
                            <tr>
                                <td><?php echo date('H:i:s', strtotime('-10 minutes')); ?></td>
                                <td>Car Added</td>
                                <td>Admin</td>
                                <td><span class="label label-success">Success</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

    <style>
        .profile-info {
            margin-bottom: 20px;
            padding: 15px;
            background: var(--bg-secondary);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }
        
        .profile-info h5 {
            margin: 0 0 8px 0;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 600;
        }
        
        .info-value {
            margin: 0;
            font-size: 16px;
            color: var(--text-primary);
            font-weight: 500;
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
    </style>

    <script>
        // Enhanced password form validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.getElementById('passwordForm');
            
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const currentPassword = passwordForm.querySelector('input[name="current_password"]').value;
                    const newPassword = passwordForm.querySelector('input[name="new_password"]').value;
                    const confirmPassword = passwordForm.querySelector('input[name="confirm_password"]').value;
                    
                    // Validation
                    if (currentPassword.length < 1) {
                        alert('Please enter your current password.');
                        e.preventDefault();
                        return;
                    }
                    
                    if (newPassword.length < 6) {
                        alert('New password must be at least 6 characters long.');
                        e.preventDefault();
                        return;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        alert('New passwords do not match. Please check and try again.');
                        e.preventDefault();
                        return;
                    }
                    
                    if (currentPassword === newPassword) {
                        alert('New password must be different from current password.');
                        e.preventDefault();
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = passwordForm.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating Password...';
                    submitBtn.disabled = true;
                    
                    // Re-enable button after 10 seconds (in case of error)
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 10000);
                });
                
                // Real-time password confirmation validation
                const newPasswordField = passwordForm.querySelector('input[name="new_password"]');
                const confirmPasswordField = passwordForm.querySelector('input[name="confirm_password"]');
                
                function validatePasswordMatch() {
                    if (confirmPasswordField.value && newPasswordField.value !== confirmPasswordField.value) {
                        confirmPasswordField.setCustomValidity('Passwords do not match');
                        confirmPasswordField.style.borderColor = '#e74c3c';
                    } else {
                        confirmPasswordField.setCustomValidity('');
                        confirmPasswordField.style.borderColor = '';
                    }
                }
                
                newPasswordField.addEventListener('input', validatePasswordMatch);
                confirmPasswordField.addEventListener('input', validatePasswordMatch);
            }
        });
    </script>

</body>
</html>