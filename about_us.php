<?php
include 'config.php';
include 'nav.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - FitPro Fitness Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-about {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            text-align: center;
        }
        
        .mission-section {
            background-color: #f8f9fa;
            padding: 80px 0;
        }
        
        .team-member {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .team-img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 5px solid #e9ecef;
        }
        
        .values-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #0d6efd;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
   <!-- Hero Section -->
<section class="hero-about">
    <div class="container-fluid px-4">
        <h1 class="display-4 fw-bold mb-4">About FitPro</h1>
        <p class="lead">Your partner in achieving fitness excellence</p>
    </div>
</section>

    <!-- Our Story -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="fw-bold mb-4">Our Story</h2>
                    <p class="mb-4">Founded in 2008, FitPro began as a small local gym with a big vision: to make personalized fitness accessible to everyone. What started as a single location has grown into a comprehensive fitness management system serving thousands of members.</p>
                    <p class="mb-4">We believe that fitness is not just about physical transformation but about building confidence, discipline, and a positive mindset that extends to all areas of life.</p>
                    <p>Our team of certified trainers, nutritionists, and wellness experts work together to create holistic programs that deliver sustainable results.</p>
                </div>
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80" alt="Our Gym" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="text-center p-4">
                        <div class="values-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="fw-bold">Our Mission</h3>
                        <p>To empower individuals to achieve their fitness goals through personalized training, nutritional guidance, and a supportive community that inspires lasting lifestyle changes.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center p-4">
                        <div class="values-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="fw-bold">Our Vision</h3>
                        <p>To become the leading fitness management platform that transforms how people approach health and wellness, making professional guidance accessible to everyone, everywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Values</h2>
                <p class="text-muted">The principles that guide everything we do</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="values-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4>Passion</h4>
                    <p class="text-muted">We're genuinely passionate about fitness and helping our members achieve their goals.</p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="values-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Community</h4>
                    <p class="text-muted">We believe in the power of community to motivate, inspire, and support.</p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="values-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Progress</h4>
                    <p class="text-muted">We focus on continuous improvement in our services and our members' results.</p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="values-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h4>Support</h4>
                    <p class="text-muted">We provide unwavering support to our members throughout their fitness journey.</p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="values-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h4>Excellence</h4>
                    <p class="text-muted">We strive for excellence in every aspect of our service and facilities.</p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="values-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Integrity</h4>
                    <p class="text-muted">We operate with honesty, transparency, and the highest ethical standards.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Journey -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Journey</h2>
                <p class="text-muted">Milestones in our growth and development</p>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <h4>2008</h4>
                    <p>FitPro founded as a single-location gym with 5 trainers and 100 founding members.</p>
                </div>
                
                <div class="timeline-item">
                    <h4>2012</h4>
                    <p>Expanded to three locations and introduced our first digital membership tracking system.</p>
                </div>
                
                <div class="timeline-item">
                    <h4>2015</h4>
                    <p>Launched personalized training programs and nutritional guidance services.</p>
                </div>
                
                <div class="timeline-item">
                    <h4>2018</h4>
                    <p>Developed the first version of our fitness management software for internal use.</p>
                </div>
                
                <div class="timeline-item">
                    <h4>2020</h4>
                    <p>Pivoted to include virtual training options during the global pandemic.</p>
                </div>
                
                <div class="timeline-item">
                    <h4>2023</h4>
                    <p>Launched the current FitPro Fitness Management System serving 5,000+ members.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>FitPro Fitness</h5>
                    <p>Your journey to a healthier lifestyle starts here.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="about_us.php" class="text-white text-decoration-none">About Us</a></li>
                        <li><a href="membership_plans.php" class="text-white text-decoration-none">Membership</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Fitness St, City</li>
                        <li><i class="fas fa-phone me-2"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope me-2"></i> info@fitpro.com</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; 2023 FitPro Fitness Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>