<?php
include 'db.php'; // Database connection

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    
    $stmt = $conn->prepare("SELECT id, service_name, amount FROM services WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<div>
            <span>" . $row['service_name'] . " - Rs" . $row['amount'] . "</span>
            <button type='button' onclick='addService(" . $row['id'] . ", \"" . $row['service_name'] . "\", " . $row['amount'] . ")'>+</button>
        </div>";
    }
    
    $stmt->close();
    $conn->close();
}
?>
