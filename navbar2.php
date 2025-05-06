<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            padding: 15px 30px;
            color: white;
            font-weight: bold;
        }
        .navbar .nav-title {
            font-size: 20px;
        }
        .navbar .nav-links {
            display: flex;
            gap: 20px;
        }
        .navbar .nav-links a, .navbar .auth-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 5px 10px;
        }
        .navbar .nav-links a:hover, .navbar .auth-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="nav-title">MOMENTO EVENTS </div>
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="about.php">About</a>
        <a href="contacts.php">Contact Us</a>

    </div>
    <div class="auth-links">
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>
