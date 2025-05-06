<?php
session_start();
include('db.php'); // Database connection

if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    die("Unauthorized access!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_SESSION['reset_email'];
    $role = $_SESSION['reset_role'];

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL WHERE email = ? AND role = ?");
    $stmt->bind_param("sss", $new_password, $email, $role);
    $stmt->execute();
    $stmt->close();

    // Clear session and redirect
    session_unset();
    session_destroy();
    $success_message = "Password reset successful!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .reset-container {
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
        .success-message {
            color: #28a745;
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: rgba(40, 167, 69, 0.1);
            border-radius: 5px;
        }
        .password-info {
            margin-bottom: 20px;
            color: #6c757d;
            text-align: left;
        }
        .login-link {
            display: inline-block;
            margin-top: 15px;
            color: #007BFF;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Include navbar2.php -->
    <?php include('navbar2.php'); ?>

    <div class="container">
        <div class="reset-container">
            <h2 class="form-title">Reset Password</h2>
            
            <?php if(isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    <div class="mt-3">
                        <a href="login.php" class="login-link">
                            <i class="fas fa-sign-in-alt"></i> Login Now
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="password-info">
                    <p><i class="fas fa-info-circle"></i> Please create a new password for your account.</p>
                    <ul class="text-muted">
                        <li>Use at least 8 characters</li>
                        <li>Include uppercase and lowercase letters</li>
                        <li>Add numbers and special characters for better security</li>
                    </ul>
                </div>
                
                <form action="rest.php" method="post">
                    <div class="mb-3">
                        <input type="password" class="form-control" name="password" required placeholder="Enter new password" minlength="8">
                    </div>
                    <div class="mb-3">
                        <input type="password" class="form-control" name="confirm_password" required placeholder="Confirm new password" minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>