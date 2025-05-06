<?php
// Start session to access session variables
session_start();

// Database connection
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $organizer_id = isset($_POST['organizer_id']) ? intval($_POST['organizer_id']) : 0;
    $current_booking_id = isset($_POST['current_booking_id']) ? intval($_POST['current_booking_id']) : 0;
    $new_booking_id = isset($_POST['new_booking_id']) ? intval($_POST['new_booking_id']) : 0;
    $admin_id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : 'reassign'; // Add action parameter
    
    // Validate input
    if ($organizer_id <= 0 || $current_booking_id <= 0) {
        $_SESSION['error_message'] = "Invalid input data. Please try again.";
        header("Location: organizer_details.php?id=" . $organizer_id);
        exit;
    }
    
    // Additional validation for reassignment
    if ($action == 'reassign' && $new_booking_id <= 0) {
        $_SESSION['error_message'] = "Please select a new booking for reassignment.";
        header("Location: organizer_details.php?id=" . $organizer_id);
        exit;
    }
    
    // Check if the new booking is already assigned to another organizer (only for reassignment)
    if ($action == 'reassign') {
        $check_sql = "SELECT organizer_id FROM organizer_assignments WHERE booking_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $new_booking_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            if ($row['organizer_id'] != $organizer_id) {
                $_SESSION['error_message'] = "This booking is already assigned to another organizer.";
                header("Location: organizer_details.php?id=" . $organizer_id);
                exit;
            }
        }
        $check_stmt->close();
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        if ($action == 'reassign') {
            // Update the assignment in the database
            $update_sql = "UPDATE organizer_assignments SET booking_id = ?, updated_at = NOW() WHERE organizer_id = ? AND booking_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iii", $new_booking_id, $organizer_id, $current_booking_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Log the reassignment
            $log_sql = "INSERT INTO assignment_logs (organizer_id, previous_booking_id, new_booking_id, admin_id, notes, action_type, created_at) 
                        VALUES (?, ?, ?, ?, ?, 'reassign', NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iiiis", $organizer_id, $current_booking_id, $new_booking_id, $admin_id, $notes);
            
            // Set success message
            $_SESSION['success_message'] = "Booking successfully reassigned.";
        } else if ($action == 'remove') {
            // Delete the assignment from the database
            $delete_sql = "DELETE FROM organizer_assignments WHERE organizer_id = ? AND booking_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $organizer_id, $current_booking_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            
            // Log the removal
            $log_sql = "INSERT INTO assignment_logs (organizer_id, previous_booking_id, new_booking_id, admin_id, notes, action_type, created_at) 
                        VALUES (?, ?, 0, ?, ?, 'remove', NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iiis", $organizer_id, $current_booking_id, $admin_id, $notes);
            
            // Set success message
            $_SESSION['success_message'] = "Booking assignment successfully removed.";
        }
        
        // Execute the log statement
        $log_stmt->execute();
        $log_stmt->close();
        
        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error processing assignment: " . $e->getMessage();
    }
    
    // Redirect back to organizer details page
    header("Location: organizer_details.php?id=" . $organizer_id);
    exit;
} else {
    // If not a POST request, redirect to organizer list
    header("Location: organizer_details.php");
    exit;
}

// Close connection
$conn->close();
?>