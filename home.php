<?php include 'navbar2.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Native Event Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; text-align: center; }

        /* Slideshow */
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: auto;
        }
        .slide {
            display: none;
            width: 100%;
            height: 400px;
        }
        img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Event Types */
        .event-types, .past-events, .about-us {
            padding: 40px;
            background: #f9f9f9;
            margin-top: 20px;
        }
        h2 { color: #6a11cb; margin-bottom: 20px; }
        .events-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .event-card {
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            width: 250px;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            position: relative;
            width: 100%;
        }.about-us {
    display: flex;
    align-items: center; /* Vertically center content */
    justify-content: space-between; /* Text on left, form on right */
    flex-wrap: wrap;
    padding: 40px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: white;
    text-align: left;
}

.about-us h2 {
    margin-bottom: 10px;
    color: white;
   
}

.about-us p {
    max-width: 500px;
}

.email-subscribe {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 30px;
    padding: 5px;
    width: fit-content;
    border: 2px solid #6a11cb;
}

.email-subscribe input {
    border: none;
    outline: none;
    padding: 10px;
    border-radius: 30px 0 0 30px;
    width: 250px;
    font-size: 16px;
}

.email-subscribe button {
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 0 30px 30px 0;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

.email-subscribe button:hover {
    background: #2575fc;
}


    </style>
</head>
<body>

<!-- Slideshow -->
<div class="slideshow-container">
    <div class="slide"><img src="image/11.webp" alt="Event 1"></div>
    <div class="slide"><img src="image/12.webp" alt="Event 2"></div>
    <div class="slide"><img src="image/13.webp" alt="Event 3"></div>
</div>

<!-- Event Types -->
<div class="event-types">
    <h2>Types of Events We Provide</h2>
    <div class="events-grid">
        <div class="event-card">Corporate Events</div>
        <div class="event-card">Weddings</div>
        <div class="event-card">Concerts</div>
        <div class="event-card">Birthday Parties</div>
        <div class="event-card">Festivals</div>
    </div>
</div>

<!-- Past Events -->
<div class="past-events">
    <h2>Our Previous Events</h2>
    <div class="events-grid">
        <div class="event-card">Event 1 - Success Story</div>
        <div class="event-card">Event 2 - Amazing Memories</div>
        <div class="event-card">Event 3 - Grand Celebration</div>
    </div>
</div>

<!-- About Us Section -->
<!-- About Us Section -->
<div class="about-us">
    <h2>Why Choose Us?</h2>
    <p>We provide top-notch event management services, ensuring every detail is perfectly executed for a memorable experience.</p>
    
    <!-- Email Subscription Form -->
    <div class="email-subscribe">
        <input type="email" placeholder="Enter your email">
        <button>Get Started</button>
    </div>
</div>


<!-- Footer -->
<div class="footer">&copy; <?php echo date("Y"); ?> Momento events. All Rights Reserved.</div>

<script>
    let slideIndex = 0;
    function showSlides() {
        let slides = document.getElementsByClassName("slide");
        for (let i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        slideIndex++;
        if (slideIndex > slides.length) { slideIndex = 1; }
        slides[slideIndex - 1].style.display = "block";
        setTimeout(showSlides, 2000);
    }
    showSlides();
</script>

</body>
</html>