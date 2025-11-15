<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premier Sports Complex | Modern Athletic Facilities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #004E89;
            --accent: #00A5E0;
            --dark: #292F36;
            --light: #F7FFF7;
            --gradient: linear-gradient(135deg, var(--primary), var(--accent));
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #ffffff;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Mobile-First Header */
        #main-header {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: var(--dark);
            padding: 1rem;
        }

        /* Background image on left side */
        #main-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?php echo validate_image($_settings->info('cover')) ?>') cover no-repeat;
            z-index: 0;
        }

        /* Gradient overlay - fading left to right */
        #main-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to left, rgba(0, 0, 0, 0.3) 100%, rgba(0, 0, 0, 0.5) 30%, rgba(0, 0, 0, .7) 50%, var(--dark) 60%);
            z-index: 1;
        }

        .hero-container {
            position: relative;
            z-index: 3;
            width: 100%;
            max-width: 600px;
            padding: 0;
            text-align: center;
        }

        /* Floating Abstract Shapes - Mobile Optimized */
        .floating-elements {
            position: absolute;
            inset: 0;
            z-index: 2;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            animation: float 8s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 15%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 50%;
            left: 25%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            width: 50px;
            height: 50px;
            bottom: 15%;
            left: 10%;
            animation-delay: 4s;
        }

        /* Hero Content Section */
        .hero-content-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            position: relative;
            z-index: 3;
        }

        .site-logo {
            max-height: 100px;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: fadeInDown 1s ease;
        }

        .site-logo:hover {
            transform: scale(1.08) translateY(-5px);
        }

        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: white;
            margin-bottom: 1rem;
            line-height: 1.1;
            letter-spacing: -1px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-content p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 2rem;
            line-height: 1.7;
            max-width: 500px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease 0.4s both;
        }

        /* Glass Card Welcome Section */
        .welcome-section {
            background: transparent;
            backdrop-filter: none;
            padding: 0;
            border-radius: 0;
            margin: 1.5rem 0;
            max-width: 500px;
            border: none;
            box-shadow: none;
            transition: transform 0.3s ease;
            animation: fadeInUp 1s ease 0.6s both;
        }

        .welcome-section:hover {
            transform: none;
        }

        .welcome-section h2 {
            color: white;
            margin-bottom: 0.8rem;
            font-size: 1.4rem;
            font-weight: 700;
            text-shadow: 0 2px 20px rgba(0, 0, 0, 0.8), 0 0 40px rgba(0, 0, 0, 0.5);
        }

        .welcome-section p {
            color: rgba(255, 255, 255, 0.98);
            line-height: 1.7;
            margin-bottom: 0;
            font-size: 0.95rem;
            text-shadow: 0 2px 15px rgba(0, 0, 0, 0.8), 0 0 30px rgba(0, 0, 0, 0.6);
        }

        /* Modern Button with Micro-interactions */
        .hero-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: var(--gradient);
            color: white;
            font-weight: 600;
            padding: 0.9rem 2rem;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            text-decoration: none;
            transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
            font-size: 1rem;
            border: 2px solid white;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            width: fit-content;
            animation: fadeInUp 1s ease 0.8s both;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hero-btn i {
            transition: transform 0.3s ease;
        }

        .hero-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 35px rgba(255, 107, 53, 0.6);
        }

        .hero-btn:hover i {
            transform: translateX(3px);
        }

        .hero-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .hero-btn:hover::before {
            left: 100%;
        }

        /* Services Section - Modern Card Grid */
        .services-section {
            padding: 3rem 1rem;
            background: #f8fafc;
            position: relative;
        }

        .services-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient);
            border-radius: 2px;
        }

        .section-header p {
            color: #666;
            font-size: 1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        #search {
            width: 100%;
            max-width: 600px;
            margin: 0 auto 2rem;
            padding: 1rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: block;
        }

        #search:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        #search::placeholder {
            color: #94a3b8;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        #service_list .item {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 1;
        }

        #service_list .item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        #service_list .item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }

        #service_list .item:hover::before {
            opacity: 0.05;
        }

        #service_list .item .callout {
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .view_service {
            background: var(--gradient);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
            margin-top: 1rem;
        }

        .view_service:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        #noResult {
            text-align: center;
            padding: 3rem;
            color: #666;
            font-size: 1.1rem;
            grid-column: 1 / -1;
        }

        /* Ultra Modern Footer */
        .modern-footer {
            background: var(--dark);
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .modern-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: white;
            position: relative;
            padding-bottom: 0.5rem;
            font-weight: 600;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--gradient);
            border-radius: 3px;
        }

        .footer-section p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            padding: 0.5rem 0;
            transition: all 0.3s ease;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
            position: relative;
            padding-left: 1.5rem;
        }

        .footer-section ul li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--primary);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .footer-section ul li:hover {
            color: white;
            transform: translateX(5px);
        }

        .footer-section ul li:hover::before {
            opacity: 1;
            left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-link {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            backdrop-filter: blur(5px);
        }

        .social-link:hover {
            background: var(--gradient);
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .contact-item:hover .contact-icon {
            background: var(--gradient);
            color: white;
            transform: rotate(15deg);
        }

        .contact-text {
            flex: 1;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 3rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Floating CTA Button */
        .floating-cta {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 60px;
            height: 60px;
            background: var(--gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.3);
            z-index: 100;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .floating-cta:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 15px 30px rgba(255, 107, 53, 0.4);
        }

        /* Mobile Navigation */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            width: 50px;
            height: 50px;
            background: var(--gradient);
            border-radius: 50%;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .mobile-menu-toggle span {
            display: block;
            width: 24px;
            height: 2px;
            background: white;
            margin: 3px 0;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        .mobile-nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 300px;
            height: 100vh;
            background: var(--dark);
            z-index: 999;
            padding: 5rem 2rem 2rem;
            transition: right 0.3s ease;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
        }

        .mobile-nav.active {
            right: 0;
        }

        .mobile-nav ul {
            list-style: none;
        }

        .mobile-nav ul li {
            margin-bottom: 1.5rem;
        }

        .mobile-nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            display: block;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .mobile-nav ul li a:hover {
            color: var(--primary);
            transform: translateX(5px);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive Design - Tablet */
        @media (min-width: 768px) {
            #main-header {
                padding: 2rem;
                justify-content: flex-start;
            }

            .hero-container {
                text-align: left;
                max-width: 600px;
            }

            .hero-content-section {
                align-items: flex-start;
            }

            .hero-content h1 {
                font-size: 3.5rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .site-logo {
                max-height: 130px;
            }

            .services-section {
                padding: 4rem 2rem;
            }

            .section-header h2 {
                font-size: 2.5rem;
            }

            .section-header p {
                font-size: 1.1rem;
            }

            .footer-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 3rem;
            }

            .floating-shape:nth-child(1) {
                width: 120px;
                height: 120px;
            }

            .floating-shape:nth-child(2) {
                width: 100px;
                height: 100px;
            }

            .floating-shape:nth-child(3) {
                width: 80px;
                height: 80px;
            }
        }

        /* Responsive Design - Desktop */
        @media (min-width: 1024px) {
            #main-header {
                padding: 0 5rem;
            }

            .hero-content h1 {
                font-size: 4.5rem;
            }

            .services-section {
                padding: 5rem 2rem;
            }

            .services-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 2rem;
            }

            .footer-container {
                grid-template-columns: repeat(3, 1fr);
            }

            .floating-shape:nth-child(1) {
                width: 150px;
                height: 150px;
            }

            .floating-shape:nth-child(2) {
                width: 120px;
                height: 120px;
            }

            .floating-shape:nth-child(3) {
                width: 100px;
                height: 100px;
            }
        }

        /* Large Screens */
        @media (min-width: 1400px) {
            #main-header {
                padding: 0 8rem;
            }
        }

        /* Animations */
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        @keyframes shimmer {
            0%, 100% {
                opacity: 0;
            }
            50% {
                opacity: 1;
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

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate {
            animation: fadeIn 1s ease forwards;
        }

        /* Hide unwanted footers */
        footer:not(.modern-footer) {
            display: none !important;
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav" id="mobileNav">
        <ul>
            <li><a href="#main-header">Home</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="./?p=facility_available">Book Now</a></li>
        </ul>
    </div>

    <!-- Overlay for mobile menu -->
    <div class="overlay" id="overlay"></div>

    <!-- Modern Full-Width Hero Header -->
    <header id="main-header">
        <div class="floating-elements">
            <div class="floating-shape"></div>
            <div class="floating-shape"></div>
            <div class="floating-shape"></div>
        </div>
        
        <div class="hero-container">
            <div class="hero-content-section">
                <div class="hero-content">
                    <!-- Dynamic Logo -->
                    <img src="<?php echo validate_image($_settings->info('logo')) ?>" alt="<?php echo $_settings->info('short_name') ?>" class="site-logo" />

                    <!-- Welcome Content -->
                    <?php include "welcome.html"; ?>

                    <!-- CTA Button with Icon -->
                    <a href="./?p=facility_available" class="hero-btn" role="button" aria-label="Book Now">
                        Book Facility Now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Services Section with Modern Grid -->
    <section class="services-section" id="services">
        <div class="services-container">
            <div class="section-header animate" style="animation-delay: 0.2s;">
                <h2>Our Facilities & Services</h2>
                <p>Explore our comprehensive range of sports facilities and professional services designed to meet your athletic needs</p>
            </div>

            <!-- Search Bar -->
            <input type="text" id="search" placeholder="Search for facilities and services...">
            
            <div class="services-grid">
                <!-- Service items would be dynamically loaded here -->
                <div id="service_list">
                    <!-- Service items would be dynamically loaded here -->
                </div>
                <div id="noResult" style="display: none;">No matching services found.</div>
            </div>
        </div>
    </section>

    <!-- Ultra Modern Footer -->
    <footer class="modern-footer" id="contact">
        <div class="footer-container">
            <div class="footer-section animate" style="animation-delay: 0.2s;">
                <h3>Social Media Accounts</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="footer-section animate" style="animation-delay: 0.3s;">
                <h3>Our Facilities</h3>
                <ul>
                    <li>Indoor Basketball Courts</li>
                    <li>Volley Ball Courts</li>
                    <li>Tennis Courts</li>
                    <li>Sports Medicine Clinic</li>
                </ul>
            </div>

            <div class="footer-section animate" style="animation-delay: 0.5s;">
                <h3>Contact Us</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                          3GJV+987, Catalunan Grande Rd, Talomo, Davao City, Davao del Sur, Philippines
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-text">
                            +1 (555) 123-4567
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            CatalunanGrande@gmail.com
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            Open Daily: 8:00 AM - 10:00 PM
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom animate" style="animation-delay: 0.6s;">
            <p>&copy; 2025 Catalunan Grande Sports Complex. All rights reserved. | <a href="#" style="color: var(--accent);">Privacy Policy</a> | <a href="#" style="color: var(--accent);">Terms of Service</a></p>
            <p>Developed by: Marvin M. Baylosis Jr.</p>
        </div>
    </footer>

    <!-- Floating CTA Button -->
    <a href="./?p=facility_available" class="floating-cta" aria-label="Quick Book">
        <i class="fas fa-calendar-alt"></i>
    </a>

    <script>
        // Mobile Menu Toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileNav = document.getElementById('mobileNav');
        const overlay = document.getElementById('overlay');

        mobileMenuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileNav.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function() {
            mobileMenuToggle.classList.remove('active');
            mobileNav.classList.remove('active');
            this.classList.remove('active');
        });

        // Enhanced Search Functionality
        $(function(){
            $('#search').on('input', function(){
                var searchTerm = $(this).val().toLowerCase().trim();
                var hasResults = false;
                
                $('#service_list .item').each(function(){
                    var itemText = $(this).text().toLowerCase().replace(/\s+/g, ' ');
                    if(itemText.includes(searchTerm)) {
                        $(this).fadeIn(300);
                        hasResults = true;
                    } else {
                        $(this).fadeOut(300);
                    }
                });
                
                if(hasResults || searchTerm === '') {
                    $('#noResult').fadeOut(300);
                } else {
                    $('#noResult').fadeIn(300);
                }
            });
            
            // Hover effects for service items
            $('#service_list .item').hover(
                function() {
                    $(this).find('.callout').addClass('shadow');
                },
                function() {
                    $(this).find('.callout').removeClass('shadow');
                }
            );
            
            // View service modal
            $('#service_list .view_service').click(function(){
                uni_modal("Service Details", "view_service.php?id=" + $(this).attr('data-id'), 'mid-large');
            });
            
            // Request form modal
            $('#send_request').click(function(){
                uni_modal("Service Request Form", "send_request.php", 'large');
            });
        });

        // Scroll animations using Intersection Observer
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, {
                threshold: 0.1
            });
            
            document.querySelectorAll('.animate').forEach(el => {
                observer.observe(el);
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                    // Close mobile menu if open
                    mobileMenuToggle.classList.remove('active');
                    mobileNav.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });

        // Enhanced parallax effect for hero background
        window.addEventListener('scroll', function() {
            const scrollPosition = window.pageYOffset;
            const header = document.querySelector('#main-header');
            
            if (header && scrollPosition < window.innerHeight) {
                header.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
            }
        });

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.animate').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>