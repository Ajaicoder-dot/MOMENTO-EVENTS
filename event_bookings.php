<?php
include 'db.php'; // Include database connection

$sql = "SELECT * FROM bookings"; // Fetch bookings
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Bookings</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<h2>Event Bookings</h2>

<table>
    <tr>
        <th>Booking Details</th>
        <th>Event Schedule</th>
        <th>Venue & Additional Details</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Decode JSON additional details
            $additionalDetails = json_decode($row['additional_details'], true);
            $additionalText = "";
            
            if (is_array($additionalDetails)) {
                foreach ($additionalDetails as $key => $value) {
                    $additionalText .= "<b>$key:</b> $value<br>";
                }
            } else {
                $additionalText = $row['additional_details']; // If not JSON, show raw data
            }

            echo "<tr>
                <td>
                    <b>ID:</b> " . $row['id'] . "<br>
                    <b>Event Head:</b> " . $row['event_head'] . "<br>
                    <b>Phone No:</b> " . $row['phone_no'] . "<br>
                    <b>Guest Email:</b> " . $row['guest_email'] . "
                </td>
                <td>
                    <b>Start Date:</b> " . $row['event_start_date'] . "<br>
                    <b>End Date:</b> " . $row['event_end_date'] . "<br>
                    <b>Start Time:</b> " . $row['event_start_time'] . "<br>
                    <b>End Time:</b> " . $row['event_end_time'] . "
                </td>
                <td>
                    <b>Venue:</b> " . $row['venue'] . "<br>
                    <b>Additional Details:</b> <br>" . $additionalText . "
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No bookings found</td></tr>";
    }
    ?>
</table>

</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
