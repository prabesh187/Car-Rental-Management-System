<?php
session_start(); // Starting Session
$error=''; // Variable To Store Error Message

if (isset($_POST['submit'])) {
    if (empty($_POST['client_username']) || empty($_POST['client_password'])) {
        $error = "Username or Password is invalid";
    } else {
        // Define $username and $password
        $client_username = $_POST['client_username'];
        $client_password = $_POST['client_password'];
        
        // Establishing Connection with Server
        require 'connection.php';
        $conn = Connect();

        // SQL query to fetch information of registered users and finds user match.
        $query = "SELECT client_username, client_password FROM clients WHERE client_username=? LIMIT 1";

        // To protect MySQL injection for Security purpose
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $client_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $client = $result->fetch_assoc();
            
            // Simple plain text password comparison
            if ($client_password === $client['client_password']) {
                $_SESSION['login_client'] = $client['client_username'];
                header("location: index.php");
                exit();
            } else {
                $error = "Username or Password is invalid";
            }
        } else {
            $error = "Username or Password is invalid";
        }
        mysqli_close($conn); // Closing Connection
    }
}
?>