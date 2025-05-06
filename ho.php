<?php include 'navbar1.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Momento Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Poppins', sans-serif;
            color: #333;
            background-color: #f9f9f9;
            line-height: 1.6;
        }
        
        /* Hero Section with Slideshow */
        .hero-section {
            position: relative;
            height: 80vh;
            overflow: hidden;
        }
        
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .slide.active {
            opacity: 1;
        }
        
        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }
        
        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 1;
            width: 80%;
            max-width: 800px;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
            animation: fadeInDown 1s ease-out;
        }
        
        .hero-content p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-btn {
            display: inline-block;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
            animation: fadeInUp 1.2s ease-out;
        }
        
        .hero-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(106, 17, 203, 0.4);
        }
        
        /* Slide Indicators */
        .slide-indicators {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 2;
        }
        
        .indicator {
            width: 12px;
            height: 12px;
            background-color: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s ease;
        }
        
        .indicator.active {
            background-color: white;
            transform: scale(1.3);
        }
        
        /* Section Styles */
        section {
            padding: 80px 20px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: #6a11cb;
            display: inline-block;
            padding-bottom: 15px;
            position: relative;
            margin-bottom: 20px;
        }
        
        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 4px;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border-radius: 2px;
        }
        
        .section-title p {
            max-width: 700px;
            margin: 0 auto;
            color: #555;
            font-size: 1.1rem;
        }
        
        /* Event Types Section */
        .event-types {
            background-color: white;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .event-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            height: 280px;
        }
        
        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(106, 17, 203, 0.2);
        }
        
        .event-card-img {
            height: 180px;
            position: relative;
        }
        
        .event-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .event-card-content {
            padding: 20px;
            text-align: center;
        }
        
        .event-card-content h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .event-card-content p {
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Past Events Section */
        .past-events {
            background-color: #f5f0ff;
        }
        
        .past-event-card {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            height: 300px;
        }
        
        .past-event-img {
            height: 100%;
            width: 100%;
        }
        
        .past-event-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .past-event-card:hover .past-event-img img {
            transform: scale(1.1);
        }
        
        .past-event-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            color: white;
            transform: translateY(70px);
            transition: transform 0.3s ease;
        }
        
        .past-event-card:hover .past-event-content {
            transform: translateY(0);
        }
        
        .past-event-content h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .past-event-content p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 15px;
            display: none;
            transition: display 0.3s ease;
        }
        
        .past-event-card:hover .past-event-content p {
            display: block;
        }
        
        /* About Us Section */
        .about-us {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            text-align: center;
            padding: 80px 20px;
            position: relative;
            overflow: hidden;
        }
        
        .about-us-content {
            max-width: 600px;
            margin: 0 auto 40px;
            position: relative;
            z-index: 1;
        }
        
        .about-us h2 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 20px;
        }
        
        .about-us p {
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        
        .features {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
            margin-bottom: 50px;
        }
        
        .feature {
            text-align: center;
            width: 200px;
        }
        
        .feature i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .feature h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        
        .feature p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .email-subscribe {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        
        .email-subscribe-inner {
            display: flex;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }
        
        .email-subscribe input {
            flex: 1;
            border: none;
            outline: none;
            padding: 15px 25px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .email-subscribe button {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border: none;
            padding: 15px 30px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }
        
        .email-subscribe button:hover {
            background: linear-gradient(135deg, #5810a7, #1e64d3);
        }
        
        /* Shape Decorations */
        .shape {
            position: absolute;
            opacity: 0.1;
            z-index: 0;
        }
        
        .shape-1 {
            bottom: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background-color: white;
        }
        
        .shape-2 {
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: white;
        }
        
        /* Footer */
        .footer {
            background: #1d1d1d;
            color: white;
            text-align: center;
            padding: 30px;
            position: relative;
        }
        
        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .hero-content h1 {
                font-size: 2.8rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
            
            .events-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                height: 70vh;
            }
            
            .hero-content h1 {
                font-size: 2.2rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .hero-btn {
                padding: 12px 25px;
                font-size: 1rem;
            }
            
            section {
                padding: 60px 15px;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .features {
                gap: 20px;
            }
            
            .feature {
                width: 160px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-section {
                height: 60vh;
            }
            
            .hero-content h1 {
                font-size: 1.8rem;
            }
            
            .section-title h2 {
                font-size: 1.7rem;
            }
            
            .events-grid {
                grid-template-columns: 1fr;
                max-width: 350px;
                margin: 0 auto;
            }
            
            .features {
                flex-direction: column;
                align-items: center;
            }
            
            .feature {
                width: 100%;
                max-width: 250px;
                margin-bottom: 30px;
            }
            
            .email-subscribe-inner {
                flex-direction: column;
                border-radius: 15px;
            }
            
            .email-subscribe input {
                width: 100%;
                border-radius: 15px 15px 0 0;
                text-align: center;
            }
            
            .email-subscribe button {
                width: 100%;
                border-radius: 0 0 15px 15px;
            }
        }
    </style>
</head>
<body>

<!-- Hero Section with Slideshow -->
<section class="hero-section">
    <div class="slide active">
        <img src="image/11.webp" alt="Event 1">
    </div>
    <div class="slide">
        <img src="image/12.webp" alt="Event 2">
    </div>
    <div class="slide">
        <img src="image/13.webp" alt="Event 3">
    </div>
    
    <div class="hero-content">
        <h1>Creating Unforgettable Moments</h1>
        <p>From weddings to corporate gatherings, we transform your vision into exceptional experiences</p>
        <a href="bookings.php" class="hero-btn">Book Your Event Now</a>
    </div>
    
    <div class="slide-indicators">
        <div class="indicator active" data-index="0"></div>
        <div class="indicator" data-index="1"></div>
        <div class="indicator" data-index="2"></div>
    </div>
</section>

<!-- Event Types Section -->
<section class="event-types">
    <div class="section-title">
        <h2>Event Services</h2>
        <p>Discover our wide range of event services tailored to meet your unique needs and expectations</p>
    </div>
    
    <div class="events-grid">
        <div class="event-card">
            <div class="event-card-img">
                <img src="image/corporate.jpg" alt="Corporate Events" onerror="this.src='https://via.placeholder.com/300x180?text=Corporate+Events'">
            </div>
            <div class="event-card-content">
                <h3>Corporate Events</h3>
                <p>Professional gatherings that leave a lasting impression</p>
            </div>
        </div>
        
        <div class="event-card">
            <div class="event-card-img">
                <img src="image/wedding.jpg" alt="Weddings" onerror="this.src='https://via.placeholder.com/300x180?text=Weddings'">
            </div>
            <div class="event-card-content">
                <h3>Weddings</h3>
                <p>Beautiful ceremonies for your special day</p>
            </div>
        </div>
        
        <div class="event-card">
            <div class="event-card-img">
                <img src="image/Concerts.jpg" alt="Concerts" onerror="this.src='https://via.placeholder.com/300x180?text=Concerts'">
            </div>
            <div class="event-card-content">
                <h3>Concerts</h3>
                <p>Electrifying music events for all audiences</p>
            </div>
        </div>
        
        <div class="event-card">
            <div class="event-card-img">
                <img src="image/birthday.jpg" alt="Birthday Parties" onerror="this.src='https://via.placeholder.com/300x180?text=Birthday+Parties'">
            </div>
            <div class="event-card-content">
                <h3>Birthday Parties</h3>
                <p>Memorable celebrations for all ages</p>
            </div>
        </div>
        
        
</section>

<!-- Past Events Section -->
<section class="past-events">
    <div class="section-title">
        <h2>Our Success Stories</h2>
        <p>Take a look at some of our successful events that left clients and attendees amazed</p>
    </div>
    
    <div class="events-grid">
        <div class="past-event-card">
            <div class="past-event-img">
                <img src="image/exhibitions.jpg" alt="Past Event 1" onerror="this.src='https://via.placeholder.com/400x300?text=Past+Event+1'">
            </div>
            <div class="past-event-content">
                <h3>Science Exhibition</h3>
                <p>Join us for an exciting Science Exhibition where innovation meets imagination.</p>
            </div>
        </div>
        
        <div class="past-event-card">
            <div class="past-event-img">
                <img src="image/beachsidewedding.jpg" alt="Past Event 2" onerror="this.src='https://via.placeholder.com/400x300?text=Past+Event+2'">
            </div>
            <div class="past-event-content">
                <h3>Beachside Wedding</h3>
                <p>A magical sunset wedding ceremony and reception for 200 guests with custom decorations and gourmet catering.</p>
            </div>
        </div>
        
        <div class="past-event-card">
            <div class="past-event-img">
                <img src="image/summer.jpg" alt="Past Event 3" onerror="this.src='https://via.placeholder.com/400x300?text=Past+Event+3'">
            </div>
            <div class="past-event-content">
                <h3>Summer Music Festival</h3>
                <p>A two-day outdoor music festival featuring 15 artists across three stages with food vendors and activities.</p>
            </div>
        </div>
        <div class="past-event-card">
            <div class="past-event-img">
                <img src="image/happystreet.jpg" alt="Past Event 3" onerror="this.src='https://via.placeholder.com/400x300?text=Past+Event+3'">
            </div>
            <div class="past-event-content">
                <h3>Happy Street</h3>
                <p>Discover the joy of community, creativity, and connection.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section class="about-us">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    
    <div class="about-us-content">
        <h2>Why Choose Momento Events?</h2>
        <p>With years of experience and a passion for perfection, we deliver unforgettable events that exceed your expectations. Our attention to detail and creative approach ensure your event is unique and memorable.</p>
    </div>
    
    <div class="features">
        <div class="feature">
            <i class="fas fa-star"></i>
            <h3>Excellence</h3>
            <p>Committed to delivering the highest quality service for every event</p>
        </div>
        
        <div class="feature">
            <i class="fas fa-paint-brush"></i>
            <h3>Creativity</h3>
            <p>Innovative ideas and unique concepts tailored to your vision</p>
        </div>
        
        <div class="feature">
            <i class="fas fa-clock"></i>
            <h3>Reliability</h3>
            <p>Punctual and dependable service from planning to execution</p>
        </div>
        
        <div class="feature">
            <i class="fas fa-handshake"></i>
            <h3>Personalized</h3>
            <p>Customized solutions that reflect your specific requirements</p>
        </div>
    </div>
    
    <div class="email-subscribe">
        <div class="email-subscribe-inner">
            <input type="email" placeholder="Enter your email to get started">
            <button>Subscribe</button>
        </div>
    </div>
</section>


<script>
    // Slideshow functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    const totalSlides = slides.length;
    
    function showSlide(index) {
        // Hide all slides and remove active class from indicators
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(indicator => indicator.classList.remove('active'));
        
        // Show the current slide and update the active indicator
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
        
        currentSlide = index;
    }
    
    // Add click event to indicators
    indicators.forEach(indicator => {
        indicator.addEventListener('click', function() {
            const slideIndex = parseInt(this.getAttribute('data-index'));
            showSlide(slideIndex);
        });
    });
    
    // Auto slide function
    function autoSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
    }
    
    // Start the slideshow
    let slideInterval = setInterval(autoSlide, 5000);
    
    // Pause slideshow when hovering over it
    const heroSection = document.querySelector('.hero-section');
    
    heroSection.addEventListener('mouseenter', function() {
        clearInterval(slideInterval);
    });
    
    heroSection.addEventListener('mouseleave', function() {
        slideInterval = setInterval(autoSlide, 5000);
    });
</script>
<?php include 'footer.php'; ?>

</body>
</html>