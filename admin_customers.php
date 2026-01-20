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
    if (isset($_POST['add_customer'])) {
        $customer_username = $conn->real_escape_string($_POST['customer_username']);
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $customer_phone = $conn->real_escape_string($_POST['customer_phone']);
        $customer_email = $conn->real_escape_string($_POST['customer_email']);
        $customer_address = $conn->real_escape_string($_POST['customer_address']);
        $customer_password = $conn->real_escape_string($_POST['customer_password']);
        
        // Check for duplicate username or email
        $check_sql = "SELECT customer_username, customer_email FROM customers WHERE customer_username = ? OR customer_email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $customer_username, $customer_email);
        $check_stmt->execute();
        $existing = $check_stmt->get_result()->fetch_assoc();
        
        if ($existing) {
            if ($existing['customer_username'] === $customer_username) {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Username already exists! Please choose a different username.</div>';
            } else {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Email address already exists! Please use a different email.</div>';
            }
        } else {
            $sql = "INSERT INTO customers (customer_username, customer_name, customer_phone, customer_email, customer_address, customer_password) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $customer_username, $customer_name, $customer_phone, $customer_email, $customer_address, $customer_password);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Customer added successfully!</div>';
                $action = 'list';
            } else {
                $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error adding customer: ' . $conn->error . '</div>';
            }
        }
    }
    
    if (isset($_POST['update_customer'])) {
        $original_username = $_POST['original_username'];
        $customer_username = $conn->real_escape_string($_POST['customer_username']);
        $customer_name = $conn->real_escape_string($_POST['customer_name']);
        $customer_phone = $conn->real_escape_string($_POST['customer_phone']);
        $customer_email = $conn->real_escape_string($_POST['customer_email']);
        $customer_address = $conn->real_escape_string($_POST['customer_address']);
        $customer_password = $conn->real_escape_string($_POST['customer_password']);
        
        // Handle password update - only update if password field is not empty
        $update_password = !empty($customer_password);
        
        // Start transaction for safe update
        $conn->begin_transaction();
        
        try {
            // Check for duplicate username or email (excluding current customer)
            $check_sql = "SELECT customer_username, customer_email FROM customers WHERE (customer_username = ? OR customer_email = ?) AND customer_username != ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("sss", $customer_username, $customer_email, $original_username);
            $check_stmt->execute();
            $existing = $check_stmt->get_result()->fetch_assoc();
            
            if ($existing) {
                if ($existing['customer_username'] === $customer_username) {
                    throw new Exception("Username already exists! Please choose a different username.");
                } else {
                    throw new Exception("Email address already exists! Please use a different email.");
                }
            }
            
            // If username is changing, we need to update related tables first
            if ($original_username !== $customer_username) {
                // Update rentedcars table first
                $update_rentals_sql = "UPDATE rentedcars SET customer_username = ? WHERE customer_username = ?";
                $update_rentals_stmt = $conn->prepare($update_rentals_sql);
                $update_rentals_stmt->bind_param("ss", $customer_username, $original_username);
                $update_rentals_stmt->execute();
            }
            
            // Update customer record - with or without password
            if ($update_password) {
                $sql = "UPDATE customers SET customer_username=?, customer_name=?, customer_phone=?, customer_email=?, customer_address=?, customer_password=? WHERE customer_username=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", $customer_username, $customer_name, $customer_phone, $customer_email, $customer_address, $customer_password, $original_username);
            } else {
                $sql = "UPDATE customers SET customer_username=?, customer_name=?, customer_phone=?, customer_email=?, customer_address=? WHERE customer_username=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssss", $customer_username, $customer_name, $customer_phone, $customer_email, $customer_address, $original_username);
            }
            
            if ($stmt->execute()) {
                $conn->commit();
                $message = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> Customer updated successfully!</div>';
                $action = 'list';
            } else {
                throw new Exception("Error updating customer: " . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Error updating customer: ' . $e->getMessage() . '</div>';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $customer_username = $_GET['delete'];
    
    // Check if customer has bookings
    $check_sql = "SELECT COUNT(*) as count FROM rentedcars WHERE customer_username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $customer_username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        $message = '<div class="alert alert-warning">Cannot delete customer with existing bookings.</div>';
    } else {
        $sql = "DELETE FROM customers WHERE customer_username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $customer_username);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Customer deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error deleting customer: ' . $conn->error . '</div>';
        }
    }
}

// Get customer for editing
$edit_customer = null;
if ($action == 'edit' && isset($_GET['username'])) {
    $customer_username = $_GET['username'];
    $sql = "SELECT * FROM customers WHERE customer_username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $customer_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_customer = $result->fetch_assoc();
    
    // If customer not found, redirect to list with error
    if (!$edit_customer) {
        $message = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Customer not found!</div>';
        $action = 'list';
    }
}

// Get all customers with booking count
$customers_sql = "SELECT c.*, COUNT(rc.id) as booking_count, 
                  SUM(CASE WHEN rc.return_status = 'R' THEN rc.total_amount ELSE 0 END) as total_spent
                  FROM customers c 
                  LEFT JOIN rentedcars rc ON c.customer_username = rc.customer_username 
                  GROUP BY c.customer_username 
                  ORDER BY c.customer_name";
$customers_result = $conn->query($customers_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers | Admin Panel</title>
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
                    <li><a href="admin_customers.php" class="active"><i class="fa fa-users"></i> Manage Customers</a></li>
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
                            <h2><i class="fa fa-users"></i> Manage Customers</h2>
                            <p class="text-muted">Complete CRUD operations for customer management with enhanced functionality</p>
                        </div>
                        <div class="col-md-4 col-sm-12 text-right">
                            <?php if ($action == 'list'): ?>
                            <a href="?action=add" class="btn btn-primary" data-tooltip="Add a new customer to the system">
                                <i class="fa fa-plus"></i> Add New Customer
                            </a>
                            <?php else: ?>
                            <a href="admin_customers.php" class="btn btn-secondary" data-tooltip="Return to customer list">
                                <i class="fa fa-list"></i> Back to List
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            
            <?php echo $message; ?>
            
                <?php if ($action == 'add' || $action == 'edit'): ?>
                <!-- Add/Edit Customer Form -->
                <div class="panel panel-default fade-in">
                    <div class="panel-heading">
                        <h4>
                            <i class="fa fa-<?php echo $action == 'add' ? 'plus' : 'edit'; ?>"></i>
                            <?php echo $action == 'add' ? 'Add New Customer' : 'Edit Customer Details'; ?>
                        </h4>
                        <small class="text-muted">
                            <?php echo $action == 'add' ? 'Fill in the customer information below' : 'Update customer information as needed'; ?>
                        </small>
                    </div>
                    <div class="panel-body">
                        <form method="POST" id="customerForm">
                            <?php if ($action == 'edit'): ?>
                            <input type="hidden" name="original_username" value="<?php echo htmlspecialchars($edit_customer['customer_username']); ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-user"></i> Username *</label>
                                        <input type="text" class="form-control" name="customer_username" 
                                               value="<?php echo htmlspecialchars($edit_customer['customer_username'] ?? ''); ?>" 
                                               required pattern="[a-zA-Z0-9_]+" 
                                               title="Username can only contain letters, numbers, and underscores"
                                               <?php echo $action == 'edit' ? 'data-tooltip="Changing username will update all related records"' : ''; ?>>
                                        <small class="text-muted">Only letters, numbers, and underscores allowed</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-id-card"></i> Full Name *</label>
                                        <input type="text" class="form-control" name="customer_name" 
                                               value="<?php echo htmlspecialchars($edit_customer['customer_name'] ?? ''); ?>" 
                                               required minlength="2" maxlength="100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-phone"></i> Phone Number *</label>
                                        <input type="tel" class="form-control" name="customer_phone" 
                                               value="<?php echo htmlspecialchars($edit_customer['customer_phone'] ?? ''); ?>" 
                                               required pattern="[0-9]{10}" maxlength="10"
                                               title="Please enter exactly 10 digits" placeholder="10-digit phone number">
                                        <small class="text-muted">Enter exactly 10 digits (e.g., 9841234567)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><i class="fa fa-envelope"></i> Email Address *</label>
                                        <input type="email" class="form-control" name="customer_email" 
                                               value="<?php echo htmlspecialchars($edit_customer['customer_email'] ?? ''); ?>" 
                                               required maxlength="100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label><i class="fa fa-map-marker"></i> Address *</label>
                                        <textarea class="form-control" name="customer_address" rows="3" 
                                                  required maxlength="255" 
                                                  placeholder="Enter complete address including city and area"><?php echo htmlspecialchars($edit_customer['customer_address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><i class="fa fa-lock"></i> Password <?php echo $action == 'add' ? '*' : ''; ?></label>
                                        <input type="password" class="form-control" name="customer_password" 
                                               value="" 
                                               <?php echo $action == 'add' ? 'required' : ''; ?> 
                                               minlength="6" maxlength="50"
                                               placeholder="<?php echo $action == 'edit' ? 'Leave empty to keep current password' : 'Enter password'; ?>">
                                        <?php if ($action == 'edit'): ?>
                                        <small class="text-muted"><i class="fa fa-info-circle"></i> Leave empty to keep current password, or enter new password to change</small>
                                        <?php else: ?>
                                        <small class="text-muted">Minimum 6 characters required</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($action == 'edit' && $edit_customer): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Customer Statistics:</strong>
                                <?php
                                $stats_sql = "SELECT COUNT(*) as bookings, SUM(total_amount) as total_spent FROM rentedcars WHERE customer_username = ?";
                                $stats_stmt = $conn->prepare($stats_sql);
                                $stats_stmt->bind_param("s", $edit_customer['customer_username']);
                                $stats_stmt->execute();
                                $stats = $stats_stmt->get_result()->fetch_assoc();
                                ?>
                                Total Bookings: <strong><?php echo $stats['bookings']; ?></strong> | 
                                Total Spent: <strong>Rs. <?php echo number_format($stats['total_spent'] ?? 0); ?></strong>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <button type="submit" name="<?php echo $action == 'add' ? 'add_customer' : 'update_customer'; ?>" 
                                        class="btn btn-success btn-lg">
                                    <i class="fa fa-save"></i> <?php echo $action == 'add' ? 'Add Customer' : 'Update Customer'; ?>
                                </button>
                                <a href="admin_customers.php" class="btn btn-secondary btn-lg">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                                <?php if ($action == 'edit'): ?>
                                <a href="admin_bookings.php?customer=<?php echo urlencode($edit_customer['customer_username']); ?>" 
                                   class="btn btn-info btn-lg">
                                    <i class="fa fa-calendar"></i> View Bookings
                                </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            
                <?php else: ?>
                <!-- Customers List -->
                <div class="panel panel-default fade-in">
                    <div class="panel-heading">
                        <h4><i class="fa fa-users"></i> All Customers</h4>
                        <small class="text-muted">Manage all registered customers in the system</small>
                    </div>
                    <div class="panel-body">
                        <?php if ($customers_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-list-ol"></i> S.No</th>
                                        <th><i class="fa fa-user"></i> Username</th>
                                        <th><i class="fa fa-id-card"></i> Name</th>
                                        <th class="d-none d-md-table-cell"><i class="fa fa-phone"></i> Phone</th>
                                        <th class="d-none d-lg-table-cell"><i class="fa fa-envelope"></i> Email</th>
                                        <th class="d-none d-xl-table-cell"><i class="fa fa-map-marker"></i> Address</th>
                                        <th><i class="fa fa-calendar"></i> Bookings</th>
                                        <th><i class="fa fa-money"></i> Total Spent</th>
                                        <th><i class="fa fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $customers_result->data_seek(0); // Reset result pointer
                                    $serial_number = 1;
                                    while ($customer = $customers_result->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $serial_number++; ?></strong></td>
                                        <td>
                                            <strong class="text-primary"><?php echo htmlspecialchars($customer['customer_username']); ?></strong>
                                            <div class="d-md-none">
                                                <small class="text-muted"><?php echo htmlspecialchars($customer['customer_phone']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($customer['customer_name']); ?>
                                            <div class="d-lg-none">
                                                <small class="text-muted"><?php echo htmlspecialchars($customer['customer_email']); ?></small>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($customer['customer_phone']); ?></td>
                                        <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($customer['customer_email']); ?></td>
                                        <td class="d-none d-xl-table-cell">
                                            <span data-tooltip="<?php echo htmlspecialchars($customer['customer_address']); ?>">
                                                <?php echo htmlspecialchars(substr($customer['customer_address'], 0, 30)) . (strlen($customer['customer_address']) > 30 ? '...' : ''); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="label <?php echo $customer['booking_count'] > 0 ? 'label-success' : 'label-default'; ?>">
                                                <?php echo $customer['booking_count']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">Rs. <?php echo number_format($customer['total_spent'] ?? 0); ?></strong>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?action=edit&username=<?php echo urlencode($customer['customer_username']); ?>" 
                                                   class="btn btn-primary" title="Edit Customer" data-tooltip="Edit customer details">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="admin_bookings.php?customer=<?php echo urlencode($customer['customer_username']); ?>" 
                                                   class="btn btn-info" title="View Bookings" data-tooltip="View customer bookings">
                                                    <i class="fa fa-calendar"></i>
                                                </a>
                                                <?php if ($customer['booking_count'] == 0): ?>
                                                <a href="?delete=<?php echo urlencode($customer['customer_username']); ?>" 
                                                   class="btn btn-danger" title="Delete Customer" data-tooltip="Delete customer (no bookings)"
                                                   onclick="return confirm('Are you sure you want to delete customer \'<?php echo htmlspecialchars($customer['customer_name']); ?>\'?\n\nThis action cannot be undone.')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                                <?php else: ?>
                                                <button class="btn btn-secondary" disabled title="Cannot delete customer with bookings" data-tooltip="Customer has existing bookings">
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
                        
                        <div class="text-center mt-3">
                            <p class="text-muted">
                                <i class="fa fa-info-circle"></i>
                                Total Customers: <strong><?php echo $customers_result->num_rows; ?></strong>
                            </p>
                        </div>
                        
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa fa-users fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Customers Found</h4>
                            <p class="text-muted">Start by adding your first customer to the system.</p>
                            <a href="?action=add" class="btn btn-primary btn-lg">
                                <i class="fa fa-plus"></i> Add First Customer
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Enhanced form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('customerForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const username = form.querySelector('input[name="customer_username"]').value;
                    const phone = form.querySelector('input[name="customer_phone"]').value;
                    const email = form.querySelector('input[name="customer_email"]').value;
                    
                    // Username validation
                    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                        alert('Username can only contain letters, numbers, and underscores.');
                        e.preventDefault();
                        return;
                    }
                    
                    // Phone validation
                    if (!/^[0-9+\-\s()]+$/.test(phone)) {
                        alert('Please enter a valid phone number.');
                        e.preventDefault();
                        return;
                    }
                    
                    // Email validation
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        alert('Please enter a valid email address.');
                        e.preventDefault();
                        return;
                    }
                    
                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                    
                    // Re-enable button after 5 seconds (in case of error)
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 5000);
                });
            }
        });
    </script>
        </div>
    </div>
</div>

</body>
</html>