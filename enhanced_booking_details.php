<!DOCTYPE html>
<html>
<?php 
include('session_customer.php');
require_once 'algorithms.php';

if(!isset($_SESSION['login_customer'])){
    session_destroy();
    header("location: customerlogin.php");
}

$algorithms = new CarRentalAlgorithms($conn);
$car_id = $_GET["id"];

// Get car details
$sql1 = "SELECT * FROM cars WHERE car_id = '$car_id'";
$result1 = mysqli_query($conn, $sql1);

if(mysqli_num_rows($result1)){
    while($row1 = mysqli_fetch_assoc($result1)){
        $car_name = $row1["car_name"];
        $car_nameplate = $row1["car_nameplate"];
        $ac_price = $row1["ac_price"];
        $non_ac_price = $row1["non_ac_price"];
        $ac_price_per_day = $row1["ac_price_per_day"];
        $non_ac_price_per_day = $row1["non_ac_price_per_day"];
        $car_img = $row1["car_img"];
    }
}
?> 
<title>Enhanced Booking Details</title>
<head>
    <script type="text/javascript" src="assets/ajs/angular.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="shortcut icon" type="image/png" href="assets/img/P.png.png">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/w3css/w3.css">
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>  
    <link rel="stylesheet" type="text/css" media="screen" href="assets/css/clientpage.css" />
    <style>
        .pricing-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
        }
        .dynamic-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 18px;
        }
        .original-price {
            text-decoration: line-through;
            color: #7f8c8d;
        }
        .price-factors {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .algorithm-info {
            background: #3498db;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .driver-recommendation {
            border: 2px solid #27ae60;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background: #d5f4e6;
        }
    </style>
</head>
<body ng-app=""> 

<!-- Navigation -->
<nav class="navbar navbar-custom navbar-fixed-top" role="navigation" style="color: black">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand page-scroll" href="index.php">
               Car Rentals - Enhanced </a>
        </div>
        
        <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
            <ul class="nav navbar-nav">
                <li><a href="index.php">Home</a></li>
                <li><a href="enhanced_booking.php">Back to Search</a></li>
                <li><a href="#"><span class="glyphicon glyphicon-user"></span> Welcome <?php echo $_SESSION['login_customer']; ?></a></li>
                <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
    
<div class="container" style="margin-top: 80px;">
    <div class="row">
        <!-- Car Details -->
        <div class="col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-car"></i> <?php echo($car_name);?></h3>
                </div>
                <div class="panel-body">
                    <img src="<?php echo $car_img; ?>" class="img-responsive" style="width: 100%; height: 250px; object-fit: cover;">
                    <br><br>
                    <h5><strong>Number Plate:</strong> <?php echo($car_nameplate);?></h5>
                    
                    <!-- Algorithm-powered pricing -->
                    <div class="algorithm-info">
                        <i class="fa fa-robot"></i> <strong>AI-Powered Dynamic Pricing</strong>
                        <br><small>Prices automatically adjusted based on demand, season, and availability</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Booking Form -->
        <div class="col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-calendar"></i> Smart Booking System</h3>
                </div>
                <div class="panel-body">
                    <form role="form" action="bookingconfirm.php" method="POST" id="bookingForm">
                        
                        <!-- Date Selection -->
                        <?php $today = date("Y-m-d") ?>
                        <div class="form-group">
                            <label><h5>Start Date:</h5></label>
                            <input type="date" name="rent_start_date" id="start_date" min="<?php echo($today);?>" required="" onchange="updatePricing()">
                        </div>
                        
                        <div class="form-group">
                            <label><h5>End Date:</h5></label>
                            <input type="date" name="rent_end_date" id="end_date" min="<?php echo($today);?>" required="" onchange="updatePricing()">
                        </div>
                        
                        <!-- Car Type Selection -->
                        <h5>Choose your car type:</h5>
                        <div class="form-group">
                            <input onclick="updatePricing()" type="radio" name="radio" value="ac" ng-model="myVar" id="ac_radio"> 
                            <label for="ac_radio"><b>With AC</b></label>&nbsp;&nbsp;
                            <input onclick="updatePricing()" type="radio" name="radio" value="non_ac" ng-model="myVar" id="non_ac_radio">
                            <label for="non_ac_radio"><b>Without AC</b></label>
                        </div>
                        
                        <!-- Charge Type -->
                        <h5>Charge type:</h5>
                        <div class="form-group">
                            <input onclick="updatePricing()" type="radio" name="radio1" value="km" id="km_radio">
                            <label for="km_radio"><b>per KM</b></label>&nbsp;&nbsp;
                            <input onclick="updatePricing()" type="radio" name="radio1" value="days" id="days_radio">
                            <label for="days_radio"><b>per day</b></label>
                        </div>
                        
                        <!-- Dynamic Pricing Display -->
                        <div id="pricing-display" class="pricing-card" style="display: none;">
                            <h4><i class="fa fa-tag"></i> Smart Pricing</h4>
                            <div id="price-content"></div>
                            <div class="price-factors">
                                <small><strong>Price factors considered:</strong></small>
                                <div id="price-factors-list"></div>
                            </div>
                        </div>
                        
                        <!-- Optimal Driver Assignment -->
                        <div class="form-group">
                            <label><h5><i class="fa fa-user"></i> Recommended Driver (AI Selected):</h5></label>
                            <select name="driver_id_from_dropdown" ng-model="myVar1" class="form-control">
                                <?php
                                // Use algorithm to get optimal driver
                                $optimal_driver = $algorithms->assignOptimalDriver($car_id);
                                
                                if ($optimal_driver) {
                                    echo "<option value='{$optimal_driver['driver_id']}' selected>";
                                    echo "{$optimal_driver['driver_name']} (Recommended by AI)";
                                    echo "</option>";
                                }
                                
                                // Get other available drivers (ALL available drivers, not just client-specific)
                                $sql2 = "SELECT * FROM driver d WHERE d.driver_availability = 'yes' 
                                        AND d.driver_id != " . ($optimal_driver['driver_id'] ?? 0) . "
                                        ORDER BY d.driver_name";
                                $result2 = mysqli_query($conn, $sql2);

                                if(mysqli_num_rows($result2) > 0){
                                    while($row2 = mysqli_fetch_assoc($result2)){
                                        // Show client info for better selection
                                        $client_info = "";
                                        if($row2["client_username"]) {
                                            $client_sql = "SELECT client_name FROM clients WHERE client_username = '{$row2["client_username"]}'";
                                            $client_result = mysqli_query($conn, $client_sql);
                                            if($client_result && mysqli_num_rows($client_result) > 0) {
                                                $client_row = mysqli_fetch_assoc($client_result);
                                                $client_info = " (Fleet: {$client_row['client_name']})";
                                            }
                                        } else {
                                            $client_info = " (Independent)";
                                        }
                                        echo "<option value='{$row2['driver_id']}'>{$row2['driver_name']}{$client_info}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <!-- Driver Details -->
                        <?php if ($optimal_driver): ?>
                        <div class="driver-recommendation">
                            <h5><i class="fa fa-star"></i> AI Recommended Driver</h5>
                            <p><strong>Name:</strong> <?php echo $optimal_driver['driver_name']; ?></p>
                            <p><strong>Experience:</strong> <?php echo $optimal_driver['completed_trips'] ?? 0; ?> completed trips</p>
                            <p><strong>Rating:</strong> <?php echo round($optimal_driver['avg_rating'] ?? 5, 1); ?>/5.0</p>
                            <p><strong>Contact:</strong> <?php echo $optimal_driver['driver_phone']; ?></p>
                            <small><i class="fa fa-info-circle"></i> This driver was selected based on performance, availability, and location optimization.</small>
                        </div>
                        <?php endif; ?>
                        
                        <input type="hidden" name="hidden_carid" value="<?php echo $car_id; ?>">
                        
                        <div class="text-center">
                            <input type="submit" name="submit" value="Book with Smart Pricing" class="btn btn-success btn-lg">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Algorithm Information -->
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-info-circle"></i> How Our Smart System Works</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h5><i class="fa fa-line-chart"></i> Dynamic Pricing</h5>
                            <p>Our AI analyzes demand patterns, seasonal trends, and availability to offer you the best possible price in real-time.</p>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="fa fa-user-check"></i> Optimal Driver Matching</h5>
                            <p>Advanced algorithms consider driver performance, location, and experience to assign the best driver for your trip.</p>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="fa fa-shield-alt"></i> Smart Recommendations</h5>
                            <p>Machine learning analyzes your preferences and booking history to provide personalized recommendations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePricing() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const carType = document.querySelector('input[name="radio"]:checked')?.value;
    const chargeType = document.querySelector('input[name="radio1"]:checked')?.value;
    
    if (startDate && endDate && carType && chargeType) {
        // Calculate dynamic pricing via AJAX
        $.ajax({
            url: 'calculate_dynamic_price.php',
            method: 'POST',
            data: {
                car_id: <?php echo $car_id; ?>,
                start_date: startDate,
                end_date: endDate,
                car_type: carType,
                charge_type: chargeType,
                base_ac_price: <?php echo $ac_price_per_day; ?>,
                base_non_ac_price: <?php echo $non_ac_price_per_day; ?>,
                base_ac_km: <?php echo $ac_price; ?>,
                base_non_ac_km: <?php echo $non_ac_price; ?>
            },
            success: function(response) {
                const data = JSON.parse(response);
                displayPricing(data);
            }
        });
    }
}

function displayPricing(data) {
    const pricingDisplay = document.getElementById('pricing-display');
    const priceContent = document.getElementById('price-content');
    const factorsList = document.getElementById('price-factors-list');
    
    let priceHtml = `
        <div class="dynamic-price">Rs. ${data.dynamic_price}</div>
        <div class="original-price">Original: Rs. ${data.base_price}</div>
        <div class="text-success">You save: Rs. ${data.base_price - data.dynamic_price}</div>
    `;
    
    let factorsHtml = `
        <small>• Season adjustment: ${data.season_factor > 0 ? '+' : ''}${(data.season_factor * 100).toFixed(0)}%</small><br>
        <small>• Weekend pricing: ${data.weekend_factor > 0 ? '+' : ''}${(data.weekend_factor * 100).toFixed(0)}%</small><br>
        <small>• Demand factor: ${data.demand_factor > 0 ? '+' : ''}${(data.demand_factor * 100).toFixed(0)}%</small><br>
        <small>• Availability factor: ${data.availability_factor > 0 ? '+' : ''}${(data.availability_factor * 100).toFixed(0)}%</small>
    `;
    
    priceContent.innerHTML = priceHtml;
    factorsList.innerHTML = factorsHtml;
    pricingDisplay.style.display = 'block';
}
</script>

</body>
<footer class="site-footer">
    <div class="container">
        <hr>
        <div class="row">
            <div class="col-sm-6">
                <h5>© <?php echo date("Y"); ?> Car Rentals - Enhanced with AI Algorithms</h5>
            </div>
        </div>
    </div>
</footer>
</html>