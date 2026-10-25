<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitSync Pro | About & Blog Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
            --secondary-color: #ff6600;
            --secondary-dark: #cc5200;
            --gradient-primary: linear-gradient(135deg, #007bff, #0056b3);
            --gradient-secondary: linear-gradient(135deg, #ff6600, #cc5200);
            --light-gradient: linear-gradient(180deg, #007bff1a, #ff66001a);
            --dark-bg: #1a1a1a;
            --text-gray: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* --- Navbar Styling --- */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 1rem 0;
            transition: all 0.3s;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            font-weight: 500;
            color: #444 !important;
            margin: 0 10px;
            position: relative;
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background: var(--gradient-primary);
            transition: width 0.3s;
        }

        .nav-link:hover::after, .nav-link.active::after {
            width: 100%;
        }

        .btn-custom {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        /* --- Page Transition Logic --- */
        .page-section {
            display: none; /* Hidden by default */
            animation: fadeIn 0.5s ease-in-out;
        }

        .page-section.active {
            display: block; /* Show active page */
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- HERO SECTIONS (Shared & Specific) --- */
        .page-hero {
            padding: 120px 0 80px;
            position: relative;
            background: #fff;
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: var(--light-gradient);
            z-index: 0;
            clip-path: polygon(20% 0, 100% 0, 100% 100%, 0% 100%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        /* --- ABOUT PAGE SPECIFIC --- */
        .stat-box {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-10px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
        }

        .team-card {
            border: none;
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .team-img-wrapper {
            height: 250px;
            overflow: hidden;
            position: relative;
        }

        .team-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .team-card:hover .team-img-wrapper img {
            transform: scale(1.1);
        }

        .team-social {
            position: absolute;
            bottom: 15px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .team-card:hover .team-social {
            opacity: 1;
        }

        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 40px;
            border-left: 2px solid #e9ecef;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -9px;
            top: 0;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--secondary-color);
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #e9ecef;
        }

        /* --- BLOG PAGE SPECIFIC --- */
        .blog-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }

        .blog-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .blog-thumb {
            height: 220px;
            overflow: hidden;
            position: relative;
        }

        .blog-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .blog-card:hover .blog-thumb img {
            transform: scale(1.1);
        }

        .blog-category {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--gradient-primary);
            color: #fff;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .blog-content {
            padding: 25px;
        }

        .blog-meta {
            font-size: 0.85rem;
            color: var(--text-gray);
            margin-bottom: 15px;
        }

        .blog-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 15px;
            line-height: 1.4;
            color: #333;
        }

        .blog-link {
            color: var(--secondary-color);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .blog-link:hover {
            gap: 10px;
            color: var(--secondary-dark);
        }

        .filter-btn {
            border: 2px solid #e9ecef;
            background: transparent;
            padding: 8px 25px;
            border-radius: 30px;
            margin: 0 5px 15px;
            font-weight: 500;
            transition: all 0.3s;
            color: #666;
        }

        .filter-btn:hover, .filter-btn.active {
            background: var(--gradient-primary);
            border-color: transparent;
            color: #fff;
        }

        .blog-featured {
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            height: 400px;
            margin-bottom: 50px;
        }

        .blog-featured img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .featured-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            padding: 40px;
            color: #fff;
        }

        /* --- CONTACT & FOOTER --- */
        .contact-info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--light-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-right: 20px;
            flex-shrink: 0;
        }

        .map-container {
            border-radius: 15px;
            overflow: hidden;
            height: 400px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .footer {
            background: #222;
            color: #fff;
            padding: 70px 0 20px;
            margin-top: 80px;
        }
        
        /* Animations */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-up {
            animation: slideInUp 0.6s ease-out forwards;
        }

        /* Responsive Fixes */
        @media (max-width: 768px) {
            .page-hero::before {
                width: 100%;
                clip-path: none;
                opacity: 0.1;
            }
            .hero-content h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#" onclick="switchPage('home')">
                <i class="fas fa-dumbbell me-2"></i>FitSync Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" onclick="switchPage('home')">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" id="nav-about" onclick="switchPage('about')">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="nav-blog" onclick="switchPage('blog')">Fitness Blog</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a href="#" class="btn btn-custom btn-primary shadow">Join Now</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- =======================
         ABOUT US PAGE SECTION
    ======================== -->
    <section id="page-about" class="page-section active">
        <!-- Hero -->
        <div class="page-hero">
            <div class="container hero-content">
                <div class="row align-items-center">
                    <div class="col-lg-6 animate-up">
                        <span class="text-primary fw-bold text-uppercase ls-1">Who We Are</span>
                        <h1 class="display-4 fw-bold mb-4 mt-2">Empowering Your Fitness Journey Since 2015</h1>
                        <p class="lead text-muted mb-4">FitSync Pro isn't just a software; it's a movement. We bridge the gap between technology and physical well-being, providing gyms and individuals with the tools they need to succeed.</p>
                        <div class="d-flex gap-3">
                            <a href="#contact-section" class="btn btn-primary btn-custom rounded-pill">Get in Touch</a>
                            <a href="#team-section" class="btn btn-outline-secondary btn-custom rounded-pill">Meet The Team</a>
                        </div>
                    </div>
                    <div class="col-lg-6 mt-5 mt-lg-0 animate-up" style="animation-delay: 0.2s;">
                        <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Gym Interior" class="img-fluid rounded-4 shadow-lg">
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Counter -->
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-box border-top border-4 border-primary">
                        <i class="fas fa-users fa-2x text-primary mb-3"></i>
                        <span class="stat-number">12k+</span>
                        <p class="mb-0 text-muted">Active Members</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box border-top border-4 border-secondary">
                        <i class="fas fa-dumbbell fa-2x text-secondary mb-3"></i>
                        <span class="stat-number">50+</span>
                        <p class="mb-0 text-muted">Expert Trainers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box border-top border-4 border-info">
                        <i class="fas fa-trophy fa-2x text-info mb-3"></i>
                        <span class="stat-number">8+</span>
                        <p class="mb-0 text-muted">Years of Excellence</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Our Story / Timeline -->
        <section class="bg-white py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h6 class="text-primary fw-bold text-uppercase">Our History</h6>
                    <h2 class="fw-bold">The FitSync Journey</h2>
                </div>
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="timeline-item">
                            <span class="text-primary fw-bold">2015 - The Beginning</span>
                            <h4 class="mt-2">Humble Garage Roots</h4>
                            <p class="text-muted">Started by two fitness enthusiasts in a small garage, FitSync began as a simple Excel sheet to track client macros.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-primary fw-bold">2018 - Expansion</span>
                            <h4 class="mt-2">Going Digital</h4>
                            <p class="text-muted">Launched our first mobile app, allowing members to track workouts on the go. User base hit 1,000.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-primary fw-bold">2021 - Global Reach</span>
                            <h4 class="mt-2">Partnership & Growth</h4>
                            <p class="text-muted">Partnered with major gym chains across 3 countries. Introduced the AI-driven meal planner.</p>
                        </div>
                        <div class="timeline-item">
                            <span class="text-primary fw-bold">2023 - Present</span>
                            <h4 class="mt-2">The Pro Ecosystem</h4>
                            <p class="text-muted">Full ecosystem launch including wearables integration, VR workouts, and comprehensive admin dashboards.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section id="team-section" class="py-5 bg-light">
            <div class="container">
                <div class="text-center mb-5">
                    <h6 class="text-primary fw-bold text-uppercase">Experts</h6>
                    <h2 class="fw-bold">Meet The Team</h2>
                </div>
                <div class="row g-4">
                    <!-- Member 1 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-img-wrapper">
                                <img src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="CEO">
                                <div class="team-social">
                                    <a href="#" class="btn btn-sm btn-primary rounded-circle"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="btn btn-sm btn-primary rounded-circle"><i class="fab fa-linkedin"></i></a>
                                </div>
                            </div>
                            <div class="p-4 text-center">
                                <h5 class="fw-bold">Alex Morgan</h5>
                                <p class="text-primary small fw-bold text-uppercase">Founder & CEO</p>
                                <p class="text-muted small">Visionary leader with 15 years in fitness management.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Member 2 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-img-wrapper">
                                <img src="https://images.unsplash.com/photo-1594381898411-846e7d193883?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Head Trainer">
                                <div class="team-social">
                                    <a href="#" class="btn btn-sm btn-primary rounded-circle"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                            <div class="p-4 text-center">
                                <h5 class="fw-bold">Sarah Jenks</h5>
                                <p class="text-primary small fw-bold text-uppercase">Head Trainer</p>
                                <p class="text-muted small">Certified strength coach specializing in hypertrophy.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Member 3 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-img-wrapper">
                                <img src="https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Nutritionist">
                                <div class="team-social">
                                    <a href="#" class="btn btn-sm btn-primary rounded-circle"><i class="fab fa-facebook-f"></i></a>
                                </div>
                            </div>
                            <div class="p-4 text-center">
                                <h5 class="fw-bold">Dr. Mike Ross</h5>
                                <p class="text-primary small fw-bold text-uppercase">Lead Nutritionist</p>
                                <p class="text-muted small">PhD in Dietetics focusing on athletic performance.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Member 4 -->
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="team-img-wrapper">
                                <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Dev">
                                <div class="team-social">
                                    <a href="#" class="btn btn-sm btn-primary rounded-circle"><i class="fab fa-github"></i></a>
                                </div>
                            </div>
                            <div class="p-4 text-center">
                                <h5 class="fw-bold">Emily Chen</h5>
                                <p class="text-primary small fw-bold text-uppercase">Tech Lead</p>
                                <p class="text-muted small">Ensuring your data is secure and the app runs fast.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location & Contact -->
        <section id="contact-section" class="py-5">
            <div class="container">
                <div class="row g-5">
                    <div class="col-lg-5">
                        <h6 class="text-primary fw-bold text-uppercase">Contact Us</h6>
                        <h2 class="fw-bold mb-4">Get In Touch</h2>
                        <p class="text-muted mb-4">Have questions about memberships or our software? Drop by our HQ or send us a message. We're here to help you crush your goals.</p>
                        
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h6 class="fw-bold">Headquarters</h6>
                                <p class="text-muted mb-0">123 Fitness Blvd, Suite 400<br>Health City, HC 90210</p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <h6 class="fw-bold">Phone</h6>
                                <p class="text-muted mb-0">+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <div class="contact-info-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <h6 class="fw-bold">Email</h6>
                                <p class="text-muted mb-0">support@fitsyncpro.com</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-7">
                        <form class="bg-white p-4 rounded-4 shadow-sm border">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control bg-light border-0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control bg-light border-0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control bg-light border-0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Message</label>
                                    <textarea rows="4" class="form-control bg-light border-0"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary btn-custom w-100">Send Message</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Map Placeholder -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="map-container bg-secondary d-flex align-items-center justify-content-center text-white">
                            <div class="text-center">
                                <i class="fas fa-map-marked-alt fa-3x mb-3"></i>
                                <h4>Interactive Map Loading...</h4>
                                <p>(Simulated Location: 123 Fitness Blvd)</p>
                            </div>
                            <!-- In a real scenario, an iframe would go here -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>

    <!-- =======================
         BLOG PAGE SECTION
    ======================== -->
    <section id="page-blog" class="page-section">
        <!-- Blog Hero -->
        <div class="page-hero bg-dark text-white">
            <div class="container hero-content text-center">
                <span class="text-secondary fw-bold text-uppercase">Insights & News</span>
                <h1 class="display-4 fw-bold mb-4 mt-2">The FitSync Pro Blog</h1>
                <p class="lead text-white-50 mx-auto" style="max-width: 600px;">Expert advice on nutrition, workout routines, and the latest technology to keep you at the top of your game.</p>
            </div>
        </div>

        <!-- Blog Content -->
        <div class="container py-5">
            
            <!-- Filter Buttons -->
            <div class="text-center mb-5">
                <button class="filter-btn active" onclick="filterBlog('all', this)">All Posts</button>
                <button class="filter-btn" onclick="filterBlog('workout', this)">Workouts</button>
                <button class="filter-btn" onclick="filterBlog('nutrition', this)">Nutrition</button>
                <button class="filter-btn" onclick="filterBlog('lifestyle', this)">Lifestyle</button>
                <button class="filter-btn" onclick="filterBlog('tech', this)">Gym Tech</button>
            </div>

            <!-- Featured Post -->
            <div class="blog-featured shadow-lg animate-up">
                <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Featured Blog">
                <div class="featured-overlay">
                    <span class="badge bg-warning text-dark mb-2">Trending</span>
                    <h2 class="fw-bold">10 Mistakes You're Making in the Gym (And How to Fix Them)</h2>
                    <p class="mb-0 d-none d-md-block">Are you maximizing your gains? Our top trainers break down the most common errors that could be stalling your progress.</p>
                    <a href="#" class="btn btn-primary btn-custom rounded-pill mt-3">Read Article</a>
                </div>
            </div>

            <!-- Blog Grid -->
            <div class="row g-4" id="blog-grid">
                
                <!-- Post 1 (Workout) -->
                <div class="col-lg-4 col-md-6 blog-item workout animate-up">
                    <div class="blog-card">
                        <div class="blog-thumb">
                            <img src="https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="HIIT">
                            <span class="blog-category">Workout</span>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <i class="far fa-calendar-alt me-2"></i> Oct 12, 2023
                                <span class="mx-2">•</span>
                                <i class="far fa-clock me-2"></i> 5 min read
                            </div>
                            <h3 class="blog-title">The Ultimate HIIT Routine for Busy Professionals</h3>
                            <p class="text-muted small">Short on time? This 20-minute high-intensity session will burn calories for hours.</p>
                            <a href="#" class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Post 2 (Nutrition) -->
                <div class="col-lg-4 col-md-6 blog-item nutrition animate-up" style="animation-delay: 0.1s;">
                    <div class="blog-card">
                        <div class="blog-thumb">
                            <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Healthy Food">
                            <span class="blog-category">Nutrition</span>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <i class="far fa-calendar-alt me-2"></i> Oct 10, 2023
                                <span class="mx-2">•</span>
                                <i class="far fa-clock me-2"></i> 8 min read
                            </div>
                            <h3 class="blog-title">Meal Prep 101: Save Time and Eat Healthier</h3>
                            <p class="text-muted small">Stop guessing what to cook. Here is your complete guide to weekly meal prepping.</p>
                            <a href="#" class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Post 3 (Tech) -->
                <div class="col-lg-4 col-md-6 blog-item tech animate-up" style="animation-delay: 0.2s;">
                    <div class="blog-card">
                        <div class="blog-thumb">
                            <img src="https://images.unsplash.com/photo-1556656793-02715d8dd726?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Smart Watch">
                            <span class="blog-category">Tech</span>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <i class="far fa-calendar-alt me-2"></i> Oct 08, 2023
                                <span class="mx-2">•</span>
                                <i class="far fa-clock me-2"></i> 6 min read
                            </div>
                            <h3 class="blog-title">How FitSync Pro Integrates with Your Wearables</h3>
                            <p class="text-muted small">Syncing data from your Apple Watch or Garmin has never been easier. Here is how to set it up.</p>
                            <a href="#" class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Post 4 (Lifestyle) -->
                <div class="col-lg-4 col-md-6 blog-item lifestyle animate-up">
                    <div class="blog-card">
                        <div class="blog-thumb">
                            <img src="https://images.unsplash.com/photo-1544367563-12123d8965cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Yoga">
                            <span class="blog-category">Lifestyle</span>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <i class="far fa-calendar-alt me-2"></i> Oct 05, 2023
                                <span class="mx-2">•</span>
                                <i class="far fa-clock me-2"></i> 4 min read
                            </div>
                            <h3 class="blog-title">Recovery is Key: Yoga for Lifters</h3>
                            <p class="text-muted small">Improve your flexibility and reduce muscle soreness with these simple poses.</p>
                            <a href="#" class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Post 5 (Workout) -->
                <div class="col-lg-4 col-md-6 blog-item workout animate-up" style="animation-delay: 0.1s;">
                    <div class="blog-card">
                        <div class="blog-thumb">
                            <img src="https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Weights">
                            <span class="blog-category">Workout</span>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <i class="far fa-calendar-alt me-2"></i> Oct 01, 2023
                                <span class="mx-2">•</span>
                                <i class="far fa-clock me-2"></i> 10 min read
                            </div>
                            <h3 class="blog-title">Progressive Overload Explained</h3>
                            <p class="text-muted small">The golden rule of building muscle. Learn how to apply it effectively without injury.</p>
                            <a href="#" class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Post 6 (Nutrition) -->
                <div class="col-lg-4 col-md-6 blog-item nutrition animate-up" style="animation-delay: 0.2s;">
                    <div class="blog-card">
                        <div class="blog-thumb">
                            <img src="https://images.unsplash.com/photo-1621592495313-f9a8093f427c?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" alt="Supplements">
                            <span class="blog-category">Nutrition</span>
                        </div>
                        <div class="blog-content">
                            <div class="blog-meta">
                                <i class="far fa-calendar-alt me-2"></i> Sep 28, 2023
                                <span class="mx-2">•</span>
                                <i class="far fa-clock me-2"></i> 7 min read
                            </div>
                            <h3 class="blog-title">Do You Really Need Protein Powder?</h3>
                            <p class="text-muted small">An evidence-based look at supplementation and where it fits in a balanced diet.</p>
                            <a href="#" class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Load More -->
            <div class="text-center mt-5">
                <button class="btn btn-outline-primary btn-custom rounded-pill px-5">Load More Articles</button>
            </div>
        </div>
        
        <!-- Newsletter -->
        <section class="py-5 bg-white">
            <div class="container">
                <div class="bg-dark rounded-4 p-5 text-center text-white position-relative overflow-hidden">
                    <div class="position-relative z-1">
                        <h3 class="fw-bold mb-3">Subscribe to Our Newsletter</h3>
                        <p class="mb-4 text-white-50">Get the latest fitness tips and blog updates delivered straight to your inbox.</p>
                        <div class="row justify-content-center g-2">
                            <div class="col-md-5">
                                <input type="email" class="form-control form-control-lg" placeholder="Enter your email address">
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-warning btn-custom w-100 fw-bold">Subscribe</button>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative Circle -->
                    <div style="width: 300px; height: 300px; background: var(--primary-color); opacity: 0.1; border-radius: 50%; position: absolute; top: -100px; right: -50px;"></div>
                </div>
            </div>
        </section>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="h4 text-white fw-bold mb-3">FitSync Pro</div>
                    <p class="text-white-50">Your all-in-one solution for gym management, fitness tracking, and personal training coordination.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white hover-primary"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white hover-primary"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white hover-primary"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3 text-white">Quick Links</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><a href="#" onclick="switchPage('home')" class="text-decoration-none text-white-50">Home</a></li>
                        <li class="mb-2"><a href="#" onclick="switchPage('about')" class="text-decoration-none text-white-50">About Us</a></li>
                        <li class="mb-2"><a href="#" onclick="switchPage('blog')" class="text-decoration-none text-white-50">Blog</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Pricing</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3 text-white">Services</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Gym Management</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Personal Training</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white-50">Diet Plans</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3 text-white">Contact</h6>
                    <p class="text-white-50 small">123 Fitness Street, Health City</p>
                    <p class="text-white-50 small">info@fitsyncpro.com</p>
                    <p class="text-white-50 small">+1 555 123 4567</p>
                </div>
            </div>
            <div class="border-top border-secondary pt-4 mt-4 text-center text-white-50 small">
                &copy; 2023 FitSync Pro. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>