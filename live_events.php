<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = 'localhost';
$dbname = 'event_management';
$username = 'root';
$password = ''; // XAMPP default

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle event booking if form is submitted
if (isset($_POST['book_event']) && isset($_SESSION['user_id'])) {
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];
    
    // Check if user already booked this event
    $checkQuery = "SELECT * FROM event_bookings WHERE event_id = ? AND user_id = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$event_id, $user_id]);
    
    if ($checkStmt->rowCount() > 0) {
        $bookingMessage = "You have already booked this event!";
        $bookingStatus = "warning";
    } else {
        // Check if event has reached maximum capacity
        $capacityQuery = "SELECT max_attendees, 
                         (SELECT COUNT(*) FROM event_bookings WHERE event_id = ?) AS current_bookings 
                         FROM events WHERE id = ?";
        $capacityStmt = $pdo->prepare($capacityQuery);
        $capacityStmt->execute([$event_id, $event_id]);
        $capacityData = $capacityStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($capacityData['current_bookings'] >= $capacityData['max_attendees']) {
            $bookingMessage = "Sorry, this event has reached maximum capacity!";
            $bookingStatus = "error";
        } else {
            // Insert booking
            $bookQuery = "INSERT INTO event_bookings (event_id, user_id, booking_date) VALUES (?, ?, NOW())";
            $bookStmt = $pdo->prepare($bookQuery);
            
            try {
                $bookStmt->execute([$event_id, $user_id]);
                $bookingMessage = "Event booked successfully!";
                $bookingStatus = "success";
            } catch (PDOException $e) {
                $bookingMessage = "Error booking event: " . $e->getMessage();
                $bookingStatus = "error";
            }
        }
    }
}

// Get filter parameters
$category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

// Build query based on filters
$query = "SELECT e.*, o.name as organizer_name,
          (SELECT COUNT(*) FROM event_bookings WHERE event_id = e.id) AS booked_seats
          FROM events e
          JOIN organizers o ON e.organizer_id = o.id
          WHERE e.status = 'live' AND e.start_time >= NOW()";

$params = [];

if (!empty($category)) {
    $query .= " AND e.category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $query .= " AND (e.event_name LIKE ? OR e.description LIKE ? OR e.location LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY e.start_time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$liveEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categoryQuery = "SELECT DISTINCT category FROM events WHERE status = 'live' ORDER BY category";
$categoryStmt = $pdo->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            margin: 0;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h1 {
            text-align: center;
            color: #6a11cb;
            margin-bottom: 30px;
            font-size: 2.5rem;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
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

        .filter-button {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .filter-button:hover {
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .event-image {
            height: 200px;
            background-color: #f0f0f0;
            background-size: cover;
            background-position: center;
        }

        .event-content {
            padding: 20px;
        }

        .event-title {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.5rem;
        }

        .event-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-item i {
            color: #6a11cb;
            width: 20px;
            text-align: center;
        }

        .event-description {
            font-size: 16px;
            color: #333;
            line-height: 1.5;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .seats-info {
            font-size: 14px;
            color: #666;
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

        .no-events {
            text-align: center;
            color: #666;
            margin-top: 50px;
            font-size: 18px;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
                gap: 10px;
            }
            
            .filter-group {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h1>Live Events</h1>
        
        <?php if (isset($bookingMessage)): ?>
            <div class="alert alert-<?php echo $bookingStatus; ?>">
                <?php echo $bookingMessage; ?>
            </div>
        <?php endif; ?>
        
        <div class="filters">
            <form action="" method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="category">Filter by Category</label>
                    <select name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="search">Search Events</label>
                    <input type="text" name="search" id="search" placeholder="Search by name, description or location" 
                           value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                
                <button type="submit" class="filter-button">Apply Filters</button>
            </form>
        </div>

        <?php if (!empty($liveEvents)): ?>
            <div class="events-grid">
                <?php foreach ($liveEvents as $event): ?>
                    <div class="event-card">
                        <div class="event-image" style="background-image: url('<?php echo !empty($event['image_url']) ? htmlspecialchars($event['image_url']) : 'images/default-event.jpg'; ?>')"></div>
                        <div class="event-content">
                            <h2 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h2>
                            
                            <div class="event-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <span>Organized by: <?php echo htmlspecialchars($event['organizer_name']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>
                                        <?php echo isset($event['start_time']) ? date("d M Y, H:i", strtotime($event['start_time'])) : 'N/A'; ?>
                                    </span>
                                </div>
                                <?php if (isset($event['category']) && !empty($event['category'])): ?>
                                <div class="meta-item">
                                    <i class="fas fa-tag"></i>
                                    <span><?php echo htmlspecialchars($event['category']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="event-description">
                                <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                            </div>
                            
                            <div class="event-footer">
                                <div class="seats-info">
                                    <strong><?php echo $event['booked_seats']; ?></strong> / 
                                    <?php echo isset($event['max_attendees']) ? $event['max_attendees'] : 'Unlimited'; ?> seats booked
                                </div>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" name="book_event" class="book-btn" 
                                        <?php echo (isset($event['max_attendees']) && $event['booked_seats'] >= $event['max_attendees']) ? 'disabled' : ''; ?>>
                                        <?php echo (isset($event['max_attendees']) && $event['booked_seats'] >= $event['max_attendees']) ? 'Fully Booked' : 'Book Now'; ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                <a href="login.php" class="book-btn">Login to Book</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-events">
                <i class="fas fa-calendar-times" style="font-size: 48px; color: #6a11cb; margin-bottom: 20px; display: block;"></i>
                <p>No live events found matching your criteria.</p>
                <?php if (!empty($category) || !empty($search)): ?>
                    <p>Try adjusting your filters or <a href="live_events.php" style="color: #6a11cb;">view all events</a>.</p>
                <?php else: ?>
                    <p>Check back later for upcoming events.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add animation to alert messages
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 1s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 1000);
                }, 5000);
            });
        });
    </script>
</body>
</html>
