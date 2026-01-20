<?php
session_start(); // Starting Session
$error=''; // Variable To Store Error Message

if (isset($_POST['submit'])) {
    if (empty($_POST['customer_username']) || empty($_POST['customer_password'])) {
        $error = "Username or Password is invalid";
    } else {
        // Define $username and $password
        $customer_username = $_POST['customer_username'];
        $customer_password = $_POST['customer_password'];
        
        // Establishing Connection with Server
        require 'connection.php';
        $conn = Connect();

        // SQL query to fetch information of registered users and finds user match.
        $query = "SELECT customer_username, customer_password FROM customers WHERE customer_username=? LIMIT 1";

        // To protect MySQL injection for Security purpose
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $customer_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            
            // Check if password is hashed (starts with $2y$, $2a$, or $2x$ for bcrypt)
            if (strpos($customer['customer_password'], '$2y$') === 0 || 
                strpos($customer['customer_password'], '$2a$') === 0 || 
                strpos($customer['customer_password'], '$2x$') === 0) {
                
                // Use password_verify for hashed passwords
                if (password_verify($customer_password, $customer['customer_password'])) {
                    $_SESSION['login_customer'] = $customer['customer_username'];
                    header("location: index.php");
                    exit();
                } else {
                    $error = "Username or Password is invalid";
                }
            } else {
                // Fallback for plain text passwords (for existing users)
                if ($customer_password === $customer['customer_password']) {
                    // Update to hashed password for security
                    $hashed_password = password_hash($customer_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE customers SET customer_password=? WHERE customer_username=?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("ss", $hashed_password, $customer_username);
                    $update_stmt->execute();
                    
                    $_SESSION['login_customer'] = $customer['customer_username'];
                    header("location: index.php");
                    exit();
                } else {
                    $error = "Username or Password is invalid";
                }
            }
        } else {
            $error = "Username or Password is invalid";
        }
        mysqli_close($conn); // Closing Connection
    }
}
?>