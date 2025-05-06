<?php
session_start();
include('db.php'); // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $role = $_POST['role']; // Get selected role

    // Check if email & role exist in the database
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $otp = rand(100000, 999999); // Generate OTP
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_role'] = $role; // Store role in session

        // Store OTP in database
        $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ? AND role = ?");
        $stmt->bind_param("iss", $otp, $email, $role);
        $stmt->execute();
        $stmt->close();

        // Send OTP via Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ajaiofficial06@gmail.com';  // Replace with your email
            $mail->Password = 'pxqzpxdkdbfgbfah'; // Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('ajaiofficial06@gmail.com', 'native ');
            $mail->addAddress($email);
            $mail->Subject = "Password Reset OTP";
            $mail->Body = "Your OTP for password reset is: $otp";

            $mail->send();
            header("Location: verify_otp.php"); // Redirect to OTP verification page
            exit();
        } catch (Exception $e) {
            echo "Email could not be sent. Error: " . $mail->ErrorInfo;
        }
    } else {
        $error_message = "Email or role not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .forgot-container {
            max-width: 450px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            text-align: center;
            margin-bottom: 25px;
            color: #343a40;
        }
        .form-control {
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #007BFF;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #6c757d;
            text-decoration: none;
        }
        .back-link:hover {
            color: #343a40;
        }
    </style>
</head>
<body>
    <!-- Include navbar2.php instead of hardcoded navbar -->
    <?php include('navbar2.php'); ?>

    <div class="container">
        <div class="forgot-container">
            <h2 class="form-title">Forgot Password</h2>
            
            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="forgot_password.php" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                </div>
                
                <div class="mb-3">
                    <label for="role" class="form-label">Select Your Role</label>
                    <select class="form-select form-control" id="role" name="role" required>
                        <option value="" disabled selected>Choose your role</option>
                        <option value="admin">Admin</option>
                        <option value="event organizer">Event Organizer</option>
                        
                        <option value="user">User</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Send OTP</button>
            </form>
            
            <a href="login.php" class="back-link">Back to Login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>