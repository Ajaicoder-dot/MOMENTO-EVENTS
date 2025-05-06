<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to update a booking.");
}

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];
    $event_head = $_POST['event_head'];
    $phone_no = $_POST['phone_no'];
    $guest_email = $_POST['guest_email'];
    $event_start_date = $_POST['event_start_date'];
    $event_end_date = $_POST['event_end_date'];
    $event_start_time = $_POST['event_start_time'];
    $event_end_time = $_POST['event_end_time'];
    $venue = $_POST['venue'];
    $service_category = $_POST['service_category'];
    $user_id = $_SESSION['user_id'];
    $additional_details = isset($_POST['additional_details']) ? json_encode($_POST['additional_details']) : null;

    // Ensure "uploads" directory exists
    $target_dir = "image/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Function to handle image uploads
    function uploadImage($imageFieldName, $target_dir) {
        if (!empty($_FILES[$imageFieldName]["name"])) {
            $file_name = basename($_FILES[$imageFieldName]["name"]);
            $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $target_file = $target_dir . uniqid() . '.' . $imageFileType;
            $allowed_types = ["jpg", "jpeg", "png", "gif"];

            if (!in_array($imageFileType, $allowed_types)) {
                return null; // Return null if file type is not allowed
            }

            if (move_uploaded_file($_FILES[$imageFieldName]["tmp_name"], $target_file)) {
                return $target_file; // Return the file path
            }
        }
        return null; // Return null if upload fails or no file uploaded
    }

    // Upload new images (if provided)
    $birthday_image = uploadImage("birthday_image", $target_dir);
    $birthday_invite = uploadImage("birthday_invite", $target_dir);

    // Fetch existing images from the database
    $sql = "SELECT birthday_image FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_booking = $result->fetch_assoc();
    $stmt->close();

    // Decode existing image paths
    $existing_images = json_decode($existing_booking['birthday_image'], true) ?? [];

    // Merge new images (replace if uploaded, keep old ones if not)
    $image_paths = json_encode(array_filter([
        $birthday_image ?: ($existing_images[0] ?? null),
        $birthday_invite ?: ($existing_images[1] ?? null),
    ]));

    // Update booking details in the database
    $sql = "UPDATE bookings SET 
    event_head = ?, 
    phone_no = ?, 
    guest_email = ?, 
    event_start_date = ?, 
    event_end_date = ?, 
    event_start_time = ?, 
    event_end_time = ?, 
    venue = ?, 
    service_category_id = ?, 
    additional_details = ?, 
    birthday_image = ?
    WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error in SQL statement: " . $conn->error);
}

// Correcting bind_param to match placeholders
$stmt->bind_param("ssssssssisssi", 
    $event_head, 
    $phone_no, 
    $guest_email, 
    $event_start_date, 
    $event_end_date, 
    $event_start_time, 
    $event_end_time, 
    $venue, 
    $service_category, 
    $additional_details, 
    $image_paths, 
    $booking_id,  // int
    $user_id       // int
);


    if ($stmt->execute()) {
        echo "Booking successfully updated!";
        header("Location: bookings.php"); // Redirect to bookings page
        exit();
    } else {
        echo "Error updating booking: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
