<?php
session_start();
require 'db.php'; // Include the database connection
require 'includes/Blog.php'; // Include the Blog class

$blog = new Blog($conn);

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$blog_id = $_GET['id'];
$blog_data = $blog->getBlogById($blog_id)->fetch_assoc();

if (!$blog_data) {
    die('Blog post not found.');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($blog_data['title']) ?></title>
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

<!-- Blog Post Content -->
<div class="container mt-5">
    <h1><?= htmlspecialchars($blog_data['title']) ?></h1>
    <img src="<?= htmlspecialchars($blog_data['image']) ?>" class="img-fluid mb-4" alt="Blog Image">
    <p><?= nl2br($blog_data['content']) ?></p>

        <!-- Like Button -->
    <span id="like-count"><?= $blog->getLikesCount($blog_id) ?> Likes</span>
    <?php if ($is_logged_in): ?>
        <button id="like-btn" class="btn btn-primary" data-liked="<?= $blog->isLikedByUser($blog_id, $_SESSION['user_id']) ? 'true' : 'false' ?>">
            <?= $blog->isLikedByUser($blog_id, $_SESSION['user_id']) ? 'Unlike' : 'Like' ?>
        </button>
    <?php endif; ?>


    <!-- Share Button -->
    <button class="btn btn-success" onclick="copyToClipboard()">Copy Link</button>
    <a class="btn btn-info" href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://yourdomain.com/view.php?id=' . $blog_id) ?>" target="_blank">Share on Facebook</a>
<!-- Comments Section -->
<div class="mt-5" id="comments-section">
    <!-- Comments will be loaded here via AJAX -->
</div>

<!-- Comment Form -->
<?php if ($is_logged_in): ?>
    <form id="comment-form">
        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
        <input type="hidden" name="blog_id" value="<?= $blog_id ?>">
        <div class="mb-3">
            <textarea name="comment" class="form-control" placeholder="Add your comment..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Comment</button>
    </form>
<?php else: ?>
    <p>Please <a href="login.php">log in</a> to add a comment.</p>
<?php endif; ?>


</div>

<script>
$(document).ready(function() {
    // Load comments
    function loadComments() {
        $.get('comments.php', { blog_id: <?= $blog_id ?> }, function(data) {
            $('#comments-section').html(data);
        });
    }
    loadComments(); // Initially load comments

    // Add a new comment
    $('#comment-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'comments.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response === 'success') {
                    alert('Comment added successfully!');
                    loadComments();
                } else {
                    alert('Error adding comment.');
                }
            }
        });
    });

    // Edit a comment
$(document).on('click', '.edit-comment', function() {
    let commentDiv = $(this).closest('.comment');
    let commentId = commentDiv.data('comment-id');
    let commentText = commentDiv.find('.comment-text').text();
    

});

// Save the edited comment
$(document).on('click', '.edit-comment', function() {
    let commentId = $(this).closest('.comment').data('comment-id');
    let newComment = prompt('Edit your comment:');
    if (newComment) {
        $.ajax({
            url: 'comments.php',
            method: 'POST',
            data: {
                id: commentId,          // This is the comment ID
                comment: newComment,     // This is the new comment text
                edit_comment: true,      // This indicates the action to perform
                user_id: <?= $_SESSION['user_id'] ?>,  // Include user_id in the request
                blog_id: <?= $blog_id ?>  // Include blog_id in the request
            },
            success: function(response) {
                if (response === 'success') {
                    alert('Comment updated successfully!');
                    loadComments();
                } else {
                    alert('Error updating comment.');
                }
            }
        });
    }
});

    // Delete a comment
    $(document).on('click', '.delete-comment', function() {
        if (confirm('Are you sure you want to delete this comment?')) {
            let commentId = $(this).closest('.comment').data('comment-id');
            $.ajax({
                url: 'comments.php',
                method: 'POST',
                data: {
                    id: commentId,
                    delete_comment: true
                },
                success: function(response) {
                    if (response === 'success') {
                        alert('Comment deleted successfully!');
                        loadComments();
                    } else {
                        alert('Error deleting comment.');
                    }
                }
            });
        }
    });
});


//liked function
$(document).ready(function() {
    // Check if the like button exists (it only exists for logged-in users)
    if ($('#like-btn').length) {
        $('#like-btn').on('click', function() {
            const blogId = <?= $blog_id ?>;
            const userId = <?= $_SESSION['user_id'] ?>;
            let liked = $(this).data('liked') === true;  // Check if it's currently liked

            // Optimistically update the button UI before waiting for a server response
            $(this).text(liked ? 'Like' : 'Unlike').data('liked', !liked);
            const currentCount = parseInt($('#like-count').text());
            $('#like-count').text(currentCount + (liked ? -1 : 1) + ' Likes');

            // Send AJAX request to like or unlike
            $.ajax({
                url: 'like.php',
                method: 'POST',
                data: {
                    blog_id: blogId,
                    user_id: userId,
                    action: liked ? 'unlike' : 'like'
                },
                success: function(response) {
                    if (response !== 'liked' && response !== 'unliked') {
                        // If there's an error, revert the UI change
                        alert('Unexpected error. Please try again.');
                        // Revert UI changes back to the original state
                        $('#like-btn').text(liked ? 'Unlike' : 'Like').data('liked', liked);
                        $('#like-count').text(currentCount + ' Likes');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // If there's an AJAX error, revert the UI change
                    alert('Error occurred while processing your request.');
                    // Revert UI changes back to the original state
                    $('#like-btn').text(liked ? 'Unlike' : 'Like').data('liked', liked);
                    $('#like-count').text(currentCount + ' Likes');
                }
            });
        });
    }
});


// Function to copy blog link to clipboard
function copyToClipboard() {
    const tempInput = document.createElement('input');
    tempInput.value = window.location.href;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    alert('Link copied to clipboard!');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
