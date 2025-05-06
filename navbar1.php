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
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .navbar .nav-title:hover {
            transform: scale(1.05);
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
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .navbar .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        
        .navbar .nav-links a.active {
            background-color: rgba(255, 255, 255, 0.25);
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
            z-index: 1010;
        }
        
        .bar {
            width: 25px;
            height: 3px;
            background-color: white;
            margin: 3px 0;
            border-radius: 3px;
            transition: 0.4s;
        }
        
        .bar.change:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }
        
        .bar.change:nth-child(2) {
            opacity: 0;
        }
        
        .bar.change:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            color: white;
            font-size: 14px;
            text-align: right;
            display: none; /* Hidden by default, shown on larger screens */
        }
        
        .user-name {
            font-weight: 600;
            font-size: 15px;
        }
        
        .user-role {
            font-size: 13px;
            opacity: 0.9;
        }
        
        @media (min-width: 1200px) {
            .user-info {
                display: block;
            }
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
                height: calc(100vh - 70px);
                overflow-y: auto;
            }
            
            .navbar .nav-links.active {
                left: 0;
            }
            
            .navbar .nav-links a {
                margin: 5px 0;
                width: 90%;
                margin: 0 auto;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="nav-title" onclick="window.location.href='index.php'">
        <i class="fas fa-calendar-check"></i> MOMENTO EVENTS
    </div>
    
    <div class="menu-toggle" id="mobile-menu">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </div>
    
    <div class="nav-links" id="nav-links">
        <a href="ho.php"><i class="fas fa-home"></i> Home</a>
        <a href="event_booking_details.php"><i class="fas fa-book"></i> User Details</a>
        <a href="manage_bookings.php"><i class="fas fa-check-circle"></i> Accept/Reject Bookings</a>
        <a href="organizer_details.php"><i class="fas fa-user-tie"></i> Organizer Details</a>
        <a href="manage_requests.php" class="nav-link">
            <i class="fas fa-ticket-alt"></i> Manage Requests
        </a>
    </div>
    
    <div class="user-section">
        <div class="user-info">
            <div class="user-name">Hi, Admin</div>
            <div class="user-role"></div>
        </div>
        <button class="logout-button" onclick="window.location.href='logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</div>

<div style="padding: 20px; text-align: center;">
    <h1>Welcome to Momento Events</h1>
    <p>Your complete event management solution</p>
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
    
    // Highlight current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.nav-links > a');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
        });
    });
</script>

</body>
</html>