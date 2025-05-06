<?php
// Database connection
include 'db.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Booking ID is required']);
    exit;
}

// Get booking ID and sanitize it
$booking_id = intval($_GET['id']);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Prepare and execute query to get booking details
$sql = "SELECT * FROM book WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if booking exists
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Booking not found']);
    exit;
}

// Fetch booking data
$booking = $result->fetch_assoc();

// Convert dates to readable format if needed
if (isset($booking['created_at'])) {
    $booking['created_at'] = date('M d, Y h:i A', strtotime($booking['created_at']));
}
if (isset($booking['event_start_date'])) {
    $booking['event_start_date'] = date('M d, Y', strtotime($booking['event_start_date']));
}
if (isset($booking['event_end_date'])) {
    $booking['event_end_date'] = date('M d, Y', strtotime($booking['event_end_date']));
}

// Return booking data as JSON
header('Content-Type: application/json');
echo json_encode($booking);
?>