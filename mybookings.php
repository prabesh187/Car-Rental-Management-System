<!DOCTYPE html>
<html>
<?php 
session_start();
require 'connection.php';
$conn = Connect();
?>
<head>
<link rel="shortcut icon" type="image/png" href="assets/img/P.png.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/w3css/w3.css">
<link rel="stylesheet" type="text/css" href="assets/css/customerlogin.css">
<script type="text/javascript" src="assets/js/jquery.min.js"></script>
<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="assets/css/clientpage.css" />
</head>
<body id="page-top" data-spy="scroll" data-target=".navbar-fixed-top">
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

<!-- Current Bookings Section -->
<?php if (mysqli_num_rows($result_current) > 0): ?>
<div class="container">
    <div class="jumbotron" style="background-color: #d4edda; border-color: #c3e6cb;">
        <h1 class="text-center" style="color: #155724;">Your Current Bookings</h1>
        <p class="text-center" style="color: #155724;">Cars currently rented by you</p>
    </div>
</div>

<div class="table-responsive" style="padding-left: 50px; padding-right: 50px;">
    <table class="table table-striped table-bordered">
        <thead class="thead-success">
            <tr>
                <th width="12%">Car</th>
                <th width="10%">Start Date</th>
                <th width="10%">End Date</th>
                <th width="8%">Fare</th>
                <th width="10%">Status</th>
                <th width="15%">Driver</th>
                <th width="15%">Driver Contact</th>
                <th width="15%">Fleet Contact</th>
                <th width="5%">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($current_row = mysqli_fetch_assoc($result_current)): ?>
            <tr style="background-color: #f8f9fa;">
                <td>
                    <strong><?php echo $current_row["car_name"]; ?></strong><br>
                    <small class="text-muted"><?php echo $current_row["car_nameplate"]; ?></small>
                </td>
                <td><?php echo date('M d, Y', strtotime($current_row["rent_start_date"])); ?></td>
                <td><?php echo date('M d, Y', strtotime($current_row["rent_end_date"])); ?></td>
                <td>Rs. <?php 
                    if($current_row["charge_type"] == "days"){
                        echo ($current_row["fare"] . "/day");
                    } else {
                        echo ($current_row["fare"] . "/km");
                    }
                ?></td>
                <td>
                    <?php 
                    $today = date('Y-m-d');
                    $end_date = $current_row["rent_end_date"];
                    if($today > $end_date): ?>
                        <span class="label label-danger">⚠️ Overdue</span>
                    <?php else: ?>
                        <span class="label label-success">✅ Active</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo $current_row["driver_name"]; ?></strong><br>
                    <small class="text-muted"><?php echo $current_row["driver_gender"]; ?></small><br>
                    <small class="text-info">DL: <?php echo $current_row["dl_number"]; ?></small>
                </td>
                <td>
                    <strong><?php echo $current_row["driver_phone"]; ?></strong>
                </td>
                <td>
                    <?php if($current_row["client_name"]): ?>
                        <strong><?php echo $current_row["client_name"]; ?></strong><br>
                        <small class="text-info"><?php echo $current_row["client_phone"]; ?></small>
                    <?php else: ?>
                        <small class="text-muted">Independent Driver</small>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="prereturncar.php" class="btn btn-warning btn-sm">Return</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<hr style="margin: 40px 0;">
<?php endif; ?>
 
<?php $login_customer = $_SESSION['login_customer']; 

    // Query for current bookings (not returned yet)
    $sql_current = "SELECT rc.*, c.*, d.driver_name, d.driver_phone, d.driver_gender, d.dl_number, 
                           cl.client_name, cl.client_phone
                    FROM rentedcars rc 
                    JOIN cars c ON rc.car_id = c.car_id 
                    JOIN driver d ON rc.driver_id = d.driver_id
                    LEFT JOIN clients cl ON d.client_username = cl.client_username
                    WHERE rc.customer_username='$login_customer' AND rc.return_status='NR'
                    ORDER BY rc.booking_date DESC";
    $result_current = $conn->query($sql_current);

    // Enhanced query to include driver and client information for completed bookings
    $sql1 = "SELECT rc.*, c.*, d.driver_name, d.driver_phone, d.driver_gender, d.dl_number, 
                    cl.client_name, cl.client_phone
             FROM rentedcars rc 
             JOIN cars c ON rc.car_id = c.car_id 
             JOIN driver d ON rc.driver_id = d.driver_id
             LEFT JOIN clients cl ON d.client_username = cl.client_username
             WHERE rc.customer_username='$login_customer' AND rc.return_status='R'
             ORDER BY rc.booking_date DESC";
    $result1 = $conn->query($sql1);

    if (mysqli_num_rows($result1) > 0) {
?>
<div class="container">
      <div class="jumbotron">
        <h1 class="text-center">Your Bookings</h1>
        <p class="text-center"> Hope you enjoyed our service </p>
      </div>
    </div>

    <div class="table-responsive" style="padding-left: 100px; padding-right: 100px;" >
<table class="table table-striped">
  <thead class="thead-dark">
<tr>
<th width="12%">Car</th>
<th width="10%">Start Date</th>
<th width="10%">End Date</th>
<th width="8%">Fare</th>
<th width="10%">Distance (kms)</th>
<th width="8%">Days</th>
<th width="12%">Total Amount</th>
<th width="15%">Driver</th>
<th width="15%">Contact</th>
</tr>
</thead>
<?php
        while($row = mysqli_fetch_assoc($result1)) {
?>
<tr>
<td><?php echo $row["car_name"]; ?></td>
<td><?php echo $row["rent_start_date"] ?></td>
<td><?php echo $row["rent_end_date"]; ?></td>
<td>Rs.  <?php 
            if($row["charge_type"] == "days"){
                    echo ($row["fare"] . "/day");
                } else {
                    echo ($row["fare"] . "/km");
                }
            ?></td>
<td><?php  if($row["charge_type"] == "days"){
                    echo ("-");
                } else {
                    echo ($row["distance"]);
                } ?></td>
<td><?php echo $row["no_of_days"]; ?> </td>
<td>Rs.  <?php echo $row["total_amount"]; ?></td>
<td>
    <strong><?php echo $row["driver_name"]; ?></strong><br>
    <small class="text-muted"><?php echo $row["driver_gender"]; ?></small><br>
    <small class="text-info">DL: <?php echo $row["dl_number"]; ?></small>
</td>
<td>
    <strong><?php echo $row["driver_phone"]; ?></strong><br>
    <?php if($row["client_name"]): ?>
        <small class="text-muted">Fleet: <?php echo $row["client_name"]; ?></small><br>
        <small class="text-info"><?php echo $row["client_phone"]; ?></small>
    <?php else: ?>
        <small class="text-muted">Independent Driver</small>
    <?php endif; ?>
</td>
</tr>
<?php        } ?>
                </table>
                </div> 
        <?php } else {
            ?>
        <div class="container">
      <div class="jumbotron">
        <h1 class="text-center">You have not rented any cars till now!</h1>
        <p class="text-center"> Please rent cars in order to view your data here. </p>
      </div>
    </div>

            <?php
        } ?>   

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