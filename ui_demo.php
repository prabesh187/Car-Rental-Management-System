<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern UI Demo - Car Rental System</title>
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
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand page-scroll" href="#page-top">
                    <i class="fa fa-car"></i> Car Rentals
                </a>
            </div>

            <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                <ul class="nav navbar-nav">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#cars">Cars</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="admin_login.php" class="btn-modern" style="margin-left: 10px;">Admin Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Premium Car Rentals</h1>
                <p class="hero-subtitle">Experience luxury and comfort with our modern fleet of vehicles and professional drivers</p>
                <div class="hero-cta">
                    <a href="#cars" class="btn-modern btn-lg">Browse Cars</a>
                    <a href="#features" class="btn-modern btn-success-modern btn-lg" style="margin-left: 15px;">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section-modern">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Us?</h2>
                <p>We provide the best car rental experience with modern technology and exceptional service</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-car"></i>
                        </div>
                        <h4>Modern Fleet</h4>
                        <p>Our cars are regularly maintained and updated with the latest models for your comfort and safety.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-user-tie"></i>
                        </div>
                        <h4>Professional Drivers</h4>
                        <p>Experienced and licensed drivers who know the city well and prioritize your safety.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <h4>24/7 Service</h4>
                        <p>Round-the-clock availability for all your transportation needs, whenever you need us.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cars Section -->
    <section id="cars" class="section-modern" style="background: var(--bg-secondary);">
        <div class="container">
            <div class="section-title">
                <h2>Our Premium Fleet</h2>
                <p>Choose from our wide range of luxury and economy vehicles</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="car-card">
                        <img src="assets/img/cars/car1.jpg" alt="BMW 6-Series" class="car-image" onerror="this.src='https://via.placeholder.com/400x200/3498db/ffffff?text=BMW+6-Series'">
                        <div class="car-details">
                            <h4 class="car-name">BMW 6-Series</h4>
                            <p class="car-price">Rs. 3,500 / day</p>
                            <p class="text-muted">Luxury sedan with premium features</p>
                            <a href="#" class="btn-modern">Book Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="car-card">
                        <img src="assets/img/cars/car2.jpg" alt="Audi A4" class="car-image" onerror="this.src='https://via.placeholder.com/400x200/27ae60/ffffff?text=Audi+A4'">
                        <div class="car-details">
                            <h4 class="car-name">Audi A4</h4>
                            <p class="car-price">Rs. 3,200 / day</p>
                            <p class="text-muted">Elegant design with advanced technology</p>
                            <a href="#" class="btn-modern">Book Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="car-card">
                        <img src="assets/img/cars/car3.jpg" alt="Mercedes E-Class" class="car-image" onerror="this.src='https://via.placeholder.com/400x200/e74c3c/ffffff?text=Mercedes+E-Class'">
                        <div class="car-details">
                            <h4 class="car-name">Mercedes E-Class</h4>
                            <p class="car-price">Rs. 4,000 / day</p>
                            <p class="text-muted">Ultimate luxury and comfort</p>
                            <a href="#" class="btn-modern">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center" style="margin-top: 40px;">
                <a href="booking.php" class="btn-modern btn-lg">View All Cars</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section-modern">
        <div class="container">
            <div class="section-title">
                <h2>Get In Touch</h2>
                <p>Ready to book your next ride? Contact us today!</p>
            </div>
            
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="form-modern">
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Full Name</label>
                                        <input type="text" class="form-control-modern" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-modern">
                                        <label class="form-label-modern">Email Address</label>
                                        <input type="email" class="form-control-modern" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Phone Number</label>
                                <input type="tel" class="form-control-modern" required>
                            </div>
                            
                            <div class="form-group-modern">
                                <label class="form-label-modern">Message</label>
                                <textarea class="form-control-modern" rows="4" required></textarea>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn-modern btn-lg">Send Message</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="footer-section">
                        <h4><i class="fa fa-car"></i> Car Rentals</h4>
                        <p>Premium car rental service with modern fleet and professional drivers. Your comfort and safety are our top priorities.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="footer-section">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="#home">Home</a></li>
                            <li><a href="#cars">Our Cars</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="footer-section">
                        <h4>Contact Info</h4>
                        <ul>
                            <li><i class="fa fa-phone"></i> +977-1-234567</li>
                            <li><i class="fa fa-envelope"></i> info@carrentals.com</li>
                            <li><i class="fa fa-map-marker"></i> Kathmandu, Nepal</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0 20px;">
            
            <div class="text-center">
                <p>&copy; 2024 Car Rentals. All rights reserved. | Modern UI with Dark/Light Theme</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Demo notification
        setTimeout(() => {
            if (window.modernCustomer) {
                window.modernCustomer.showNotification('Welcome to the new modern UI! Try the dark/light theme toggle.', 'success');
            }
        }, 2000);
    </script>
</body>
</html>