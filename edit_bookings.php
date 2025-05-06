<?php
session_start();
include 'db.php'; // Database connection
include 'navbar3.php'; // Include your navigation bar if needed

// Use PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

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

// Check if user is logged in (if you have user authentication)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // If no user authentication, you can remove this or redirect to login
    $user_id = null;
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id <= 0) {
    echo "Invalid booking ID";
    exit();
}

// Check if booking exists and is within the 2-day edit window
$check_sql = "SELECT *, DATEDIFF(NOW(), created_at) as days_since_creation 
             FROM book WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows == 0) {
    echo "Booking not found.";
    exit();
}

$booking = $result->fetch_assoc();

// Check if the booking is older than 2 days
if ($booking['days_since_creation'] > 2) {
    echo "<div style='text-align: center; margin-top: 50px; padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px;'>
            <h2>Editing Not Allowed</h2>
            <p>Bookings can only be edited within 2 days of creation.</p>
            <p>This booking was created " . $booking['days_since_creation'] . " days ago.</p>
            <a href='view_booking.php' style='display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Return to Bookings</a>
          </div>";
    exit();
}

// If user authentication is implemented, check if the booking belongs to the current user
if ($user_id !== null && $booking['user_id'] != $user_id) {
    echo "You do not have permission to edit this booking.";
    exit();
}

// Process form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $event_head = $_POST['event_head'];
    $phone_no = $_POST['phone_no'];
    $guest_email = $_POST['guest_email'] ?? null;
    $guest_address = $_POST['guest_address'] ?? null;
    $event_start_date = $_POST['event_start_date'];
    $event_end_date = $_POST['event_end_date'];
    $event_start_time = $_POST['event_start_time'];
    $event_end_time = $_POST['event_end_time'];
    $venue = $_POST['venue'];
    $service_category = $_POST['service_category'] ?? $booking['service_category_id'];
    $selected_services = $_POST['selected_services'] ?? $booking['selected_services'];
    $total_amount = $_POST['total_amount'] ?? $booking['total_amount'];
    
    // Handle additional details if your form includes them
    $additional_details = isset($_POST['additional_details']) ? json_encode($_POST['additional_details']) : $booking['additional_details'];

    // Handle image uploads if your form includes them
    $target_dir = "image/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    function uploadImage($imageFieldName, $target_dir, $existing_image = null) {
        if (!empty($_FILES[$imageFieldName]["name"])) {
            $file_name = basename($_FILES[$imageFieldName]["name"]);
            $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $target_file = $target_dir . uniqid() . '.' . $imageFileType;
            $allowed_types = ["jpg", "jpeg", "png", "gif"];

            if (!in_array($imageFileType, $allowed_types)) {
                return $existing_image;
            }
            
            if (move_uploaded_file($_FILES[$imageFieldName]["tmp_name"], $target_file)) {
                return $target_file;
            }
        }
        return $existing_image;
    }

    // Get existing images from database
    $existing_images = !empty($booking['birthday_image']) ? json_decode($booking['birthday_image'], true) : [];
    $existing_birthday_image = isset($existing_images[0]) ? $existing_images[0] : null;
    $existing_birthday_invite = isset($existing_images[1]) ? $existing_images[1] : null;

    // Upload new images or keep existing ones
    $birthday_image = uploadImage("birthday_image", $target_dir, $existing_birthday_image);
    $birthday_invite = uploadImage("birthday_invite", $target_dir, $existing_birthday_invite);
    $image_paths = json_encode(array_filter([$birthday_image, $birthday_invite]));

    // Update the database
    $update_sql = "UPDATE book SET 
                  event_head = ?, 
                  phone_no = ?, 
                  guest_email = ?, 
                  guest_address = ?, 
                  event_start_date = ?, 
                  event_end_date = ?, 
                  event_start_time = ?, 
                  event_end_time = ?, 
                  venue = ?, 
                  service_category_id = ?, 
                  selected_services = ?, 
                  total_amount = ?, 
                  additional_details = ?, 
                  birthday_image = ? 
                  WHERE id = ?";
                  
    $update_stmt = $conn->prepare($update_sql);
    
    if (!$update_stmt) {
        die("Error in SQL statement: " . $conn->error);
    }

    $update_stmt->bind_param("ssssssssssssssi", 
        $event_head, 
        $phone_no, 
        $guest_email, 
        $guest_address,
        $event_start_date, 
        $event_end_date, 
        $event_start_time, 
        $event_end_time, 
        $venue, 
        $service_category, 
        $selected_services, 
        $total_amount, 
        $additional_details, 
        $image_paths,
        $booking_id
    );

    if ($update_stmt->execute()) {
        // Get admin email from users table where role is admin
        $admin_query = "SELECT email FROM users WHERE role = 'admin'";
        $admin_stmt = $conn->prepare($admin_query);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin_email = null;
        $email_sent = false;
        
        if ($admin_row = $admin_result->fetch_assoc()) {
            $admin_email = $admin_row['email'];
            
            // Get service category name
            $category_query = "SELECT category_name FROM service_categories WHERE id = ?";
            $category_stmt = $conn->prepare($category_query);
            $category_stmt->bind_param("i", $service_category);
            $category_stmt->execute();
            $category_result = $category_stmt->get_result();
            $category_name = "";
            
            if ($category_row = $category_result->fetch_assoc()) {
                $category_name = $category_row['category_name'];
            }
            
            // Create a list of changes made
            $changes = [];
            
            if ($original_booking['event_head'] != $event_head) {
                $changes[] = "Name changed from '{$original_booking['event_head']}' to '{$event_head}'";
            }
            
            if ($original_booking['phone_no'] != $phone_no) {
                $changes[] = "Phone number changed from '{$original_booking['phone_no']}' to '{$phone_no}'";
            }
            
            if ($original_booking['guest_email'] != $guest_email) {
                $changes[] = "Guest email changed from '{$original_booking['guest_email']}' to '{$guest_email}'";
            }
            
            if ($original_booking['guest_address'] != $guest_address) {
                $changes[] = "Guest address changed from '{$original_booking['guest_address']}' to '{$guest_address}'";
            }
            
            if ($original_booking['event_start_date'] != $event_start_date) {
                $changes[] = "Start date changed from '{$original_booking['event_start_date']}' to '{$event_start_date}'";
            }
            
            if ($original_booking['event_end_date'] != $event_end_date) {
                $changes[] = "End date changed from '{$original_booking['event_end_date']}' to '{$event_end_date}'";
            }
            
            if ($original_booking['event_start_time'] != $event_start_time) {
                $changes[] = "Start time changed from '{$original_booking['event_start_time']}' to '{$event_start_time}'";
            }
            
            if ($original_booking['event_end_time'] != $event_end_time) {
                $changes[] = "End time changed from '{$original_booking['event_end_time']}' to '{$event_end_time}'";
            }
            
            if ($original_booking['venue'] != $venue) {
                $changes[] = "Venue changed from '{$original_booking['venue']}' to '{$venue}'";
            }
            
            if ($original_booking['service_category_id'] != $service_category) {
                // Get original category name
                $orig_cat_query = "SELECT category_name FROM service_categories WHERE id = ?";
                $orig_cat_stmt = $conn->prepare($orig_cat_query);
                $orig_cat_stmt->bind_param("i", $original_booking['service_category_id']);
                $orig_cat_stmt->execute();
                $orig_cat_result = $orig_cat_stmt->get_result();
                $orig_category_name = "";
                
                if ($orig_cat_row = $orig_cat_result->fetch_assoc()) {
                    $orig_category_name = $orig_cat_row['category_name'];
                }
                
                $changes[] = "Service category changed from '{$orig_category_name}' to '{$category_name}'";
                $orig_cat_stmt->close();
            }
            
            if ($original_booking['selected_services'] != $selected_services) {
                $changes[] = "Selected services have been updated";
            }
            
            if ($original_booking['total_amount'] != $total_amount) {
                $changes[] = "Total amount changed from '{$original_booking['total_amount']}' to '{$total_amount}'";
            }
            
            // Only send email if there are changes
            if (!empty($changes)) {
                // Create email content
                $changes_list = "<ul>";
                foreach ($changes as $change) {
                    $changes_list .= "<li>{$change}</li>";
                }
                $changes_list .= "</ul>";
                
                $booking_details = "
                <h2>Booking Update Notification</h2>
                <p>Booking ID: #{$booking_id}</p>
                <p>The following changes have been made to the booking:</p>
                {$changes_list}
                <h3>Updated Booking Details:</h3>
                <p><strong>Event Head:</strong> {$event_head}</p>
                <p><strong>Phone:</strong> {$phone_no}</p>
                <p><strong>Start Date:</strong> {$event_start_date}</p>
                <p><strong>End Date:</strong> {$event_end_date}</p>
                <p><strong>Start Time:</strong> {$event_start_time}</p>
                <p><strong>End Time:</strong> {$event_end_time}</p>
                <p><strong>Venue:</strong> {$venue}</p>
                <p><strong>Category:</strong> {$category_name}</p>
                <p><strong>Total Amount:</strong> ${$total_amount}</p>
                ";
                
                // Send email to admin
                $admin_subject = "Booking Update Notification - ID #{$booking_id}";
                $admin_message = $booking_details;
                $email_sent = sendNotificationEmail($admin_email, $admin_subject, $admin_message);
            }
            
            $admin_stmt->close();
            if (isset($category_stmt)) $category_stmt->close();
        }
        
        // Create success message with email status
        $success_message = 'Booking successfully updated!';
        if (!empty($changes)) {
            if ($email_sent) {
                $success_message .= ' Admin has been notified of your changes.';
            } else {
                $success_message .= ' However, we could not notify the admin.';
            }
        }
        
        echo "<script>
                alert('" . $success_message . "');
                window.location.href = 'view_booking.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Error: " . $update_stmt->error . "');
              </script>";
    }
    
    $update_stmt->close();
    $conn->close();
}

// Parse the stored images if needed
$images = !empty($booking['birthday_image']) ? json_decode($booking['birthday_image'], true) : [];
$birthday_image = isset($images[0]) ? $images[0] : '';
$birthday_invite = isset($images[1]) ? $images[1] : '';

// Parse additional details if exists
$additional_details = !empty($booking['additional_details']) ? json_decode($booking['additional_details'], true) : [];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding-bottom: 40px;
            color: #333;
        }
        
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        h1 {
            text-align: center;
            color: #2c3e50;
            margin: 40px 0;
            font-weight: 700;
            font-size: 2.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1:after {
            content: '';
            position: absolute;
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, #6a11cb 0%, #2575fc 100%);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .edit-notice {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            color: #856404;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            animation: fadeIn 0.8s ease;
        }
        
        .edit-notice i {
            margin-right: 15px;
            font-size: 24px;
            color: #ffc107;
        }
        
        .time-remaining {
            font-weight: 600;
            color: #e74c3c;
            margin-top: 5px;
            font-size: 1.05rem;
        }
        
        form {
            max-width: 850px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.8s ease;
        }
        
        form:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: #3498db;
            font-size: 1.4rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
            font-size: 0.95rem;
        }
        
        label i {
            margin-right: 8px;
            color: #3498db;
        }
        
        input[type="text"], 
        input[type="email"], 
        input[type="date"], 
        input[type="time"], 
        select, 
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
        }
        
        input:focus, 
        select:focus, 
        textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        .input-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .input-group input {
            flex: 1;
        }
        
        button {
            background: linear-gradient(to right, #4CAF50, #45a049);
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        
        button:hover {
            background: linear-gradient(to right, #45a049, #3d8b3d);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        button i {
            margin-right: 8px;
        }
        
        #toggle_input {
            padding: 12px 20px;
            font-size: 0.9rem;
            background: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        #toggle_input:hover {
            background: linear-gradient(to right, #2980b9, #2573a7);
        }
        
        #service_list {
            margin: 15px 0;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
            max-height: 200px;
            overflow-y: auto;
        }
        
        #service_list:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        #selected_services_list {
            list-style-type: none;
            padding: 0;
            margin: 15px 0;
        }
        
        #selected_services_list li {
            padding: 12px 15px;
            margin-bottom: 10px;
            background-color: #f0f7ff;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease;
        }
        
        #selected_services_list li:hover {
            transform: translateX(5px);
            background-color: #e6f2ff;
        }
        
        .remove-service {
            background: linear-gradient(to right, #e74c3c, #c0392b);
            padding: 8px 12px;
            font-size: 0.8rem;
            margin-left: 10px;
            border-radius: 6px;
        }
        
        .remove-service:hover {
            background: linear-gradient(to right, #c0392b, #a93226);
        }
        
        .amount-box {
            background-color: #f0f7ff;
            border-radius: 8px;
            padding: 15px 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .amount-box:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .total-amount {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 1.2rem;
            color: #2c3e50;
        }
        
        .date-time-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .back-btn {
            background: linear-gradient(to right, #6c757d, #5a6268);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .back-btn:hover {
            background: linear-gradient(to right, #5a6268, #4e555b);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.8s ease;
        }
        
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .date-time-group {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .input-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            form {
                padding: 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .form-footer {
                flex-direction: column;
                gap: 15px;
            }
            
            .back-btn, button[type="submit"] {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    <script>
        function fetchServices(categoryId, selectedServices = '') {
            if (categoryId === "") {
                document.getElementById("service_list").innerHTML = "";
                document.getElementById("additional_fields").innerHTML = "";
                return;
            }
            
            fetch('get_services.php?category_id=' + categoryId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("service_list").innerHTML = data;
                    document.getElementById("service_list").classList.add("animate-fadeIn");
                    
                    // If there are selected services, pre-select them
                    if (selectedServices) {
                        const services = selectedServices.split('|')[0].split(',').map(s => s.trim());
                        preSelectServices(services);
                    }
                });
            
            fetch('get_additional_fields.php?category_id=' + categoryId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("additional_fields").innerHTML = data;
                    document.getElementById("additional_fields").classList.add("animate-fadeIn");
                    
                    // Pre-fill additional fields
                    fillAdditionalFields();
                });
        }
        
        // Function to pre-select services based on your UI
        function preSelectServices(serviceNames) {
            // This needs to be customized based on your actual service list HTML structure
            // This is just an example that assumes checkboxes with data-name attribute
            const checkboxes = document.querySelectorAll('#service_list input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                const serviceName = checkbox.getAttribute('data-name');
                if (serviceNames.includes(serviceName)) {
                    checkbox.checked = true;
                    // If you're using the addService function from your original code
                    const serviceId = checkbox.value;
                    const amount = checkbox.getAttribute('data-amount');
                    addService(serviceId, serviceName, amount);
                }
            });
        }
        
        // Function to fill additional fields
        function fillAdditionalFields() {
            <?php if (!empty($additional_details)): ?>
            const additionalDetails = <?php echo json_encode($additional_details); ?>;
            for (const [key, value] of Object.entries(additionalDetails)) {
                const field = document.querySelector(`[name="additional_details[${key}]"]`);
                if (field) {
                    field.value = value;
                }
            }
            <?php endif; ?>
        }

        // Initialize guest input toggle
        function initGuestInputToggle() {
            const hasEmail = Boolean("<?php echo $booking['guest_email']; ?>");
            const hasAddress = Boolean("<?php echo $booking['guest_address']; ?>");
            
            let isEmail = hasEmail || (!hasEmail && !hasAddress);
            const inputField = document.getElementById("guest_input");
            const toggleButton = document.getElementById("toggle_input");
            
            if (isEmail) {
                inputField.setAttribute("type", "email");
                inputField.setAttribute("name", "guest_email");
                inputField.value = "<?php echo htmlspecialchars($booking['guest_email'] ?? ''); ?>";
                inputField.placeholder = "Enter Email";
                toggleButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Use Address';
            } else {
                inputField.setAttribute("type", "text");
                inputField.setAttribute("name", "guest_address");
                inputField.value = "<?php echo htmlspecialchars($booking['guest_address'] ?? ''); ?>";
                inputField.placeholder = "Enter Address";
                toggleButton.innerHTML = '<i class="fas fa-exchange-alt"></i> Use Email';
            }
            
            toggleButton.addEventListener("click", function() {
                if (isEmail) {
                    inputField.setAttribute("type", "text");
                    inputField.setAttribute("name", "guest_address");
                    inputField.value = "<?php echo htmlspecialchars($booking['guest_address'] ?? ''); ?>";
                    inputField.placeholder = "Enter Address";
                    this.innerHTML = '<i class="fas fa-exchange-alt"></i> Use Email';
                } else {
                    inputField.setAttribute("type", "email");
                    inputField.setAttribute("name", "guest_email");
                    inputField.value = "<?php echo htmlspecialchars($booking['guest_email'] ?? ''); ?>";
                    inputField.placeholder = "Enter Email";
                    this.innerHTML = '<i class="fas fa-exchange-alt"></i> Use Address';
                }
                
                // Add animation when switching
                inputField.classList.add("animate-fadeIn");
                setTimeout(() => {
                    inputField.classList.remove("animate-fadeIn");
                }, 500);
                
                isEmail = !isEmail;
            });
        }

        // Run when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Get the initial service category and selected services
            const categoryId = '<?php echo $booking['service_category_id']; ?>';
            const selectedServices = '<?php echo $booking['selected_services']; ?>';
            
            // Initialize services
            if (categoryId) {
                fetchServices(categoryId, selectedServices);
            }
            
            // Set the initial value for the service category dropdown
            document.getElementById('service_category').value = categoryId;
            
            // Initialize the guest input toggle
            initGuestInputToggle();
            
            // Add staggered animations to form groups
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                setTimeout(() => {
                    group.classList.add('animate-fadeIn');
                }, 100 * index);
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="edit-notice">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <strong>Edit Window:</strong> Bookings can only be edited within 2 days of creation. 
                <div class="time-remaining">You have <?php echo (2 - $booking['days_since_creation']); ?> day(s) left to edit this booking.</div>
            </div>
        </div>

        <h1 class="animate-fadeIn">Edit Your Booking</h1>
        
        <form action="edit_bookings.php?id=<?php echo $booking_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user"></i> Personal Information
                </div>
                
                <div class="form-group">
                    <label for="event_head"><i class="fas fa-user-circle"></i> Name:</label>
                    <input type="text" name="event_head" id="event_head" value="<?php echo htmlspecialchars($booking['event_head']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_no"><i class="fas fa-phone"></i> Phone Number:</label>
                    <input type="text" name="phone_no" id="phone_no" value="<?php echo htmlspecialchars($booking['phone_no']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="guest_input"><i class="fas fa-address-book"></i> Guest Contact:</label>
                    <div class="input-group">
                        <input type="email" id="guest_input" name="guest_email" placeholder="Enter Email" required>
                        <button type="button" id="toggle_input"><i class="fas fa-exchange-alt"></i> Use Address</button>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-calendar-alt"></i> Event Schedule
                </div>
                
                <div class="date-time-group">
                    <div class="form-group">
                        <label for="event_start_date"><i class="fas fa-calendar"></i> Start Date:</label>
                        <input type="date" name="event_start_date" id="event_start_date" value="<?php echo htmlspecialchars($booking['event_start_date']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_end_date"><i class="fas fa-calendar-check"></i> End Date:</label>
                        <input type="date" name="event_end_date" id="event_end_date" value="<?php echo htmlspecialchars($booking['event_end_date']); ?>" required>
                    </div>
                </div>
                
                <div class="date-time-group">
                    <div class="form-group">
                        <label for="event_start_time"><i class="fas fa-clock"></i> Start Time:</label>
                        <input type="time" name="event_start_time" id="event_start_time" value="<?php echo htmlspecialchars($booking['event_start_time']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_end_time"><i class="fas fa-hourglass-end"></i> End Time:</label>
                        <input type="time" name="event_end_time" id="event_end_time" value="<?php echo htmlspecialchars($booking['event_end_time']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="venue"><i class="fas fa-map-marker-alt"></i> Venue:</label>
                    <input type="text" name="venue" id="venue" value="<?php echo htmlspecialchars($booking['venue']); ?>" required>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-concierge-bell"></i> Services
                </div>
                
                <div class="form-group">
                    <label for="service_category"><i class="fas fa-tags"></i> Service Category:</label>
                    <select name="service_category" id="service_category" onchange="fetchServices(this.value)" required>
                        <option value="">Select Category</option>
                        <?php
                        $cat_result = $conn->query("SELECT id, category_name FROM service_categories");
                        while ($cat_row = $cat_result->fetch_assoc()) { 
                            $selected = ($cat_row['id'] == $booking['service_category_id']) ? 'selected' : '';
                            echo "<option value='" . $cat_row['id'] . "' $selected>" . $cat_row['category_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-clipboard-list"></i> Available Services:</label>
                    <div id="service_list"></div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Selected Services:</label>
                    <input type="hidden" id="selected_services" name="selected_services" value="<?php echo htmlspecialchars($booking['selected_services']); ?>">
                    <ul id="selected_services_list"></ul>
                </div>
                
                <div class="amount-box">
                    <div class="total-amount">
                        <span>Total Amount:</span>
                        <span>$<span id="total_amount"><?php echo htmlspecialchars($booking['total_amount']); ?></span></span>
                    </div>
                    <input type="hidden" id="total_amount_input" name="total_amount" value="<?php echo htmlspecialchars($booking['total_amount']); ?>">
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-info-circle"></i> Additional Information
                </div>
                
                <div id="additional_fields"></div>
            </div>
            
            <div class="form-footer">
                <a href="view_booking.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
                <button type="submit"><i class="fas fa-save"></i> Update Booking</button>
            </div>
        </form>
    </div>

    <script>
        let totalAmount = parseFloat('<?php echo $booking['total_amount']; ?>') || 0;
        let selectedServices = new Map(); // Store services uniquely
        
        function addService(serviceId, serviceName, amount) {
            amount = parseFloat(amount);
            
            if (selectedServices.has(serviceId)) {
                return; // Prevent duplicate entries
            }
            
            selectedServices.set(serviceId, { name: serviceName, amount: amount });
            updateServiceList();
        }
        
        function removeService(serviceId) {
            if (selectedServices.has(serviceId)) {
                // Add animation before removing
                const items = document.getElementById("selected_services_list").getElementsByTagName("li");
                for (let i = 0; i < items.length; i++) {
                    if (items[i].dataset.id == serviceId) {
                        items[i].style.opacity = "0";
                        items[i].style.transform = "translateX(20px)";
                        setTimeout(() => {
                            selectedServices.delete(serviceId);
                            updateServiceList();
                        }, 300);
                        return;
                    }
                }
                selectedServices.delete(serviceId);
                updateServiceList();
            }
        }
        
        function updateServiceList() {
            const serviceList = document.getElementById("selected_services_list");
            const serviceInput = document.getElementById("selected_services");
            const totalAmountInput = document.getElementById("total_amount_input");
            serviceList.innerHTML = "";
            
            totalAmount = 0; // Reset before recalculating
            let serviceData = [];
            
            selectedServices.forEach((service, id) => {
                totalAmount += service.amount;
                serviceData.push({id: id, name: service.name, amount: service.amount});
                
                let listItem = document.createElement("li");
                listItem.dataset.id = id;
                listItem.innerHTML = `
                    <span>${service.name} - $${service.amount.toFixed(2)}</span>
                    <button type="button" class="remove-service" onclick="removeService(${id})">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                `;
                serviceList.appendChild(listItem);
            });
            
            // Update the input field with JSON data
            serviceInput.value = JSON.stringify(serviceData);
            
            // Update total amount display with animation
            const totalDisplay = document.getElementById("total_amount");
            totalDisplay.innerText = totalAmount.toFixed(2);
            
            // Add pulse animation to total amount
            const totalContainer = document.querySelector('.amount-box');
            totalContainer.classList.add('animate-pulse');
            setTimeout(() => {
                totalContainer.classList.remove('animate-pulse');
            }, 1000);
            
            // Store total amount in hidden input field
            totalAmountInput.value = totalAmount.toFixed(2);
        }
        
        // Date validation
        document.getElementById("event_end_date").addEventListener("change", function() {
            const startDate = document.getElementById("event_start_date").value;
            const endDate = this.value;
            
            if(startDate && endDate && new Date(endDate) < new Date(startDate)) {
                alert("End date cannot be earlier than start date");
                this.value = startDate; // Reset to start date
            }
        });
    </script>
</body>
</html>