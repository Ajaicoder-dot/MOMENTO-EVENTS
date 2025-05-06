<?php
session_start();
include 'db.php'; // Database connection
include 'navbar3.php';

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
        $mail->Username   =  'ajaiofficial06@gmail.com'; 
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
    $service_category = $_POST['service_category'];
    $user_id = $_SESSION['user_id'];
    $selected_services = $_POST['selected_services'] ?? null;
    $total_amount = $_POST['total_amount'] ?? 0;
    $additional_details = isset($_POST['additional_details']) ? json_encode($_POST['additional_details']) : null;

    // Upload Images
    $target_dir = "image/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    function uploadImage($imageFieldName, $target_dir) {
        if (!empty($_FILES[$imageFieldName]["name"])) {
            $file_name = basename($_FILES[$imageFieldName]["name"]);
            $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $target_file = $target_dir . uniqid() . '.' . $imageFileType;
            $allowed_types = ["jpg", "jpeg", "png", "gif"];

            if (!in_array($imageFileType, $allowed_types)) {
                return null;
            }
            if (move_uploaded_file($_FILES[$imageFieldName]["tmp_name"], $target_file)) {
                return $target_file;
            }
        }
        return null;
    }

    $birthday_image = uploadImage("birthday_image", $target_dir);
    $birthday_invite = uploadImage("birthday_invite", $target_dir);
    $image_paths = json_encode(array_filter([$birthday_image, $birthday_invite]));

    // Insert into database
    $sql = "INSERT INTO book (event_head, phone_no, guest_email, guest_address, event_start_date, event_end_date, event_start_time, event_end_time, venue, service_category_id, selected_services, total_amount, user_id, additional_details, birthday_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error in SQL statement: " . $conn->error);
    }

    $stmt->bind_param("sssssssssssssss", 
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
    $user_id, 
    $additional_details, 
    $image_paths
    );

    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;

        // Get admin email from users table where role is admin
        $admin_query = "SELECT email FROM users WHERE role = 'admin'";
        $admin_stmt = $conn->prepare($admin_query);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin_email = null;
        
        if ($admin_row = $admin_result->fetch_assoc()) {
            $admin_email = $admin_row['email'];
            error_log("Admin email found: " . $admin_email); // Debugging
        } else {
            error_log("Admin email not found."); // Debugging
        }
        
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
        
        // Prepare selected services for display
        $services_list = "";
        if ($selected_services) {
            $services_array = json_decode($selected_services, true);
            if (is_array($services_array)) {
                foreach ($services_array as $service) {
                    $services_list .= "- " . $service['name'] . " ($" . number_format($service['amount'], 2) . ")<br>";
                }
            }
        }
        
        // Create email content
        $booking_details = "
        <h2>Event Booking Confirmation</h2>
        <p>Booking ID: #{$booking_id}</p>
        <h3>Event Details:</h3>
        <p><strong>Event Head:</strong> {$event_head}</p>
        <p><strong>Phone:</strong> {$phone_no}</p>
        <p><strong>Start Date:</strong> {$event_start_date}</p>
        <p><strong>End Date:</strong> {$event_end_date}</p>
        <p><strong>Start Time:</strong> {$event_start_time}</p>
        <p><strong>End Time:</strong> {$event_end_time}</p>
        <p><strong>Venue:</strong> {$venue}</p>
        <p><strong>Category:</strong> {$category_name}</p>
        <h3>Selected Services:</h3>
        {$services_list}
        <p><strong>Total Amount:</strong> $" . number_format($total_amount, 2) . "</p>
        ";
        
        // Send email to guest if email is provided
        if (!empty($guest_email)) {
            $guest_subject = "Your Event Booking Confirmation";
            $guest_message = $booking_details . "<p>Thank you for booking your event with us!</p>";
            if (sendNotificationEmail($guest_email, $guest_subject, $guest_message)) {
                error_log("Email sent to guest: " . $guest_email); // Debugging
            } else {
                error_log("Failed to send email to guest: " . $guest_email); // Debugging
            }
        }
        
        // Send email to admin
        if (!empty($admin_email)) {
            $admin_subject = "New Event Booking Notification";
            $admin_message = $booking_details . "<p>A new event has been booked in the system.</p>";
            if (sendNotificationEmail($admin_email, $admin_subject, $admin_message)) {
                error_log("Email sent to admin: " . $admin_email); // Debugging
            } else {
                error_log("Failed to send email to admin: " . $admin_email); // Debugging
            }
        }
        
        $admin_stmt->close();
        if (isset($category_stmt)) $category_stmt->close();
        
        echo "<script>
                alert('Booking successfully added!');
                window.location.href = 'bookings.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Error: " . $stmt->error . "');
              </script>";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Booking</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link rel="stylesheet" href="">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            padding-bottom: 20px;
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"], 
        input[type="email"],
        input[type="date"],
        input[type="time"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        #guest_input {
            transition: all 0.3s ease-in-out;
            padding: 10px;
            font-size: 16px;
            width: 100%;
            max-width: 500px;
        }
        
        #toggle_input {
            padding: 10px 15px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }
        
        #toggle_input:hover {
            background-color: #0056b3;
        }
        
        #service_list {
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        
        #selected_services_list {
            list-style-type: none;
            padding: 0;
        }
        
        #selected_services_list li {
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f0f0f0;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        #selected_services_list button {
            background-color: #ff4d4d;
            padding: 5px 10px;
        }
        
        #selected_services_list button:hover {
            background-color: #e60000;
        }
        
        .file-upload {
            margin-bottom: 15px;
        }
        
        .file-label {
            display: block;
            margin-bottom: 8px;
        }
    </style>
    <script>
        function fetchServices(categoryId) {
            if (categoryId === "") {
                document.getElementById("service_list").innerHTML = "";
                document.getElementById("additional_fields").innerHTML = "";
                return;
            }
            
            fetch('get_services.php?category_id=' + categoryId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("service_list").innerHTML = data;
                });
            
            fetch('get_additional_fields.php?category_id=' + categoryId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById("additional_fields").innerHTML = data;
                });
        }
    </script>
</head>
<body>

    <h1>BOOK YOUR EVENTS HERE</h1>
    <form action="bookings.php" method="post" enctype="multipart/form-data">
        <label for="event_head">Name:</label>
        <input type="text" name="event_head" required>
        
        <label for="phone_no">Phone No:</label>
        <input type="text" name="phone_no" required>
        
        <label for="guest_input">Guest Email or Address:</label>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
            <input type="email" id="guest_input" name="guest_email" placeholder="Enter Email" required>
            <button type="button" id="toggle_input">Use Address</button>
        </div>

        <label for="service_category">Select Service Category:</label>
        <select name="service_category" id="service_category" onchange="fetchServices(this.value)" required>
            <option value="">Select Category</option>
            <?php
            $result = $conn->query("SELECT id, category_name FROM service_categories");
            while ($row = $result->fetch_assoc()) { 
                echo "<option value='" . $row['id'] . "'>" . $row['category_name'] . "</option>";
            }
            ?>
        </select>
        
        <label>Services Provided:</label>
        <div id="service_list"></div> <!-- Services from get_services.php will be loaded here -->

        <input type="hidden" id="selected_services" name="selected_services">
        <label>Selected Services:</label>
        <ul id="selected_services_list"></ul>

        <p>Total Amount: <span id="total_amount">0</span></p>
        <input type="hidden" id="total_amount_input" name="total_amount" value="0">

        <label for="event_start_date">Event Start Date:</label>
        <input type="date" name="event_start_date" id="event_start_date" required>
        
        <label for="event_end_date">Event End Date:</label>
        <input type="date" name="event_end_date" id="event_end_date" required>
        
        <label for="event_start_time">Event Start Time:</label>
        <input type="time" name="event_start_time" required>
        
        <label for="event_end_time">Event End Time:</label>
        <input type="time" name="event_end_time" required>
        
        <label for="venue">Venue:</label>
        <input type="text" name="venue" required>
        
        <div class="file-upload">
            <label class="file-label">Event Image (Optional):</label>
            <input type="file" name="birthday_image" accept="image/*">
        </div>
        
        <div class="file-upload">
            <label class="file-label">Invitation Image (Optional):</label>
            <input type="file" name="birthday_invite" accept="image/*">
        </div>
        
        <label>Additional Details:</label>
        <div id="additional_fields"></div>
        
        <button type="submit">Submit Booking</button>
    </form>

<script>
    // Toggle between email and address input
    let isEmail = true; // Track input type

    document.getElementById("toggle_input").addEventListener("click", function () {
        const inputField = document.getElementById("guest_input");

        if (isEmail) {
            inputField.setAttribute("type", "text");
            inputField.setAttribute("name", "guest_address");
            inputField.placeholder = "Enter Address";
            this.textContent = "Use Email";
        } else {
            inputField.setAttribute("type", "email");
            inputField.setAttribute("name", "guest_email");
            inputField.placeholder = "Enter Email";
            this.textContent = "Use Address";
        }

        isEmail = !isEmail; // Toggle state
    });

    // Service selection and total calculation
    let totalAmount = 0;
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
            listItem.innerHTML = `${service.name} - ${service.amount.toFixed(2)} 
                <button type="button" onclick="removeService(${id})">Remove</button>`;
            serviceList.appendChild(listItem);
        });

        // Update the hidden input with JSON data of selected services
        serviceInput.value = JSON.stringify(serviceData);

        // Update total amount display
        document.getElementById("total_amount").innerText = totalAmount.toFixed(2);

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

    // Show placeholder content for get_services.php
    document.addEventListener("DOMContentLoaded", function() {
        // This is just a placeholder. In reality, this content would come from get_services.php
        document.getElementById("service_list").innerHTML = 
            '<p>Please select a service category to see available services.</p>';
    });
</script>
</body>
<?php include 'footer.php'; ?>
    
</html>