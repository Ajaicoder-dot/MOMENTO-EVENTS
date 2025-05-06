CREATE DATABASE user_system;

USE user_system;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
ALTER TABLE users 
ADD COLUMN role ENUM('admin', 'event organizer', 'service provider') NOT NULL DEFAULT 'service provider';

Birthday Party

Corporate Events

Exhibitions

House Warming

Marriage / Wedding

Reception



{"birthday_person":"ajai","age_turning":"1","birthday_theme":"Superhero","num_invitees":"26","custom_service":"nil"}





<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_head = $_POST['event_head'];
    $phone_no = $_POST['phone_no'];
    $guest_email = $_POST['guest_email'];
    $event_start_date = $_POST['event_start_date'];
    $event_end_date = $_POST['event_end_date'];
    $event_start_time = $_POST['event_start_time'];
    $event_end_time = $_POST['event_end_time'];
    $venue = $_POST['venue'];
    $service_category_id = $_POST['service_category']; // Get category ID from the form
    $user_id = $_SESSION['user_id']; // Assuming user is logged in

    // Convert additional details to JSON format
    $additional_details = json_encode($_POST['additional_details'] ?? []);

    // SQL to insert booking details into the database
    $sql = "INSERT INTO bookings (event_head, phone_no, guest_email, event_start_date, event_end_date, event_start_time, event_end_time, venue, service_category_id, user_id, additional_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssis", $event_head, $phone_no, $guest_email, $event_start_date, $event_end_date, $event_start_time, $event_end_time, $venue, $service_category_id, $user_id, $additional_details);

    if ($stmt->execute()) {
        echo "Booking successful!";
    } else {
        echo "Error: " . $conn->error;
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
    <form action="bookings.php" method="post" enctype="multipart/form-data">
        <label for="event_head">Event Head:</label>
        <input type="text" name="event_head" required>
        <br>
        <label for="phone_no">Phone No:</label>
        <input type="text" name="phone_no" required>
        <br>
        <label for="guest_email">Your Email:</label>
        <input type="email" name="guest_email" required>
        <br>
        <label for="event_start_date">Event Start Date:</label>
        <input type="date" name="event_start_date" required>
        <br>
        <label for="event_end_date">Event End Date:</label>
        <input type="date" name="event_end_date" required>
        <br>
        <label for="event_start_time">Event Start Time:</label>
        <input type="time" name="event_start_time" required>
        <br>
        <label for="event_end_time">Event End Time:</label>
        <input type="time" name="event_end_time" required>
        <br>
        <label for="venue">Venue:</label>
        <input type="text" name="venue" required>
        <br>
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
        <br>
        <label>Services Provided:</label>
        <div id="service_list"></div>
        <br>
        <div id="additional_fields"></div>
        <br>
        <button type="submit">Submit Booking</button>
    </form>
</body>
</html>





<?php
include 'db.php'; // Database connection

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    
    $stmt = $conn->prepare("SELECT id, service_name FROM services WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['id'] . "'>" . $row['service_name'] . "</option>";
    }
    
    $stmt->close();
    $conn->close();
}
?>

















-------------------------------------
<?php
session_start();
include 'db.php'; // Database connection
include 'navbar3.php';

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

    $stmt->bind_param("ssssssssissdsss", 
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
        echo "Booking successfully added!";
        header("Location: bookings.php");
        exit();
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
    
    <link rel="stylesheet" href="style.css">
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
        <br>
        <label for="phone_no">Phone No:</label>
        <input type="text" name="phone_no" required>
        <br>
        <label for="guest_input">Guest Email or Address:</label>
<div style="display: flex; align-items: center; gap: 10px;">
    <input type="email" id="guest_input" name="guest_email" placeholder="Enter Email" required>
    <button type="button" id="toggle_input">Use Address</button>
</div>


   <style>
    #guest_input {
        transition: all 0.3s ease-in-out;
        padding: 10px;
        font-size: 16px;
        width: 1000px;
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
</style>


<script>
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
</script>

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
        <br>
        <label>Services Provided:</label>
        <input type="text" id="selected_services" name="selected_services" readonly>
<div id="service_list"></div> <!-- Services from get_services.php will be loaded here -->

<!-- Input field showing selected services and total amount -->


<!-- Display total amount separately -->


<ul id="selected_services_list"></ul>

<p>Total Amount: $<span id="total_amount">0</span></p>


<script>
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
        serviceList.innerHTML = "";

        totalAmount = 0; // Reset before recalculating
        let serviceNames = [];

        selectedServices.forEach((service, id) => {
            totalAmount += service.amount;
            serviceNames.push(service.name);

            let listItem = document.createElement("li");
            listItem.innerHTML = `${service.name} - $${service.amount} 
                <button type="button" onclick="removeService(${id})">-</button>`;
            serviceList.appendChild(listItem);
        });

        // Update the input field with services and total amount
        serviceInput.value = serviceNames.length > 0
            ? `${serviceNames.join(", ")} | Total: $${totalAmount.toFixed(2)}`
            : "";

        // Update total amount display
        document.getElementById("total_amount").innerText = totalAmount.toFixed(2);
    }
</script>
        <br>
        <label for="event_start_date">Event Start Date:</label>
        <input type="date" name="event_start_date" required>
        <br>
        <label for="event_end_date">Event End Date:</label>
        <input type="date" name="event_end_date" required>
        <br>
        <label for="event_start_time">Event Start Time:</label>
        <input type="time" name="event_start_time" required>
        <br>
        <label for="event_end_time">Event End Time:</label>
        <input type="time" name="event_end_time" required>
        <br>
 <label for="venue">Venue:</label>
        <input type="text" name="venue" required>
       
        <br>
        <label>Additional Details:</label>
        <div id="additional_fields"></div>
        <br>
        <button type="submit">Submit Booking</button>
    </form>
</body>
</html>

























<?php
session_start();
include 'db.php'; // Database connection
include 'navbar3.php';

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
    $venue,  // Now correctly mapped as string
    $service_category, 
    $selected_services, 
    $total_amount, 
    $user_id, 
    $additional_details, 
    $image_paths
);

    if ($stmt->execute()) {
        echo "Booking successfully added!";
        header("Location: bookings.php");
        exit();
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
    
    <link rel="stylesheet" href="style.css">
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
        <br>
        <label for="phone_no">Phone No:</label>
        <input type="text" name="phone_no" required>
        <br>
        <label for="guest_input">Guest Email or Address:</label>
<div style="display: flex; align-items: center; gap: 10px;">
    <input type="email" id="guest_input" name="guest_email" placeholder="Enter Email" required>
    <button type="button" id="toggle_input">Use Address</button>
</div>


   <style>
    #guest_input {
        transition: all 0.3s ease-in-out;
        padding: 10px;
        font-size: 16px;
        width: 1000px;
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
</style>


<script>
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
</script>

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
        <br>
        <label>Services Provided:</label>
        <input type="text" id="selected_services" name="selected_services" readonly>
<div id="service_list"></div> <!-- Services from get_services.php will be loaded here -->

<!-- Input field showing selected services and total amount -->


<!-- Display total amount separately -->


<ul id="selected_services_list"></ul>

<p>Total Amount: $<span id="total_amount">0</span></p>
<input type="hidden" id="total_amount_input" name="total_amount" value="0">



<script>
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
    const totalAmountInput = document.getElementById("total_amount_input"); // Get the hidden input field
    serviceList.innerHTML = "";

    totalAmount = 0; // Reset before recalculating
    let serviceNames = [];

    selectedServices.forEach((service, id) => {
        totalAmount += service.amount;
        serviceNames.push(service.name);

        let listItem = document.createElement("li");
        listItem.innerHTML = `${service.name} - $${service.amount} 
            <button type="button" onclick="removeService(${id})">-</button>`;
        serviceList.appendChild(listItem);
    });

    // Update the input field with services and total amount
    serviceInput.value = serviceNames.length > 0
        ? `${serviceNames.join(", ")} | Total: $${totalAmount.toFixed(2)}`
        : "";

    // Update total amount display
    document.getElementById("total_amount").innerText = totalAmount.toFixed(2);

    // Store total amount in hidden input field
    totalAmountInput.value = totalAmount.toFixed(2);
}

</script>
        <br>
        <label for="event_start_date">Event Start Date:</label>
        <input type="date" name="event_start_date" required>
        <br>
        <label for="event_end_date">Event End Date:</label>
        <input type="date" name="event_end_date" required>
        <br>
        <label for="event_start_time">Event Start Time:</label>
        <input type="time" name="event_start_time" required>
        <br>
        <label for="event_end_time">Event End Time:</label>
        <input type="time" name="event_end_time" required>
        <br>
 <label for="venue">Venue:</label>
        <input type="text" name="venue" required>
       
        <br>
        <label>Additional Details:</label>
        <div id="additional_fields"></div>
        <br>
        <button type="submit">Submit Booking</button>
    </form>
</body>
</html>