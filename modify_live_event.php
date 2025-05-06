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

// Initialize variables
$event_id = null;
$event = null;
$success_message = null;
$error_message = null;

// Get organizer's events
$organizer_id = $_SESSION['user_id'];
$events_query = "SELECT id, event_name, event_date FROM live_events 
                WHERE organizer_id = ? 
                ORDER BY event_date DESC";
$stmt = mysqli_prepare($conn, $events_query);
mysqli_stmt_bind_param($stmt, "i", $organizer_id);
mysqli_stmt_execute($stmt);
$events_result = mysqli_stmt_get_result($stmt);

// Handle event selection
if (isset($_GET['event_id'])) {
    $event_id = mysqli_real_escape_string($conn, $_GET['event_id']);
    
    // Get event details
    $query = "SELECT * FROM live_events WHERE id = ? AND organizer_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $event_id, $organizer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $event = mysqli_fetch_assoc($result);
    } else {
        $error_message = "Event not found or you don't have permission to edit it.";
    }
}

// Process form submission for updating event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_event'])) {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_description = mysqli_real_escape_string($conn, $_POST['event_description']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $event_time = mysqli_real_escape_string($conn, $_POST['event_time']);
    $event_duration = mysqli_real_escape_string($conn, $_POST['event_duration']);
    $event_location = mysqli_real_escape_string($conn, $_POST['event_location']);
    $max_attendees = mysqli_real_escape_string($conn, $_POST['max_attendees']);
    
    // Check if the event belongs to the current organizer
    $check_query = "SELECT id FROM live_events WHERE id = ? AND organizer_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $event_id, $organizer_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update event in database
        $update_sql = "UPDATE live_events SET 
                      event_name = ?, 
                      event_description = ?, 
                      event_date = ?, 
                      event_time = ?, 
                      event_duration = ?, 
                      event_location = ?, 
                      max_attendees = ?, 
                      updated_at = NOW() 
                      WHERE id = ? AND organizer_id = ?";
        
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssssiiiis", 
                              $event_name, 
                              $event_description, 
                              $event_date, 
                              $event_time, 
                              $event_duration, 
                              $event_location, 
                              $max_attendees, 
                              $event_id, 
                              $organizer_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $success_message = "Event updated successfully!";
            
            // Refresh event data
            $query = "SELECT * FROM live_events WHERE id = ? AND organizer_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ii", $event_id, $organizer_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $event = mysqli_fetch_assoc($result);
        } else {
            $error_message = "Error updating event: " . mysqli_error($conn);
        }
    } else {
        $error_message = "You don't have permission to edit this event.";
    }
}

// Process form submission for deleting event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = mysqli_real_escape_string($conn, $_POST['event_id']);
    
    // Check if the event belongs to the current organizer
    $check_query = "SELECT id FROM live_events WHERE id = ? AND organizer_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ii", $event_id, $organizer_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // First delete all bookings for this event
        $delete_bookings_sql = "DELETE FROM event_bookings WHERE event_id = ?";
        $delete_bookings_stmt = mysqli_prepare($conn, $delete_bookings_sql);
        mysqli_stmt_bind_param($delete_bookings_stmt, "i", $event_id);
        mysqli_stmt_execute($delete_bookings_stmt);
        
        // Then delete the event
        $delete_sql = "DELETE FROM live_events WHERE id = ? AND organizer_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "ii", $event_id, $organizer_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            $success_message = "Event deleted successfully!";
            $event = null; // Clear event data
            $event_id = null;
            
            // Refresh the events list
            $events_query = "SELECT id, event_name, event_date FROM live_events 
                            WHERE organizer_id = ? 
                            ORDER BY event_date DESC";
            $stmt = mysqli_prepare($conn, $events_query);
            mysqli_stmt_bind_param($stmt, "i", $organizer_id);
            mysqli_stmt_execute($stmt);
            $events_result = mysqli_stmt_get_result($stmt);
        } else {
            $error_message = "Error deleting event: " . mysqli_error($conn);
        }
    } else {
        $error_message = "You don't have permission to delete this event.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Live Event</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            display: flex;
            gap: 30px;
        }
        
        .events-sidebar {
            width: 300px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            height: fit-content;
        }
        
        .events-sidebar h2 {
            color: #6a11cb;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .event-list {
            list-style: none;
            padding: 0;
        }
        
        .event-list li {
            margin-bottom: 10px;
        }
        
        .event-list a {
            display: block;
            padding: 12px 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .event-list a:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }
        
        .event-list a.active {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }
        
        .event-date {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .event-form {
            flex: 1;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .no-event-selected {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .no-event-selected i {
            font-size: 48px;
            color: #6a11cb;
            margin-bottom: 20px;
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
            margin-bottom: 10px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #cb1111 0%, #fc2525 100%);
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
        
        .btn-delete:hover {
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
        
        .button-group {
            display: flex;
            gap: 10px;
        }
        
        .no-events {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
            }
            
            .events-sidebar {
                width: 100%;
            }
            
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
        <div class="events-sidebar">
            <h2>Your Live Events</h2>
            
            <?php if (mysqli_num_rows($events_result) > 0): ?>
                <ul class="event-list">
                    <?php while ($row = mysqli_fetch_assoc($events_result)): ?>
                        <li>
                            <a href="?event_id=<?php echo $row['id']; ?>" class="<?php echo ($event_id == $row['id']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($row['event_name']); ?>
                                <div class="event-date">
                                    <?php echo date('F j, Y', strtotime($row['event_date'])); ?>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="no-events">
                    <p>You haven't created any events yet.</p>
                    <p><a href="live_event.php" style="color: #6a11cb;">Create your first event</a></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="event-form">
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
            
            <?php if ($event): ?>
                <h1>Modify Live Event</h1>
                
                <form method="POST" action="">
                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                    
                    <div class="form-group">
                        <label for="event_name">Event Name</label>
                        <input type="text" id="event_name" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_description">Event Description</label>
                        <textarea id="event_description" name="event_description" required><?php echo htmlspecialchars($event['event_description']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date">Event Date</label>
                            <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="event_time">Event Time</label>
                            <input type="time" id="event_time" name="event_time" value="<?php echo htmlspecialchars($event['event_time']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_duration">Duration (minutes)</label>
                            <input type="number" id="event_duration" name="event_duration" min="15" value="<?php echo htmlspecialchars($event['event_duration']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="max_attendees">Maximum Attendees</label>
                            <input type="number" id="max_attendees" name="max_attendees" min="1" value="<?php echo htmlspecialchars($event['max_attendees']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_location">Event Location</label>
                        <input type="text" id="event_location" name="event_location" value="<?php echo htmlspecialchars($event['event_location']); ?>" required>
                    </div>
                    
                    <div class="button-group">
                        <button type="submit" name="update_event" class="btn-submit">Update Event</button>
                        <button type="submit" name="delete_event" class="btn-delete" onclick="return confirm('Are you sure you want to delete this event? This will also remove all bookings.');">Delete Event</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="no-event-selected">
                    <i class="fas fa-calendar-alt"></i>
                    <h2>No Event Selected</h2>
                    <p>Please select an event from the sidebar to modify its details.</p>
                    <?php if (mysqli_num_rows($events_result) == 0): ?>
                        <p>You haven't created any events yet. <a href="live_event.php" style="color: #6a11cb;">Create your first event</a></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Set minimum date to today for new events
        const today = new Date().toISOString().split('T')[0];
        const dateInput = document.getElementById('event_date');
        if (dateInput) {
            // Only set min date if the event date is in the future
            const eventDate = dateInput.value;
            if (new Date(eventDate) > new Date()) {
                dateInput.setAttribute('min', today);
            }
        }
    </script>
</body>
</html>