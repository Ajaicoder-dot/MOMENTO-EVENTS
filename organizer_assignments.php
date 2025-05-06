<?php
// Database connection
include 'db.php';
session_start();

// Check if user is logged in and is an organizer

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'event organizer') {
    header("Location: index.php?error=unauthorized");
    exit;
}

$user_id = $_SESSION['user_id'];

// Process form submission for accepting/rejecting assignments
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $assignment_id = $_POST['assignment_id'];
    $action = $_POST['action'];
    
    if ($action == 'accept') {
        $sql = "UPDATE organizer_assignments SET status = 'accepted', updated_at = NOW() WHERE id = ? AND organizer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $assignment_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = "Assignment accepted successfully!";
    } 
    elseif ($action == 'reject' && isset($_POST['reject_reason'])) {
        $reject_reason = $_POST['reject_reason'];
        $sql = "UPDATE organizer_assignments SET status = 'rejected', reject_reason = ?, updated_at = NOW() WHERE id = ? AND organizer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $reject_reason, $assignment_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
        $_SESSION['message'] = "Assignment rejected successfully."; // Modified message with "successfully" for clarity
    }
    
    // Redirect to prevent form resubmission
    header("Location: organizer_assignments.php");
    exit;
}
// Get assignments for this organizer
$sql = "SELECT oa.*, b.event_head, b.phone_no, b.guest_email, b.guest_address, 
        b.event_start_date, b.event_end_date, b.event_start_time, b.event_end_time, 
        b.venue, b.selected_services, b.total_amount, b.additional_details,
        u.username as admin_name 
        FROM organizer_assignments oa
        JOIN book b ON oa.booking_id = b.id
        JOIN users u ON oa.admin_id = u.id
        WHERE oa.organizer_id = ?
        ORDER BY oa.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$assignments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments</title>
    <style>
        /* General Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 2.5rem;
            color: #2c3e50;
            font-size: 2.5rem;
            position: relative;
            padding-bottom: 0.8rem;
            font-weight: 700;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 5px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4);
            border-radius: 10px;
        }
        
        /* Message Styles */
        .message {
            padding: 1.2rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        
        .rejected {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        
        /* Assignment Cards */
        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .assignment-card {
            background-color: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .assignment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            padding: 1.8rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }
        
        .event-name {
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 700;
        }
        
        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .status-accepted {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card-body {
            padding: 1.8rem;
        }
        
        .info-item {
            margin-bottom: 1.2rem;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c5ce7;
            display: inline-block;
            min-width: 130px;
            position: relative;
            flex-shrink: 0;
        }
        
        .info-label:after {
            content: ':';
            position: absolute;
            right: 10px;
        }
        
        .info-item span:not(.info-label) {
            word-break: break-word;
            overflow-wrap: break-word;
            flex: 1;
        }
        
        .info-item {
            margin-bottom: 1.2rem;
            display: flex;
            align-items: flex-start;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c5ce7;
            display: inline-block;
            min-width: 130px;
            position: relative;
            flex-shrink: 0;
        }
        
        .info-label:after {
            content: ':';
            position: absolute;
            right: 10px;
        }
        
        .info-item span:not(.info-label) {
            word-break: break-word;
            overflow-wrap: break-word;
            flex: 1;
        }
        
        .card-footer {
            padding: 1.5rem;
            background-color: #f8f9fa;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 0.6rem 1.8rem;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-accept {
            background: linear-gradient(135deg, #00b09b, #96c93d);
            color: white;
        }
        
        .btn-reject {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white;
        }
        
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-show-more {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .btn-show-more:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        
        .btn-show-more.active {
            background: linear-gradient(135deg, #764ba2, #667eea);
        }
        
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
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 8% auto;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 80%;
            max-width: 600px;
            animation: modalopen 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-30px) scale(0.95);}
            to {opacity: 1; transform: translateY(0) scale(1);}
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .close:hover,
        .close:focus {
            color: #333;
            text-decoration: none;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border 0.3s ease, box-shadow 0.3s ease;
            resize: vertical;
        }
        
        .form-group textarea:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1);
            outline: none;
        }
        
        .form-actions {
            text-align: center;
            margin-top: 25px;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            margin-top: 2rem;
        }
        
        .empty-title {
            color: #6c757d;
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .empty-state p {
            color: #adb5bd;
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        /* Additional styling for better readability */
        .basic-details, .additional-details {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
        }
        
        .additional-details {
            border-top: 2px dashed #e9ecef;
            padding-top: 1.5rem;
            animation: fadeIn 0.5s ease;
        }
        
        /* Services list styling */
        .services-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .services-list li {
            padding: 8px 12px;
            margin-bottom: 8px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            border-left: 3px solid #6c5ce7;
        }
        
        .services-list li:last-child {
            margin-bottom: 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .assignments-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                flex-direction: column;
            }
            
            .info-label {
                min-width: auto;
                margin-bottom: 0.3rem;
            }
            
            .info-label:after {
                display: none;
            }
            
            .card-footer {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        /* Add some animations */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .status-pending {
            animation: pulse 2s infinite;
        }
    </style>
    <!-- Add Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h1 class="page-title">My Booking Assignments</h1>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="message <?php echo (strpos($_SESSION['message'], 'rejected') !== false) ? 'rejected' : 'success'; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(empty($assignments)): ?>
            <div class="empty-state">
                <h2 class="empty-title">No assignments found</h2>
                <p>You don't have any booking assignments yet.</p>
            </div>
        <?php else: ?>
            <div class="assignments-grid">
                <?php foreach($assignments as $assignment): ?>
                    <div class="assignment-card">
                        <div class="card-header">
                            <h2 class="event-name"><?php echo htmlspecialchars($assignment['event_head']); ?></h2>
                            <span class="status-badge status-<?php echo $assignment['status']; ?>">
                                <?php echo ucfirst(htmlspecialchars($assignment['status'])); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="basic-details">
                                <div class="info-item">
                                    <span class="info-label">Event Name:</span>
                                    <span><?php echo htmlspecialchars($assignment['event_head']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Event Date:</span>
                                    <span>
                                        <?php echo htmlspecialchars($assignment['event_start_date']); ?>
                                        <?php if(isset($assignment['event_end_date']) && !empty($assignment['event_end_date'])): ?>
                                         to <?php echo htmlspecialchars($assignment['event_end_date']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Event Time:</span>
                                    <span>
                                        <?php echo isset($assignment['event_start_time']) ? htmlspecialchars($assignment['event_start_time']) : 'N/A'; ?>
                                        <?php if(isset($assignment['event_end_time']) && !empty($assignment['event_end_time'])): ?>
                                         to <?php echo htmlspecialchars($assignment['event_end_time']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Venue:</span>
                                    <span><?php echo isset($assignment['venue']) ? htmlspecialchars($assignment['venue']) : 'N/A'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Contact:</span>
                                    <span><?php echo isset($assignment['phone_no']) ? htmlspecialchars($assignment['phone_no']) : 'N/A'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email:</span>
                                    <span><?php echo isset($assignment['guest_email']) ? htmlspecialchars($assignment['guest_email']) : 'N/A'; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Address:</span>
                                    <span><?php echo isset($assignment['guest_address']) ? htmlspecialchars($assignment['guest_address']) : 'N/A'; ?></span>
                                </div>
                                <div style="text-align: center; margin-top: 1.5rem;">
                                    <button class="btn-show-more" onclick="toggleDetails(this, <?php echo $assignment['id']; ?>)">Show More Details</button>
                                </div>
                            </div>
                            
                            <div class="additional-details" id="details-<?php echo $assignment['id']; ?>" style="display: none;">
                                <div class="info-item">
                                    <span class="info-label">Services:</span>
                                    <span>
                                        <?php 
                                            if(isset($assignment['selected_services'])) {
                                                $services = json_decode($assignment['selected_services'], true);
                                                if(is_array($services)) {
                                                    echo "<ul class='services-list'>";
                                                    foreach($services as $service) {
                                                        if(isset($service['name'])) {
                                                            echo "<li>" . htmlspecialchars($service['name']);
                                                            if(isset($service['amount'])) {
                                                                echo " - Rs" . htmlspecialchars($service['amount']);
                                                            }
                                                            echo "</li>";
                                                        }
                                                    }
                                                    echo "</ul>";
                                                } else {
                                                    echo htmlspecialchars($assignment['selected_services']);
                                                }
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Amount:</span>
                                    <span><?php echo isset($assignment['total_amount']) ? 'Rs'.htmlspecialchars($assignment['total_amount']) : 'N/A'; ?></span>
                                </div>
                                <?php if(!empty($assignment['additional_details'])): ?>
                                    <div class="info-item">
                                        <span class="info-label">Additional Details:</span>
                                        <span>
                                            <?php 
                                                $details = json_decode($assignment['additional_details'], true);
                                                if(is_array($details)) {
                                                    foreach($details as $key => $value) {
                                                        echo "<strong>".htmlspecialchars(ucfirst(str_replace('_', ' ', $key))).":</strong> ".htmlspecialchars($value)."<br>";
                                                    }
                                                } else {
                                                    echo nl2br(htmlspecialchars($assignment['additional_details']));
                                                }
                                            ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <div class="info-item">
                                    <span class="info-label">Assigned By:</span>
                                    <span><?php echo htmlspecialchars($assignment['admin_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Assigned On:</span>
                                    <span><?php echo htmlspecialchars($assignment['created_at']); ?></span>
                                </div>
                                <?php if(!empty($assignment['notes'])): ?>
                                    <div class="info-item">
                                        <span class="info-label">Notes:</span>
                                        <span><?php echo nl2br(htmlspecialchars($assignment['notes'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if($assignment['status'] == 'rejected' && !empty($assignment['reject_reason'])): ?>
                                    <div class="info-item">
                                        <span class="info-label">Reject Reason:</span>
                                        <span><?php echo nl2br(htmlspecialchars($assignment['reject_reason'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if($assignment['status'] != 'completed'): ?>
                            <div class="card-footer">
                                <?php if($assignment['status'] == 'rejected'): ?>
                                    <form method="post" action="">
                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-accept">Accept</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if($assignment['status'] == 'pending'): ?>
                                    <form method="post" action="">
                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn btn-accept">Accept</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if($assignment['status'] != 'rejected'): ?>
                                    <button class="btn btn-reject" onclick="openRejectModal(<?php echo $assignment['id']; ?>)">Reject</button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Reject Assignment</h2>
            <form id="rejectForm" method="post" action="">
                <input type="hidden" id="reject_assignment_id" name="assignment_id">
                <input type="hidden" name="action" value="reject">
                
                <div class="form-group">
                    <label for="reject_reason">Reason for Rejection:</label>
                    <textarea id="reject_reason" name="reject_reason" rows="4" required placeholder="Please provide a reason for rejecting this assignment"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-reject">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Get the modal
        var modal = document.getElementById("rejectModal");
        var assignmentIdInput = document.getElementById("reject_assignment_id");
        
        // Function to open the modal
        function openRejectModal(assignmentId) {
            modal.style.display = "block";
            assignmentIdInput.value = assignmentId;
        }
        
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        // Function to toggle additional details
        function toggleDetails(button, assignmentId) {
            var detailsDiv = document.getElementById('details-' + assignmentId);
            if (detailsDiv.style.display === 'none') {
                detailsDiv.style.display = 'block';
                button.textContent = 'Hide Details';
                button.classList.add('active');
            } else {
                detailsDiv.style.display = 'none';
                button.textContent = 'Show More Details';
                button.classList.remove('active');
            }
        }
    </script>
</body>
</html>