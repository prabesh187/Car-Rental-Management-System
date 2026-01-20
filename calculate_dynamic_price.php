<?php
require 'connection.php';
require_once 'algorithms.php';

$conn = Connect();
$algorithms = new CarRentalAlgorithms($conn);

if ($_POST) {
    $car_id = $_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $car_type = $_POST['car_type'];
    $charge_type = $_POST['charge_type'];
    
    // Determine base price
    $base_price = 0;
    if ($car_type == 'ac' && $charge_type == 'days') {
        $base_price = $_POST['base_ac_price'];
    } elseif ($car_type == 'ac' && $charge_type == 'km') {
        $base_price = $_POST['base_ac_km'];
    } elseif ($car_type == 'non_ac' && $charge_type == 'days') {
        $base_price = $_POST['base_non_ac_price'];
    } elseif ($car_type == 'non_ac' && $charge_type == 'km') {
        $base_price = $_POST['base_non_ac_km'];
    }
    
    // Calculate dynamic price
    $dynamic_price = $algorithms->calculateDynamicPrice($car_id, $start_date, $end_date, $base_price);
    
    // Calculate individual factors for display
    $season_factor = 0;
    $weekend_factor = 0;
    $demand_factor = 0;
    $availability_factor = 0;
    
    // Season factor
    $month = date('n', strtotime($start_date));
    if (in_array($month, [12, 1, 2])) {
        $season_factor = 0.2;
    } elseif (in_array($month, [6, 7, 8])) {
        $season_factor = 0.3;
    }
    
    // Weekend factor
    $start_day = date('N', strtotime($start_date));
    if ($start_day >= 5) {
        $weekend_factor = 0.15;
    }
    
    // Demand factor (simplified)
    $sql = "SELECT COUNT(*) as bookings FROM rentedcars WHERE rent_start_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_assoc()['bookings'];
    $demand_factor = min($bookings * 0.05, 0.5);
    
    // Availability factor
    $sql = "SELECT COUNT(*) as available_cars FROM cars WHERE car_availability = 'yes'";
    $available = $conn->query($sql)->fetch_assoc()['available_cars'];
    if ($available < 3) {
        $availability_factor = 0.3;
    } elseif ($available < 5) {
        $availability_factor = 0.15;
    }
    
    $response = [
        'base_price' => $base_price,
        'dynamic_price' => $dynamic_price,
        'season_factor' => $season_factor,
        'weekend_factor' => $weekend_factor,
        'demand_factor' => $demand_factor,
        'availability_factor' => $availability_factor,
        'total_multiplier' => ($dynamic_price / $base_price) - 1
    ];
    
    echo json_encode($response);
}
?>