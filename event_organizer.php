<?php
session_start();
include 'db.php';

// Ensure user is logged in and is an event organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    die("You must be logged in as an event organizer to manage bookings.");
}

$sql = "SELECT * FROM bookings";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            margin: 20px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        thead th, tbody td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f1f1f1;
        }

        button {
            padding: 8px 12px;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }

        .approve-button {
            background-color: #28a745;
        }
        .approve-button:hover {
            background-color: #218838;
        }

        .reschedule-button {
            background-color: #ffc107;
            color: black;
        }
        .reschedule-button:hover {
            background-color: #e0a800;
        }

        .cancel-button {
            background-color: #dc3545;
        }
        .cancel-button:hover {
            background-color: #c82333;
        }

        span {
            font-weight: bold;
        }
    </style>
    <script>
        function handleAction(bookingId, action) {
            let message = "";
            let url = "manage_booking.php";
            
            if (action === "cancel") {
                message = prompt("Enter reason for cancellation:");
                if (!message) return;
            } else if (action === "reschedule") {
                message = prompt("Enter new date and time:");
                if (!message) return;
            }

            fetch(url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "id=" + bookingId + "&action=" + action + "&message=" + encodeURIComponent(message)
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
    </script>
</head>
<body>
    <h1>Manage All Bookings</h1>
    <table border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>User ID</th>
                <th>Event Head</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Venue</th>
                <th>Service Category</th>
                <th>Status</th>
                <th>Vendor</th>
                <th>Assigned Staff</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $serial = 1;
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $serial++ ?></td>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['event_head']) ?></td>
                    <td><?= htmlspecialchars($row['event_start_date']) ?></td>
                    <td><?= htmlspecialchars($row['event_end_date']) ?></td>
                    <td><?= htmlspecialchars($row['venue']) ?></td>
                    <td><?= htmlspecialchars($row['service_category_id']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['vendor'] ?? 'Not Assigned') ?></td>
                    <td><?= htmlspecialchars($row['assigned_staff'] ?? 'Not Assigned') ?></td>
                    <td>
                        <?php if ($row['status'] == 'Pending'): ?>
                            <button class="approve-button" onclick="handleAction(<?= $row['id'] ?>, 'approve')">Approve</button>
                        <?php endif; ?>
                        <button class="reschedule-button" onclick="handleAction(<?= $row['id'] ?>, 'reschedule')">Reschedule</button>
                        <button class="cancel-button" onclick="handleAction(<?= $row['id'] ?>, 'cancel')">Cancel</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
