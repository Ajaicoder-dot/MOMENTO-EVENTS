<?php
// Database connection
include 'db.php';
session_start(); // Add session start for navbar

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if organizer ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Get specific organizer
    $organizer_id = intval($_GET['id']);
    $sql = "SELECT * FROM users WHERE id = ? AND (role = 'organizer' OR role = 'event organizer' OR role LIKE '%organizer%')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $organizer_id);
} else {
    // Get all organizers - using more flexible matching for role
    $sql = "SELECT * FROM users WHERE role = 'organizer' OR role = 'event organizer' OR role LIKE '%organizer%'";
    $stmt = $conn->prepare($sql);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

// Check if organizers exist
if ($result->num_rows === 0) {
    // If no organizers found with flexible matching, let's check what roles exist
    $check_sql = "SELECT DISTINCT role FROM users";
    $check_result = $conn->query($check_sql);
    
    $available_roles = [];
    while ($row = $check_result->fetch_assoc()) {
        $available_roles[] = $row['role'];
    }
    
    $error_message = 'No organizers found';
    $roles_info = $available_roles;
} else {
    // Process organizer data
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        // Get single organizer with limited fields
        $organizer = $result->fetch_assoc();
        
        // Get assignment information for this organizer
        $assignment_sql = "SELECT oa.booking_id, b.event_head, b.phone_no 
                          FROM organizer_assignments oa 
                          JOIN book b ON oa.booking_id = b.id 
                          WHERE oa.organizer_id = ?";
        $assignment_stmt = $conn->prepare($assignment_sql);
        $assignment_stmt->bind_param("i", $organizer_id);
        $assignment_stmt->execute();
        $assignment_result = $assignment_stmt->get_result();
        
        if ($assignment_result->num_rows > 0) {
            $organizer['assignments'] = [];
            while ($assignment = $assignment_result->fetch_assoc()) {
                $organizer['assignments'][] = $assignment;
            }
        }
        
        $organizers = [$organizer]; // Put in array for consistent template handling
        $assignment_stmt->close();
    } else {
        // Get all organizers
        $organizers = [];
        while ($row = $result->fetch_assoc()) {
            // Get assignment information for each organizer
            $assignment_sql = "SELECT oa.booking_id, b.event_head, b.phone_no 
                              FROM organizer_assignments oa 
                              JOIN book b ON oa.booking_id = b.id 
                              WHERE oa.organizer_id = ?";
            $assignment_stmt = $conn->prepare($assignment_sql);
            $assignment_stmt->bind_param("i", $row['id']);
            $assignment_stmt->execute();
            $assignment_result = $assignment_stmt->get_result();
            
            if ($assignment_result->num_rows > 0) {
                $row['assignments'] = [];
                while ($assignment = $assignment_result->fetch_assoc()) {
                    $row['assignments'][] = $assignment;
                }
            }
            
            $organizers[] = $row;
            $assignment_stmt->close();
        }
    }
}

// Close connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizers</title>
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        /* Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .navbar-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .navbar-logo i {
            margin-right: 0.5rem;
        }
        
        .navbar-menu {
            display: flex;
        }
        
        .navbar-item {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .navbar-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        
        .navbar-item.active {
            background-color: rgba(255, 255, 255, 0.3);
            font-weight: bold;
        }
        
        /* Container Styles */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
            font-size: 2.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 2px;
        }
        
        /* Organizer Cards */
        .organizers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .organizer-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .organizer-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .card-header h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .info-label {
            font-weight: bold;
            min-width: 80px;
            color: #6a11cb;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background-color: #e9f5ff;
            color: #2575fc;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        
        .card-footer {
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
            text-align: center;
        }
        
        .contact-btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .contact-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        /* Error Message */
        .error-container {
            text-align: center;
            padding: 3rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            color: #e74c3c;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        .roles-list {
            margin: 1.5rem 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .role-item {
            padding: 0.3rem 0.8rem;
            background-color: #f1f1f1;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar1.php'; ?>
    
    <div class="container">
        <h1 class="page-title">Event Organizers</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="error-container">
                <h2 class="error-title"><?php echo $error_message; ?></h2>
                <p>Available roles in the system:</p>
                <div class="roles-list">
                    <?php foreach ($roles_info as $role): ?>
                        <span class="role-item"><?php echo htmlspecialchars($role); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="organizers-grid">
                <?php foreach ($organizers as $organizer): ?>
                    <div class="organizer-card">
                        <div class="card-header">
                            <h2><?php echo htmlspecialchars($organizer['username']); ?></h2>
                            <span class="role-badge"><?php echo htmlspecialchars($organizer['role']); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span><?php echo htmlspecialchars($organizer['email']); ?></span>
                            </div>
                            <?php if (isset($organizer['id'])): ?>
                            <div class="info-item">
                                <span class="info-label">ID:</span>
                                <span><?php echo htmlspecialchars($organizer['id']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($organizer['assignments']) && !empty($organizer['assignments'])): ?>
                            <div class="assignments-section">
                                <h3>Current Assignments</h3>
                                <?php foreach ($organizer['assignments'] as $assignment): ?>
                                <div class="assignment-item">
                                    <div class="assignment-event">
                                        <strong>Booking ID:</strong> <?php echo htmlspecialchars($assignment['booking_id']); ?>
                                    </div>
                                    <div class="assignment-contact">
                                        <strong>Assigned to:</strong> <?php echo htmlspecialchars($assignment['event_head']); ?>
                                    </div>
                                    <div class="assignment-phone">
                                        <strong>Contact:</strong> <?php echo htmlspecialchars($assignment['phone_no']); ?>
                                    </div>
                                    <div class="assignment-actions">
                                        <button class="reassign-btn" onclick="openReassignModal(<?php echo $organizer['id']; ?>, '<?php echo htmlspecialchars($organizer['username']); ?>', <?php echo $assignment['booking_id']; ?>)">Reassign</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <a href="mailto:<?php echo htmlspecialchars($organizer['email']); ?>" class="contact-btn">Contact</a>
                            <!-- Temporarily show for all users to test -->
                            <button class="assign-btn" onclick="openAssignModal(<?php echo $organizer['id']; ?>, '<?php echo htmlspecialchars($organizer['username']); ?>')">Assign Booking</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Assignment Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Assign Booking to <span id="organizerName"></span></h2>
            <form id="assignForm" method="post" action="process_assignment.php">
                <input type="hidden" id="organizer_id" name="organizer_id">
                <input type="hidden" name="admin_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
                
                <div class="form-group">
                    <label for="booking">Select Booking:</label>
                    <select id="booking" name="booking_id" required>
                        <option value="">-- Select a booking --</option>
                        <!-- Bookings will be loaded here via AJAX -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes for Organizer:</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Add any specific instructions or details"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Send Assignment Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reassign Modal -->
    <div id="reassignModal" class="modal">
        <div class="modal-content">
            <span class="close-reassign close">&times;</span>
            <h2>Reassign <span id="reassignOrganizerName"></span></h2>
            <p>Current booking ID: <span id="currentBookingId"></span></p>
            <form id="reassignForm" method="post" action="process_reassignment.php">
                <input type="hidden" id="reassign_organizer_id" name="organizer_id">
                <input type="hidden" id="current_booking_id" name="current_booking_id">
                <input type="hidden" name="admin_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
                
                <div class="form-group">
                    <label for="new_booking">Select New Booking:</label>
                    <select id="new_booking" name="new_booking_id" required>
                        <option value="">-- Select a booking --</option>
                        <!-- Bookings will be loaded here via AJAX -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="reassign_notes">Notes:</label>
                    <textarea id="reassign_notes" name="notes" rows="3" placeholder="Add any specific instructions or details"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Confirm Reassignment</button>
                    <button type="submit" name="action" value="remove" class="remove-btn">Remove Assignment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Get the modal
        var modal = document.getElementById("assignModal");
        var reassignModal = document.getElementById("reassignModal");
        var organizerNameSpan = document.getElementById("organizerName");
        var reassignOrganizerNameSpan = document.getElementById("reassignOrganizerName");
        var organizerIdInput = document.getElementById("organizer_id");
        var reassignOrganizerIdInput = document.getElementById("reassign_organizer_id");
        var currentBookingIdSpan = document.getElementById("currentBookingId");
        var currentBookingIdInput = document.getElementById("current_booking_id");
        var bookingSelect = document.getElementById("booking");
        var newBookingSelect = document.getElementById("new_booking");
        
        // Function to open the modal
        function openAssignModal(organizerId, organizerName) {
            modal.style.display = "block";
            organizerNameSpan.textContent = organizerName;
            organizerIdInput.value = organizerId;
            
            // Show loading indicator
            bookingSelect.innerHTML = '<option value="">Loading bookings...</option>';
            
            // Load available bookings via AJAX
            loadBookings(bookingSelect);
        }
        
        // Function to open the reassign modal
        function openReassignModal(organizerId, organizerName, currentBookingId) {
            reassignModal.style.display = "block";
            reassignOrganizerNameSpan.textContent = organizerName;
            reassignOrganizerIdInput.value = organizerId;
            currentBookingIdSpan.textContent = currentBookingId;
            currentBookingIdInput.value = currentBookingId;
            
            // Show loading indicator
            newBookingSelect.innerHTML = '<option value="">Loading bookings...</option>';
            
            // Load available bookings via AJAX
            loadBookings(newBookingSelect);
        }
        
        // Function to load bookings
        function loadBookings(selectElement) {
            fetch('get_bookings.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        selectElement.innerHTML = '<option value="">Error loading bookings</option>';
                        console.error('Error:', data.error);
                        return;
                    }
                    
                    selectElement.innerHTML = '<option value="">-- Select a booking --</option>';
                    
                    if (data.length === 0) {
                        selectElement.innerHTML += '<option value="" disabled>No available bookings found</option>';
                    } else {
                        data.forEach(booking => {
                            selectElement.innerHTML += `<option value="${booking.id}">${booking.event_name} - ${booking.event_date}</option>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading bookings:', error);
                    selectElement.innerHTML = '<option value="">Error loading bookings</option>';
                });
        }
        
        // Get the <span> elements that close the modals
        var span = document.getElementsByClassName("close")[0];
        var reassignSpan = document.getElementsByClassName("close-reassign")[0];
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
        
        reassignSpan.onclick = function() {
            reassignModal.style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
            if (event.target == reassignModal) {
                reassignModal.style.display = "none";
            }
        }
    </script>
    
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
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
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 80%;
            max-width: 600px;
            animation: modalopen 0.3s;
        }
        
        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: #333;
            text-decoration: none;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-actions {
            text-align: center;
            margin-top: 20px;
        }
        
        .submit-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        .assign-btn {
            display: inline-block;
            margin-left: 10px;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(135deg, #11cb4f 0%, #25c4fc 100%);
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .assign-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(37, 196, 252, 0.4);
        }
        
        .assignments-section {
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        
        .assignments-section h3 {
            color: #6a11cb;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }
        
        .assignment-item {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .assignment-item:last-child {
            margin-bottom: 0;
        }
        
        .assignment-event {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .assignment-contact, .assignment-phone {
            font-size: 0.9rem;
            color: #555;
        }
        
        .assignment-actions {
                margin-top: 10px;
                text-align: right;
            }
            
            .resign-btn {
                padding: 5px 10px;
                background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
                color: white;
                border: none;
                border-radius: 15px;
                font-size: 0.8rem;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .resign-btn:hover {
                transform: scale(1.05);
                box-shadow: 0 3px 8px rgba(231, 76, 60, 0.4);
            }
            .reassign-btn {
                padding: 5px 10px;
                background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
                color: white;
                border: none;
                border-radius: 15px;
                font-size: 0.8rem;
                font-weight: bold;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .reassign-btn:hover {
                transform: scale(1.05);
                box-shadow: 0 3px 8px rgba(243, 156, 18, 0.4);
            }
            .remove-btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    border: none;
    border-radius: 25px;
    font-weight: bold;
    cursor: pointer;
    margin-left: 10px;
    transition: all 0.3s ease;
}

.remove-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
}
    </style>
</body>
</html>