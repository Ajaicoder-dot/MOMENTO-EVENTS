<?php
session_start();
include('db.php'); // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['otp'])) {
        $entered_otp = $_POST['otp'];
        $email = $_SESSION['reset_email']; // Get stored email
        $role = $_SESSION['reset_role'];   // Get stored role

        // Check if OTP is correct
        $stmt = $conn->prepare("SELECT otp FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $stmt->bind_result($stored_otp);
        $stmt->fetch();
        $stmt->close();

        if ($entered_otp == $stored_otp) {
            $_SESSION['otp_verified'] = true;
            header("Location: rest.php"); // Redirect to reset password page
            exit();
        } else {
            $error_message = "Invalid OTP! Please try again.";
        }
    } elseif (isset($_POST['resend_otp'])) {
        // Generate a new OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

        // Store new OTP in the database
        $stmt = $conn->prepare("UPDATE users SET otp = ? WHERE email = ? AND role = ?");
        $stmt->bind_param("iss", $otp, $_SESSION['reset_email'], $_SESSION['reset_role']);
        $stmt->execute();
        $stmt->close();

        // Send the new OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ajaiofficial06@gmail.com';  // Replace with your email
            $mail->Password = 'pxqzpxdkdbfgbfah'; // Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('ajaiofficial06@gmail.com', 'Seminar Hall Booking');
            $mail->addAddress($_SESSION['reset_email']);
            $mail->Subject = "New OTP for Password Reset";
            $mail->Body = "Your new OTP is: $otp";

            $mail->send();
            $success_message = "A new OTP has been sent to your email.";
        } catch (Exception $e) {
            $error_message = "Error sending email: " . $mail->ErrorInfo;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .otp-container {
            max-width: 450px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
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
            text-align: center;
            font-size: 18px;
            letter-spacing: 5px;
        }
        .btn-primary {
            background-color: #007BFF;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
        .success-message {
            color: #28a745;
            text-align: center;
            margin-bottom: 15px;
        }
        .timer {
            font-size: 16px;
            color: #dc3545;
            margin: 15px 0;
            font-weight: 600;
        }
        .hidden {
            display: none;
        }
        .otp-info {
            margin-bottom: 20px;
            color: #6c757d;
        }
    </style>
    <script>
        let timeLeft = 60;
        function startTimer() {
            let timerElement = document.getElementById("timer");
            let resendButton = document.getElementById("resend-otp");
            let otpInput = document.getElementById("otp-input");
            let submitBtn = document.getElementById("submit-btn");

            let timerInterval = setInterval(function () {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerElement.innerHTML = "<i class='fas fa-exclamation-circle'></i> OTP expired! Request a new one.";
                    otpInput.disabled = true;
                    submitBtn.disabled = true;
                    resendButton.classList.remove("hidden");
                } else {
                    timerElement.innerHTML = "<i class='fas fa-clock'></i> Time left: " + timeLeft + "s";
                }
                timeLeft -= 1;
            }, 1000);
        }

        window.onload = startTimer;
    </script>
</head>
<body>
    <!-- Include navbar2.php -->
    <?php include('navbar2.php'); ?>

    <div class="container">
        <div class="otp-container">
            <h2 class="form-title">OTP Verification</h2>
            
            <?php if(isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <p class="otp-info">We've sent a verification code to your email. Please enter it below.</p>
            
            <p class="timer" id="timer"><i class="fas fa-clock"></i> Time left: 60s</p>
            
            <form action="verify_otp.php" method="post">
                <div class="mb-3">
                    <input type="text" class="form-control" id="otp-input" name="otp" required placeholder="Enter OTP" maxlength="6">
                </div>
                <button type="submit" id="submit-btn" class="btn btn-primary">Verify OTP</button>
            </form>
            
            <form action="verify_otp.php" method="post" class="mt-3">
                <input type="hidden" name="resend_otp" value="1">
                <button type="submit" id="resend-otp" class="btn btn-secondary <?php echo !isset($error_message) ? 'hidden' : ''; ?>">
                    <i class="fas fa-sync-alt"></i> Resend OTP
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>