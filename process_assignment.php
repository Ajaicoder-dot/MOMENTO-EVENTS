<?php
// Database connection
include 'db.php';
session_start();

// Check if user is admin


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $organizer_id = $_POST['organizer_id'];
    $booking_id = $_POST['booking_id'];
    $admin_id = $_POST['admin_id'];
    $notes = $_POST['notes'];
    $status = 'pending'; // Initial status is pending
    
    // Insert assignment into database
    $sql = "INSERT INTO organizer_assignments (organizer_id, booking_id, admin_id, notes, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiss", $organizer_id, $booking_id, $admin_id, $notes, $status);
    
    if ($stmt->execute()) {
        // Redirect back to organizer details with success message
        $_SESSION['assignment_message'] = "Assignment request sent successfully!";
        header("Location: organizer_details.php");
        exit;
    } else {
        // Redirect back with error message
        $_SESSION['assignment_error'] = "Error: " . $stmt->error;
        header("Location: organizer_details.php");
        exit;
    }
    
    $stmt->close();
}

$conn->close();
?>