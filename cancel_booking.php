<?php
session_start();
include 'db.php'; // Database connection

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

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    
    // Get the cancellation reason
    $cancel_reason = $_POST['cancel_reason'];
    if ($cancel_reason == 'Other' && !empty($_POST['other_reason'])) {
        $cancel_reason = $_POST['other_reason'];
    }
    
    // First, get the booking details to include in the email
    // Modified query to use username instead of name
    $booking_query = "SELECT b.*, u.username, u.email as user_email, 
                     sc.category_name 
                     FROM book b 
                     LEFT JOIN users u ON b.user_id = u.id 
                     LEFT JOIN service_categories sc ON b.service_category_id = sc.id 
                     WHERE b.id = ?";
    $booking_stmt = $conn->prepare($booking_query);
    $booking_stmt->bind_param("i", $booking_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    
    if ($booking_row = $booking_result->fetch_assoc()) {
        // Update the booking status to cancelled - using cancelled_at instead of cancel_date
        $update_sql = "UPDATE book SET cancelled = 1, cancel_reason = ?, cancelled_at = NOW() WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $cancel_reason, $booking_id);
        
        if ($update_stmt->execute()) {
            // Get admin email
            $admin_query = "SELECT email FROM users WHERE role = 'admin'";
            $admin_stmt = $conn->prepare($admin_query);
            $admin_stmt->execute();
            $admin_result = $admin_stmt->get_result();
            
            if ($admin_row = $admin_result->fetch_assoc()) {
                $admin_email = $admin_row['email'];
                
                // Create email content
                $email_subject = "Booking Cancellation Notification - ID #{$booking_id}";
                
                // Use username instead of name
                $user_name = isset($booking_row['username']) ? $booking_row['username'] : "User";
                $user_email = isset($booking_row['user_email']) ? $booking_row['user_email'] : $booking_row['guest_email'];
                
                $email_body = "
                <h2>Booking Cancellation Notification</h2>
                <p>A booking has been cancelled in the system.</p>
                
                <h3>Booking Details:</h3>
                <p><strong>Booking ID:</strong> #{$booking_id}</p>
                <p><strong>Event Head:</strong> {$booking_row['event_head']}</p>
                <p><strong>Phone:</strong> {$booking_row['phone_no']}</p>
                <p><strong>Email:</strong> {$booking_row['guest_email']}</p>
                <p><strong>Start Date:</strong> {$booking_row['event_start_date']} at {$booking_row['event_start_time']}</p>
                <p><strong>End Date:</strong> {$booking_row['event_end_date']} at {$booking_row['event_end_time']}</p>
                <p><strong>Venue:</strong> {$booking_row['venue']}</p>
                <p><strong>Category:</strong> {$booking_row['category_name']}</p>
                <p><strong>Total Amount:</strong> $" . number_format($booking_row['total_amount'], 2) . "</p>
                
                <h3>Cancellation Information:</h3>
                <p><strong>Cancelled By:</strong> {$user_name} ({$user_email})</p>
                <p><strong>Cancellation Reason:</strong> {$cancel_reason}</p>
                <p><strong>Cancellation Date:</strong> " . date('Y-m-d H:i:s') . "</p>
                ";
                
                // Send email to admin
                $email_sent = sendNotificationEmail($admin_email, $email_subject, $email_body);
                
                // Redirect with success message
                $redirect_url = "view_booking.php?cancel=success";
                if ($email_sent) {
                    $redirect_url .= "&email=sent";
                }
                header("Location: " . $redirect_url);
                exit();
            } else {
                // No admin found, just redirect with success
                header("Location: view_booking.php?cancel=success");
                exit();
            }
        } else {
            // Error updating
            header("Location: view_booking.php?cancel=error&message=" . urlencode($update_stmt->error));
            exit();
        }
    } else {
        // Booking not found
        header("Location: view_booking.php?cancel=error&message=booking_not_found");
        exit();
    }
} else {
    // Invalid request
    header("Location: view_bookings.php");
    exit();
}
?>