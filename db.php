<?php
$servername = "localhost";
$username = "root"; // Change this if you set a MySQL password
$password = ""; // Change this if needed
$database = "user_system"; // Ensure this matches your database name

$conn = new mysqli($servername, $username, $password, $database);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
