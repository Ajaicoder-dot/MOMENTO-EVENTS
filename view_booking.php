

<?php
// Database connection
include 'db.php';
include 'navbar3.php';

// Use PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'vendor/autoload.php';

// Function to send email notification
function sendNotificationEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ajaiofficial06@gmail.com'; 
        $mail->Password   = 'pxqzpxdkdbfgbfah'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('ajaiofficial06@gmail.com', 'Event Booking System');
        $mail->addAddress($to); // Add recipient email
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for update message
if (isset($_GET['update']) && $_GET['update'] == 'success') {
    $update_message = "Booking has been successfully updated.";
}

// Check for cancellation message
if (isset($_GET['cancel']) && $_GET['cancel'] == 'success') {
    $cancel_message = "Booking has been successfully cancelled.";
    if (isset($_GET['email']) && $_GET['email'] == 'sent') {
        $cancel_message .= " Admin has been notified via email.";
    }
}

// Check for approval message
if (isset($_GET['approval']) && $_GET['approval'] == 'success') {
    $approval_message = "Booking status has been updated successfully.";
    if (isset($_GET['email']) && $_GET['email'] == 'sent') {
        $approval_message .= " Email notification has been sent to the user.";
    }
}

// Get user role - this should be set in your authentication system
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';

// Handle approval/rejection actions (only for admin)
if ($is_admin && isset($_POST['action']) && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    
    // Get booking details first
    $booking_query = "SELECT b.*, sc.category_name 
                     FROM book b 
                     LEFT JOIN service_categories sc ON b.service_category_id = sc.id 
                     WHERE b.id = ?";
    $booking_stmt = $conn->prepare($booking_query);
    $booking_stmt->bind_param("i", $booking_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_row = $booking_result->fetch_assoc();
    
    // Get user email if available
    $user_email = "";
    if (isset($booking_row['user_id']) && !empty($booking_row['user_id'])) {
        $user_query = "SELECT username, email FROM users WHERE id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $booking_row['user_id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        if ($user_row = $user_result->fetch_assoc()) {
            $user_email = $user_row['email'];
        }
    }
    
    // If no user email, use guest email
    if (empty($user_email) && isset($booking_row['guest_email'])) {
        $user_email = $booking_row['guest_email'];
    }
    
    if ($action == 'accept') {
        $sql = "UPDATE book SET approval_status = 'Accepted', approval_date = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute() && !empty($user_email)) {
            // Send acceptance email to user
            $subject = "Your Booking #$booking_id Has Been Accepted";
            $message = "
            <h2>Booking Acceptance Notification</h2>
            <p>Dear {$booking_row['event_head']},</p>
            <p>We're pleased to inform you that your booking has been accepted.</p>
            
            <h3>Booking Details:</h3>
            <p><strong>Booking ID:</strong> #{$booking_id}</p>
            <p><strong>Event:</strong> {$booking_row['category_name']}</p>
            <p><strong>Start Date:</strong> {$booking_row['event_start_date']} at {$booking_row['event_start_time']}</p>
            <p><strong>End Date:</strong> {$booking_row['event_end_date']} at {$booking_row['event_end_time']}</p>
            <p><strong>Venue:</strong> {$booking_row['venue']}</p>
            <p><strong>Total Amount:</strong> $" . number_format($booking_row['total_amount'], 2) . "</p>
            
            <p>Thank you for choosing our services. If you have any questions, please don't hesitate to contact us.</p>
            ";
            
            $email_sent = sendNotificationEmail($user_email, $subject, $message);
            $redirect_url = "view_bookings.php?approval=success";
            if ($email_sent) {
                $redirect_url .= "&email=sent";
            }
            header("Location: " . $redirect_url);
            exit();
        }
    } 
    else if ($action == 'reject') {
        // If reason is "Other", use the text from other_reason
        if ($reason == 'Other' && isset($_POST['other_reason']) && !empty($_POST['other_reason'])) {
            $reason = $_POST['other_reason'];
        }
        
        $sql = "UPDATE book SET approval_status = 'Rejected', approval_date = NOW(), rejection_reason = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $reason, $booking_id);
        
        if ($stmt->execute() && !empty($user_email)) {
            // Send rejection email to user
            $subject = "Your Booking #$booking_id Status Update";
            $message = "
            <h2>Booking Status Notification</h2>
            <p>Dear {$booking_row['event_head']},</p>
            <p>We regret to inform you that we are unable to accept your booking at this time.</p>
            
            <h3>Booking Details:</h3>
            <p><strong>Booking ID:</strong> #{$booking_id}</p>
            <p><strong>Event:</strong> {$booking_row['category_name']}</p>
            <p><strong>Start Date:</strong> {$booking_row['event_start_date']} at {$booking_row['event_start_time']}</p>
            <p><strong>End Date:</strong> {$booking_row['event_end_date']} at {$booking_row['event_end_time']}</p>
            <p><strong>Venue:</strong> {$booking_row['venue']}</p>
            
            <h3>Reason for Rejection:</h3>
            <p>{$reason}</p>
            
            <p>We apologize for any inconvenience this may cause. Please feel free to contact us if you have any questions or would like to discuss alternative options.</p>
            ";
            
            $email_sent = sendNotificationEmail($user_email, $subject, $message);
            $redirect_url = "view_bookings.php?approval=success";
            if ($email_sent) {
                $redirect_url .= "&email=sent";
            }
            header("Location: " . $redirect_url);
            exit();
        }
    }
    
    header("Location: view_bookings.php?approval=success");
    exit();
}

// Fetch booking details with creation date and approval status
$sql = "SELECT 
        id, event_head, phone_no, guest_email, guest_address, 
        event_start_date, event_end_date, event_start_time, event_end_time, 
        total_amount, venue, created_at, 
        DATEDIFF(NOW(), created_at) as days_since_creation,
        DATEDIFF(NOW(), event_end_date) as days_after_event,
        cancelled, cancel_reason, approval_status, rejection_reason
        FROM book 
        WHERE 
            (cancelled = 0 OR cancelled IS NULL) AND 
            (approval_status != 'Rejected' OR approval_status IS NULL) AND 
            (approval_status != 'Accepted' OR DATEDIFF(NOW(), event_end_date) <= 1)
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --grey-color: #6c757d;
            --body-bg: #f5f8fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--body-bg);
            color: var(--dark-color);
            line-height: 1.6;
            padding: 0px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: var(--dark-color);
            font-size: 28px;
            margin: 0;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            margin-bottom: 25px;
            transition: var(--transition);
        }
        
        .message {
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s;
        }
        
        .message i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .update-message, .approval-message {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        
        .cancel-message {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        
        .booking-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: var(--card-shadow);
        }
        
        .booking-table th, 
        .booking-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .booking-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        .booking-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .booking-table tr:hover {
            background-color: #f1f4f9;
        }
        
        .booking-table tr:last-child td {
            border-bottom: none;
        }
        
        .btn {
            padding: 8px 12px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .edit-btn {
            background-color: var(--primary-color);
            color: white;
        }
        
        .edit-btn:hover {
            background-color: #2980b9;
        }
        
        .disabled-btn {
            background-color: #ced4da;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .cancel-btn {
            background-color: var(--danger-color);
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #c0392b;
        }
        
        .accept-btn {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .accept-btn:hover {
            background-color: #27ae60;
        }
        
        .reject-btn {
            background-color: var(--warning-color);
            color: white;
        }
        
        .reject-btn:hover {
            background-color: #e67e22;
        }
        
        .status-indicator {
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        .editable {
            color: var(--secondary-color);
        }
        
        .not-editable {
            color: var(--danger-color);
        }
        
        .cancelled-row {
            background-color: #fff5f5;
        }
        
        .cancelled-row td {
            color: #6c757d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cancelled-status {
            background-color: #fde8e8;
            color: #e53e3e;
        }
        
        .accepted-status {
            background-color: #def7ec;
            color: #0e9f6e;
        }
        
        .rejected-status {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .pending-status {
            background-color: #e1effe;
            color: #3f83f8;
        }
        
        .reason-text {
            font-size: 12px;
            margin-top: 5px;
            font-style: italic;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #ddd;
            width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideIn 0.3s;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .close {
            color: var(--grey-color);
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.2s;
        }
        
        .close:hover,
        .close:focus {
            color: var(--dark-color);
            text-decoration: none;
            cursor: pointer;
        }
        
        .reason-option {
            margin: 15px 0;
            display: flex;
            align-items: flex-start;
        }
        
        .reason-option input[type="radio"] {
            margin-top: 3px;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .reason-option label {
            cursor: pointer;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            min-height: 80px;
            transition: border-color 0.3s;
        }
        
        textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .modal-buttons {
            margin-top: 25px;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .modal-buttons button {
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .confirm-cancel {
            background-color: var(--danger-color);
            color: white;
            border: none;
        }
        
        .confirm-cancel:hover {
            background-color: #c0392b;
        }
        
        .confirm-reject {
            background-color: var(--warning-color);
            color: white;
            border: none;
        }
        
        .confirm-reject:hover {
            background-color: #e67e22;
        }
        
        .cancel-cancel, .cancel-modal {
            background-color: #e2e8f0;
            color: var(--dark-color);
            border: none;
        }
        
        .cancel-cancel:hover, .cancel-modal:hover {
            background-color: #cbd5e0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .booking-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 15% auto;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-check"></i> Booking Management System</h1>
            <div class="header-actions">
                <a href="homes.php" class="btn edit-btn"><i class="fas fa-home"></i> Home</a>
                <a href="bookings.php" class="btn accept-btn"><i class="fas fa-plus"></i> New Booking</a>
            </div>
        </div>
        
        <?php if (isset($update_message)): ?>
        <div class="message update-message">
            <i class="fas fa-check-circle"></i> <?php echo $update_message; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($cancel_message)): ?>
        <div class="message cancel-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $cancel_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($approval_message)): ?>
        <div class="message approval-message">
            <i class="fas fa-check-circle"></i> <?php echo $approval_message; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-list"></i> Booking Details</h2>
            
            <?php if ($result->num_rows > 0): ?>
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Event Head</th>
                        <th>Contact</th>
                        <th>Event Details</th>
                        <th>Venue & Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        $editable = $row['days_since_creation'] <= 2;
                        $edit_status_class = $editable ? 'editable' : 'not-editable';
                        $edit_btn_class = $editable ? 'edit-btn' : 'disabled-btn';
                        $row_class = $row['cancelled'] ? 'cancelled-row' : '';
                        $approval_status = isset($row['approval_status']) ? $row['approval_status'] : 'Pending';
                        
                        echo "<tr class='$row_class'>
                            <td>{$row['event_head']}</td>
                            <td>
                                <div><i class='fas fa-phone'></i> {$row['phone_no']}</div>
                                <div><i class='fas fa-envelope'></i> {$row['guest_email']}</div>
                                <div><i class='fas fa-map-marker-alt'></i> {$row['guest_address']}</div>
                            </td>
                            <td>
                                <div><strong>Start:</strong> {$row['event_start_date']} at {$row['event_start_time']}</div>
                                <div><strong>End:</strong> {$row['event_end_date']} at {$row['event_end_time']}</div>
                            </td>
                            <td>
                                <div><strong>Venue:</strong> {$row['venue']}</div>
                                <div><strong>Amount:</strong> " . number_format($row['total_amount'], 2) . "</div>
                            </td>
                            <td>";
                        
                        if ($row['cancelled']) {
                            echo "<span class='status-badge cancelled-status'>Cancelled</span>";
                            if (!empty($row['cancel_reason'])) {
                                echo "<div class='reason-text'>Reason: {$row['cancel_reason']}</div>";
                            }
                        } else {
                            // Display approval status
                            if ($approval_status == 'Accepted') {
                                echo "<span class='status-badge accepted-status'>Accepted</span>";
                            } else if ($approval_status == 'Rejected') {
                                echo "<span class='status-badge rejected-status'>Rejected</span>";
                                if (!empty($row['rejection_reason'])) {
                                    echo "<div class='reason-text'>Reason: {$row['rejection_reason']}</div>";
                                }
                            } else {
                                echo "<span class='status-badge pending-status'>Pending</span>";
                            }
                        }
                        
                        echo "</td><td>";
                        
                        // Only show actions for active bookings and non-rejected bookings
                        if (!$row['cancelled'] && $approval_status != 'Rejected') {
                            // Show edit button if editable (within 2 days)
                            if ($editable) {
                                echo "<a href='edit_bookings.php?id={$row['id']}' class='btn edit-btn'><i class='fas fa-edit'></i> Edit</a>";
                                echo "<span class='status-indicator $edit_status_class'><i class='fas fa-clock'></i> " . (2 - $row['days_since_creation']) . " days left</span>";
                            } else {
                                echo "<button class='btn disabled-btn' title='Editing only allowed within 2 days of booking'><i class='fas fa-edit'></i> Edit</button>";
                                echo "<span class='status-indicator $edit_status_class'><i class='fas fa-ban'></i> Not editable</span>";
                            }
                            
                            // Show cancel button
                            echo "<button onclick='openCancelModal({$row['id']})' class='btn cancel-btn'><i class='fas fa-times'></i> Cancel</button>";
                            
                            // Only show accept/reject buttons for pending bookings if user is admin
                            if ($is_admin && $approval_status == 'Pending') {
                                echo "<button onclick='acceptBooking({$row['id']})' class='btn accept-btn'><i class='fas fa-check'></i> Accept</button>";
                                echo "<button onclick='openRejectModal({$row['id']})' class='btn reject-btn'><i class='fas fa-ban'></i> Reject</button>";
                            }
                        } else {
                            // For cancelled or rejected bookings, show no actions
                            echo "<span class='status-indicator not-editable'><i class='fas fa-info-circle'></i> No actions available</span>";
                        }
                        
                        echo "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="message" style="background-color: #f8f9fa; border: 1px solid #ddd;">
                    <i class="fas fa-info-circle"></i> No bookings found in the system.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- The Cancel Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-times-circle"></i> Cancel Booking</h3>
                <span class="close" onclick="closeModal('cancelModal')">&times;</span>
            </div>
            
            <p>Please select a reason for cancelling this booking:</p>
            
            <form id="cancelForm" action="cancel_booking.php" method="post">
                <input type="hidden" id="booking_id" name="booking_id" value="">
                
                <div class="reason-option">
                    <input type="radio" id="reason1" name="cancel_reason" value="Change of plans" required>
                    <label for="reason1">Change of plans</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reason2" name="cancel_reason" value="Found a better venue">
                    <label for="reason2">Found a better venue</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reason3" name="cancel_reason" value="Event cancelled">
                    <label for="reason3">Event cancelled</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reason4" name="cancel_reason" value="Other">
                    <label for="reason4">Other (please specify)</label>
                    <textarea id="other_reason" name="other_reason" rows="3" placeholder="Please specify your reason"></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="cancel-cancel" onclick="closeModal('cancelModal')">Back</button>
                    <button type="submit" class="confirm-cancel"><i class="fas fa-check"></i> Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- The Reject Modal (only for admin) -->
    <?php if ($is_admin): ?>
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fas fa-ban"></i> Reject Booking</h3>
                <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            </div>
            
            <p>Please provide a reason for rejecting this booking:</p>
            
            <form id="rejectForm" action="" method="post">
                <input type="hidden" id="reject_booking_id" name="booking_id" value="">
                <input type="hidden" name="action" value="reject">
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason1" name="reason" value="Venue not available" required>
                    <label for="reject_reason1">Venue not available</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason2" name="reason" value="Scheduling conflict">
                    <label for="reject_reason2">Scheduling conflict</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason3" name="reason" value="Insufficient information">
                    <label for="reject_reason3">Insufficient information</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason4" name="reason" value="Other">
                    <label for="reject_reason4">Other (please specify)</label>
                    <textarea id="reject_other_reason" name="other_reason" rows="3" placeholder="Please specify your reason"></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="cancel-modal" onclick="closeModal('rejectModal')">Back</button>
                    <button type="submit" class="confirm-reject"><i class="fas fa-check"></i> Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Hidden form for accept action -->
    <form id="acceptForm" action="" method="post" style="display: none;">
        <input type="hidden" id="accept_booking_id" name="booking_id" value="">
        <input type="hidden" name="action" value="accept">
    </form>
    <?php endif; ?>
    
    <script>
        // Get the modals
        var cancelModal = document.getElementById("cancelModal");
        <?php if ($is_admin): ?>
        var rejectModal = document.getElementById("rejectModal");
        <?php endif; ?>
        
        // When the user clicks on the cancel button, open the modal
        function openCancelModal(id) {
            document.getElementById("booking_id").value = id;
            cancelModal.style.display = "block";
            
            // Reset form
            document.getElementById("cancelForm").reset();
            document.getElementById("other_reason").style.display = "none";
        }
        
        <?php if ($is_admin): ?>
        // When the user clicks on the reject button, open the modal
        function openRejectModal(id) {
            document.getElementById("reject_booking_id").value = id;
            rejectModal.style.display = "block";
            
            // Reset form
            document.getElementById("rejectForm").reset();
            document.getElementById("reject_other_reason").style.display = "none";
        }
        
        // Accept booking function
        function acceptBooking(id) {
            document.getElementById("accept_booking_id").value = id;
            document.getElementById("acceptForm").submit();
        }
        <?php endif; ?>
        
        // Close the specified modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == cancelModal) {
                closeModal('cancelModal');
            }
            <?php if ($is_admin): ?>
            if (event.target == rejectModal) {
                closeModal('rejectModal');
            }
            <?php endif; ?>
        }
        
        // Show/hide the "Other reason" textarea based on selection for cancel
        document.querySelectorAll('input[name="cancel_reason"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'Other' && this.checked) {
                    document.getElementById('other_reason').style.display = 'block';
                } else {
                    document.getElementById('other_reason').style.display = 'none';
                }
            });
        });
        
        <?php if ($is_admin): ?>
        // Show/hide the "Other reason" textarea based on selection for reject
        document.querySelectorAll('input[name="reason"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'Other' && this.checked) {
                    document.getElementById('reject_other_reason').style.display = 'block';
                } else {
                    document.getElementById('reject_other_reason').style.display = 'none';
                }
            });
        });
        <?php endif; ?>
        
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            var messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
<?php include 'footer.php'; ?>
    
</html>