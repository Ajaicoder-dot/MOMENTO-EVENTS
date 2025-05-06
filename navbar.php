<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); 
    exit;
}
?>
<!-- Font Awesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
        text-decoration: none;
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
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 80%;
        max-width: 500px;
        position: relative;
    }
    
    .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close-btn:hover {
        color: black;
    }
    
    .user-details {
        margin-top: 20px;
    }
    
    .user-details p {
        margin: 10px 0;
        font-size: 16px;
    }
</style>

<div class="navbar">
    <div class="nav-title" onclick="window.location.href='index.php'">
        <i class="fas fa-calendar-check"></i> Momento Events
    </div>
    
    <div class="menu-toggle" onclick="toggleMenu()">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </div>
    
    <div class="nav-links" id="navLinks">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="live_event.php"><i class="fas fa-calendar-plus"></i> Add Live Event</a>
        
        <a href="modify_live_event.php"><i class="fas fa-edit"></i> Modify/Delete Events</a>
        <a href="organizer_assignments.php"><i class="fas fa-list-alt"></i> My Bookings</a>
        <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
        <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
    </div>
    
    <div class="user-section">
        <div class="user-info">
            <div class="user-name"><?php echo $_SESSION['username']; ?></div>
            <?php if(isset($_SESSION['role'])): ?>
            <div class="user-role"><?php echo $_SESSION['role']; ?></div>
            <?php endif; ?>
        </div>
        <a href="logout.php" class="logout-button">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<!-- Profile Modal -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h2>User Details</h2>
        <div class="user-details">
            <p><strong>Name:</strong> <?php echo $_SESSION['username']; ?></p>
            <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
            <p><strong>Role:</strong> <?php echo $_SESSION['role']; ?></p>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById("profileModal").style.display = "block";
    }
    
    function closeModal() {
        document.getElementById("profileModal").style.display = "none";
    }
    
    window.onclick = function(event) {
        var modal = document.getElementById("profileModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    
    // Toggle mobile menu
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("active");
        
        // Toggle the hamburger icon animation
        const bars = document.querySelectorAll(".bar");
        bars.forEach(bar => {
            bar.classList.toggle("change");
        });
    }
    
    // Add active class to current page link
    document.addEventListener("DOMContentLoaded", function() {
        const currentPage = window.location.pathname.split("/").pop();
        const navLinks = document.querySelectorAll(".nav-links a");
        
        navLinks.forEach(link => {
            const linkHref = link.getAttribute("href");
            if (linkHref === currentPage) {
                link.classList.add("active");
            }
        });
    });
</script>