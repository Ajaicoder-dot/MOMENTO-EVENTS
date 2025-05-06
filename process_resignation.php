<?php
// Database connection
include 'db.php';
session_start();

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (!isset($_POST['assignment_id']) || !isset($_POST['organizer_id']) || !isset($_POST['resign_reason'])) {
        $_SESSION['error'] = "Missing required fields";
        header("Location: organizer_details.php");
        exit;
    }
    
    $assignment_id = intval($_POST['assignment_id']);
    $organizer_id = intval($_POST['organizer_id']);
    $resign_reason = trim($_POST['resign_reason']);
    
    // Verify the assignment exists and belongs to the specified organizer
    $check_sql = "SELECT * FROM organizer_assignments WHERE id = ? AND organizer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $assignment_id, $organizer_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Invalid assignment or organizer";
        header("Location: organizer_details.php");
        exit;
    }
    
    // Update the assignment status to 'rejected' and add the rejection reason
    $update_sql = "UPDATE organizer_assignments SET status = 'rejected', reject_reason = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $resign_reason, $assignment_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Organizer has been resigned from the assignment successfully";
    } else {
        $_SESSION['error'] = "Failed to resign organizer: " . $conn->error;
    }
    
    $update_stmt->close();
    $check_stmt->close();
    
    // Redirect back to organizer details page
    header("Location: organizer_details.php");
    exit;
} else {
    // If not a POST request, redirect to the organizer details page
    header("Location: organizer_details.php");
    exit;
}

$conn->close();
?>