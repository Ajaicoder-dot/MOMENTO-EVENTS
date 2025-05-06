<?php
include 'db.php'; // Database connection

if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];
    
    $fields = '';

    switch ($category_id) {
        case 1: // Birthday Party
            $fields .= '<label for="birthday_person">Birthday Person Name:</label>';
            $fields .= '<input type="text" name="additional_details[birthday_person]" required><br>';

            $fields .= '<label for="age_turning">Age Turning:</label>';
            $fields .= '<input type="number" name="additional_details[age_turning]" required><br>';

            $fields .= '<label for="birthday_theme">Birthday Theme:</label>';
            $fields .= '<select name="additional_details[birthday_theme]" required>';
            $fields .= '<option value="">Select a Theme</option>';
            $fields .= '<option value="Traditional">Traditional</option>';
            $fields .= '<option value="Modern">Modern</option>';
            $fields .= '<option value="Superhero">Superhero</option>';
            $fields .= '<option value="Cartoon">Cartoon</option>';
            $fields .= '<option value="Other">Other</option>';
            $fields .= '</select><br>';

            $fields .= '<label for="num_invitees">Number of Invitees:</label>';
            $fields .= '<input type="number" name="additional_details[num_invitees]" required><br>';

            $fields .= '<label for="birthday_image">Upload Birthday Person Image:</label>';
            $fields .= '<input type="file" name="birthday_image" accept="image/*" required><br>';

            $fields .= '<label for="birthday_invite">Upload Birthday Invitation:</label>';
            $fields .= '<input type="file" name="birthday_invite" accept="image/*" required><br>';
            break;

        case 2: // Corporate Events
            $fields .= '<label for="company_name">Company Name:</label>';
            $fields .= '<input type="text" name="additional_details[company_name]" required><br>';

            $fields .= '<label for="event_type">Event Type:</label>';
            $fields .= '<select name="additional_details[event_type]" required>';
            $fields .= '<option value="">Select an Event Type</option>';
            $fields .= '<option value="Conference">Conference</option>';
            $fields .= '<option value="Seminar">Seminar</option>';
            $fields .= '<option value="Workshop">Workshop</option>';
            $fields .= '<option value="Team Building">Team Building</option>';
            $fields .= '<option value="Product Launch">Product Launch</option>';
            $fields .= '<option value="Networking Event">Networking Event</option>';
            $fields .= '<option value="Other">Other</option>';
            $fields .= '</select><br>';

            $fields .= '<label for="agenda">Event Agenda:</label>';
            $fields .= '<textarea name="additional_details[agenda]" required></textarea><br>';
            break;

        case 3: // Exhibitions
            $fields .= '<label for="exhibition_theme">Exhibition Theme:</label>';
            $fields .= '<input type="text" name="additional_details[theme]" required><br>';

            $fields .= '<label for="stall_size">Stall Dimensions Required:</label>';
            $fields .= '<input type="text" name="additional_details[stall_size]" required><br>';

            $fields .= '<label for="requirements">Special Requirements:</label>';
            $fields .= '<textarea name="additional_details[requirements]"></textarea><br>';
            break;

        case 4: // House Warming
            $fields .= '<label for="home_address">New Home Address:</label>';
            $fields .= '<input type="text" name="additional_details[home_address]" required><br>';

            $fields .= '<label for="rituals_needed">Housewarming Rituals Needed? (Yes/No):</label>';
            $fields .= '<input type="text" name="additional_details[rituals_needed]" required><br>';

            $fields .= '<label for="guest_list">Guest List:</label>';
            $fields .= '<textarea name="additional_details[guest_list]" required></textarea><br>';

            $fields .= '<label for="catering_requirements">Catering Requirements:</label>';
            $fields .= '<textarea name="additional_details[catering_requirements]"></textarea><br>';
            break;

        case 5: // Marriage Function
            $fields .= '<label for="wedding_theme">Wedding Theme:</label>';
            $fields .= '<input type="text" name="additional_details[wedding_theme]" required><br>';

            $fields .= '<label for="groom_name">Groom Name:</label>';
            $fields .= '<input type="text" name="additional_details[groom_name]" required><br>';

            $fields .= '<label for="bride_name">Bride Name:</label>';
            $fields .= '<input type="text" name="additional_details[bride_name]" required><br>';

            $fields .= '<label for="guest_list">Guest List:</label>';
            $fields .= '<textarea name="additional_details[guest_list]" required></textarea><br>';

            $fields .= '<label for="catering_menu">Catering Preferences:</label>';
            $fields .= '<textarea name="additional_details[catering_menu]" required></textarea><br>';
            break;

        case 6: // Reception
            $fields .= '<label for="reception_theme">Reception Theme:</label>';
            $fields .= '<input type="text" name="additional_details[reception_theme]"><br>';

            $fields .= '<label for="guest_list">Guest List:</label>';
            $fields .= '<textarea name="additional_details[guest_list]"></textarea><br>';

            $fields .= '<label for="performers">Performers / Special Guests:</label>';
            $fields .= '<input type="text" name="additional_details[performers]"><br>';
            break;
    }

    // Additional Service Field
    $fields .= '<label for="custom_service">Other Service:</label>';
    $fields .= '<input type="text" name="additional_details[custom_service]" placeholder="Enter any additional service needed and any quries "><br>';

    echo $fields;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<link rel="stylesheet" href="style.css">
<body>
    
</body>
</html>
