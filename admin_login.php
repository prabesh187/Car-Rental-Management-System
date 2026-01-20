<?php
session_start();
$error = '';

if (isset($_POST['submit'])) {
    if (empty($_POST['admin_username']) || empty($_POST['admin_password'])) {
        $error = "Username or Password is invalid";
    } else {
        $admin_username = $_POST['admin_username'];
        $admin_password = $_POST['admin_password'];
        
        // Check admin credentials from database
        require 'connection.php';
        $conn = Connect();
        
        // Check if admin_users table exists
        $table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
        
        if ($table_check->num_rows > 0) {
            // Use database authentication
            $sql = "SELECT admin_id, admin_username, admin_password, admin_name FROM admin_users WHERE admin_username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $admin_username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                
                if (password_verify($admin_password, $admin['admin_password'])) {
                    // Update last login
                    $update_login = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE admin_id = ?");
                    $update_login->bind_param("i", $admin['admin_id']);
                    $update_login->execute();
                    
                    $_SESSION['login_admin'] = $admin['admin_username'];
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_name'] = $admin['admin_name'];
                    header("location: admin_dashboard.php");
                } else {
                    $error = "Invalid admin credentials";
                }
            } else {
                $error = "Invalid admin credentials";
            }
        } else {
            // Fallback to hardcoded credentials if table doesn't exist
            if ($admin_username === 'admin' && $admin_password === 'admin123') {
                $_SESSION['login_admin'] = $admin_username;
                $_SESSION['admin_name'] = 'Administrator';
                header("location: admin_dashboard.php");
            } else {
                $error = "Invalid admin credentials";
            }
        }
    }
}

if(isset($_SESSION['login_admin'])){
    header("location: admin_dashboard.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | Car Rental System</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/P.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/w3css/w3.css">
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .admin-login-container {
            margin-top: 100px;
        }
        .admin-panel {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .admin-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .admin-icon {
            font-size: 60px;
            color: #3498db;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container admin-login-container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="admin-panel">
                <div class="admin-header">
                    <i class="fa fa-shield admin-icon"></i>
                    <h2>Admin Panel</h2>
                    <p class="text-muted">Car Rental Management System</p>
                </div>
                
                <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="admin_username">
                            <i class="fa fa-user"></i> Admin Username
                        </label>
                        <input class="form-control" id="admin_username" type="text" 
                               name="admin_username" placeholder="Enter admin username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="admin_password">
                            <i class="fa fa-lock"></i> Admin Password
                        </label>
                        <input class="form-control" id="admin_password" type="password" 
                               name="admin_password" placeholder="Enter admin password" required>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary btn-block btn-lg" name="submit" type="submit">
                            <i class="fa fa-sign-in"></i> Login to Admin Panel
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <small class="text-muted">
                        Default credentials: admin / admin123<br>
                        <a href="index.php">← Back to Main Site</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>