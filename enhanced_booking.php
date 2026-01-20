<!DOCTYPE html>
<html>
<?php 
include('session_customer.php');
require_once 'algorithms.php';
require_once 'search_algorithms.php';

if(!isset($_SESSION['login_customer'])){
    session_destroy();
    header("location: customerlogin.php");
}

// Initialize algorithm classes
$algorithms = new CarRentalAlgorithms($conn);
$search = new SearchAlgorithms($conn);
?> 
<title>Enhanced Car Booking</title>
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
        .recommendation-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
        }
        .price-dynamic {
            color: #e74c3c;
            font-weight: bold;
        }
        .price-original {
            text-decoration: line-through;
            color: #7f8c8d;
        }
        .algorithm-badge {
            background: #3498db;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        .search-filters {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body ng-app="carRental"> 

<!-- Navigation (same as original) -->
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
                <li><a href="#"><span class="glyphicon glyphicon-user"></span> Welcome <?php echo $_SESSION['login_customer']; ?></a></li>
                <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container" style="margin-top: 80px;">
    
    <!-- Enhanced Search Filters -->
    <div class="search-filters">
        <h4><i class="fa fa-search"></i> Smart Car Search <span class="algorithm-badge">AI Powered</span></h4>
        <form method="GET" action="">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="car_name" placeholder="Car name..." 
                           value="<?php echo $_GET['car_name'] ?? ''; ?>" id="carNameInput">
                    <div id="autocomplete-results" class="list-group" style="position: absolute; z-index: 1000; width: 100%;"></div>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="min_price" placeholder="Min Price" 
                           value="<?php echo $_GET['min_price'] ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" name="max_price" placeholder="Max Price" 
                           value="<?php echo $_GET['max_price'] ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="sort_by">
                        <option value="balanced" <?php echo ($_GET['sort_by'] ?? '') == 'balanced' ? 'selected' : ''; ?>>Smart Sort</option>
                        <option value="price_low" <?php echo ($_GET['sort_by'] ?? '') == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="popularity" <?php echo ($_GET['sort_by'] ?? '') == 'popularity' ? 'selected' : ''; ?>>Most Popular</option>
                        <option value="rating" <?php echo ($_GET['sort_by'] ?? '') == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search"></i> Smart Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php
    // Get search criteria
    $search_criteria = [
        'car_name' => $_GET['car_name'] ?? '',
        'min_price' => $_GET['min_price'] ?? null,
        'max_price' => $_GET['max_price'] ?? null,
        'preferred_price' => ($_GET['min_price'] ?? 0 + $_GET['max_price'] ?? 1000) / 2
    ];
    
    // Get recommendations for the customer
    $recommendations = $algorithms->recommendCars($_SESSION['login_customer'], 3);
    
    // Get search results
    if (!empty(array_filter($search_criteria))) {
        $cars = $search->advancedCarSearch($search_criteria);
        $cars = $search->intelligentSort($cars, $_GET['sort_by'] ?? 'balanced');
    } else {
        // Default: show all available cars with smart sorting
        $sql = "SELECT c.*, AVG(COALESCE(5, 5)) as avg_rating, COUNT(rc.id) as total_bookings
                FROM cars c
                LEFT JOIN rentedcars rc ON c.car_id = rc.car_id
                WHERE c.car_availability = 'yes'
                GROUP BY c.car_id";
        $result = $conn->query($sql);
        $cars = $result->fetch_all(MYSQLI_ASSOC);
        $cars = $search->intelligentSort($cars, $_GET['sort_by'] ?? 'balanced');
    }
    ?>

    <!-- Personalized Recommendations -->
    <?php if (!empty($recommendations)): ?>
    <div class="row">
        <div class="col-md-12">
            <h3><i class="fa fa-star"></i> Recommended for You <span class="algorithm-badge">ML Powered</span></h3>
            <div class="row">
                <?php foreach ($recommendations as $rec): ?>
                <div class="col-md-4">
                    <div class="recommendation-card">
                        <img src="<?php echo $rec['car']['car_img']; ?>" class="img-responsive" style="height: 150px; width: 100%; object-fit: cover;">
                        <h5><strong><?php echo $rec['car']['car_name']; ?></strong></h5>
                        <p class="text-muted"><?php echo $rec['reason']; ?></p>
                        
                        <?php
                        // Calculate dynamic pricing
                        $today = date('Y-m-d');
                        $tomorrow = date('Y-m-d', strtotime('+1 day'));
                        $dynamic_ac_price = $algorithms->calculateDynamicPrice(
                            $rec['car']['car_id'], 
                            $today, 
                            $tomorrow, 
                            $rec['car']['ac_price_per_day']
                        );
                        ?>
                        
                        <div class="price-info">
                            <span class="price-dynamic">Rs. <?php echo $dynamic_ac_price; ?>/day (AC)</span>
                            <?php if ($dynamic_ac_price != $rec['car']['ac_price_per_day']): ?>
                                <br><span class="price-original">Rs. <?php echo $rec['car']['ac_price_per_day']; ?></span>
                                <span class="algorithm-badge">Dynamic Price</span>
                            <?php endif; ?>
                        </div>
                        
                        <a href="enhanced_booking_details.php?id=<?php echo $rec['car']['car_id']; ?>" 
                           class="btn btn-success btn-sm">
                            <i class="fa fa-car"></i> Book Now
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <hr>
    <?php endif; ?>

    <!-- Search Results -->
    <div class="row">
        <div class="col-md-12">
            <h3><i class="fa fa-list"></i> Available Cars 
                <?php if (!empty(array_filter($search_criteria))): ?>
                    <span class="algorithm-badge">Smart Filtered</span>
                <?php endif; ?>
            </h3>
            
            <?php if (!empty($cars)): ?>
            <div class="row">
                <?php foreach ($cars as $car): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <img src="<?php echo $car['car_img']; ?>" class="img-responsive" 
                                 style="height: 200px; width: 100%; object-fit: cover;">
                            
                            <h4><strong><?php echo $car['car_name']; ?></strong></h4>
                            <p><strong>Plate:</strong> <?php echo $car['car_nameplate']; ?></p>
                            
                            <?php
                            // Dynamic pricing
                            $today = date('Y-m-d');
                            $tomorrow = date('Y-m-d', strtotime('+1 day'));
                            $dynamic_ac_price = $algorithms->calculateDynamicPrice(
                                $car['car_id'], $today, $tomorrow, $car['ac_price_per_day']
                            );
                            $dynamic_non_ac_price = $algorithms->calculateDynamicPrice(
                                $car['car_id'], $today, $tomorrow, $car['non_ac_price_per_day']
                            );
                            ?>
                            
                            <div class="pricing-info">
                                <p><strong>AC:</strong> 
                                    <span class="price-dynamic">Rs. <?php echo $dynamic_ac_price; ?>/day</span>
                                    <?php if ($dynamic_ac_price != $car['ac_price_per_day']): ?>
                                        <br><span class="price-original">Rs. <?php echo $car['ac_price_per_day']; ?></span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Non-AC:</strong> 
                                    <span class="price-dynamic">Rs. <?php echo $dynamic_non_ac_price; ?>/day</span>
                                    <?php if ($dynamic_non_ac_price != $car['non_ac_price_per_day']): ?>
                                        <br><span class="price-original">Rs. <?php echo $car['non_ac_price_per_day']; ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <!-- Algorithm scores for debugging -->
                            <?php if (isset($car['search_score']) || isset($car['relevance_score'])): ?>
                            <div class="text-muted small">
                                <?php if (isset($car['search_score'])): ?>
                                    Search Score: <?php echo $car['search_score']; ?>
                                <?php endif; ?>
                                <?php if (isset($car['relevance_score'])): ?>
                                    | Relevance: <?php echo $car['relevance_score']; ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="text-center" style="margin-top: 15px;">
                                <a href="enhanced_booking_details.php?id=<?php echo $car['car_id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="fa fa-calendar"></i> Book This Car
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <h4>No cars found matching your criteria.</h4>
                <p>Try adjusting your search filters or <a href="enhanced_booking.php">view all available cars</a>.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Autocomplete JavaScript -->
<script>
$(document).ready(function() {
    $('#carNameInput').on('input', function() {
        var query = $(this).val();
        if (query.length > 2) {
            $.ajax({
                url: 'autocomplete.php',
                method: 'GET',
                data: { query: query, type: 'car_name' },
                success: function(data) {
                    var results = JSON.parse(data);
                    var html = '';
                    results.forEach(function(item) {
                        html += '<a href="#" class="list-group-item autocomplete-item" data-value="' + 
                                item.text + '">' + item.text + 
                                ' <span class="badge">' + item.frequency + '</span></a>';
                    });
                    $('#autocomplete-results').html(html).show();
                }
            });
        } else {
            $('#autocomplete-results').hide();
        }
    });
    
    $(document).on('click', '.autocomplete-item', function(e) {
        e.preventDefault();
        $('#carNameInput').val($(this).data('value'));
        $('#autocomplete-results').hide();
    });
    
    $(document).click(function(e) {
        if (!$(e.target).closest('#carNameInput, #autocomplete-results').length) {
            $('#autocomplete-results').hide();
        }
    });
});
</script>

</body>
<footer class="site-footer">
    <div class="container">
        <hr>
        <div class="row">
            <div class="col-sm-6">
                <h5>© <?php echo date("Y"); ?> Car Rentals - Enhanced with AI</h5>
            </div>
        </div>
    </div>
</footer>
</html>