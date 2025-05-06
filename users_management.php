<?php
// Database connection
require_once 'db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success_message = '';
$error_message = '';

// Handle POST requests for role changes (more secure than GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Security verification failed. Please try again.";
    } else {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        // Handle role removal
        if (isset($_POST['action']) && $_POST['action'] == 'remove_role') {
            // Don't allow removing admin role from self
            if ($user_id != $_SESSION['user_id']) {
                // Update user to have no role
                $stmt = $conn->prepare("UPDATE users SET role = NULL WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                
                if ($stmt->execute()) {
                    $success_message = "User role has been removed successfully.";
                } else {
                    $error_message = "Failed to remove user role.";
                }
            } else {
                $error_message = "You cannot remove your own admin role.";
            }
        }
        
        // Handle role change
        if (isset($_POST['action']) && $_POST['action'] == 'change_role' && isset($_POST['new_role'])) {
            $new_role = $_POST['new_role'];
            
            // Validate the new role
            $valid_roles = ['admin', 'user', 'event organizer', 'service provider'];
            if (in_array($new_role, $valid_roles) && $user_id != $_SESSION['user_id']) {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->bind_param("si", $new_role, $user_id);
                
                if ($stmt->execute()) {
                    $success_message = "User role has been updated to " . htmlspecialchars($new_role) . ".";
                } else {
                    $error_message = "Failed to update user role.";
                }
            } else {
                $error_message = "Invalid role or you cannot change your own role.";
            }
        }
    }
}

// For backward compatibility, handle GET requests (but will be deprecated)
if (isset($_GET['action']) && $_GET['action'] == 'remove_role' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Don't allow removing admin role from self
    if ($user_id != $_SESSION['user_id']) {
        // Update user to have no role
        $stmt = $conn->prepare("UPDATE users SET role = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Redirect to clear GET parameters
        header("Location: users_management.php");
        exit();
    }
}

// For backward compatibility, handle GET requests (but will be deprecated)
if (isset($_GET['action']) && $_GET['action'] == 'change_role' && isset($_GET['id']) && isset($_GET['role'])) {
    $user_id = intval($_GET['id']);
    $new_role = $_GET['role'];
    
    // Validate the new role
    $valid_roles = ['admin', 'user', 'event organizer', 'service provider'];
    if (in_array($new_role, $valid_roles) && $user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        $stmt->execute();
        
        // Redirect to clear GET parameters
        header("Location: users_management.php");
        exit();
    }
}

// Fetch all users
$sql = "SELECT id, username, email, role FROM users ORDER BY FIELD(role, 'admin') DESC, username ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .user-table th, .user-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .user-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .user-table tr:hover {
            background-color: #f1f1f1;
        }
        
        .role-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
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
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .btn-remove {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-role {
            background-color: #6c757d;
            color: white;
        }
        
        .my-role {
            display: block;
            margin-top: 5px;
            font-style: italic;
            color: #6c757d;
        }
        
        .action-header {
            text-align: center;
        }
        
        .no-action {
            color: #6c757d;
            font-style: italic;
            text-align: center;
        }
        
        .role-dropdown {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
        }
        
        .role-dropdown a, .role-dropdown button {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
            width: 100%;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .role-dropdown a:hover, .role-dropdown button:hover {
            background-color: #f1f1f1;
        }
        
        .dropdown {
            position: relative;
            display: inline-block;
            margin-bottom: 5px;
        }
        
        .dropdown:hover .role-dropdown {
            display: block;
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
    </style>
</head>
<body>
    <?php include 'navbar1.php'; ?>
    
    <div class="container">
        <h1>User Management</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="action-header">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Set appropriate role class for styling
                        $role_class = 'role-other';
                        $role_display = $row['role'];
                        
                        if ($row['role'] === 'admin') {
                            $role_class = 'role-admin';
                        } else if ($row['role'] === 'user') {
                            $role_class = 'role-user';
                        } else if ($row['role'] === 'event organizer') {
                            $role_class = 'role-event-organizer';
                        } else if ($row['role'] === 'service provider') {
                            $role_class = 'role-service-provider';
                        }
                        
                        $is_current_user = ($row['id'] == $_SESSION['user_id']);
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($row['username']); ?>
                                <?php if ($is_current_user): ?>
                                    <span class="my-role">My Role</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if (!empty($row['role'])): ?>
                                    <span class="role-badge <?php echo $role_class; ?>">
                                        <?php echo htmlspecialchars($role_display); ?>
                                    </span>
                                <?php else: ?>
                                    <em>None</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$is_current_user): ?>
                                    <div class="dropdown">
                                        <button class="action-btn btn-role">Change Role</button>
                                        <div class="role-dropdown">
                                            <!-- Backward compatibility links (to be deprecated) -->
                                            <a href="users_management.php?action=change_role&id=<?php echo $row['id']; ?>&role=admin">Admin (Legacy)</a>
                                            <a href="users_management.php?action=change_role&id=<?php echo $row['id']; ?>&role=user">User (Legacy)</a>
                                            <a href="users_management.php?action=change_role&id=<?php echo $row['id']; ?>&role=event organizer">Event Organizer (Legacy)</a>
                                            <a href="users_management.php?action=change_role&id=<?php echo $row['id']; ?>&role=service provider">Service Provider (Legacy)</a>
                                            <a href="users_management.php?action=remove_role&id=<?php echo $row['id']; ?>">Remove Role (Legacy)</a>
                                            
                                            <!-- New secure POST forms -->
                                            <form method="post" action="">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="change_role">
                                                <input type="hidden" name="new_role" value="admin">
                                                <button type="submit">Admin</button>
                                            </form>
                                            
                                            <form method="post" action="">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="change_role">
                                                <input type="hidden" name="new_role" value="user">
                                                <button type="submit">User</button>
                                            </form>
                                            
                                            <form method="post" action="">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="change_role">
                                                <input type="hidden" name="new_role" value="event organizer">
                                                <button type="submit">Event Organizer</button>
                                            </form>
                                            
                                            <form method="post" action="">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="change_role">
                                                <input type="hidden" name="new_role" value="service provider">
                                                <button type="submit">Service Provider</button>
                                            </form>
                                            
                                            <form method="post" action="">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="remove_role">
                                                <button type="submit">Remove Role</button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit">
                                        Edit User
                                    </a>
                                <?php else: ?>
                                    <span class="no-action">Admin Self</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Hide success/error messages after 5 seconds
        setTimeout(function() {
            var alerts = document.getElementsByClassName('alert');
            for (var i = 0; i < alerts.length; i++) {
                alerts[i].style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>