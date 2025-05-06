<?php

?>

<!-- Footer Section -->
<div class="footer">
    <div class="footer-top">
        <div class="footer-logo">
            <i class="fas fa-calendar-check"></i>
            <span>MOMENTO EVENTS</span>
        </div>
        <div class="newsletter">
            <h4>Subscribe for Updates</h4>
            <div class="newsletter-form">
                <input type="email" placeholder="Enter your email">
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
    
    <div class="footer-divider">
        <div class="divider-icon"><i class="fas fa-star"></i></div>
    </div>
    
    <div class="footer-content">
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="event_booking_details.php">Events</a></li>
                <li><a href="organizer_details.php">Organizers</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Event Types</h4>
            <ul>
                <li><a href="#">Weddings</a></li>
                <li><a href="#">Corporate</a></li>
                <li><a href="#">Birthday Parties</a></li>
                <li><a href="#">Conferences</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Contact Info</h4>
            <p><i class="fas fa-map-marker-alt"></i> 123 Event Street, Suite 456</p>
            <p><i class="fas fa-phone"></i> (123) 456-7890</p>
            <p><i class="fas fa-envelope"></i> info@momentoevents.com</p>
        </div>
    </div>
    
    <div class="social-icons">
        <a href="#" class="social-bubble"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-bubble"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-bubble"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-bubble"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-bubble"><i class="fab fa-pinterest-p"></i></a>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Momento Events. All Rights Reserved.</p>
        <div class="footer-links">
            <a href="privacy.php">Privacy Policy</a> |
            <a href="terms.php">Terms & Conditions</a> |
            <a href="faq.php">FAQ</a>
        </div>
    </div>
</div>

<style>
    .footer {
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        color: white;
        text-align: center;
        width: 100%;
        font-size: 14px;
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
        padding-bottom: 20px;
    }
    
    .footer-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 25px 5%;
        flex-wrap: wrap;
    }
    
    .footer-logo {
        display: flex;
        align-items: center;
        font-size: 22px;
        font-weight: 800;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }
    
    .footer-logo i {
        margin-right: 10px;
        font-size: 24px;
    }
    
    .newsletter {
        max-width: 360px;
    }
    
    .newsletter h4 {
        margin-bottom: 10px;
    }
    
    .newsletter-form {
        display: flex;
        position: relative;
        height: 40px;
    }
    
    .newsletter-form input {
        flex: 1;
        border: none;
        padding: 10px 15px;
        border-radius: 20px;
        font-size: 14px;
        width: 100%;
    }
    
    .newsletter-form button {
        position: absolute;
        right: 4px;
        top: 4px;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        border: none;
        color: white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .newsletter-form button:hover {
        transform: scale(1.05);
    }
    
    .footer-divider {
        position: relative;
        height: 1px;
        background-color: rgba(255, 255, 255, 0.2);
        margin: 10px 5%;
    }
    
    .divider-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
    }
    
    .footer-content {
        display: flex;
        justify-content: space-around;
        padding: 30px 5%;
        flex-wrap: wrap;
        text-align: left;
    }
    
    .footer-section {
        margin-bottom: 20px;
        min-width: 200px;
    }
    
    .footer-section h4 {
        position: relative;
        margin-bottom: 15px;
        font-size: 16px;
        font-weight: 600;
        padding-bottom: 10px;
    }
    
    .footer-section h4:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 2px;
        width: 30px;
        background-color: white;
    }
    
    .footer-section ul {
        list-style: none;
        padding: 0;
    }
    
    .footer-section ul li {
        margin-bottom: 8px;
    }
    
    .footer-section a {
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .footer-section a:before {
        content: '→';
        margin-right: 6px;
        opacity: 0;
        transform: translateX(-10px);
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .footer-section a:hover:before {
        opacity: 1;
        transform: translateX(0);
    }
    
    .footer-section p {
        margin-bottom: 8px;
        display: flex;
        align-items: center;
    }
    
    .footer-section p i {
        margin-right: 10px;
        min-width: 20px;
    }
    
    .social-icons {
        margin: 20px 0;
    }
    
    .social-bubble {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        background-color: white;
        color: #6a11cb;
        border-radius: 50%;
        margin: 0 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .social-bubble:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        background-color: #f0f0f0;
    }
    
    .footer-bottom {
        margin-top: 20px;
        padding: 0 5%;
    }
    
    .footer-bottom p {
        margin-bottom: 10px;
    }
    
    .footer-links a {
        color: white;
        text-decoration: none;
        margin: 0 10px;
        transition: all 0.3s ease;
    }
    
    .footer-links a:hover {
        text-decoration: underline;
    }
    
    @media (max-width: 900px) {
        .footer-top {
            flex-direction: column;
            gap: 20px;
            text-align: center;
        }
        
        .footer-logo {
            justify-content: center;
        }
        
        .footer-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .footer-section h4:after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .footer-section p {
            justify-content: center;
        }
        
        .footer-section a:before {
            display: none;
        }
    }
</style>