<?php
session_start();
include('../db.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle status update and response
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $admin_response = $_POST['admin_response'];
    $updated_at = date("Y-m-d H:i:s");
    
    $stmt = $conn->prepare("UPDATE requests SET status = ?, admin_response = ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("sssi", $status, $admin_response, $updated_at, $request_id);
    
    if ($stmt->execute()) {
        $success_message = "Request updated successfully!";
        
        // Get user email for notification
        $user_query = $conn->query("SELECT u.email FROM requests r JOIN users u ON r.user_id = u.user_id WHERE r.id = $request_id");
        if ($user_query && $user_query->num_rows > 0) {
            $user = $user_query->fetch_assoc();
            $user_email = $user['email'];
            
            // Send email notification to user (you can implement this using PHPMailer)
            // sendEmailNotification($user_email, "Your Request Status Updated", "Your request has been updated. Please check your account for details.");
        }
    } else {
        $error_message = "Error updating request: " . $stmt->error;
    }
    
    $stmt->close();
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// Build query with filters
$query = "SELECT r.*, u.username, u.email FROM requests r JOIN users u ON r.user_id = u.user_id WHERE 1=1";

if (!empty($status_filter)) {
    $query .= " AND r.status = '$status_filter'";
}

if (!empty($date_filter)) {
    $query .= " AND DATE(r.created_at) = '$date_filter'";
}

$query .= " ORDER BY r.created_at DESC";
$requests_query = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: #6a11cb;
            font-size: 32px;
            font-weight: 700;
        }
        
        .filter-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .filter-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-filter:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        .btn-reset {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-reset:hover {
            background: #5a6268;
        }
        
        .requests-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .request-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .request-card:hover {
            transform: translateY(-5px);
        }
        
        .request-header {
            padding: 15px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }
        
        .request-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .request-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .request-body {
            padding: 15px;
        }
        
        .request-info {
            margin-bottom: 15px;
        }
        
        .request-info-item {
            display: flex;
            margin-bottom: 8px;
        }
        
        .request-info-label {
            font-weight: 600;
            width: 100px;
            color: #555;
        }
        
        .request-info-value {
            flex: 1;
        }
        
        .request-message {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            max-height: 150px;
            overflow-y: auto;
        }
        
        .request-attachment {
            margin-bottom: 15px;
        }
        
        .request-attachment a {
            color: #6a11cb;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .request-attachment a:hover {
            text-decoration: underline;
        }
        
        .request-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-completed {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .priority-low {
            background-color: #d4edda;
            color: #155724;
        }
        
        .request-actions {
            margin-top: 15px;
        }
        
        .btn-respond {
            background: #6a11cb;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-respond:hover {
            background: #5a0db1;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
        
        .modal-close {
            font-size: 24px;
            cursor: pointer;
            color: #aaa;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
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
        
        .no-requests {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: #777;
        }
        
        @media (max-width: 768px) {
            .requests-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include('../admin/navbar.php'); ?>
    
    <div class="container">
        <h1 class="page-title">
            <i class="fas fa-ticket-alt"></i> Manage User Requests
        </h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-container">
            <form action="" method="get" style="display: flex; flex-wrap: wrap; gap: 15px; width: 100%;">
                <div class="filter-group">
                    <label for="status" class="filter-label">Filter by Status</label>
                    <select name="status" id="status" class="filter-control">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="Rejected" <?php echo $status_filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date" class="filter-label">Filter by Date</label>
                    <input type="date" name="date" id="date" class="filter-control" value="<?php echo $date_filter; ?>">
                </div>
                
                <div class="filter-group" style="display: flex; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    
                    <a href="manage_requests.php" class="btn-reset">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </form>
        </div>
        
        <?php if ($requests_query && $requests_query->num_rows > 0): ?>
            <div class="requests-container">
                <?php while ($request = $requests_query->fetch_assoc()): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div class="request-title"><?php echo htmlspecialchars($request['subject']); ?></div>
                            <div class="request-meta">
                                <span><?php echo date('M d, Y', strtotime($request['created_at'])); ?></span>
                                <span class="request-status status-<?php echo strtolower($request['status']); ?>">
                                    <?php echo htmlspecialchars($request['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="request-body">
                            <div class="request-info">
                                <div class="request-info-item">
                                    <div class="request-info-label">User:</div>
                                    <div class="request-info-value"><?php echo htmlspecialchars($request['username']); ?> (<?php echo htmlspecialchars($request['email']); ?>)</div>
                                </div>
                                
                                <div class="request-info-item">
                                    <div class="request-info-label">Type:</div>
                                    <div class="request-info-value"><?php echo htmlspecialchars($request['request_type']); ?></div>
                                </div>
                                
                                <div class="request-info-item">
                                    <div class="request-info-label">Priority:</div>
                                    <div class="request-info-value">
                                        <span class="request-status priority-<?php echo strtolower($request['priority']); ?>">
                                            <?php echo htmlspecialchars($request['priority']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="request-message">
                                <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                            </div>
                            
                            <?php if (!empty($request['attachment'])): ?>
                                <div class="request-attachment">
                                    <a href="../<?php echo $request['attachment']; ?>" target="_blank">
                                        <i class="fas fa-paperclip"></i> View Attachment
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($request['admin_response'])): ?>
                                <div style="margin-top: 15px;">
                                    <div style="font-weight: 600; margin-bottom: 5px;">Admin Response:</div>
                                    <div style="background: #e9ecef; padding: 10px; border-radius: 5px;">
                                        <?php echo nl2br(htmlspecialchars($request['admin_response'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="request-actions">
                                <button class="btn-respond" onclick="openResponseModal(<?php echo $request['id']; ?>, '<?php echo htmlspecialchars($request['subject']); ?>', '<?php echo htmlspecialchars($request['status']); ?>', '<?php echo htmlspecialchars(addslashes($request['admin_response'] ?? '')); ?>')">
                                    <i class="fas fa-reply"></i> Respond to Request
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-requests">
                <i class="fas fa-inbox" style="font-size: 50px; display: block; margin-bottom: 20px;"></i>
                <h3>No requests found</h3>
                <p>There are no requests matching your filter criteria.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Response Modal -->
    <div id="responseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Respond to Request</h2>
                <span class="modal-close" onclick="closeResponseModal()">&times;</span>
            </div>
            
            <form id="responseForm" action="" method="post">
                <input type="hidden" id="request_id" name="request_id">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="request_subject" class="form-label">Request Subject</label>
                        <input type="text" id="request_subject" class="form-control" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">Update Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_response" class="form-label">Your Response</label>
                        <textarea name="admin_response" id="admin_response" class="form-control" required placeholder="Enter your response to the user..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeResponseModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Submit Response</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functionality
        const modal = document.getElementById('responseModal');
        
        function openResponseModal(requestId, subject, status, adminResponse) {
            document.getElementById('request_id').value = requestId;
            document.getElementById('request_subject').value = subject;
            
            const statusSelect = document.getElementById('status');
            for (let i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].value === status) {
                    statusSelect.selectedIndex = i;
                    break;
                }
            }
            
            document.getElementById('admin_response').value = adminResponse;
            
            modal.style.display = 'block';
        }
        
        function closeResponseModal() {
            modal.style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                closeResponseModal();
            }
        }
    </script>
</body>
</html>






CREATE TABLE IF NOT EXISTS `requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `request_type` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `priority` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `admin_response` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE organizer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    organizer_id INT NOT NULL,
    request_date DATETIME NOT NULL,
    response_date DATETIME NULL,
    status ENUM('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending',
    rejection_reason TEXT NULL,
    FOREIGN KEY (booking_id) REFERENCES book(id),
    FOREIGN KEY (organizer_id) REFERENCES users(id)
);

-- Add organizer_status column to book table
ALTER TABLE book ADD COLUMN organizer_status ENUM('None', 'Sent', 'Accepted', 'Rejected') DEFAULT 'None';