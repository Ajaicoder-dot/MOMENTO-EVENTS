<?php
include 'db.php';
include 'navbar2.php';
// Fetch service categories
$serviceCategories = [];
$result = $conn->query("SELECT id, category_name FROM service_categories");
while ($row = $result->fetch_assoc()) {
    $serviceCategories[] = $row;
}
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $service_category_id = ($role === "service provider") ? $_POST['service_category'] : NULL;

    // Validate role to prevent invalid values
    $allowed_roles = ['admin', 'event organizer', 'service provider', 'user'];
    if (!in_array($role, $allowed_roles)) {
        $error_message = "Invalid role selected.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, service_category_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $username, $email, $password, $role, $service_category_id);

        if ($stmt->execute()) {
            $success_message = "Registration successful! <a href='login.php'>Login here</a>";
        } else {
            $error_message = "Error: " . $stmt->error;
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
    <title>Register - Native Event Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: url('image/img5.jpeg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
        }
        .footer {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            text-align: center;
            padding: 10px;
            position: fixed;
            width: 100%;
            bottom: 0;
            color: white;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.85);
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 350px;
            margin: 80px auto;
            min-height: 400px;
        }
        .register-container h2 { font-size: 24px; margin-bottom: 20px; }
        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn-container { display: flex; justify-content: center; 
        }
        button {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            padding: 10px 16px;
            width: auto;
            border: none;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover { background: linear-gradient(45deg, #5a0fb4, #1e5edc); }
        .error { color: red; margin-bottom: 10px; }
        .success { color: green; margin-bottom: 10px; }
        .login-link { margin-top: 15px; font-size: 14px; }
        .login-link a { color: #6a11cb; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
        #service-category-container { display: none; }
    </style>
    <script>
        function toggleServiceCategory() {
            var role = document.getElementById("role").value;
            var serviceCategoryContainer = document.getElementById("service-category-container");

            if (role === "service provider") {
                serviceCategoryContainer.style.display = "block";
            } else {
                serviceCategoryContainer.style.display = "none";
            }
        }
    </script>
</head>
<body>
<!-- Registration Form -->
<div class="register-container">
    <h2>Register</h2>
    
    <?php if (!empty($error_message)) echo "<p class='error'>$error_message</p>"; ?>
    <?php if (!empty($success_message)) echo "<p class='success'>$success_message</p>"; ?>
    
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <select name="role" id="role" required onchange="toggleServiceCategory()">
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="event organizer">Event Organizer</option>
            <option value="user">User</option>
        </select>
        <div id="service-category-container">
            <select name="service_category">
                <option value="" disabled selected>Select Service Category</option>
                <?php foreach ($serviceCategories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="btn-container">
            <button type="submit">Register</button>
        </div>
    </form>
    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
</div>

<!-- Footer -->
<div class="footer">&copy; <?php echo date("Y"); ?> Momento Events. All Rights Reserved.</div>

</body>
</html>