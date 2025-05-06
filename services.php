<?php
include 'db.php'; // Include database connection

// Fetch service categories
$result = $conn->query("SELECT * FROM service_categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Main Styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            text-align: center;
        }
        
        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .header-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .btn-view {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            border: none;
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(106, 17, 203, 0.3);
        }
        
        .btn-view:hover {
            background: linear-gradient(45deg, #5b0fb3, #1e68e0);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(106, 17, 203, 0.4);
        }
        
        .btn-view i {
            margin-left: 5px;
        }
        
        /* Category Grid */
        .category-grid {
            padding: 0 1rem;
        }
        
        .category-item {
            margin-bottom: 2rem;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            position: relative;
            width: 100%;
            border-radius: 20px 20px 0 0;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.6s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
        .delay-6 { animation-delay: 0.6s; }
    </style>
</head>
<body>

<?php include 'navbar3.php'; // Include the navbar ?>

<div class="page-header">
    <div class="container">
        <div class="header-content">
            <h1 class="header-title animate-fade-in">Our Services</h1>
            <p class="header-subtitle animate-fade-in delay-1">Discover our comprehensive range of services designed to make your event perfect</p>
        </div>
    </div>
</div>

<div class="container">
    <div class="row category-grid">
        <?php 
        $delay = 2;
        while ($row = $result->fetch_assoc()): 
        ?>
            <div class="col-md-4 category-item animate-fade-in delay-<?= $delay ?>">
                <div class="card h-100">
                    <?php if (!empty($row['image_url'])): ?>
                        <img src="<?= htmlspecialchars($row['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['category_name']) ?>">
                    <?php else: ?>
                        <img src="image/default.jpg" class="card-img-top" alt="Default Image">
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($row['category_name']) ?></h5>
                        <a href="service_details.php?category=<?= urlencode($row['id']) ?>" class="btn btn-view">
                            View Services <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php 
        $delay = $delay >= 6 ? 2 : $delay + 1;
        endwhile; 
        ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
