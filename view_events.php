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

// Check for session messages
if (isset($_SESSION['booking_success'])) {
    $booking_success = $_SESSION['booking_success'];
    unset($_SESSION['booking_success']);
}

if (isset($_SESSION['booking_error'])) {
    $booking_error = $_SESSION['booking_error'];
    unset($_SESSION['booking_error']);
}

// Get event categories for filter
$categories_sql = "SELECT DISTINCT event_category FROM live_events WHERE event_date >= CURDATE() ORDER BY event_category";
$categories_result = mysqli_query($conn, $categories_sql);
$categories = [];
while ($category_row = mysqli_fetch_assoc($categories_result)) {
    if (!empty($category_row['event_category'])) {
        $categories[] = $category_row['event_category'];
    }
}

// Remove the check for existing bookings
// Previous restriction code removed

// Handle search and filtering
$where_conditions = ["e.event_date >= CURDATE()"];
$search_term = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $where_conditions[] = "(e.event_name LIKE '%$search_term%' OR e.event_description LIKE '%$search_term%' OR e.event_location LIKE '%$search_term%')";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_filter = mysqli_real_escape_string($conn, $_GET['category']);
    $where_conditions[] = "e.event_category = '$category_filter'";
}

// Build the WHERE clause
$where_clause = implode(' AND ', $where_conditions);

// Get all upcoming live events with filters
$sql = "SELECT e.*, u.username as organizer_name, 
        (SELECT COUNT(*) FROM event_bookings WHERE event_id = e.id) as booked_seats 
        FROM live_events e 
        JOIN users u ON e.organizer_id = u.id 
        WHERE $where_clause
        ORDER BY e.event_date ASC, e.event_time ASC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Events</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        
        h1 {
            color: #6a11cb;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .filter-section {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .filter-btn {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .event-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .event-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 15px 20px;
        }
        
        .event-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }
        
        .event-organizer {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .event-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .event-info {
            margin-bottom: 15px;
        }
        
        .event-info p {
            margin: 8px 0;
            display: flex;
            align-items: center;
        }
        
        .event-info i {
            width: 25px;
            color: #6a11cb;
            margin-right: 10px;
        }
        
        .event-category {
            display: inline-block;
            background-color: #f0f0f0;
            color: #333;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .event-description {
            margin: 15px 0;
            color: #555;
            line-height: 1.5;
            flex-grow: 1;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        .event-description.expanded {
            -webkit-line-clamp: unset;
        }
        
        .read-more {
            color: #6a11cb;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            display: inline-block;
        }
        
        .event-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }
        
        .seats-info {
            font-size: 14px;
            color: #555;
        }
        
        .book-btn {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .book-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
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
        
        .download-ticket {
            margin-top: 10px;
        }
        
        .ticket-btn {
            display: inline-block;
            background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%);
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .ticket-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        
        .no-events {
            text-align: center;
            padding: 50px;
            color: #555;
            font-size: 18px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .event-price {
            font-weight: bold;
            color: #6a11cb;
            margin-top: 10px;
        }
        
        .event-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            display: block;
        }
        
        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <?php include 'navbar3.php'; ?>
    
    <div class="container">
        <h1>Upcoming Live Events</h1>
        
        <?php if (isset($booking_success)): ?>
            <div class="alert alert-success">
                <?php echo $booking_success; ?>
                <div class="download-ticket">
                    <a href="generate_ticket.php?booking_id=<?php echo $_SESSION['last_booking_id']; ?>&event_id=<?php echo $_SESSION['last_event_id']; ?>" class="ticket-btn" target="_blank">
                        <i class="fas fa-download"></i> Download Ticket
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($booking_error)): ?>
            <div class="alert alert-danger">
                <?php echo $booking_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-section">
            <form action="" method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="search">Search Events</label>
                    <input type="text" id="search" name="search" placeholder="Search by name, description or location" value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                
                <?php if (!empty($categories)): ?>
                <div class="filter-group">
                    <label for="category">Filter by Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="filter-group" style="flex: 0 0 auto;">
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-search"></i> Filter Events
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="events-grid">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="event-card">
                        <?php if (!empty($row['event_image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['event_image']); ?>" alt="<?php echo htmlspecialchars($row['event_name']); ?>" class="event-image">
                        <?php endif; ?>
                        
                        <div class="event-header">
                            <h3 class="event-title"><?php echo htmlspecialchars($row['event_name']); ?></h3>
                            <div class="event-organizer">Organized by: <?php echo htmlspecialchars($row['organizer_name']); ?></div>
                        </div>
                        
                        <div class="event-body">
                            <div class="event-info">
                                <p><i class="fas fa-calendar"></i> <?php echo date('F j, Y', strtotime($row['event_date'])); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($row['event_time'])); ?> (<?php echo $row['event_duration']; ?> minutes)</p>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['event_location']); ?></p>
                                
                                <?php if (!empty($row['event_category'])): ?>
                                    <p><i class="fas fa-tag"></i> <span class="event-category"><?php echo htmlspecialchars($row['event_category']); ?></span></p>
                                <?php endif; ?>
                                
                                <?php if (isset($row['ticket_price'])): ?>
                                    <p class="event-price">
                                        <i class="fas fa-ticket-alt"></i>
                                        <?php echo ($row['ticket_price'] > 0) ? '₹' . number_format($row['ticket_price'], 2) : 'Free Entry'; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="event-description" id="desc-<?php echo $row['id']; ?>">
                                <?php echo nl2br(htmlspecialchars($row['event_description'])); ?>
                            </div>
                            
                            <span class="read-more" onclick="toggleDescription(<?php echo $row['id']; ?>)">Read more</span>
                        </div>
                        
                        <div class="event-footer">
                            <div class="seats-info">
                                <strong><?php echo $row['booked_seats']; ?></strong> / <?php echo $row['max_attendees']; ?> seats booked
                            </div>
                            
                            <?php 
                            $available = $row['max_attendees'] - $row['booked_seats'];
                            if ($available > 0): 
                            ?>
                            <a href="booking.php?event_id=<?php echo $row['id']; ?>" class="book-btn">Book Now</a>
                            <?php else: ?>
                            <button type="button" class="book-btn" disabled>Fully Booked</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-times" style="font-size: 48px; color: #6a11cb; margin-bottom: 20px;"></i>
                <p>No upcoming events available at the moment.</p>
                <?php if (!empty($search_term) || isset($_GET['category'])): ?>
                    <p>Try adjusting your search filters or <a href="view_events.php" style="color: #6a11cb;">view all events</a>.</p>
                <?php else: ?>
                    <p>Please check back later or contact an event organizer.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function toggleDescription(id) {
            const desc = document.getElementById('desc-' + id);
            const readMore = desc.nextElementSibling;
            
            if (desc.classList.contains('expanded')) {
                desc.classList.remove('expanded');
                readMore.textContent = 'Read more';
            } else {
                desc.classList.add('expanded');
                readMore.textContent = 'Read less';
            }
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
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