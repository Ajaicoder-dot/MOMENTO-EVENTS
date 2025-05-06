<?php
// Database connection
include 'db.php';
session_start();

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get all organizers
$sql = "SELECT id, username FROM users WHERE role = 'organizer' OR role = 'event organizer' OR role LIKE '%organizer%'";
$result = $conn->query($sql);

$organizers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $organizers[] = [
            'id' => $row['id'],
            'username' => $row['username']
        ];
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($organizers);

$conn->close();
?>