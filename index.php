<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitSync Pro | Gym Management & Fitness Tracking</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #ff6600;
            --gradient-primary: linear-gradient(135deg, #007bff, #0056b3);
            --gradient-secondary: linear-gradient(135deg, #ff6600, #cc5200);
            --light-gradient: linear-gradient(180deg, #007bff1a, #ff66001a);
        }
        
        body {
            background: var(--light-gradient);
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 100px 0;
            background: var(--light-gradient);
        }
        
        .hero-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }
        
        .hero-text {
            flex: 1;
            min-width: 300px;
            animation: fadeInLeft 1s ease-out;
        }
        
        .hero-text h1 {
            font-size: 3.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-text p {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 30px;
            color: #555;
        }
        
        .hero-slideshow {
            flex: 1;
            min-width: 300px;
            height: 550px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
            animation: fadeInRight 1s ease-out;
            position: relative;
        }
        
        .slideshow-container {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background-size: cover;
            background-position: center;
        }
        
        .slide.active {
            opacity: 1;
        }
        
        .slide-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        
        .slide-control {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .slide-control.active {
            background: var(--primary-color);
            transform: scale(1.2);
        }
        
        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .hero-buttons .btn {
            border-radius: 50px;
            padding: 0.75rem 2rem;
            transition: 0.3s;
            font-weight: 500;
            border: none;
        }
        
        .hero-buttons .btn-primary {
            background: var(--gradient-primary);
        }
        
        .hero-buttons .btn-secondary {
            background: var(--gradient-secondary);
        }
        
        .hero-buttons .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        /* Section Styling */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: inline-block;
            position: relative;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            width: 70%;
            height: 4px;
            background: var(--gradient-secondary);
            bottom: -10px;
            left: 15%;
            border-radius: 2px;
        }
        
        /* Feature Cards */
        .feature-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 15px;
            overflow: hidden;
            border: none;
            height: 100%;
            background: #fff;
            padding: 30px 20px;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-gradient);
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        /* Product Cards */
        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            background-color: #fff;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        
        .product-card img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .product-card .card-body {
            text-align: center;
            padding: 20px;
        }
        
        .product-card .btn {
            border-radius: 50px;
            padding: 8px 25px;
            background: var(--gradient-primary);
            border: none;
        }
        
        /* Values Section */
        .values-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }
        
        .value-item {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            flex: 1;
            min-width: 250px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .value-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .value-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        /* Search Section */
        .search-section {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 60px;
        }
        
        .search-section h3 {
            margin-bottom: 25px;
            color: var(--primary-color);
        }
        
        /* Footer */
        .footer {
            background: #333;
            color: #fff;
            padding: 70px 0 30px;
            margin-top: 80px;
        }
        
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .footer h5 {
            color: #fff;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        .footer ul {
            list-style: none;
            padding: 0;
        }
        
        .footer ul li {
            margin-bottom: 12px;
        }
        
        .footer ul li a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer ul li a:hover {
            color: var(--secondary-color);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            color: #fff;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 30px;
            margin-top: 50px;
            text-align: center;
            color: #aaa;
            font-size: 0.9rem;
        }
        
        /* Animations */
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero-container {
                flex-direction: column;
            }
            
            .hero-text, .hero-slideshow {
                width: 100%;
            }
            
            .hero-text h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<?php include 'header.php'; ?>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-container">
            <div class="hero-text">
                <h1>Transform Your Fitness Journey with FitSync Pro</h1>
                <p>Track calories, monitor progress, connect with trainers, and achieve your fitness goals with our all-in-one gym management platform. Join thousands of members who have transformed their lives with our system.</p>
                
                <div class="hero-buttons">
                    <button class="btn btn-primary">Get Started Free</button>
                    <button class="btn btn-secondary">Book a Demo</button>
                </div>
                
                <div class="mt-5">
                    <h4><i class="fas fa-check-circle text-primary"></i> Admin Dashboard</h4>
                    <h4><i class="fas fa-check-circle text-primary"></i> Member Progress Tracking</h4>
                    <h4><i class="fas fa-check-circle text-primary"></i> Trainer Management</h4>
                    <h4><i class="fas fa-check-circle text-primary"></i> Secure Payment Processing</h4>
                </div>
            </div>
            
            <div class="hero-slideshow">
                <div class="slideshow-container">
                    <div class="slide active" style="background-image: url('img/1.jpg');"></div>
                    <div class="slide" style="background-image: url('img/2.jpg');"></div>
                    <div class="slide" style="background-image: url('img/3.jpg');"></div>
                    <div class="slide" style="background-image: url('img/4.jpg');"></div>
                    <div class="slide" style="background-image: url('img/5.jpg');"></div>
                </div>
                <div class="slide-controls">
                    <div class="slide-control active" data-index="0"></div>
                    <div class="slide-control" data-index="1"></div>
                    <div class="slide-control" data-index="2"></div>
                    <div class="slide-control" data-index="3"></div>
                    <div class="slide-control" data-index="4"></div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="container py-5">
        <div class="section-title">
            <h2>Our Features</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h4>Admin Dashboard</h4>
                    <p>Manage members, trainers, and gym operations with our comprehensive admin panel. Add, delete, and modify data with ease.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Progress Tracking</h4>
                    <p>Members can log and track their fitness progress over time with detailed analytics and visualization tools.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h4>Workout Plans</h4>
                    <p>Trainers can create, modify, and assign personalized workout plans to clients based on their fitness goals.</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h4>Secure Payments</h4>
                    <p>Integrated secure online payment options for membership fees and trainer bookings with automated receipts.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Users Section -->
    <section class="container py-5">
        <div class="section-title">
            <h2>Our Users</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="product-card">
                    <img src="img/6.jpg" alt="Gym Members">
                    <div class="card-body">
                        <h4 class="card-title">Gym Members</h4>
                        <p class="card-text">Track progress, book trainers, manage memberships, and achieve fitness goals with personalized plans.</p>
                        <a href="#" class="btn btn-primary">Member Login</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="product-card">
                    <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Trainers">
                    <div class="card-body">
                        <h4 class="card-title">Trainers</h4>
                        <p class="card-text">Manage clients, create workout plans, track attendance, and update dietary recommendations.</p>
                        <a href="#" class="btn btn-primary">Trainer Login</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="product-card">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80" alt="Admin">
                    <div class="card-body">
                        <h4 class="card-title">Gym Administrators</h4>
                        <p class="card-text">Oversee operations, manage memberships, coordinate trainers, and monitor gym performance.</p>
                        <a href="#" class="btn btn-primary">Admin Login</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    
    
    <!-- Values Section -->
    <section class="container py-5">
        <div class="section-title">
            <h2>Our Values</h2>
        </div>
        
        <div class="values-container">
            <div class="value-item">
                <div class="value-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h4>Health First</h4>
                <p>We prioritize your physical and mental wellbeing above all else in our fitness approach.</p>
            </div>
            
            <div class="value-item">
                <div class="value-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h4>Community</h4>
                <p>Building a supportive fitness community where members motivate and inspire each other.</p>
            </div>
            
            <div class="value-item">
                <div class="value-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h4>Security</h4>
                <p>Your data and payment information are protected with enterprise-grade security measures.</p>
            </div>
            
            <div class="value-item">
                <div class="value-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h4>Innovation</h4>
                <p>Continuously improving our platform with the latest fitness technology and methodologies.</p>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-logo">FitSync Pro</div>
                    <p>Your all-in-one solution for gym management, fitness tracking, and personal training coordination. Join thousands who have transformed their fitness journey with us.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Features</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Services</h5>
                    <ul>
                        <li><a href="#">Member Management</a></li>
                        <li><a href="#">Trainer Coordination</a></li>
                        <li><a href="#">Progress Tracking</a></li>
                        <li><a href="#">Payment Processing</a></li>
                        <li><a href="#">Nutrition Planning</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Us</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Fitness Street, Health City</li>
                        <li><i class="fas fa-phone me-2"></i> (555) 123-4567</li>
                        <li><i class="fas fa-envelope me-2"></i> info@fitsyncpro.com</li>
                    </ul>
                    <div class="mt-4">
                        <h6>Subscribe to Newsletter</h6>
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your Email">
                            <button class="btn btn-primary" type="button">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 FitSync Pro. All rights reserved. | Designed for Gym Management & Fitness Tracking</p>
                <p class="mt-2">This system helps track calories, plan exercises, and promote strong muscles and bones through regular physical activity.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Slideshow functionality
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.slide');
            const controls = document.querySelectorAll('.slide-control');
            let currentSlide = 0;
            
            // Function to show a specific slide
            function showSlide(index) {
                // Hide all slides
                slides.forEach(slide => slide.classList.remove('active'));
                controls.forEach(control => control.classList.remove('active'));
                
                // Show selected slide
                slides[index].classList.add('active');
                controls[index].classList.add('active');
                currentSlide = index;
            }
            
            // Add click events to controls
            controls.forEach((control, index) => {
                control.addEventListener('click', () => {
                    showSlide(index);
                });
            });
            
            // Auto-advance slides every 5 seconds
            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }, 5000);
            
            // Animate elements on scroll
            function animateOnScroll() {
                const elements = document.querySelectorAll('.feature-card, .product-card, .value-item');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if(elementPosition < screenPosition) {
                        element.style.animation = 'fadeInUp 0.8s ease-out forwards';
                    }
                });
            }
            
            // Initial call
            animateOnScroll();
            
            // Listen for scroll events
            window.addEventListener('scroll', animateOnScroll);
            
            // Button click handlers
            document.querySelectorAll('.btn-primary').forEach(button => {
                if(button.textContent.includes('Get Started')) {
                    button.addEventListener('click', function() {
                        alert('Welcome to FitSync Pro! Redirecting to registration page...');
                    });
                }
            });
        });
    </script>
</body>
</html>