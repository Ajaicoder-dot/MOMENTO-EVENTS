<?php
// Database connection
include 'db.php';
include 'navbar1.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle approval/rejection actions
if (isset($_POST['action']) && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    
    if ($action == 'accept') {
        $sql = "UPDATE book SET approval_status = 'Accepted', approval_date = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $success_message = "Booking #$booking_id has been successfully accepted.";
    } 
    else if ($action == 'reject') {
        // Handle 'Other' reason
        if (isset($_POST['other_reason']) && !empty($_POST['other_reason']) && $reason == 'Other') {
            $reason = $_POST['other_reason'];
        }
        
        $sql = "UPDATE book SET approval_status = 'Rejected', approval_date = NOW(), rejection_reason = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $reason, $booking_id);
        $stmt->execute();
        $success_message = "Booking #$booking_id has been rejected.";
    }
}

// Get filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';

// Build the SQL query based on filters
$sql = "SELECT id, event_head, phone_no, guest_email, event_start_date, 
        event_end_date, event_start_time, event_end_time, total_amount, venue,
        created_at, approval_status, cancelled
        FROM book WHERE 1=1";

// Apply status filter
if ($status_filter != 'all') {
    if ($status_filter == 'pending') {
        $sql .= " AND (approval_status = 'Pending' OR approval_status IS NULL)";
    } else {
        $sql .= " AND approval_status = '$status_filter'";
    }
}

// Apply date filter
if ($date_filter != 'all') {
    if ($date_filter == 'today') {
        $sql .= " AND DATE(created_at) = CURDATE()";
    } else if ($date_filter == 'week') {
        $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } else if ($date_filter == 'month') {
        $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }
}

// Only show non-cancelled bookings by default
$sql .= " AND cancelled = 0 ORDER BY created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Booking Approval</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        label {
            font-weight: bold;
        }
        select, button {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            padding: 8px 15px;
        }
        button:hover {
            background-color: #45a049;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .pending {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .accepted {
            background-color: #d4edda;
            color: #155724;
        }
        .rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            border: none;
            color: white;
        }
        .btn-accept {
            background-color: #28a745;
        }
        .btn-reject {
            background-color: #dc3545;
        }
        .btn-view {
            background-color: #17a2b8;
        }
        .badge {
            font-size: 11px;
            padding: 3px 6px;
            border-radius: 3px;
            background-color: #6c757d;
            color: white;
            margin-left: 5px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .reason-option {
            margin: 10px 0;
        }
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }
        .modal-buttons {
            margin-top: 20px;
            text-align: right;
        }
        .no-results {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .pagination a {
            padding: 5px 10px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .booking-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        .detail-item {
            padding: 5px;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Booking Approval Dashboard</h1>
        
        <?php if (isset($success_message)): ?>
        <div class="success-message">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <div class="filters">
            <form method="get" action="">
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Accepted" <?php echo $status_filter == 'Accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="Rejected" <?php echo $status_filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    
                    <label for="date_filter">Date:</label>
                    <select name="date_filter" id="date_filter">
                        <option value="all" <?php echo $date_filter == 'all' ? 'selected' : ''; ?>>All Time</option>
                        <option value="today" <?php echo $date_filter == 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $date_filter == 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo $date_filter == 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                    </select>
                    
                    <button type="submit">Apply Filters</button>
                    <a href="manage_bookings.php" style="margin-left: 10px; color: #666; text-decoration: none;">Reset</a>
                </div>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Event Date</th>
                    <th>Venue</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Determine status class
                        $status_class = 'pending';
                        if ($row['approval_status'] == 'Accepted') {
                            $status_class = 'accepted';
                        } else if ($row['approval_status'] == 'Rejected') {
                            $status_class = 'rejected';
                        }
                        
                        // Format the status text
                        $status_text = $row['approval_status'] ?: 'Pending';
                        
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['event_head']}</td>
                            <td>{$row['phone_no']}<br><small>{$row['guest_email']}</small></td>
                            <td>" . date('M d, Y', strtotime($row['event_start_date'])) . "<br><small>{$row['event_start_time']} - {$row['event_end_time']}</small></td>
                            <td>{$row['venue']}</td>
                            <td>" . number_format($row['total_amount'], 2) . "</td>
                            <td><span class='status-badge {$status_class}'>{$status_text}</span></td>
                            <td class='action-buttons'>";
                            
                        // Only show accept/reject buttons for pending bookings
                        if ($status_text == 'Pending') {
                            echo "<button class='btn btn-accept' onclick='acceptBooking({$row['id']})'>Accept</button>
                                  <button class='btn btn-reject' onclick='openRejectModal({$row['id']})'>Reject</button>";
                        }
                        
                        echo "<button class='btn btn-view' onclick='viewBookingDetails({$row['id']})'>Details</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='no-results'>No bookings found matching your criteria</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <!-- The Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            <h3>Rejection Reason</h3>
            <p>Please provide a reason for rejecting this booking:</p>
            
            <form id="rejectForm" action="" method="post">
                <input type="hidden" id="reject_booking_id" name="booking_id" value="">
                <input type="hidden" name="action" value="reject">
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason1" name="reason" value="Venue not available" required>
                    <label for="reject_reason1">Venue not available</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason2" name="reason" value="Scheduling conflict">
                    <label for="reject_reason2">Scheduling conflict</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason3" name="reason" value="Insufficient information">
                    <label for="reject_reason3">Insufficient information</label>
                </div>
                
                <div class="reason-option">
                    <input type="radio" id="reject_reason4" name="reason" value="Other">
                    <label for="reject_reason4">Other (please specify)</label>
                    <textarea id="reject_other_reason" name="other_reason" rows="3" style="display:none;" placeholder="Please specify your reason"></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" onclick="closeModal('rejectModal')" style="background-color: #ccc;">Cancel</button>
                    <button type="submit" style="background-color: #dc3545;">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- The Booking Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('detailsModal')">&times;</span>
            <h3>Booking Details</h3>
            <div id="bookingDetails">
                Loading...
            </div>
            <div class="modal-buttons">
                <button type="button" onclick="closeModal('detailsModal')" style="background-color: #ccc;">Close</button>
            </div>
        </div>
    </div>
    
    <!-- Hidden form for accept action -->
    <form id="acceptForm" action="" method="post" style="display: none;">
        <input type="hidden" id="accept_booking_id" name="booking_id" value="">
        <input type="hidden" name="action" value="accept">
    </form>
    
    <script>
        // Get the modals
        var rejectModal = document.getElementById("rejectModal");
        var detailsModal = document.getElementById("detailsModal");
        
        // Open the reject modal
        function openRejectModal(id) {
            document.getElementById("reject_booking_id").value = id;
            rejectModal.style.display = "block";
            
            // Reset form
            document.getElementById("rejectForm").reset();
            document.getElementById("reject_other_reason").style.display = "none";
        }
        
        // Accept booking function
        function acceptBooking(id) {
            if (confirm("Are you sure you want to accept this booking?")) {
                document.getElementById("accept_booking_id").value = id;
                document.getElementById("acceptForm").submit();
            }
        }
        
        // View booking details
        function viewBookingDetails(id) {
            detailsModal.style.display = "block";
            document.getElementById("bookingDetails").innerHTML = "Loading booking #" + id + " details...";
            
            // Create and send AJAX request to get booking details
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_booking_details.php?id=" + id, true);
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        // Log the raw response for debugging
                        console.log("Raw response:", xhr.responseText);
                        
                        try {
                            var response = JSON.parse(xhr.responseText);
                            
                            // Check if there's an error in the response
                            if (response.error) {
                                document.getElementById("bookingDetails").innerHTML = 
                                    "Error: " + response.error;
                                return;
                            }
                            
                            // Build HTML for displaying booking details
                            var html = '<div class="booking-details">';
                            
                            html += '<div class="detail-item"><span class="detail-label">Booking ID:</span> #' + response.id + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">Created:</span> ' + response.created_at + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">Name:</span> ' + response.event_head + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">Phone:</span> ' + response.phone_no + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">Email:</span> ' + response.guest_email + '</div>';
                            
                            // Only show event_type if available
                            if (response.event_type) {
                                html += '<div class="detail-item"><span class="detail-label">Event Type:</span> ' + response.event_type + '</div>';
                            }
                            
                            html += '<div class="detail-item"><span class="detail-label">Start Date:</span> ' + response.event_start_date + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">End Date:</span> ' + response.event_end_date + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">Start Time:</span> ' + response.event_start_time + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">End Time:</span> ' + response.event_end_time + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">Venue:</span> ' + response.venue + '</div>';
                            html += '<div class="detail-item"><span class="detail-label">Total Amount:</span> $' + 
                                parseFloat(response.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + 
                                '</div>';
                            
                            // Only show special requests if available
                            if (response.special_requests) {
                                html += '<div class="detail-item" style="grid-column: span 2;"><span class="detail-label">Special Requests:</span><br>' + 
                                    response.special_requests + '</div>';
                            }
                            
                            html += '</div>';
                            
                            // Add action buttons if the booking is pending
                            if (response.approval_status === 'Pending' || !response.approval_status) {
                                html += '<div class="action-buttons" style="justify-content: center; margin-top: 15px;">';
                                html += '<button class="btn btn-accept" onclick="acceptBooking(' + response.id + ')">Accept Booking</button>';
                                html += '<button class="btn btn-reject" onclick="closeModal(\'detailsModal\'); openRejectModal(' + 
                                    response.id + ');">Reject Booking</button>';
                                html += '</div>';
                            }
                            
                            document.getElementById("bookingDetails").innerHTML = html;
                        } catch (e) {
                            document.getElementById("bookingDetails").innerHTML = 
                                "Error parsing response: " + e.message + "<br><br>" +
                                "Raw response: <pre>" + xhr.responseText.substring(0, 300) + "...</pre>";
                        }
                    } else {
                        document.getElementById("bookingDetails").innerHTML = 
                            "Error loading booking details. Status: " + xhr.status;
                    }
                }
            };
            
            xhr.onerror = function() {
                document.getElementById("bookingDetails").innerHTML = 
                    "Network error occurred while trying to fetch booking details.";
            };
            
            xhr.send();
        }
        
        // Close the specified modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == rejectModal) {
                closeModal('rejectModal');
            }
            if (event.target == detailsModal) {
                closeModal('detailsModal');
            }
        }
        
        // Show/hide the "Other reason" textarea based on selection
        document.querySelectorAll('input[name="reason"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                if (this.value === 'Other' && this.checked) {
                    document.getElementById('reject_other_reason').style.display = 'block';
                } else {
                    document.getElementById('reject_other_reason').style.display = 'none';
                }
            });
        });
    </script>
</body>
<?php include 'footer.php'; ?>
</html>