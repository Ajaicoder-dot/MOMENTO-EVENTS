<?php
include 'db.php';
include 'navbar2.php';

session_start();

// Fetch service categories
$serviceCategories = [];
$result = $conn->query("SELECT id, category_name FROM service_categories");
while ($row = $result->fetch_assoc()) {
    $serviceCategories[] = $row;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $selected_role = $_POST['role'];
    
    // Validate input
    if (empty($username) || empty($password) || empty($selected_role)) {
        $error = "All fields are required.";
    } else {
        // Fetch user data from the database
        $stmt = $conn->prepare("SELECT id, password, role, email, service_category_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hashed_password, $db_role, $email, $service_category_id);
        
        $user_found = false;
        while ($stmt->fetch()) {
            if (password_verify($password, $hashed_password) && $selected_role == $db_role) {
                $user_found = true;
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $db_role;
                $_SESSION['email'] = $email;
                $_SESSION['service_category_id'] = ($db_role == "service provider") ? $service_category_id : null;

                // Redirect based on role
                switch ($db_role) {
                    case "admin":
                        header("Location: ho.php");
                        break;
                    case "event organizer":
                        header("Location: index.php");
                        break;
                    case "service provider":
                        header("Location: service_provider_dashboard.php");
                        break;
                    default:
                        header("Location: homes.php");
                        break;
                }
                exit;
            }
        }
        
        if (!$user_found) {
            $error = "Invalid username, password, or role selection.";
        }
        
        $stmt->close();
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Native Event Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: url('image/img5.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
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
        .login-container {
            background: rgba(255, 255, 255, 0.85);
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
            margin: 80px auto;
            min-height: 400px;
        }
        .login-container h2 { font-size: 24px; margin-bottom: 20px; }
        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn-container { display: flex; justify-content: center; }
        button {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            padding: 10px 16px;
            border: none;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }
        button:hover { background: linear-gradient(45deg, #5a0ea1, #1b5acc); }
        .error { color: red; margin-bottom: 10px; }
        .register-link { margin-top: 15px; font-size: 14px; }
        .register-link a { color: #6a11cb; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<!-- Login Form -->
<div class="login-container">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <select name="role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="event organizer">Event Organizer</option>
           
            <option value="user">User</option>
        </select>
        
        <div class="btn-container">
            <button type="submit">Login</button>
        </div>
    </form>
    <p class="register-link">Don't have an account? <a href="register.php">Register now</a></p>
    <p class="forgot-password"><a href="forgot_password.php">Forgot Password?</a></p>

</div>

<!-- Footer -->
<div class="footer">&copy; <?php echo date("Y"); ?> Momento Events. All Rights Reserved.</div>

</body>
</html>