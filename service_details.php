<?php
include 'db.php'; // Include database connection

// Get the category ID from URL
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

if ($category_id == 0) {
    die("Invalid category ID.");
}

// Fetch category details
$category_result = $conn->query("SELECT category_name FROM service_categories WHERE id = $category_id");

if ($category_result->num_rows == 0) {
    die("Category not found.");
}

$category = $category_result->fetch_assoc();

// Fetch services under the selected category
$services_result = $conn->query("SELECT * FROM services WHERE category_id = $category_id");

// Fetch images dynamically based on category
$event_images = [];
$images_result = $conn->query("SELECT image_url FROM services WHERE category_id = $category_id LIMIT 6");
while ($row = $images_result->fetch_assoc()) {
    $event_images[] = $row['image_url'];
}

// If fewer than 6 images, fill with default placeholders
while (count($event_images) < 6) {
    $event_images[] = "image/5.jpg";
}

// Define unique headings & descriptions per category
$category_content = [
    1 => [
        "title" => "🎂 Celebrate Birthdays with Joy & Laughter!",
        "description" => "Make birthdays extra special with customized decorations, delicious cakes, and unforgettable moments."
    ],
    2 => [
        "description" => "From seamless planning to engaging entertainment, we create professional and memorable corporate gatherings."
    ],
    3 => [
        "title" => "🏢 Showcase Your Brand with Impact!",
        "description" => "Stand out with stunning booth designs, dynamic presentations, and engaging audience experiences."
    ],
    4 => [
        "title" => "🏡 Celebrate Your New Home with Love & Warmth!",
        "description" => "Welcome guests with elegant decor, delicious food, and a cozy ambiance for a memorable housewarming event."
    ],
    5 => [
        "title" => "💍 Say 'I Do' to a Perfect Wedding!",
        "description" => "From breathtaking decor to seamless coordination, we make your big day truly magical and stress-free."
    ],
    6 => [
        "title" => "🎊 Celebrate Your New Beginning in Style!",
        "description" => "A grand reception with dazzling decor, delightful cuisine, and entertainment that keeps the celebration alive."
    ],
];

// Set dynamic title & description based on category ID
$title = $category_content[$category_id]['title'] ?? "🎉 Celebrate with Us!.";

$description = $category_content[$category_id]['description'] ?? "We provide premium event services to make your special moments unforgettable.";

?>










<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['category_name']) ?> Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> 
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .content-section {
            padding: 50px 0;
        }
        .zigzag-container {
            max-width: 900px;
            margin: auto;
        }
        .zigzag {
            display: flex;
            align-items: center;
            margin-bottom: 50px;
        }
        .zigzag:nth-child(even) {
            flex-direction: row-reverse;
        }
        .zigzag img {
            width: 45%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 3px 3px 10px rgba(0, 0, 0, 0.1);
        }
        .zigzag .text {
            width: 50%;
            padding: 20px;
        }     .footer {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            position: relative;
            width: 100%;}
            .btn {
                background: linear-gradient(45deg, #6a11cb, #2575fc);
            }
    </style>
</head>
<body>

<?php include 'navbar3.php'; ?>

<!-- INTRODUCTION SECTION -->
<div class="container content-section text-center">
    <h2 class="mb-3"><?= htmlspecialchars($title) ?></h2>
    <p class="lead"><?= htmlspecialchars($description) ?></p>
</div>

<!-- ZIGZAG IMAGE LAYOUT -->
<?php
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Define zigzag content for different categories

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Define zigzag content for six categories
$zigzag_content = [
    1 => [ // Birthday
        ["title" => "🎉 Let’s Make Memories!", "desc" => "From start to finish, we create magical moments that last forever.", "img" => "image/birthday.jpg"],
        ["title" => "🎂 Cake & Celebration!", "desc" => "Delicious treats and joyful vibes – because every event deserves sweetness!", "img" => "image/cake.jpg"],
        ["title" => "🎊 Grand Decorations!", "desc" => "Transform any venue into a breathtaking setup with our expert decorators.", "img" => "image/img3.jpg"],
        ["title" => "📸 Capture the Moment!", "desc" => "Professional photography to preserve your best memories in stunning detail.", "img" => "image/photo_video.jpg"],
        ["title" => "🎶 Music & Entertainment!", "desc" => "Exciting performances, DJs, and live music for an unforgettable experience.", "img" => "image/dj.jpg"],
        ["title" => "🎁 Gifts & Surprises!", "desc" => "Specially curated gifts and surprises to add a personal touch to your event.", "img" => "image/gift.jpg"]
    ],
    2 => [ // corporate events
        ["title" => "📢 Professional Event Management!", "desc" => "From conferences to product launches, we handle everything with precision.", "img" => "image/manage.jpg"],
        ["title" => "🎙️ Seamless AV & Tech Setup!", "desc" => "High-quality sound, lighting, and presentation setups for impactful events.", "img" => "image/av tech.jpg"],
        ["title" => "🍽️ Catering & Refreshments!", "desc" => "Customized menu options to suit every corporate gathering.", "img" => "image/catering.jpg"],
        ["title" => "📸 Event Coverage!", "desc" => "Capture keynote moments with professional photography and videography.", "img" => "image/event coverage.jpg"],
        ["title" => "🎭 Team-Building & Entertainment!", "desc" => "Engaging activities and entertainment to keep the team motivated.", "img" => "image/team.jpg"],
       
    ],
    3 => [ // Exhibition
        ["title" => "🏢 Stunning Booth Designs", "desc" => "Custom-built exhibition booths that attract visitors and showcase your brand.", "img" => "image/booth.jpg"],
        ["title" => "📢 Marketing & Promotions", "desc" => "Eye-catching branding, banners, and promotional materials to boost visibility.", "img" => "image/marketing.jpg"],
        ["title" => "🎥 Live Coverage", "desc" => "Professional photography and video streaming to reach a wider audience.", "img" => "image/live.jpg"],
        ["title" => "💬 On-Ground Engagement", "desc" => "Trained professionals for hosting, crowd management, and client engagement.", "img" => "image/on ground.jpg"],
        ["title" => "🍽️ VIP Hospitality!", "desc" => "Exclusive catering and lounge services for special guests and business partners.", "img" => "image/hospitality.jpg"],
       
    ],
    4 => [ // House warming
        ["title" => "🌿 Elegant Home Decor", "desc" => "Breathtaking floral arrangements and themed decor to welcome guests.", "img" => "image/home decor.jpg"],
        ["title" => "🍽️ Lavish Catering", "desc" => "A variety of delicious cuisines to make your housewarming special.", "img" => "image/catering.2.jpg"],
        ["title" => "📸 Cherish the Moments", "desc" => "Capture your special occasion with our professional photographers.", "img" => "image/moments.jpg"],
        ["title" => "🎶 Soothing Music! ", "desc" => "Instrumental music, live bands, and background scores for a relaxed vibe.", "img" => "image/music.jpg"],
        ["title" => "🎁 Unique Housewarming Gifts", "desc" => "Thoughtful return gifts for guests to remember the day.", "img" => "image/gift.jpg"],
       
    ],
    5 => [ // Marriage/wedding
        ["title" => "🎊 Dream Wedding Planning!", "desc" => "From invitations to decorations, we make your big day truly magical.", "img" => "image/wedding planner.jpg"],
        ["title" => "🌺 Spectacular Decor!", "desc" => "Gorgeous floral arrangements and elegant setups for a picture-perfect wedding.", "img" => "image/marriage decor.jpg"],
        ["title" => "📸 Timeless Memories! ", "desc" => "Pre-wedding shoots, cinematic videography, and candid photography.", "img" => "image/heart memories.jpg"],
        ["title" => "🍽️ Luxurious Catering!", "desc" => "A grand feast with customized menu options for all tastes and traditions.", "img" => "image/luxurious catering.jpg"],
        ["title" => "🎵 Mesmerizing Entertainment!", "desc" => "Live music, dance performances, and DJs to create the perfect ambiance.", "img" => "image/mesmerizing.jpg"],
      
    ],
    6 => [ // Reception
        ["title" => "🌟 Grand Entry Setup!", "desc" => "Dramatic entrances with fireworks, floral showers, and red carpets.", "img" => "image/grand entry.jpg"],
        ["title" => "🍽️ Exquisite Dining!", "desc" => "A diverse and lavish spread to delight your guests.", "img" => "image/exquisite dining.jpg"],
        ["title" => "📸 Cinematic Photography!", "desc" => "Capture every smile, dance, and toast in breathtaking detail.", "img" => "image/cinematic photography.jpg"],
        ["title" => "🎶 Live Music & Performances!", "desc" => "From soulful melodies to upbeat DJ mixes, we set the perfect mood.", "img" => "image/entertainment.jpg"],
        ["title" => "🎁 Exclusive Guest Gifting", "desc" => "Personalized keepsakes for your loved ones to take home.", "img" => "image/gift.jpg"],
       
    ]
];

// Default to an empty array if the category ID doesn't exist
$selected_zigzag_content = $zigzag_content[$category_id] ?? [];

?>

<!-- ZIGZAG IMAGE LAYOUT -->
<div class="zigzag-container">
    <?php foreach ($selected_zigzag_content as $index => $content): ?>
        <div class="zigzag">
            <img src="<?= htmlspecialchars($content['img']) ?>" alt="Event Image">
            <div class="text">
                <h4><?= htmlspecialchars($content['title']) ?></h4>
                <p><?= htmlspecialchars($content['desc']) ?></p>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- SERVICES LIST -->
<div class="container content-section">
    <h3 class="text-center">Our Services for <?= htmlspecialchars($category['category_name']) ?></h3>
    <div class="row justify-content-center">
        <?php if ($services_result->num_rows > 0): ?>
            <?php while ($service = $services_result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-3 shadow-lg">
                        <?php if (!empty($service['image_url'])): ?>
                            <img src="<?= htmlspecialchars($service['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($service['service_name']) ?>">
                        <?php else: ?>
                            <img src="images/default.jpg" class="card-img-top" alt="Default Image">
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($service['service_name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($service['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No services available for this category.</p>
        <?php endif; ?>
    </div>

    <!-- Single "Book Now" Button for the whole category -->
    <div class="text-center mt-4">
        <a href="bookings.php?category_id=<?= $category_id ?>" class="btn btn-success btn-lg">Book Now</a>
    </div>
</div>
