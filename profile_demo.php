<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Demo | Car Rental System</title>
    <link rel="shortcut icon" type="image/png" href="assets/img/P.png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/modern-customer.css">
    
    <!-- JavaScript Libraries -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/modern-customer.js"></script>
</head>

<body>
    <!-- Theme Toggle Button -->
    <div class="theme-toggle">
        <i class="fa fa-moon-o" id="theme-icon"></i>
        <span id="theme-text">Dark</span>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand page-scroll" href="index.php">
                    <i class="fa fa-car"></i> Car Rental System
                </a>
            </div>
        </div>
    </nav>

    <!-- Profile Demo Section -->
    <section class="section-modern" style="margin-top: 80px;">
        <div class="container">
            <div class="section-title">
                <h2>Profile System Demo</h2>
                <p>Explore the new profile features for customers, clients, and drivers</p>
            </div>
            
            <div class="row">
                <!-- Customer Profile -->
                <div class="col-md-4">
                    <div class="modern-card text-center">
                        <div class="feature-icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <h4>Customer Profile</h4>
                        <p class="text-muted">Personal profile for customers who rent cars. View booking history, update personal information, and track spending.</p>
                        
                        <div class="profile-features">
                            <ul class="list-unstyled text-left">
                                <li><i class="fa fa-check text-success"></i> Personal information management</li>
                                <li><i class="fa fa-check text-success"></i> Booking history and statistics</li>
                                <li><i class="fa fa-check text-success"></i> Total spending tracking</li>
                                <li><i class="fa fa-check text-success"></i> Password update functionality</li>
                                <li><i class="fa fa-check text-success"></i> Recent bookings overview</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <a href="customer_profile.php" class="btn-modern" onclick="demoLogin('customer')">
                                <i class="fa fa-eye"></i> View Customer Profile
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Client Profile -->
                <div class="col-md-4">
                    <div class="modern-card text-center">
                        <div class="feature-icon">
                            <i class="fa fa-briefcase"></i>
                        </div>
                        <h4>Client Profile</h4>
                        <p class="text-muted">Business profile for fleet owners. Manage business information, view fleet statistics, and track revenue.</p>
                        
                        <div class="profile-features">
                            <ul class="list-unstyled text-left">
                                <li><i class="fa fa-check text-success"></i> Business information management</li>
                                <li><i class="fa fa-check text-success"></i> Fleet statistics (cars & drivers)</li>
                                <li><i class="fa fa-check text-success"></i> Revenue tracking and analytics</li>
                                <li><i class="fa fa-check text-success"></i> Recent bookings for fleet</li>
                                <li><i class="fa fa-check text-success"></i> Business contact updates</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <a href="client_profile.php" class="btn-modern btn-success-modern" onclick="demoLogin('client')">
                                <i class="fa fa-eye"></i> View Client Profile
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Driver Profile -->
                <div class="col-md-4">
                    <div class="modern-card text-center">
                        <div class="feature-icon">
                            <i class="fa fa-id-card"></i>
                        </div>
                        <h4>Driver Profile</h4>
                        <p class="text-muted">Professional profile for drivers. View trip history, performance ratings, and update contact information.</p>
                        
                        <div class="profile-features">
                            <ul class="list-unstyled text-left">
                                <li><i class="fa fa-check text-success"></i> Driver performance statistics</li>
                                <li><i class="fa fa-check text-success"></i> Trip history and earnings</li>
                                <li><i class="fa fa-check text-success"></i> Rating system display</li>
                                <li><i class="fa fa-check text-success"></i> Contact information updates</li>
                                <li><i class="fa fa-check text-success"></i> Availability status</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <a href="driver_profile.php?driver_id=1" class="btn-modern btn-warning-modern">
                                <i class="fa fa-eye"></i> View Driver Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Differences Explanation -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="modern-card">
                        <h4><i class="fa fa-info-circle"></i> System Roles Explained</h4>
                        <p class="text-muted">Understanding the difference between customers, clients, and drivers</p>
                        
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="role-explanation">
                                    <h5><i class="fa fa-user text-primary"></i> Customers</h5>
                                    <p><strong>Who:</strong> End users who rent cars</p>
                                    <p><strong>Purpose:</strong> Book cars for personal/business use</p>
                                    <p><strong>Access:</strong> Customer portal to make bookings</p>
                                    <p><strong>Example:</strong> John needs a car for vacation</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="role-explanation">
                                    <h5><i class="fa fa-briefcase text-success"></i> Clients</h5>
                                    <p><strong>Who:</strong> Car owners/fleet companies</p>
                                    <p><strong>Purpose:</strong> Provide cars to the rental system</p>
                                    <p><strong>Access:</strong> Client portal to manage fleet</p>
                                    <p><strong>Example:</strong> "Harry's Car Fleet" company</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="role-explanation">
                                    <h5><i class="fa fa-id-card text-warning"></i> Drivers</h5>
                                    <p><strong>Who:</strong> Professional drivers</p>
                                    <p><strong>Purpose:</strong> Drive cars for customers</p>
                                    <p><strong>Access:</strong> Driver portal for trip management</p>
                                    <p><strong>Example:</strong> Bruno works for Harry's fleet</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Password Update Fix -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="modern-card">
                        <h4><i class="fa fa-lock"></i> Password Update Fix</h4>
                        <p class="text-muted">Enhanced password management across all user types</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>What was fixed:</h5>
                                <ul>
                                    <li><i class="fa fa-check text-success"></i> Optional password updates (leave empty to keep current)</li>
                                    <li><i class="fa fa-check text-success"></i> Proper validation for new passwords</li>
                                    <li><i class="fa fa-check text-success"></i> Secure password handling in admin panel</li>
                                    <li><i class="fa fa-check text-success"></i> Transaction-based updates for data integrity</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>How it works now:</h5>
                                <ul>
                                    <li><i class="fa fa-info text-info"></i> Password field is optional during updates</li>
                                    <li><i class="fa fa-info text-info"></i> Only updates password if new one is provided</li>
                                    <li><i class="fa fa-info text-info"></i> Maintains existing password if field is empty</li>
                                    <li><i class="fa fa-info text-info"></i> Proper error handling and user feedback</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="admin_customers.php" class="btn-modern">
                                <i class="fa fa-cog"></i> Test Admin Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function demoLogin(type) {
            // Simple demo login simulation
            if (type === 'customer') {
                // Set a demo customer session
                fetch('demo_login.php?type=customer&username=james')
                    .then(() => window.location.href = 'customer_profile.php')
                    .catch(() => alert('Demo login failed. Please try accessing through proper login.'));
            } else if (type === 'client') {
                // Set a demo client session
                fetch('demo_login.php?type=client&username=harry')
                    .then(() => window.location.href = 'client_profile.php')
                    .catch(() => alert('Demo login failed. Please try accessing through proper login.'));
            }
        }
    </script>

    <style>
        .profile-features ul {
            margin: 20px 0;
        }
        
        .profile-features li {
            padding: 5px 0;
            font-size: 14px;
        }
        
        .role-explanation {
            padding: 20px;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 20px;
        }
        
        .role-explanation h5 {
            margin-bottom: 15px;
        }
        
        .role-explanation p {
            margin-bottom: 8px;
            font-size: 14px;
        }
    </style>
</body>
</html>