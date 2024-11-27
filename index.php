<?php
session_start();
require 'db.php'; // Include the database connection
require 'includes/Blog.php'; // Include the Blog class

$blog = new Blog($conn);
$blogs = $blog->getAllBlogs();

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Space Cat Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

  <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="uploads/hi.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
            Space Cat Blog
        </a>
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
                    <?php if ($_SESSION['user_role'] === 'admin'): ?> <!-- Check if user is admin -->
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Admin</a> <!-- Admin Button -->
                        </li>
                    <?php endif; ?>
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
        <h1 class="text-center">iPhone Blog</h1>

        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-6 offset-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search blogs...">
            </div>
        </div>

        <!-- Blog List -->
        <div id="blog-list" class="row">
            <?php if ($blogs->num_rows > 0): ?>
                <?php while ($blog = $blogs->fetch_assoc()): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="<?= $blog['image'] ?>" class="card-img-top" alt="Blog Image">
                            <div class="card-body">
                                <h5 class="card-title"><?= $blog['title'] ?></h5>
                                <p class="card-text"><?= substr($blog['content'], 0, 100) ?>...</p>
                                <a href="view.php?id=<?= $blog['id'] ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">No blogs found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>

        // AJAX Search Function
        $(document).ready(function() {
        const defaultContent = $('#blog-list').html(); // Store the default content

        $('#search-input').on('input', function() {
            var query = $(this).val();

            if (query.length > 0) {
                $.ajax({
                    url: 'search.php',
                    type: 'POST',
                    data: { search_query: query },
                    success: function(data) {
                        $('#blog-list').html(data);
                    }
                });
            } else {
                // If the input is empty, return to the default content
                $('#blog-list').html(defaultContent);
            }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
