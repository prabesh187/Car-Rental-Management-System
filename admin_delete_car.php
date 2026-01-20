<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['login_admin'])){
    header("location: admin_login.php");
    exit();
}

$conn = Connect();

if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    
    // Check if car has any bookings
    $check_bookings_sql = "SELECT COUNT(*) as count FROM rentedcars WHERE car_id = ?";
    $check_stmt = $conn->prepare($check_bookings_sql);
    $check_stmt->bind_param("i", $car_id);
    $check_stmt->execute();
    $booking_count = $check_stmt->get_result()->fetch_assoc()['count'];
    
    if ($booking_count > 0) {
        $_SESSION['admin_message'] = '<div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> 
            Cannot delete car with existing bookings (' . $booking_count . ' bookings found). 
            Set car to unavailable instead or contact system administrator.
        </div>';
    } else {
        // Start transaction for safe deletion
        $conn->begin_transaction();
        
        try {
            // Get car details for confirmation message
            $car_sql = "SELECT car_name, car_nameplate FROM cars WHERE car_id = ?";
            $car_stmt = $conn->prepare($car_sql);
            $car_stmt->bind_param("i", $car_id);
            $car_stmt->execute();
            $car_data = $car_stmt->get_result()->fetch_assoc();
            
            if (!$car_data) {
                throw new Exception("Car not found");
            }
            
            // Delete from clientcars table first (foreign key constraint)
            $delete_clientcars_sql = "DELETE FROM clientcars WHERE car_id = ?";
            $stmt1 = $conn->prepare($delete_clientcars_sql);
            $stmt1->bind_param("i", $car_id);
            
            if (!$stmt1->execute()) {
                throw new Exception("Failed to remove car from client assignments: " . $conn->error);
            }
            
            // Delete from cars table
            $delete_car_sql = "DELETE FROM cars WHERE car_id = ?";
            $stmt2 = $conn->prepare($delete_car_sql);
            $stmt2->bind_param("i", $car_id);
            
            if (!$stmt2->execute()) {
                throw new Exception("Failed to delete car: " . $conn->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['admin_message'] = '<div class="alert alert-success">
                <i class="fa fa-check-circle"></i> 
                Car "' . htmlspecialchars($car_data['car_name']) . '" (' . htmlspecialchars($car_data['car_nameplate']) . ') 
                has been successfully deleted from the system.
            </div>';
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $_SESSION['admin_message'] = '<div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i> 
                Error deleting car: ' . htmlspecialchars($e->getMessage()) . '
            </div>';
        }
    }
} else {
    $_SESSION['admin_message'] = '<div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i> 
        Invalid car ID provided.
    </div>';
}

// Redirect back to cars management page
header("location: admin_cars.php");
exit();
?>