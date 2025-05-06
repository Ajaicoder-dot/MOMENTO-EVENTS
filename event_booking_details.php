<?php
// Database connection
require_once 'db.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle user removal if requested
if (isset($_GET['action']) && $_GET['action'] == 'remove_user' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Don't allow removing self
    if ($user_id != $_SESSION['user_id']) {
        // Delete the user from the database
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Redirect to clear GET parameters
        header("Location: event_booking_details.php?deleted=true");
        exit();
    }
}

// Handle role removal if requested
if (isset($_GET['action']) && $_GET['action'] == 'remove_role' && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    // Don't allow removing admin role from self
    if ($user_id != $_SESSION['user_id']) {
        // Update user to have no role
        $stmt = $conn->prepare("UPDATE users SET role = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Redirect to clear GET parameters
        header("Location: event_booking_details.php");
        exit();
    }
}

// Handle role change if requested
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
        header("Location: event_booking_details.php");
        exit();
    }
}

// Fetch all users
$sql = "SELECT id, username, email, role FROM users ORDER BY FIELD(role, 'admin') DESC, username ASC";
$result = $conn->query($sql);

// Success messages
$success_message = '';
if (isset($_GET['deleted']) && $_GET['deleted'] == 'true') {
    $success_message = 'User has been successfully removed.';
}
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
        
        .role-dropdown a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        
        .role-dropdown a:hover {
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
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }
        
        .confirmation-modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
            text-align: center;
        }
        
        .modal-buttons {
            margin-top: 20px;
        }
        
        .modal-btn {
            padding: 8px 16px;
            margin: 0 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-confirm {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'navbar1.php'; ?>
    
    <div class="container">
        <h1>User Management</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
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
                                            <a href="event_booking_details.php?action=change_role&id=<?php echo $row['id']; ?>&role=admin">Admin</a>
                                            <a href="event_booking_details.php?action=change_role&id=<?php echo $row['id']; ?>&role=user">User</a>
                                            <a href="event_booking_details.php?action=change_role&id=<?php echo $row['id']; ?>&role=event organizer">Event Organizer</a>
                                            <a href="event_booking_details.php?action=change_role&id=<?php echo $row['id']; ?>&role=service provider">Service Provider</a>
                                            <a href="event_booking_details.php?action=remove_role&id=<?php echo $row['id']; ?>">Remove Role</a>
                                        </div>
                                    </div>
                                    
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit">
                                        Edit User
                                    </a>
                                    
                                    <button class="action-btn btn-remove" onclick="confirmDeleteUser(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>')">
                                        Remove User
                                    </button>
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
    
    <!-- Confirmation Modal for User Deletion -->
    <div id="deleteConfirmModal" class="confirmation-modal">
        <div class="modal-content">
            <h3>Confirm User Removal</h3>
            <p>Are you sure you want to remove the user <span id="deleteUserName"></span>?</p>
            <p>This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="modal-btn btn-cancel" onclick="closeModal()">Cancel</button>
                <a id="confirmDeleteLink" href="#" class="modal-btn btn-confirm">Remove User</a>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Function to show delete confirmation modal
        function confirmDeleteUser(userId, userName) {
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('confirmDeleteLink').href = 'event_booking_details.php?action=remove_user&id=' + userId;
            document.getElementById('deleteConfirmModal').style.display = 'block';
        }
        
        // Function to close the modal
        function closeModal() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }
        
        // Close the modal if user clicks outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('deleteConfirmModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>