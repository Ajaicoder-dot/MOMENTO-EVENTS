<?php
include 'navbar3.php';
include'db.php';
 // Update with your database name

// Create connection


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $remarks = $_POST['remarks'];

    // Insert into database
    $sql = "INSERT INTO contact_messages (name, email, phone, city, remarks) VALUES ('$name', '$email', '$phone', '$city', '$remarks')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Your message has been sent successfully!'); window.location.href='contact.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MOMENTO EVENTS </title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .container { display: flex; max-width: 1100px; margin: 50px auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .contact-form { flex: 1; padding: 20px; }
        .contact-form h2 { margin-bottom: 20px; }
        .contact-form input, .contact-form textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        .contact-form button { background: linear-gradient(45deg, #6a11cb, #2575fc); color: white; padding: 10px 16px; border: none; border-radius: 4px; cursor: pointer; }
        .contact-form button:hover { background: linear-gradient(45deg, #5a0fb4, #1e5edc); }
        .contact-info { flex: 1; padding: 20px; background: #f9f9f9; border-left: 2px solid #ddd; }
        .contact-info h2 { margin-bottom: 20px; }
        .contact-info p { margin-bottom: 10px; }
        .map-container { margin-top: 20px; }
        iframe { width: 100%; height: 250px; border: none; }

        .social-icons a {
            text-decoration: none;
            color: #333;
            font-size: 24px;
            margin: 0 10px;
            transition: 0.3s;
        }
        .social-icons a:hover { color: #2575fc; }

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
    <div class="contact-form">
        <h2>Contact Us</h2>
        <form method="POST">
    <input type="text" name="name" placeholder="Your Name" required>
    <input type="email" name="email" placeholder="Your Email" required>
    <input type="tel" name="phone" placeholder="Your Phone" required>
    <input type="text" name="city" placeholder="Your City" required>
    <textarea name="remarks" placeholder="Your Message" rows="5" required></textarea>
    <button type="submit">Submit</button>
</form>

    </div>

    <div class="contact-info">
        <h2>Get in Touch</h2>
        <p><strong>Address:</strong>Main road puducherry </p>
        <p><strong>Phone:</strong> +91 9361685137</p>
        <p><strong>Email:</strong> contact@nativeevents.com</p>
        <p><strong>Follow Us:</strong></p>
        <div class="social-icons">
            <a href="#"><i class="fa-brands fa-facebook"></i></a>
            <a href="#"><i class="fa-brands fa-twitter"></i></a>
            <a href="#"><i class="fa-brands fa-instagram"></i></a>
        </div>
        <div class="map-container">
    <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3916.4795561732656!2d79.85384331471812!3d12.013873391507185!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a5361e932f49f7d%3A0x3e5d3d0f6c6d7b6e!2sPondicherry%20University!5e0!3m2!1sen!2sin!4v1709630410823!5m2!1sen!2sin" 
        width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>

    </div>
</div>

<div class="footer">&copy; <?php echo date("Y"); ?> Momento Events. All Rights Reserved.</div>

</body>
</html>
