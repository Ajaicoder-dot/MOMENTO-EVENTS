<?php
// Database connection
require_once 'db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error_message = '';
$success_message = '';
$user_data = null;

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users_management.php");
    exit();
}

$user_id = intval($_GET['id']);

// Don't allow editing self through this page
if ($user_id == $_SESSION['user_id']) {
    header("Location: users_management.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Security verification failed. Please try again.";
    } else {
        // Validate input
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        if (empty($username) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please provide a valid username and email.";
        } else {
            // Check if email is already used by another user
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_stmt->bind_param("si", $email, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error_message = "Email is already in use by another account.";
            } else {
                // Update user information
                $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $username, $email, $user_id);
                
                if ($update_stmt->execute()) {
                    $success_message = "User information updated successfully.";
                    
                    // Update password if provided
                    if (!empty($_POST['password'])) {
                        if ($_POST['password'] === $_POST['confirm_password']) {
                            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                            $pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                            $pwd_stmt->bind_param("si", $password_hash, $user_id);
                            $pwd_stmt->execute();
                            $success_message .= " Password has been updated.";
                        } else {
                            $error_message = "Passwords do not match.";
                        }
                    }
                } else {
                    $error_message = "Error updating user information.";
                }
            }
        }
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: users_management.php");
    exit();
}

$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .user-role {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        
        .role-admin {
            background-color: #007bff;
            color: white;
        }
        
        .role-user {
            background-color: #17a2b8;
            color: white;
        }
        
        .role-event-organizer {
            background-color: #28a745;
            color: white;
        }
        
        .role-service-provider {
            background-color: #6c757d;
            color: white;
        }
        
        .role-other {
            background-color: #f8f9fa;
            color: #212529;
        }
        
        .password-section {
            border-top: 1px solid #ddd;
            padding-top: 20px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <?php include 'navbar1.php'; ?>
    
    <div class="container">
        <h1>Edit User</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($user_data): ?>
            <?php
                // Set appropriate role class for styling
                $role_class = 'role-other';
                
                if ($user_data['role'] === 'admin') {
                    $role_class = 'role-admin';
                } else if ($user_data['role'] === 'user') {
                    $role_class = 'role-user';
                } else if ($user_data['role'] === 'event organizer') {
                    $role_class = 'role-event-organizer';
                } else if ($user_data['role'] === 'service provider') {
                    $role_class = 'role-service-provider';
                }
            ?>
            
            <div>
                <span class="user-role <?php echo $role_class; ?>">
                    <?php echo !empty($user_data['role']) ? htmlspecialchars($user_data['role']) : 'No Role'; ?>
                </span>
                <small>(To change role, return to User Management)</small>
            </div>
            
            <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>
                
                <div class="password-section">
                    <h3>Change Password</h3>
                    <p><small>Leave blank to keep current password</small></p>
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="users_management.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <p>User not found.</p>
            <a href="users_management.php" class="btn btn-secondary">Back to User Management</a>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            
            if (password !== confirm) {
                this.setCustomValidity("Passwords don't match");
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>