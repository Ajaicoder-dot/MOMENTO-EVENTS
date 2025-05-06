<?php
include 'navbar3.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Native Event Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; color: #333; }
        .container { max-width: 1100px; margin: 50px auto; padding: 20px; }
        h1, h2 { text-align: center; color: #222; margin-bottom: 20px; }
        p { line-height: 1.7; }
        
        /* Hero Section */
        .hero { display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #6a11cb, #2575fc); padding: 40px; border-radius: 10px; color: white; }
        .hero h1 { font-size: 32px; }
        .hero p { max-width: 60%; }
        .hero img { max-width: 300px; border-radius: 10px; }

        /* Values Section */
        .values-container { display: flex; justify-content: space-between; text-align: center; margin-top: 40px; }
        .value-box { flex: 1; padding: 20px; margin: 10px; background: white; border-radius: 8px; box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1); transition: transform 0.3s; }
        .value-box:hover { transform: translateY(-5px); }
        .value-box i { font-size: 40px; color: #6a11cb; margin-bottom: 10px; }

        /* Timeline Section */
        .timeline { position: relative; padding: 50px 0; }
        .timeline::before { content: ''; position: absolute; width: 4px; background: #6a11cb; top: 0; bottom: 0; left: 50%; transform: translateX(-50%); }
        .timeline-item { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .timeline-item:nth-child(even) { flex-direction: row-reverse; }
        .timeline-item-content { width: 45%; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1); position: relative; }
        .timeline-item-content::before { content: ''; position: absolute; width: 15px; height: 15px; background: #6a11cb; border-radius: 50%; top: 20px; left: -8px; }
        .timeline-item:nth-child(even) .timeline-item-content::before { left: auto; right: -8px; }

        /* Call To Action */
        .cta { text-align: center; padding: 30px; background: linear-gradient(135deg, #6a11cb, #2575fc); color: white; margin-top: 50px; border-radius: 8px; }
        .cta a { color: white; font-weight: bold; text-decoration: none; }

        /* Footer */
        .footer {
            text-align: center;
            position: fixed;
            width: 100%;
            bottom: 0;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            padding: 15px 30px;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Hero Section -->
    <div class="hero">
        <div>
            <h1>Welcome to Momento Event Management</h1>
            <p>At **Momento  Event Management**, we craft unforgettable experiences. From corporate galas to personal celebrations, our expertise ensures flawless execution.</p>
        </div>
        <img src="image/1.jpg" alt="Event Management">
    </div>

    <!-- Our Core Values -->
    <h2>Our Core Values</h2>
    <div class="values-container">
        <div class="value-box">
            <i class="fas fa-lightbulb"></i>
            <h3>Innovation</h3>
            <p>We integrate cutting-edge technology into event planning for seamless execution.</p>
        </div>
        <div class="value-box">
            <i class="fas fa-star"></i>
            <h3>Excellence</h3>
            <p>Every event we curate is driven by perfection and precision.</p>
        </div>
        <div class="value-box">
            <i class="fas fa-users"></i>
            <h3>Customer First</h3>
            <p>Your vision is our blueprint, ensuring personalized event experiences.</p>
        </div>
    </div>

    <!-- Timeline Section -->
    <h2>Our Journey</h2>
    <div class="timeline">
        <div class="timeline-item">
            <div class="timeline-item-content">
                <h3>2015 - The Beginning</h3>
                <p>Momento Event Management started as a small team passionate about event planning.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-item-content">
                <h3>2018 - Expansion</h3>
                <p>We expanded our services to include corporate events, conferences, and weddings.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-item-content">
                <h3>2022 - Tech Integration</h3>
                <p>Launched an AI-driven event booking system, making event planning more efficient.</p>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta">
        <h2>Let's Plan Your Dream Event</h2>
        <p>Partner with us to create something unforgettable. <a href="contact.php">Contact us today</a> and bring your vision to life.</p>
    </div>
</div>

<!-- Footer -->
<div class="footer">&copy; <?php echo date("Y"); ?> Momento Events. All Rights Reserved.</div>

</body>
</html>
