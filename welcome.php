<?php
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header ("Location: login.php");
    exit;
}
?>

<!-- Page Content -->
<div class="content" style="padding-bottom: 60px;"> <!-- Ensure content doesn't overlap -->
    <h1>Welcome to the Dashboard</h1>
    <p>This is the main content of the page.</p>
</div>

<?php include 'footer.php'; ?> <!-- Include Footer -->

