<?php
session_start();

// Simple demo login for testing profiles
if (isset($_GET['type']) && isset($_GET['username'])) {
    $type = $_GET['type'];
    $username = $_GET['username'];
    
    switch ($type) {
        case 'customer':
            $_SESSION['login_customer'] = $username;
            echo json_encode(['status' => 'success', 'message' => 'Customer demo login successful']);
            break;
            
        case 'client':
            $_SESSION['login_client'] = $username;
            echo json_encode(['status' => 'success', 'message' => 'Client demo login successful']);
            break;
            
        case 'driver':
            $_SESSION['driver_id'] = $username;
            echo json_encode(['status' => 'success', 'message' => 'Driver demo login successful']);
            break;
            
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid login type']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}
?>