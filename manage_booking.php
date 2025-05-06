<?php
include 'db.php';
include 'navbar3.php';

// Function to safely parse JSON and convert to readable text
function parseJsonToText($jsonString) {
    // If it's not a valid JSON, return the original string
    $parsed = json_decode($jsonString, true);
    
    if ($parsed === null) {
        return $jsonString;
    }
    
    // Special handling for selected_services
    if (strpos($jsonString, '"service_name"') !== false || strpos($jsonString, '"name"') !== false) {
        $services = [];
        $index = 0;
        foreach ($parsed as $service) {
            if (isset($service['name']) && isset($service['amount'])) {
                $services[] = sprintf(
                    '<div class="service-item">%d: %s, %s</div>',
                    $index++,
                    htmlspecialchars($service['name']),
                    htmlspecialchars($service['amount'])
                );
            } elseif (isset($service['service_name']) && isset($service['service_price'])) {
                $services[] = sprintf(
                    '<div class="service-item">%d: %s, %s</div>',
                    $index++,
                    htmlspecialchars($service['service_name']),
                    htmlspecialchars($service['service_price'])
                );
            }
        }
        return implode('<br>', $services);
    }
    
    // If it's an associative array
    if (is_array($parsed) && !empty($parsed)) {
        $textOutput = [];
        
        // Handle different JSON structures
        foreach ($parsed as $key => $value) {
            // If value is an array, convert it to comma-separated string
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            
            // Format key-value pairs
            $textOutput[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
        }
        
        return implode('<br>', $textOutput);
    }
    
    // If it's a simple array
    if (is_array($parsed)) {
        return implode(', ', $parsed);
    }
    
    // Fallback
    return $jsonString;
}

// SQL query to fetch all records from the book table
$sql = "SELECT 
    id,
    event_head, 
    phone_no, 
    guest_email, 
    guest_address, 
    event_start_date, 
    event_end_date, 
    event_start_time, 
    event_end_time, 
    venue, 
    selected_services, 
    total_amount, 
    additional_details, 
    cancelled, 
    cancel_reason, 
    approval_status, 
    rejection_reason 
FROM book";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Management Dashboard</title>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-light: #a5b4fc;
            --primary-dark: #4f46e5;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --background-color: #f3f4f6;
            --card-background: #ffffff;
            --border-color: #e5e7eb;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            margin: 0;
            padding: 0;
            color: var(--text-primary);
            overflow-x: hidden;
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 2rem;
            animation: fadeIn 0.8s ease-in;
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5rem;
            color: #111827;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            text-align: center;
            position: relative;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        h1::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-light), var(--primary-dark));
            border-radius: 4px;
            animation: shimmer 3s infinite linear;
            background-size: 1000px 100%;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            animation: slideInRight 0.6s ease-out;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.9), rgba(249, 250, 251, 0.9));
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .top-buttons {
            display: flex;
            gap: 1rem;
        }

        .home-button, .booking-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .home-button::before, .booking-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
            z-index: -1;
        }

        .home-button:hover::before, .booking-button:hover::before {
            left: 100%;
        }

        .home-button:hover, .booking-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }

        .home-button:active, .booking-button:active {
            transform: translateY(1px);
        }

        .table-container {
            background: var(--card-background);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.4s ease;
            animation: fadeIn 1s ease-in;
            border: 1px solid rgba(229, 231, 235, 0.5);
            position: relative;
        }

        .table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-dark), var(--primary-light));
            z-index: 1;
        }

        .table-container:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
            transform: translateY(-8px);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0;
        }

        th {
            background: linear-gradient(to right, #f9fafb, #f3f4f6);
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            color: var(--text-primary);
            padding: 1.5rem 1rem;
            border-bottom: 2px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 10;
            transition: all 0.3s ease;
        }

        th:hover {
            background: linear-gradient(to right, #f3f4f6, #e5e7eb);
        }

        td {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
            transition: all 0.3s ease;
        }

        tr {
            transition: all 0.3s ease;
            position: relative;
        }

        tr:hover {
            transform: translateX(5px);
        }

        tr::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background-color: var(--primary-color);
            opacity: 0;
            transition: all 0.3s ease;
        }

        tr:hover::after {
            width: 5px;
            opacity: 1;
        }

        tr:hover td {
            background-color: #f9fafb;
        }

        .guest-info {
            display: grid;
            gap: 0.75rem;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label i {
            color: var(--primary-color);
            transition: transform 0.3s ease;
        }

        tr:hover .info-label i {
            transform: scale(1.2) rotate(5deg);
            color: var(--primary-dark);
        }

        .info-value {
            color: var(--text-primary);
            transition: color 0.3s ease;
            font-weight: 500;
        }

        tr:hover .info-value {
            color: var(--primary-dark);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: capitalize;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .status-badge:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-md);
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.75rem;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            box-shadow: var(--shadow-sm);
        }

        .service-item:hover {
            transform: translateX(8px);
            background-color: #f3f4f6;
            box-shadow: var(--shadow-md);
            border-left: 3px solid var(--primary-color);
        }

        tr.cancelled {
            background-color: rgba(254, 226, 226, 0.3);
        }

        tr.approved {
            background-color: rgba(240, 253, 244, 0.3);
        }

        tr.pending {
            background-color: rgba(255, 251, 235, 0.3);
        }

        .cancelled .status-badge {
            background-color: #fee2e2;
            color: var(--danger-color);
            border: 1px solid #fecaca;
        }

        .approved .status-badge {
            background-color: #dcfce7;
            color: var(--success-color);
            border: 1px solid #bbf7d0;
        }

        .pending .status-badge {
            background-color: #fef3c7;
            color: var(--warning-color);
            border: 1px solid #fde68a;
        }

        .amount {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        tr:hover .amount {
            transform: scale(1.1);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .services-list {
            display: grid;
            gap: 0.75rem;
        }

        .booking-id {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .service-name {
            font-weight: 500;
            color: var(--text-primary);
        }

        .service-price {
            font-weight: 600;
            color: var(--primary-color);
        }

        .pdf-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
            border: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .pdf-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.6s ease;
            z-index: -1;
        }

        .pdf-button:hover::before {
            left: 100%;
        }

        .pdf-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
            background: linear-gradient(135deg, #4f46e5, #4338ca);
        }

        .pdf-button:active {
            transform: translateY(1px);
        }

        .pdf-button i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .pdf-button:hover i {
            transform: scale(1.2);
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            animation: fadeIn 1s ease-in;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.9), rgba(249, 250, 251, 0.9));
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .empty-state i {
            font-size: 5rem;
            color: var(--primary-light);
            margin-bottom: 1.5rem;
            animation: float 3s infinite ease-in-out;
        }

        .empty-state p {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-weight: 500;
        }

        @media (max-width: 1024px) {
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 900px;
            }
            
            .dashboard-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.25rem;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            .top-buttons {
                width: 100%;
                justify-content: center;
            }
            
            .dashboard-container {
                padding: 1rem;
                margin: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header-container">
            <h1>View Booking Details</h1>
            <div class="top-buttons">
                <a href="homes.php" class="home-button">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="bookings.php" class="booking-button">
                    <i class="fas fa-calendar-plus"></i> New Booking
                </a>
            </div>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Guest Information</th>
                            <th>Event Schedule</th>
                            <th>Venue</th>
                            <th>Services & Payment</th>
                            <th>Additional Details</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 0; // Initialize counter for staggered animations
                        while($row = $result->fetch_assoc()): 
                        ?>
                            <tr class="<?php 
                                if ($row['cancelled'] == 1) echo 'cancelled';
                                elseif ($row['approval_status'] == 'approved') echo 'approved';
                                elseif ($row['approval_status'] == 'pending') echo 'pending';
                            ?>" style="animation: fadeIn <?php echo 0.3 + (0.1 * $i++); ?>s ease-out;">
                                <td>
                                    <div class="guest-info">
                                        <div>
                                            <span class="info-label"><i class="fas fa-user"></i> Name:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($row['event_head']); ?></span>
                                        </div>
                                        <div>
                                            <span class="info-label"><i class="fas fa-phone"></i> Phone:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($row['phone_no']); ?></span>
                                        </div>
                                        <div>
                                            <span class="info-label"><i class="fas fa-envelope"></i> Email:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($row['guest_email']); ?></span>
                                        </div>
                                        <div>
                                            <span class="info-label"><i class="fas fa-map-marker-alt"></i> Address:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($row['guest_address']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="guest-info">
                                        <div>
                                            <span class="info-label"><i class="fas fa-calendar-day"></i> Start:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($row['event_start_date'] . ' ' . $row['event_start_time']); ?></span>
                                        </div>
                                        <div>
                                            <span class="info-label"><i class="fas fa-calendar-check"></i> End:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($row['event_end_date'] . ' ' . $row['event_end_time']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="info-label"><i class="fas fa-map-pin"></i> Location:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($row['venue']); ?></span>
                                </td>
                                <td>
                                    <div class="services-list">
                                        <div>
                                            <span class="info-label"><i class="fas fa-concierge-bell"></i> Services:</span>
                                            <div class="info-value"><?php echo parseJsonToText($row['selected_services']); ?></div>
                                        </div>
                                        <div>
                                            <span class="info-label"><i class="fas fa-money-bill-wave"></i> Total:</span>
                                            <span class="amount">₹<?php echo htmlspecialchars($row['total_amount']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="info-label"><i class="fas fa-info-circle"></i> Details:</span>
                                    <div class="info-value"><?php echo parseJsonToText($row['additional_details']); ?></div>
                                </td>
                                <td>
                                    <div class="guest-info">
                                        <div>
                                            <span class="status-badge">
                                                <i class="fas fa-<?php 
                                                    if ($row['cancelled'] == 1) echo 'ban';
                                                    elseif ($row['approval_status'] == 'approved') echo 'check-circle';
                                                    elseif ($row['approval_status'] == 'pending') echo 'clock';
                                                    else echo 'question-circle';
                                                ?>"></i>
                                                <?php 
                                                    if ($row['cancelled'] == 1) echo 'Cancelled';
                                                    else echo htmlspecialchars($row['approval_status']); 
                                                ?>
                                            </span>
                                        </div>
                                        <?php if ($row['cancelled'] == 1 && !empty($row['cancel_reason'])): ?>
                                            <div>
                                                <span class="info-label"><i class="fas fa-times-circle"></i> Cancel Reason:</span>
                                                <span class="info-value"><?php echo htmlspecialchars($row['cancel_reason']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['rejection_reason'])): ?>
                                            <div>
                                                <span class="info-label"><i class="fas fa-exclamation-circle"></i> Rejection Reason:</span>
                                                <span class="info-value"><?php echo htmlspecialchars($row['rejection_reason']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="generate_pdf.php?id=<?php echo $row['id']; ?>" class="pdf-button">
                                            <i class="fas fa-file-pdf"></i> Download PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No bookings found in the system.</p>
                <a href="bookings.php" class="booking-button">
                    <i class="fas fa-plus-circle"></i> Create New Booking
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
include 'footer.php';
$conn->close();
?>