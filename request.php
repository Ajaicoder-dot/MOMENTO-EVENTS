<?php
session_start();
include('db.php');

// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
// echo "Session data: ";
// print_r($_SESSION);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Changed from id to user_id
    $request_type = $_POST['request_type'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $priority = $_POST['priority'];
    
    // File upload handling
    $attachment = "";
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png", "pdf" => "application/pdf", "doc" => "application/msword", "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        $filename = $_FILES['attachment']['name'];
        $filetype = $_FILES['attachment']['type'];
        $filesize = $_FILES['attachment']['size'];
        
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) {
            $error_message = "Error: Please select a valid file format.";
        } else {
            // Verify file size - 5MB maximum
            $maxsize = 5 * 1024 * 1024;
            if ($filesize > $maxsize) {
                $error_message = "Error: File size is larger than the allowed limit (5MB).";
            } else {
                // Verify MIME type of the file
                if (in_array($filetype, $allowed)) {
                    // Create unique filename
                    $new_filename = uniqid() . "." . $ext;
                    $upload_dir = "uploads/requests/";
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $new_filename)) {
                        $attachment = $upload_dir . $new_filename;
                    } else {
                        $error_message = "Error: There was a problem uploading your file. Please try again.";
                    }
                } else {
                    $error_message = "Error: There was a problem with your file. Please try again.";
                }
            }
        }
    }
    
    // If no errors, insert into database
    if (empty($error_message)) {
        $status = "Pending"; // Default status for new requests
        $created_at = date("Y-m-d H:i:s");
        
        // Insert request into database
        $stmt = $conn->prepare("INSERT INTO requests (user_id, request_type, subject, message, attachment, priority, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $request_type, $subject, $message, $attachment, $priority, $status, $created_at);
        
        if ($stmt->execute()) {
            $success_message = "Your request has been submitted successfully! We'll get back to you soon.";
            
            // Get admin email for notification
            $admin_email_query = $conn->query("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
            if ($admin_email_query && $admin_email_query->num_rows > 0) {
                $admin = $admin_email_query->fetch_assoc();
                $admin_email = $admin['email'];
                
                // Send email notification to admin (you can implement this using PHPMailer)
                // This is just a placeholder for the email notification functionality
                // sendEmailNotification($admin_email, "New Request Submitted", "A new request has been submitted. Please check the admin panel.");
            }
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Get user's previous requests
$user_id = $_SESSION['user_id']; // Changed from id to user_id
$requests_query = $conn->query("SELECT * FROM requests WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Request - Momento Events</title>
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
        
        .request-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .request-form {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .request-history {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-height: 700px;
            overflow-y: auto;
        }
        
        .form-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #6a11cb;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #6a11cb;
            outline: none;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
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
        
        .request-card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #6a11cb;
        }
        
        .request-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .request-title {
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }
        
        .request-date {
            color: #777;
            font-size: 14px;
        }
        
        .request-type {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
            background-color: #e9ecef;
        }
        
        .request-message {
            color: #555;
            margin-bottom: 10px;
            line-height: 1.5;
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
        
        .no-requests {
            text-align: center;
            color: #777;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .request-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include('navbar3.php'); ?>
    
    <div class="container">
        <h1 class="page-title">
            <i class="fas fa-paper-plane"></i> Send Request
        </h1>
        
        <div class="request-container">
            <div class="request-form">
                <h2 class="form-title">Submit a New Request</h2>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form action="request.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="request_type" class="form-label">Request Type</label>
                        <select name="request_type" id="request_type" class="form-control" required>
                            <option value="" disabled selected>Select request type</option>
                            <option value="Feature Request">Feature Request</option>
                            <option value="Support">Support</option>
                            <option value="Customization">Customization</option>
                            <option value="Feedback">Feedback</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-control" required placeholder="Brief description of your request">
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Message</label>
                        <textarea name="message" id="message" class="form-control" required placeholder="Provide details about your request..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority" class="form-label">Priority</label>
                        <select name="priority" id="priority" class="form-control" required>
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="attachment" class="form-label">Attachment (Optional)</label>
                        <input type="file" name="attachment" id="attachment" class="form-control">
                        <small style="color: #777; display: block; margin-top: 5px;">
                            Supported formats: JPG, JPEG, PNG, GIF, PDF, DOC, DOCX (Max: 5MB)
                        </small>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </form>
            </div>
            
            <div class="request-history">
                <h2 class="form-title">Your Request History</h2>
                
                <?php if ($requests_query && $requests_query->num_rows > 0): ?>
                    <?php while ($request = $requests_query->fetch_assoc()): ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div class="request-title"><?php echo htmlspecialchars($request['subject']); ?></div>
                                <div class="request-date"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></div>
                            </div>
                            
                            <div class="request-type"><?php echo htmlspecialchars($request['request_type']); ?></div>
                            
                            <div class="request-message">
                                <?php echo nl2br(htmlspecialchars(substr($request['message'], 0, 100) . (strlen($request['message']) > 100 ? '...' : ''))); ?>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="request-status status-<?php echo strtolower($request['status']); ?>">
                                    <?php echo htmlspecialchars($request['status']); ?>
                                </span>
                                
                                <span class="request-status priority-<?php echo strtolower($request['priority']); ?>">
                                    <?php echo htmlspecialchars($request['priority']); ?> Priority
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-requests">
                        <i class="fas fa-inbox" style="font-size: 40px; display: block; margin-bottom: 10px;"></i>
                        <p>You haven't submitted any requests yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>