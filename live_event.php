<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Check if user has organizer role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'event organizer') {
    header("Location: index.php?error=unauthorized");
    exit;
}

// Include database connection
include 'db.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_description = mysqli_real_escape_string($conn, $_POST['event_description']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $event_time = mysqli_real_escape_string($conn, $_POST['event_time']);
    $event_duration = mysqli_real_escape_string($conn, $_POST['event_duration']);
    $event_location = mysqli_real_escape_string($conn, $_POST['event_location']);
    $max_attendees = mysqli_real_escape_string($conn, $_POST['max_attendees']);
    $event_category = mysqli_real_escape_string($conn, $_POST['event_category']);
    $ticket_price = mysqli_real_escape_string($conn, $_POST['ticket_price']);
    $organizer_id = $_SESSION['user_id'];
    
    // Handle image upload if provided
    $event_image = '';
    if(isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['event_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if(in_array(strtolower($filetype), $allowed)) {
            // Create unique filename
            $new_filename = uniqid() . '.' . $filetype;
            $upload_dir = 'uploads/events/';
            
            // Create directory if it doesn't exist
            if(!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Move the file
            if(move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_dir . $new_filename)) {
                $event_image = $upload_dir . $new_filename;
            } else {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed.";
        }
    }
    
    // Insert event into database
    $sql = "INSERT INTO live_events (event_name, event_description, event_date, event_time, 
            event_duration, event_location, max_attendees, event_category, ticket_price, 
            event_image, organizer_id, created_at) 
            VALUES ('$event_name', '$event_description', '$event_date', '$event_time', 
            '$event_duration', '$event_location', '$max_attendees', '$event_category', 
            '$ticket_price', '$event_image', '$organizer_id', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Live event created successfully!";
        // Clear form data after successful submission
        $_POST = array();
    } else {
        $error_message = "Error: " . mysqli_error($conn);
    }
}

// Get event categories
$categories_sql = "SELECT * FROM event_categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Live Event</title>
    <style>
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #6a11cb;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        input[type="text"], 
        input[type="date"], 
        input[type="time"], 
        input[type="number"],
        input[type="file"],
        textarea, 
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        textarea {
            height: 120px;
            resize: vertical;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .help-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 5px;
            display: none;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1>Create Live Event</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="event_name">Event Name</label>
                <input type="text" id="event_name" name="event_name" value="<?php echo isset($_POST['event_name']) ? htmlspecialchars($_POST['event_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="event_description">Event Description</label>
                <textarea id="event_description" name="event_description" required><?php echo isset($_POST['event_description']) ? htmlspecialchars($_POST['event_description']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date" value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="event_time">Event Time</label>
                    <input type="time" id="event_time" name="event_time" value="<?php echo isset($_POST['event_time']) ? htmlspecialchars($_POST['event_time']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="event_duration">Duration (minutes)</label>
                    <input type="number" id="event_duration" name="event_duration" min="15" value="<?php echo isset($_POST['event_duration']) ? htmlspecialchars($_POST['event_duration']) : '60'; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="max_attendees">Maximum Attendees</label>
                    <input type="number" id="max_attendees" name="max_attendees" min="1" value="<?php echo isset($_POST['max_attendees']) ? htmlspecialchars($_POST['max_attendees']) : '50'; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="event_category">Event Category</label>
                    <select id="event_category" name="event_category" required>
                        <option value="">Select a category</option>
                        <?php if(mysqli_num_rows($categories_result) > 0): ?>
                            <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['event_category']) && $_POST['event_category'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <option value="1">General</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="ticket_price">Ticket Price (₹)</label>
                    <input type="number" id="ticket_price" name="ticket_price" min="0" step="0.01" value="<?php echo isset($_POST['ticket_price']) ? htmlspecialchars($_POST['ticket_price']) : '0'; ?>" required>
                    <div class="help-text">Enter 0 for free events</div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="event_location">Event Location</label>
                <input type="text" id="event_location" name="event_location" value="<?php echo isset($_POST['event_location']) ? htmlspecialchars($_POST['event_location']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="event_image">Event Image (Optional)</label>
                <input type="file" id="event_image" name="event_image" accept="image/*" onchange="previewImage(this)">
                <div class="help-text">Recommended size: 1200x630 pixels. Max file size: 2MB</div>
                <img id="imagePreview" class="preview-image" src="#" alt="Image Preview">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-submit">Create Live Event</button>
            </div>
        </form>
    </div>
    
    <script>
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('event_date').setAttribute('min', today);
        
        // Image preview functionality
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>