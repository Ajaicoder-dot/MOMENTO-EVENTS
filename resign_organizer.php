<?php
// Database connection
include 'db.php';
session_start();

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if required parameters are provided
if (isset($_POST['organizer_id']) && isset($_POST['booking_id'])) {
    $organizer_id = intval($_POST['organizer_id']);
    $booking_id = intval($_POST['booking_id']);
    
    // Delete the assignment
    $sql = "DELETE FROM organizer_assignments WHERE organizer_id = ? AND booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $organizer_id, $booking_id);
    
    if ($stmt->execute()) {
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'No assignment found with the provided IDs']);
        }
    } else {
        echo json_encode(['error' => 'Failed to resign organizer: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['error' => 'Missing required parameters']);
}

$conn->close();
?>