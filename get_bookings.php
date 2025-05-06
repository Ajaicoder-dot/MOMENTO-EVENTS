<?php
// Database connection
include 'db.php';
session_start();

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Temporarily remove admin check for testing
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
//     echo json_encode(['error' => 'Unauthorized access']);
//     exit;
// }

// Use this simpler query for now since we're working with your actual book table
try {
    $sql = "SELECT * FROM book WHERE cancelled = 0 AND approval_status != 'Rejected'";
    $result = $conn->query($sql);
    
    if ($result === false) {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
        exit;
    }
    
    $bookings = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = [
                'id' => $row['id'],
                'event_name' => $row['event_head'], // Using event_head as the event name
                'event_date' => $row['event_start_date'], // Using event_start_date
                'customer_name' => $row['event_head'] // Using event_head as customer name since there's no specific name field
            ];
        }
    }
    
    // Return bookings as JSON
    header('Content-Type: application/json');
    echo json_encode($bookings);
} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
}

// Close connection
$conn->close();
?>