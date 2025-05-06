<?php
session_start();
include 'navbar.php'; // Include Navbar

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momento Events</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Hero Section with Enhanced Slider -->
    <div class="hero-section">
        <div class="slider">
            <div class="slides">
                <div class="slide">
                    <img src="image/13.webp" alt="Event 1">
                    <div class="slide-content">
                        <h2 class="animate-text">Creating Unforgettable Moments</h2>
                        <p class="animate-text-delay">Creating memories that last a lifetime</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="image/13 webp" alt="Event 2">
                    <div class="slide-content">
                        <h2 class="animate-text">Exceptional Experiences</h2>
                        <p class="animate-text-delay">Crafted with passion and precision</p>
                    </div>
                </div>
                <div class="slide">
                    <img src="image/13.webp" alt="Event 3">
                    <div class="slide-content">
                        <h2 class="animate-text">Award-Winning Platform</h2>
                        <p class="animate-text-delay">Excellence in every detail</p>
                    </div>
                </div>
            </div>
            <button class="slider-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
            <div class="slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features">
        <div class="feature-card">
            <div class="icon-container">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3>Event Planning</h3>
            <p>Comprehensive planning services for events of all sizes</p>
        </div>
        <div class="feature-card">
            <div class="icon-container">
                <i class="fas fa-star"></i>
            </div>
            <h3>Premium Experience</h3>
            <p>Luxury touches that make your event stand out</p>
        </div>
        <div class="feature-card">
            <div class="icon-container">
                <i class="fas fa-users"></i>
            </div>
            <h3>Expert Team</h3>
            <p>Dedicated professionals at your service</p>
        </div>
    </div>

    <!-- Previous Events & Achievements with Animation -->
    <div class="content-section">
        <div class="content-container">
            <div class="content-box reveal-left">
                <h2>Previous Events</h2>
                <p>We have successfully hosted multiple events over the years, making each one memorable and unique. Our portfolio includes corporate gatherings, weddings, conferences, and cultural celebrations.</p>
                <a href="#" class="btn-more">View Gallery <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="content-box reveal-right">
                <h2>Our Achievements</h2>
                <p>Awarded Best Event Management Platform for our outstanding service and attention to detail. We pride ourselves on exceeding expectations and delivering exceptional experiences.</p>
                <a href="#" class="btn-more">Learn More <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Testimonials Section -->
    <div class="testimonials">
        <h2>What Our Clients Say</h2>
        <div class="testimonial-container">
            <div class="testimonial">
                <div class="quote"><i class="fas fa-quote-left"></i></div>
                <p>"The team delivered beyond our expectations. Our corporate event was flawless!"</p>
                <div class="client">
                    <div class="client-info">
                        <h4>Sarah Johnson</h4>
                        <p>Marketing Director</p>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="quote"><i class="fas fa-quote-left"></i></div>
                <p>"Professional, creative, and attentive to every detail. Highly recommended!"</p>
                <div class="client">
                    <div class="client-info">
                        <h4>Michael Chen</h4>
                        <p>CEO, Tech Innovations</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    body {
        font-family: 'Poppins', Arial, sans-serif;
        margin: 0;
        padding: 0;
        color: #333;
        background-color: #f9f9f9;
        line-height: 1.6;
    }

    /* Hero Section */
    .hero-section {
        position: relative;
        height: 80vh;
        overflow: hidden;
    }

    /* Enhanced Slider Styling */
    .slider {
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .slides {
        display: flex;
        width: 300%;
        height: 100%;
        transition: transform 0.8s ease-in-out;
    }

    .slide {
        position: relative;
        width: 33.33%;
        height: 100%;
        overflow: hidden;
    }

    .slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.7);
        transition: transform 0.5s ease;
    }

    .slide:hover img {
        transform: scale(1.05);
    }

    .slide-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: white;
        width: 80%;
        z-index: 2;
    }

    .slide-content h2 {
        font-size: 3rem;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .slide-content p {
        font-size: 1.5rem;
        margin-bottom: 2rem;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }

    /* Slider Navigation */
    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255,255,255,0.3);
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
    }

    .slider-btn:hover {
        background: rgba(255,255,255,0.5);
    }

    .prev-btn {
        left: 20px;
    }

    .next-btn {
        right: 20px;
    }

    .slider-dots {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }

    .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .dot.active, .dot:hover {
        background: white;
        transform: scale(1.2);
    }

    /* Features Section */
    .features {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        padding: 50px 5%;
        background: white;
        margin-top: -50px;
        position: relative;
        z-index: 5;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border-radius: 10px;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }

    .feature-card {
        flex: 1;
        min-width: 250px;
        max-width: 350px;
        padding: 30px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 8px;
        margin: 10px;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .icon-container {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #6e8efb, #a777e3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .feature-card h3 {
        font-size: 1.5rem;
        margin-bottom: 15px;
        color: #333;
    }

    /* Content Section */
    .content-section {
        padding: 80px 5%;
        background: #f9f9f9;
    }

    .content-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        max-width: 1200px;
        margin: 0 auto;
        gap: 30px;
    }

    .content-box {
        flex: 1;
        min-width: 300px;
        background: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.5s ease, box-shadow 0.5s ease;
    }

    .content-box:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    .content-box h2 {
        font-size: 2rem;
        margin-bottom: 20px;
        color: #333;
        position: relative;
        padding-bottom: 15px;
    }

    .content-box h2:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: linear-gradient(135deg, #6e8efb, #a777e3);
    }

    .btn-more {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 25px;
        background: linear-gradient(135deg, #6e8efb, #a777e3);
        color: white;
        text-decoration: none;
        border-radius: 30px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .btn-more:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .btn-more i {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }

    .btn-more:hover i {
        transform: translateX(5px);
    }

    /* Testimonials Section */
    .testimonials {
        padding: 80px 5%;
        background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
        text-align: center;
    }

    .testimonials h2 {
        font-size: 2.5rem;
        margin-bottom: 50px;
        color: #333;
    }

    .testimonial-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .testimonial {
        flex: 1;
        min-width: 300px;
        max-width: 500px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        text-align: left;
        position: relative;
        transition: transform 0.3s ease;
    }

    .testimonial:hover {
        transform: translateY(-10px);
    }

    .quote {
        font-size: 2rem;
        color: #6e8efb;
        margin-bottom: 15px;
    }

    .testimonial p {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .client {
        display: flex;
        align-items: center;
    }

    .client-info h4 {
        margin: 0;
        font-size: 1.2rem;
        color: #333;
    }

    .client-info p {
        margin: 5px 0 0;
        font-size: 0.9rem;
        color: #666;
    }

    /* Animations */
    @keyframes slide {
        0%, 25% { transform: translateX(0%); }
        33%, 58% { transform: translateX(-33.33%); }
        66%, 91% { transform: translateX(-66.66%); }
        100% { transform: translateX(0%); }
    }

    .slides {
        animation: slide 15s infinite ease-in-out;
    }

    .slides:hover {
        animation-play-state: paused;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-text {
        animation: fadeIn 1s ease-out forwards;
    }

    .animate-text-delay {
        opacity: 0;
        animation: fadeIn 1s ease-out 0.5s forwards;
    }

    @keyframes revealLeft {
        from { opacity: 0; transform: translateX(-50px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes revealRight {
        from { opacity: 0; transform: translateX(50px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .reveal-left {
        opacity: 0;
        animation: revealLeft 1s ease-out forwards;
    }

    .reveal-right {
        opacity: 0;
        animation: revealRight 1s ease-out forwards;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-section {
            height: 60vh;
        }
        
        .slide-content h2 {
            font-size: 2rem;
        }
        
        .slide-content p {
            font-size: 1rem;
        }
        
        .features {
            margin-top: -30px;
        }
        
        .content-container {
            flex-direction: column;
        }
        
        .content-box {
            margin-bottom: 30px;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Slider functionality
        const slides = document.querySelector('.slides');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        let currentSlide = 0;
        
        // Stop the automatic animation
        slides.style.animation = 'none';
        
        function goToSlide(index) {
            currentSlide = index;
            slides.style.transform = `translateX(-${currentSlide * 33.33}%)`;
            
            // Update active dot
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });
        }
        
        // Event listeners for dots
        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => goToSlide(i));
        });
        
        // Event listeners for buttons
        prevBtn.addEventListener('click', () => {
            currentSlide = (currentSlide - 1 + 3) % 3;
            goToSlide(currentSlide);
        });
        
        nextBtn.addEventListener('click', () => {
            currentSlide = (currentSlide + 1) % 3;
            goToSlide(currentSlide);
        });
        
        // Scroll animation for content boxes
        const revealElements = document.querySelectorAll('.reveal-left, .reveal-right');
        
        function checkScroll() {
            revealElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elementTop < windowHeight - 100) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateX(0)';
                }
            });
        }
        
        window.addEventListener('scroll', checkScroll);
        checkScroll(); // Check on load
    });
    </script>

</body>
<?php

include 'footer.php'; // Include Footer
?>
</html>
