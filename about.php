<?php
session_start(); // Start the session

// Initialize the variable
$is_logged_in = isset($_SESSION['user_id']); // Check if the user is logged in
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="uploads/hi.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
            Space Cat Blog</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <?php if ($is_logged_in): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">About Us</h1>
        <p class="lead text-center">Welcome to our blog website dedicated to everything about iPhones. Here, we share the latest insights about the iPhone models and features.</p>

        <div class="row mt-5">
            <div class="col-md-4">
                <h3>Our Mission</h3>
                <p>Our mission is to provide in-depth information about iPhones to help users stay informed and make the best decisions when it comes to buying or using iPhone devices.</p>
            </div>
            <div class="col-md-4">
                <h3>What We Offer</h3>
                <p>We offer comprehensive blogs on iPhone details, detailed reviews, and comparisons between iPhone models to help our readers stay up to date with Apple’s innovations.</p>
            </div>
            <div class="col-md-4">
                <h3>Meet the Team</h3>
                <p>Our team is composed of tech enthusiasts who have a passion for Apple products, especially iPhones. We aim to share our knowledge and excitement with our readers.</p>
            </div>
        </div>
    </div>

    <footer class="bg-light text-center py-3 mt-5">
        <p>&copy; 2024 Blog Website. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>