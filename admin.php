<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        /* Ensure the whole page stretches properly */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            background: url('img1.jpg') no-repeat center center fixed;
            background-size: cover; /* Ensures the image covers the entire screen */
        }

        /* Content wrapper to push footer down */
        .content {
            flex: 1;
            padding: 20px;
            color: white;
            text-align: center;
            background: rgba(0, 0, 0, 0.5); /* Adds a slight overlay for readability */
        }

        /* Footer stays at the bottom */
        .footer {
            background-color: #222;
            color: white;
            text-align: center;
            padding: 20px 0;
            width: 100%;
            font-size: 16px;
        }

        .footer-links {
            margin: 10px 0;
        }

        .footer-links a {
            color: #00aced;
            text-decoration: none;
            margin: 0 15px;
            font-weight: bold;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 22px;
            transition: 0.3s;
        }

        .social-icons a:hover {
            color: #00aced;
            transform: scale(1.1);
        }
    </style>
</head>
<body>

    <?php include 'navbar1.php'; ?> <!-- Include the Navbar -->

    <div class="content">
        <h1>Hi, Admin!</h1>
        <p>Welcome to the Momento Events .</p>
    </div>

    <?php include 'footer.php'; ?> <!-- Include the Footer -->

</body>
</html>