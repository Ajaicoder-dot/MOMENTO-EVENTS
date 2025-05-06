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

// Check if event_id is provided
if (!isset($_GET['event_id'])) {
    header("Location: view_events.php");
    exit;
}

$event_id = mysqli_real_escape_string($conn, $_GET['event_id']);
$user_id = $_SESSION['user_id'];

// Get event details
$event_sql = "SELECT e.*, u.username as organizer_name, 
              (SELECT COUNT(*) FROM event_bookings WHERE event_id = e.id) as booked_seats 
              FROM live_events e 
              JOIN users u ON e.organizer_id = u.id 
              WHERE e.id = '$event_id'";
$event_result = mysqli_query($conn, $event_sql);

if (mysqli_num_rows($event_result) == 0) {
    header("Location: view_events.php");
    exit;
}

$event = mysqli_fetch_assoc($event_result);
$available_seats = $event['max_attendees'] - $event['booked_seats'];

// Remove the booking check code that was here
// Process booking form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_booking'])) {
    // Start output buffering to catch any unexpected output
    ob_start();
    
    // Check if it's an AJAX request
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    $selected_seats = isset($_POST['selected_seats']) ? $_POST['selected_seats'] : [];
    $num_seats = count($selected_seats);
    $payment_method = isset($_POST['payment_method']) ? mysqli_real_escape_string($conn, $_POST['payment_method']) : 'credit_card';
    
    // Validate number of seats
    if ($num_seats < 1) {
        $booking_error = "Please select at least 1 seat.";
        
        // If it's an AJAX request, return JSON response
        if ($is_ajax) {
            // Clear any previous output
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $booking_error]);
            exit;
        }
    } else if ($num_seats > $available_seats) {
        $booking_error = "You cannot book more seats than available.";
        
        // If it's an AJAX request, return JSON response
        if ($is_ajax) {
            // Clear any previous output
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $booking_error]);
            exit;
        }
    } else {
        try {
            // Check if this exact booking already exists
            $check_duplicate_sql = "SELECT id FROM event_bookings WHERE event_id = '$event_id' AND user_id = '$user_id'";
            $check_duplicate_result = mysqli_query($conn, $check_duplicate_sql);
            
            if (mysqli_num_rows($check_duplicate_result) > 0) {
                // User has already booked this event, update the existing booking
                $existing_booking = mysqli_fetch_assoc($check_duplicate_result);
                $existing_booking_id = $existing_booking['id'];
                
                $booking_sql = "UPDATE event_bookings SET 
                                num_seats = num_seats + $num_seats,
                                booking_date = NOW() 
                                WHERE id = '$existing_booking_id'";
                                
                if (mysqli_query($conn, $booking_sql)) {
                    $last_booking_id = $existing_booking_id;
                    $booking_success = "Successfully added $num_seats more " . ($num_seats > 1 ? "seats" : "seat") . " to your booking!";
                    
                    // Store booking ID for ticket generation
                    $_SESSION['last_booking_id'] = $last_booking_id;
                    $_SESSION['last_event_id'] = $event_id;
                    
                    // If it's an AJAX request, return JSON response
                    if ($is_ajax) {
                        // Clear any previous output
                        ob_end_clean();
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true, 
                            'message' => $booking_success,
                            'booking_id' => $last_booking_id
                        ]);
                        exit;
                    }
                }
            } else {
                // First time booking this event
                $booking_sql = "INSERT INTO event_bookings (event_id, user_id, num_seats, booking_date) 
                            VALUES ('$event_id', '$user_id', '$num_seats', NOW())";
                
                if (mysqli_query($conn, $booking_sql)) {
                    $last_booking_id = mysqli_insert_id($conn);
                    $booking_success = "Successfully booked $num_seats " . ($num_seats > 1 ? "seats" : "seat") . " for this event!";
                    
                    // Store booking ID for ticket generation
                    $_SESSION['last_booking_id'] = $last_booking_id;
                    $_SESSION['last_event_id'] = $event_id;
                    
                    // If it's an AJAX request, return JSON response
                    if ($is_ajax) {
                        // Clear any previous output
                        ob_end_clean();
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true, 
                            'message' => $booking_success,
                            'booking_id' => $last_booking_id
                        ]);
                        exit;
                    }
                }
            }
            
            if (mysqli_query($conn, $booking_sql)) {
                $last_booking_id = mysqli_insert_id($conn);
                $booking_success = "Successfully booked $num_seats " . ($num_seats > 1 ? "seats" : "seat") . " for this event!";
                
                // Store booking ID for ticket generation
                $_SESSION['last_booking_id'] = $last_booking_id;
                $_SESSION['last_event_id'] = $event_id;
                
                // If it's an AJAX request, return JSON response
                if ($is_ajax) {
                    // Clear any previous output
                    ob_end_clean();
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => $booking_success,
                        'booking_id' => $last_booking_id
                    ]);
                    exit;
                }
            } else {
                $booking_error = "Error booking event: " . mysqli_error($conn);
                
                // If it's an AJAX request, return JSON response
                if ($is_ajax) {
                    // Clear any previous output
                    ob_end_clean();
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $booking_error]);
                    exit;
                }
            }
        } catch (Exception $e) {
            $booking_error = "Exception occurred: " . $e->getMessage();
            
            // If it's an AJAX request, return JSON response
            if ($is_ajax) {
                // Clear any previous output
                ob_end_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $booking_error]);
                exit;
            }
        }
    }
    
    // If we get here and it's an AJAX request, something went wrong
    if ($is_ajax) {
        // Clear any previous output
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An unknown error occurred']);
        exit;
    }
    
    // For non-AJAX requests, discard any unexpected output
    ob_end_clean();
}

// Generate a grid of seats
$total_seats = $event['max_attendees'];
$rows = ceil($total_seats / 10); // 10 seats per row
$seats = [];

// Get already booked seats
$booked_seats_sql = "SELECT SUM(num_seats) as total FROM event_bookings WHERE event_id = '$event_id'";
$booked_result = mysqli_query($conn, $booked_seats_sql);
$booked_data = mysqli_fetch_assoc($booked_result);
$booked_count = $booked_data['total'] ?? 0;

// Create seat grid
for ($i = 1; $i <= $rows; $i++) {
    $row_seats = [];
    for ($j = 1; $j <= 10; $j++) {
        $seat_num = ($i - 1) * 10 + $j;
        if ($seat_num <= $total_seats) {
            // Mark seat as booked if it's within the booked count
            $is_booked = $seat_num <= $booked_count;
            $row_seats[] = [
                'number' => $seat_num,
                'booked' => $is_booked
            ];
        }
    }
    $seats[] = $row_seats;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Event - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        
        h1 {
            color: #6a11cb;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .booking-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .event-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 20px;
        }
        
        .event-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }
        
        .event-details {
            display: flex;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .event-detail {
            margin-right: 20px;
            display: flex;
            align-items: center;
        }
        
        .event-detail i {
            margin-right: 8px;
        }
        
        .booking-content {
            padding: 30px;
        }
        
        .booking-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .seat-selection {
            margin-bottom: 30px;
        }
        
        .screen {
            background: #ddd;
            height: 30px;
            border-radius: 5px;
            margin-bottom: 30px;
            text-align: center;
            line-height: 30px;
            color: #555;
            font-weight: bold;
        }
        
        .seat-grid {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .seat-row {
            display: flex;
            gap: 10px;
        }
        
        .seat {
            width: 40px;
            height: 40px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        
        .seat.available {
            background-color: #e0e0e0;
            color: #333;
        }
        
        .seat.booked {
            background-color: #f44336;
            color: white;
            cursor: not-allowed;
        }
        
        .seat.selected {
            background-color: #4CAF50;
            color: white;
        }
        
        .seat:hover:not(.booked) {
            transform: scale(1.1);
        }
        
        .seat-info {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 20px;
        }
        
        .seat-type {
            display: flex;
            align-items: center;
        }
        
        .seat-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            margin-right: 8px;
        }
        
        .available-color {
            background-color: #e0e0e0;
        }
        
        .booked-color {
            background-color: #f44336;
        }
        
        .selected-color {
            background-color: #4CAF50;
        }
        
        .payment-options {
            margin-top: 30px;
        }
        
        .payment-method {
            margin-bottom: 15px;
        }
        
        .payment-method label {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: all 0.2s ease;
        }
        
        .payment-method label:hover {
            background-color: #f9f9f9;
        }
        
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-method i {
            margin-right: 10px;
            font-size: 20px;
            color: #6a11cb;
        }
        
        .booking-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-total {
            font-weight: bold;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 16px;
        }
        
        .btn-back {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .btn-back:hover {
            background-color: #e0e0e0;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
        }
        
        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .btn-confirm:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <?php include 'navbar3.php'; ?>
    
    <div class="container">
        <h1>Book Event</h1>
        
        <div class="booking-container">
            <div class="event-header">
                <h2 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h2>
                <div class="event-details">
                    <div class="event-detail">
                        <i class="fas fa-calendar"></i>
                        <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-clock"></i>
                        <?php echo date('g:i A', strtotime($event['event_time'])); ?>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($event['event_location']); ?>
                    </div>
                    <div class="event-detail">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($event['organizer_name']); ?>
                    </div>
                </div>
            </div>
            
            <div class="booking-content">
                <?php if (isset($booking_error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $booking_error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($booking_success)): ?>
                    <div class="alert alert-success">
                        <?php echo $booking_success; ?>
                        <div class="download-ticket">
                            <a href="generate_ticket.php?booking_id=<?php echo $last_booking_id; ?>&event_id=<?php echo $event_id; ?>" class="ticket-btn" target="_blank">
                                <i class="fas fa-download"></i> Download Ticket
                            </a>
                        </div>
                    </div>
                    
                    <div class="action-buttons" style="margin-top: 20px;">
                        <a href="view_events.php" class="btn btn-back">Back to Events</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="" id="booking-form">
                        <div class="booking-section seat-selection">
                            <h3 class="section-title">Select Your Seats</h3>
                            
                            <div class="screen">STAGE</div>
                            
                            <div class="seat-grid">
                                <?php foreach ($seats as $row_index => $row): ?>
                                    <div class="seat-row">
                                        <?php foreach ($row as $seat): ?>
                                            <div class="seat <?php echo $seat['booked'] ? 'booked' : 'available'; ?>" 
                                                     onclick="toggleSeat(this)" 
                                                     data-seat="<?php echo $seat['number']; ?>">
                                                <?php echo $seat['number']; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="seat-info">
                                <div class="seat-type">
                                    <div class="seat-color available-color"></div>
                                    <span>Available</span>
                                </div>
                                <div class="seat-type">
                                    <div class="seat-color selected-color"></div>
                                    <span>Selected</span>
                                </div>
                                <div class="seat-type">
                                    <div class="seat-color booked-color"></div>
                                    <span>Booked</span>
                                </div>
                            </div>
                            
                            <div id="selected-seats-container">
                                <!-- Hidden inputs for selected seats will be added here by JavaScript -->
                            </div>
                        </div>
                        
                        <div class="booking-section payment-options">
                            <h3 class="section-title">Payment Method</h3>
                            
                            <div class="payment-method">
                                <label>
                                    <input type="radio" name="payment_method" value="credit_card" checked>
                                    <i class="fas fa-credit-card"></i>
                                    Credit/Debit Card
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <label>
                                    <input type="radio" name="payment_method" value="upi">
                                    <i class="fas fa-mobile-alt"></i>
                                    UPI Payment
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <label>
                                    <input type="radio" name="payment_method" value="net_banking">
                                    <i class="fas fa-university"></i>
                                    Net Banking
                                </label>
                            </div>
                        </div>
                        
                        <div class="booking-section">
                            <h3 class="section-title">Booking Summary</h3>
                            
                            <div class="booking-summary">
                                <div class="summary-item">
                                    <span>Event:</span>
                                    <span><?php echo htmlspecialchars($event['event_name']); ?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span>Date & Time:</span>
                                    <span><?php echo date('F j, Y', strtotime($event['event_date'])) . ' at ' . date('g:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                
                                <div class="summary-item">
                                    <span>Selected Seats:</span>
                                    <span id="selected-seats-summary">None</span>
                                </div>
                                
                                <div class="summary-item summary-total">
                                    <span>Total Amount:</span>
                                    <span id="total-amount"><?php echo ($event['ticket_price'] > 0) ? '₹0.00' : 'Free'; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="booking-response" style="display: none;"></div>
                        
                        <div class="action-buttons">
                            <a href="view_events.php" class="btn btn-back">Back to Events</a>
                            <button type="submit" name="confirm_booking" class="btn btn-confirm" id="confirm-btn" disabled>Confirm Booking</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    </div>
    
    <script>
        const selectedSeats = [];
        const ticketPrice = <?php echo (float)$event['ticket_price']; ?>;
        const confirmBtn = document.getElementById('confirm-btn');
        const selectedSeatsSummary = document.getElementById('selected-seats-summary');
        const totalAmount = document.getElementById('total-amount');
        const selectedSeatsContainer = document.getElementById('selected-seats-container');
        const bookingForm = document.getElementById('booking-form');
        const bookingResponse = document.getElementById('booking-response');
        
        function toggleSeat(seatElement) {
            // Don't allow selecting booked seats
            if (seatElement.classList.contains('booked')) {
                return;
            }
            
            const seatNumber = seatElement.getAttribute('data-seat');
            
            if (seatElement.classList.contains('selected')) {
                // Deselect seat
                seatElement.classList.remove('selected');
                seatElement.classList.add('available');
                
                // Remove from selected seats array
                const index = selectedSeats.indexOf(seatNumber);
                if (index > -1) {
                    selectedSeats.splice(index, 1);
                }
            } else {
                // Select seat
                seatElement.classList.remove('available');
                seatElement.classList.add('selected');
                
                // Add to selected seats array
                selectedSeats.push(seatNumber);
            }
            
            updateBookingSummary();
            updateHiddenInputs();
        }
        
        function updateBookingSummary() {
            if (selectedSeats.length === 0) {
                selectedSeatsSummary.textContent = 'None';
                totalAmount.textContent = ticketPrice > 0 ? '₹0.00' : 'Free';
                confirmBtn.disabled = true;
            } else {
                selectedSeatsSummary.textContent = selectedSeats.join(', ');
                
                if (ticketPrice > 0) {
                    const total = selectedSeats.length * ticketPrice;
                    totalAmount.textContent = '₹' + total.toFixed(2);
                } else {
                    totalAmount.textContent = 'Free';
                }
                
                confirmBtn.disabled = false;
            }
        }
        
        function updateHiddenInputs() {
            // Clear previous inputs
            selectedSeatsContainer.innerHTML = '';
            
            // Create hidden inputs for each selected seat
            selectedSeats.forEach(seat => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_seats[]';
                input.value = seat;
                selectedSeatsContainer.appendChild(input);
            });
        }
        
        // Add form submission handler
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Collect form data
            const formData = new FormData(bookingForm);
            
            // Ensure the confirm_booking parameter is set
            if (!formData.has('confirm_booking')) {
                formData.append('confirm_booking', '1');
            }
            
            // Send AJAX request
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                
                // Get the response text first
                return response.text();
            })
            .then(text => {
                // Try to parse as JSON
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Failed to parse response as JSON:", text.substring(0, 500) + "...");
                    throw new Error('Invalid JSON response. Server returned HTML instead of JSON.');
                }
            })
            .then(data => {
                // Reset button state
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Confirm Booking';
                
                // Show response message
                bookingResponse.style.display = 'block';
                
                if (data.success) {
                    // Success message
                    bookingResponse.className = 'alert alert-success';
                    bookingResponse.innerHTML = `
                        ${data.message}
                        <div class="download-ticket">
                            <a href="generate_ticket.php?booking_id=${data.booking_id}&event_id=<?php echo $event_id; ?>" 
                               class="ticket-btn" 
                               id="download-ticket-btn" 
                               target="_blank">
                                <i class="fas fa-download"></i> Download Ticket
                            </a>
                        </div>
                    `;
                    
                    // Automatically trigger ticket download
                    document.getElementById('download-ticket-btn').click();
                    
                    // Update available seats count
                    available_seats -= selectedSeats.length;
                    
                    // Update seat display to show booked seats
                    selectedSeats.forEach(seatNum => {
                        const seatElement = document.querySelector(`.seat[data-seat="${seatNum}"]`);
                        if (seatElement) {
                            seatElement.classList.remove('selected');
                            seatElement.classList.add('booked');
                        }
                    });
                    
                    // Clear selected seats array
                    selectedSeats.length = 0;
                    updateBookingSummary();
                    
                    // Keep the form enabled for more bookings
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = 'Book More Seats';
                    
                    // Refresh the page after 2 seconds to update seat availability
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    // Error message
                    bookingResponse.className = 'alert alert-danger';
                    bookingResponse.textContent = data.message || 'An error occurred with the booking process.';
                }
                
                // Scroll to response
                bookingResponse.scrollIntoView({ behavior: 'smooth' });
                
                // Auto-hide alert after 10 seconds for error messages only
                if (!data.success) {
                    setTimeout(() => {
                        bookingResponse.style.transition = 'opacity 1s ease';
                        bookingResponse.style.opacity = '0';
                        setTimeout(() => {
                            bookingResponse.style.display = 'none';
                            bookingResponse.style.opacity = '1';
                        }, 1000);
                    }, 10000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Confirm Booking';
                
                bookingResponse.style.display = 'block';
                bookingResponse.className = 'alert alert-danger';
                bookingResponse.textContent = error.message;
                
                // Scroll to response
                bookingResponse.scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(#booking-response)');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 1s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 1000);
                });
            }, 5000);
        });
    </script>
</body>
</html>