<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
include 'db.php';

// Check if booking_id and event_id are provided
if (!isset($_GET['booking_id']) || !isset($_GET['event_id'])) {
    die("Invalid request. Missing booking information.");
}

$booking_id = mysqli_real_escape_string($conn, $_GET['booking_id']);
$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);
$user_id = $_SESSION['user_id'];

// Verify this booking belongs to the logged-in user
$verify_sql = "SELECT * FROM event_bookings WHERE id = '$booking_id' AND event_id = '$event_id' AND user_id = '$user_id'";
$verify_result = mysqli_query($conn, $verify_sql);

if (mysqli_num_rows($verify_result) == 0) {
    die("Access denied. This booking does not belong to you.");
}

$booking_data = mysqli_fetch_assoc($verify_result);

// Get event details
$event_sql = "SELECT e.*, u.username as organizer_name 
              FROM live_events e 
              JOIN users u ON e.organizer_id = u.id 
              WHERE e.id = '$event_id'";
$event_result = mysqli_query($conn, $event_sql);

if (mysqli_num_rows($event_result) == 0) {
    die("Event not found.");
}

$event_data = mysqli_fetch_assoc($event_result);

// Get user details
$user_sql = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_sql);
$user_data = mysqli_fetch_assoc($user_result);

// Generate a unique ticket number
$ticket_number = strtoupper(substr(md5($booking_id . $event_id . $user_id . time()), 0, 8));

// Set content type to PDF
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="event_ticket_' . $booking_id . '.html"');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticket - <?php echo htmlspecialchars($event_data['event_name']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .ticket-container {
            width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .ticket-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .ticket-title {
            font-size: 28px;
            margin: 0;
            font-weight: 700;
        }
        .ticket-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .ticket-body {
            padding: 30px;
            display: flex;
        }
        .ticket-info {
            flex: 2;
        }
        .ticket-qr {
            flex: 1;
            text-align: center;
            padding-left: 20px;
            border-left: 1px dashed #ddd;
        }
        .info-group {
            margin-bottom: 20px;
        }
        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        .ticket-number {
            font-family: monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 15px;
            color: #6a11cb;
        }
        .ticket-footer {
            background-color: #f9f9f9;
            padding: 15px 30px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        .barcode {
            margin-top: 20px;
            font-family: 'Libre Barcode 39', cursive;
            font-size: 60px;
        }
        @media print {
            body {
                background-color: white;
            }
            .ticket-container {
                box-shadow: none;
                margin: 0;
                width: 100%;
            }
            .print-button {
                display: none;
            }
        }
        .print-button {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin: 20px auto;
            display: block;
            transition: all 0.3s ease;
        }
        .print-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="ticket-header">
            <h1 class="ticket-title">EVENT TICKET</h1>
            <div class="ticket-subtitle">This is your official event ticket - please bring it with you</div>
        </div>
        
        <div class="ticket-body">
            <div class="ticket-info">
                <div class="info-group">
                    <div class="info-label">Event</div>
                    <div class="info-value"><?php echo htmlspecialchars($event_data['event_name']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Date & Time</div>
                    <div class="info-value">
                        <?php echo date('F j, Y', strtotime($event_data['event_date'])); ?> at 
                        <?php echo date('g:i A', strtotime($event_data['event_time'])); ?>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Location</div>
                    <div class="info-value"><?php echo htmlspecialchars($event_data['event_location']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Attendee</div>
                    <div class="info-value"><?php echo htmlspecialchars($user_data['username']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Number of Seats</div>
                    <div class="info-value"><?php echo $booking_data['num_seats']; ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Booking Date</div>
                    <div class="info-value"><?php echo date('F j, Y', strtotime($booking_data['booking_date'])); ?></div>
                </div>
            </div>
            
            <div class="ticket-qr">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode("TICKET:$ticket_number|EVENT:$event_id|USER:$user_id|SEATS:" . $booking_data['num_seats']); ?>" alt="QR Code">
                <div class="ticket-number">#<?php echo $ticket_number; ?></div>
                <div class="barcode">*<?php echo $ticket_number; ?>*</div>
            </div>
        </div>
        
        <div class="ticket-footer">
            <p>Organized by: <?php echo htmlspecialchars($event_data['organizer_name']); ?></p>
            <p>This ticket was generated on <?php echo date('F j, Y \a\t g:i A'); ?></p>
        </div>
    </div>
    
    <button class="print-button" onclick="window.print()">Print Ticket</button>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Uncomment the line below to automatically open print dialog
            // window.print();
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>