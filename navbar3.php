<?php include 'db.php'; // Include database connection ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Momento Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .navbar {
            width: 100%;
            height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            padding: 0 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar .nav-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1px;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
        }
        
        .nav-title i {
            margin-right: 10px;
            font-size: 22px;
        }
        
        .navbar .nav-links {
            display: flex;
            gap: 5px;
        }
        
        .navbar .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .navbar .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .navbar .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.25);
            font-weight: 600;
        }
        
        /* Dropdown styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-toggle {
            color: white;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .dropdown-toggle:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            background: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px 0;
            z-index: 1;
            top: 45px;
            left: 0;
            transition: all 0.3s ease;
        }
        
        .dropdown-menu a {
            color: #333 !important;
            padding: 10px 20px !important;
            display: block;
            text-decoration: none;
            border-radius: 0 !important;
            transition: all 0.2s ease;
        }
        
        .dropdown-menu a:hover {
            background-color: #f1f1f1 !important;
            transform: none !important;
        }
        
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .logout-button {
            background-color: #ffffff;
            color: #6a11cb;
            padding: 8px 18px;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .logout-button:hover {
            background-color: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        /* Mobile menu */
        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }
        
        .bar {
            width: 25px;
            height: 3px;
            background-color: white;
            margin: 3px 0;
            border-radius: 3px;
            transition: 0.4s;
        }
        
        @media (max-width: 900px) {
            .menu-toggle {
                display: flex;
            }
            
            .navbar {
                padding: 0 20px;
            }
            
            .navbar .nav-links {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                width: 100%;
                text-align: center;
                transition: 0.5s;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                padding: 20px 0;
                gap: 10px;
            }
            
            .navbar .nav-links.active {
                left: 0;
            }
            
            .navbar .nav-links a {
                margin: 5px 0;
                width: 90%;
                margin: 0 auto;
            }
            
            .dropdown {
                width: 90%;
                margin: 0 auto;
            }
            
            .dropdown-toggle {
                width: 100%;
                justify-content: center;
            }
            
            .dropdown-menu {
                position: static;
                background: rgba(255, 255, 255, 0.1);
                box-shadow: none;
                width: 100%;
                display: none;
                margin-top: 5px;
            }
            
            .dropdown-menu a {
                color: white !important;
            }
            
            .dropdown-menu a:hover {
                background-color: rgba(255, 255, 255, 0.2) !important;
            }
            
            /* For mobile dropdown toggle */
            .dropdown-active .dropdown-menu {
                display: block;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="nav-title">
        <i class="fas fa-calendar-check"></i> MOMENTO EVENTS
    </div>
    
    <div class="menu-toggle" id="mobile-menu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </div>
    
    <div class="nav-links" id="nav-links">
        <a href="homes.php"><i class="fas fa-home"></i> Home</a>
        <a href="bookings.php"><i class="fas fa-book"></i> Bookings</a>
        <a href="services.php"><i class="fas fa-concierge-bell"></i> Services</a>
        
        <!-- Dropdown for booking details -->
        <div class="dropdown" id="booking-dropdown">
            <div class="dropdown-toggle">
                <i class="fas fa-tasks"></i> Booking Details <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu">
            <a href="view_booking.php"><i class="fas fa-edit"></i> Modify</a>
                <a href="manage_booking.php"><i class="fas fa-eye"></i> View Booking Details</a>
                <a href="view_events.php"><i class="fas fa-video"></i> Live Events</a>
                <a href="request.php"><i class="fas fa-paper-plane"></i> Send Request</a>
            </div>
        </div>
        
        <a href="abouts.php"><i class="fas fa-info-circle"></i> About</a>
        <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
    </div>
    
    <button class="logout-button" onclick="window.location.href='logout.php'">
        <i class="fas fa-sign-out-alt"></i> Logout
    </button>
</div>

<script>
    // JavaScript for mobile menu toggle
    const mobileMenu = document.getElementById('mobile-menu');
    const navLinks = document.getElementById('nav-links');
    
    mobileMenu.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        
        // Animate the hamburger icon
        const bars = document.querySelectorAll('.bar');
        bars.forEach(bar => bar.classList.toggle('change'));
    });
    
    // Mobile dropdown toggle
    document.addEventListener('DOMContentLoaded', function() {
        // For mobile view dropdown toggle
        if (window.innerWidth <= 900) {
            const dropdownToggle = document.querySelector('.dropdown-toggle');
            const dropdown = document.getElementById('booking-dropdown');
            
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                dropdown.classList.toggle('dropdown-active');
            });
        }
        
        // Highlight current page
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-links > a, .dropdown-menu a');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage) {
                link.classList.add('active');
                
                // If it's a dropdown item, also highlight the dropdown toggle
                if (link.closest('.dropdown-menu')) {
                    link.closest('.dropdown').querySelector('.dropdown-toggle').classList.add('active');
                }
            } else {
                link.classList.remove('active');
            }
        });
    });
</script>

</body>
</html>