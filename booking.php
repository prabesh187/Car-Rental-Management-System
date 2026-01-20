<!DOCTYPE html>
<html>
<?php 
 include('session_customer.php');
if(!isset($_SESSION['login_customer'])){
    session_destroy();
    header("location: customerlogin.php");
}
?> 
<title>Book Car </title>
<head>
    <script type="text/javascript" src="assets/ajs/angular.min.js"> </script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="shortcut icon" type="image/png" href="assets/img/P.png.png">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/w3css/w3.css">
  <script type="text/javascript" src="assets/js/jquery.min.js"></script>
  <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>  
  <script type="text/javascript" src="assets/js/custom.js"></script> 
 <link rel="stylesheet" type="text/css" media="screen" href="assets/css/clientpage.css" />
</head>
<body ng-app=""> 


      <!-- Navigation -->
     <!-- Navigation -->
     <nav class="navbar navbar-custom navbar-fixed-top" role="navigation" style="color: black">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <i class="fa fa-bars"></i>
                    </button>
                <a class="navbar-brand page-scroll" href="index.php">
                   Car Rentals </a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->

            <?php
                if(isset($_SESSION['login_client'])){
            ?> 
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="index.php">Home</a>
                    </li>
                    <li>
                        <a href="#"><span class="glyphicon glyphicon-user"></span> Welcome <?php echo $_SESSION['login_client']; ?></a>
                    </li>
                    <li>
                    <ul class="nav navbar-nav navbar-right">
            <li><a href="#" class="dropdown-toggle active" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-user"></span> Control Panel <span class="caret"></span> </a>
                <ul class="dropdown-menu">
              <li> <a href="entercar.php">Add Car</a></li>
              <li> <a href="enterdriver.php"> Add Driver</a></li>
              <li> <a href="clientview.php">View</a></li>

            </ul>
            </li>
          </ul>
                    </li>
                    <li>
                        <a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a>
                    </li>
                </ul>
            </div>
            
            <?php
                }
                else if (isset($_SESSION['login_customer'])){
            ?>
            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="index.php">Home</a>
                    </li>
                    <li>
                        <a href="#"><span class="glyphicon glyphicon-user"></span> Welcome <?php echo $_SESSION['login_customer']; ?></a>
                    </li>
                    <ul class="nav navbar-nav">
            <li><a href="#" class="dropdown-toggle active" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Garagge <span class="caret"></span> </a>
                <ul class="dropdown-menu">
              <li> <a href="prereturncar.php">Return Now</a></li>
              <li> <a href="mybookings.php"> My Bookings</a></li>
            </ul>
            </li>
          </ul>
                    <li>
                        <a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a>
                    </li>
                </ul>
            </div>

            <?php
            }
                else {
            ?>

            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="index.php">Home</a>
                    </li>
                    <li>
                        <a href="clientlogin.php">Employee</a>
                    </li>
                    <li>
                        <a href="customerlogin.php">Customer</a>
                    </li>
                    <li>
                        <a href="#"> FAQ </a>
                    </li>
                </ul>
            </div>
                <?php   }
                ?>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>
    
<div class="container" style="margin-top: 65px;" >
    <div class="col-md-7" style="float: none; margin: 0 auto;">
      <div class="form-area">
        <form role="form" action="bookingconfirm.php" method="POST" onsubmit="return validateBookingForm()">
        <br style="clear: both">
          <br>

        <?php
        $car_id = $_GET["id"];
        
        // Check if car exists and is available for booking
        $sql1 = "SELECT * FROM cars WHERE car_id = '$car_id' AND car_availability = 'yes'";
        $result1 = mysqli_query($conn, $sql1);

        if(mysqli_num_rows($result1)){
            while($row1 = mysqli_fetch_assoc($result1)){
                $car_name = $row1["car_name"];
                $car_nameplate = $row1["car_nameplate"];
                $ac_price = $row1["ac_price"];
                $non_ac_price = $row1["non_ac_price"];
                $ac_price_per_day = $row1["ac_price_per_day"];
                $non_ac_price_per_day = $row1["non_ac_price_per_day"];
            }
        } else {
            // Car not available or doesn't exist
            echo "<div class='alert alert-danger' style='margin: 20px 0; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<h4 style='color: #721c24;'>⚠️ Car Not Available</h4>";
            echo "<p style='color: #721c24;'>The selected car is not available for booking or does not exist.</p>";
            echo "<p><a href='index.php' class='btn btn-primary'>← Back to Car Selection</a></p>";
            echo "</div>";
            echo "</div></div></body></html>";
            exit();
        }

        ?>

          <!-- <div class="form-group"> -->
              <h5> Selected Car:&nbsp;  <b><?php echo($car_name);?></b></h5>
         <!-- </div> -->
         
          <!-- <div class="form-group"> -->
            <h5> Number Plate:&nbsp;<b> <?php echo($car_nameplate);?></b></h5>
          <!-- </div>      -->
        <!-- <div class="form-group"> -->
        <?php $today = date("Y-m-d") ?>
          <label><h5>Start Date:</h5></label>
            <input type="date" name="rent_start_date" min="<?php echo($today);?>" required="">
            &nbsp; 
          <label><h5>End Date:</h5></label>
          <input type="date" name="rent_end_date" min="<?php echo($today);?>" required="">
        <!-- </div>      -->
        
        <h5> Choose your car type: <span style="color: red;">*</span> &nbsp;
            <input onclick="reveal()" type="radio" name="radio" value="ac" ng-model="myVar" required> <b>With AC </b>&nbsp;
            <input onclick="reveal()" type="radio" name="radio" value="non_ac" ng-model="myVar" required><b>With-Out AC </b>
                
        
        <div ng-switch="myVar"> 
        <div ng-switch-default>
                    <!-- <div class="form-group"> -->
                <h5>Fare: <h5>    
                <!-- </div>    -->
                     </div>
                    <div ng-switch-when="ac">
                    <!-- <div class="form-group"> -->
                <h5>Fare: <b><?php echo("Rs. " . $ac_price . "/km and Rs. " . $ac_price_per_day . "/day");?></b><h5>    
                <!-- </div>    -->
                     </div>
                     <div ng-switch-when="non_ac">
                     <!-- <div class="form-group"> -->
                <h5>Fare: <b><?php echo("Rs. " . $non_ac_price . "/km and Rs. " . $non_ac_price_per_day . "/day");?></b><h5>    
                <!-- </div>   -->
                     </div>
        </div>

         <h5> Charge type: <span style="color: red;">*</span> &nbsp;
            <input onclick="reveal()" type="radio" name="radio1" value="km" required><b> per KM</b> &nbsp;
            <input onclick="reveal()" type="radio" name="radio1" value="days" required><b> per day</b>

            <br><br>
                <!-- <form class="form-group"> -->
                Select a driver: <span style="color: red;">*</span> &nbsp;
                <select name="driver_id_from_dropdown" ng-model="myVar1" required>
                        <option value="" disabled selected>Select a driver...</option>
                        <?php
                        // Modified query to show ALL available drivers, not just those assigned to the car's client
                        $sql2 = "SELECT * FROM driver d WHERE d.driver_availability = 'yes' ORDER BY d.driver_name";
                        $result2 = mysqli_query($conn, $sql2);

                        if(mysqli_num_rows($result2) > 0){
                            while($row2 = mysqli_fetch_assoc($result2)){
                                $driver_id = $row2["driver_id"];
                                $driver_name = $row2["driver_name"];
                                $driver_gender = $row2["driver_gender"];
                                $driver_phone = $row2["driver_phone"];
                                
                                // Show if driver is assigned to a client or independent
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
                    ?>
  

                    <option value="<?php echo($driver_id); ?>"><?php echo($driver_name . $client_info); ?>
                   

                    <?php }} 
                    else{
                        ?>
                    <option value="" disabled selected>Sorry! No Drivers are currently available, try again later...</option>
                        <?php
                    }
                    ?>
                </select>
                <!-- </form> -->
                <div ng-switch="myVar1">
                

                <?php
                        // Modified query to show ALL available drivers for driver details display
                        $sql3 = "SELECT * FROM driver d WHERE d.driver_availability = 'yes' ORDER BY d.driver_name";
                        $result3 = mysqli_query($conn, $sql3);

                        if(mysqli_num_rows($result3) > 0){
                            while($row3 = mysqli_fetch_assoc($result3)){
                                $driver_id = $row3["driver_id"];
                                $driver_name = $row3["driver_name"];
                                $driver_gender = $row3["driver_gender"];
                                $driver_phone = $row3["driver_phone"];
                                
                                // Get client information for display
                                $client_display = "Independent Driver";
                                if($row3["client_username"]) {
                                    $client_sql = "SELECT client_name FROM clients WHERE client_username = '{$row3["client_username"]}'";
                                    $client_result = mysqli_query($conn, $client_sql);
                                    if($client_result && mysqli_num_rows($client_result) > 0) {
                                        $client_row = mysqli_fetch_assoc($client_result);
                                        $client_display = "Fleet: " . $client_row['client_name'];
                                    }
                                }

                ?>

                <div ng-switch-when="<?php echo($driver_id); ?>">
                    <h5>Driver Name:&nbsp; <b><?php echo($driver_name); ?></b></h5>
                    <p>Gender:&nbsp; <b><?php echo($driver_gender); ?></b> </p>
                    <p>Contact:&nbsp; <b><?php echo($driver_phone); ?></b> </p>
                    <p>Assignment:&nbsp; <b><?php echo($client_display); ?></b> </p>
                </div>
                <?php }} ?>
                </div>
                <input type="hidden" name="hidden_carid" value="<?php echo $car_id; ?>">
                
         
           <input type="submit"name="submit" value="Rent Now" class="btn btn-warning pull-right">     
        </form>
        
      </div>
      <div class="col-md-12" style="float: none; margin: 0 auto; text-align: center;">
            <h6><strong>Note:</strong> You will be charged with extra <span class="text-danger">Rs. 500</span> for each day after the due date ends.</h6>
        </div>
    </div>

<script>
function validateBookingForm() {
    // Check if car type is selected
    var carType = document.querySelector('input[name="radio"]:checked');
    if (!carType) {
        alert('Please select a car type (AC or Non-AC)');
        return false;
    }
    
    // Check if charge type is selected
    var chargeType = document.querySelector('input[name="radio1"]:checked');
    if (!chargeType) {
        alert('Please select a charge type (per KM or per day)');
        return false;
    }
    
    // Check if driver is selected
    var driverSelect = document.querySelector('select[name="driver_id_from_dropdown"]');
    if (!driverSelect.value) {
        alert('Please select a driver');
        return false;
    }
    
    // Check if dates are valid
    var startDate = document.querySelector('input[name="rent_start_date"]').value;
    var endDate = document.querySelector('input[name="rent_end_date"]').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return false;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        alert('End date must be after start date');
        return false;
    }
    
    return true;
}
</script>

</body>
<footer class="site-footer">
        <div class="container">
            <hr>
            <div class="row">
                <div class="col-sm-6">
                    <h5>© <?php echo date("Y"); ?> Car Rentals</h5>
                </div>
            </div>
        </div>
    </footer>
</html>