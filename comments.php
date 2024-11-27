<?php
session_start();
require 'db.php'; // Include database connection



// Add new comment to the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_comment']) && isset($_POST['comment'])) {
    // Adding a new comment
    $user_id = $_POST['user_id'];
    $blog_id = $_POST['blog_id'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO comments (blog_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $blog_id, $user_id, $comment);
    $stmt->execute();
    $stmt->close();

    echo 'success';
    exit();
}

// Handle editing comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'], $_POST['id'], $_POST['comment'], $_POST['user_id'])) {
    $comment_id = $_POST['id'];
    $user_id = $_POST['user_id'];
    $new_comment = $_POST['comment'];

    // Edit comment
    $stmt = $conn->prepare("UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $new_comment, $comment_id, $user_id);
    
    if ($stmt->execute()) {
        echo 'success'; // Return success message
    } else {
        file_put_contents('error.log', "SQL Error: " . $stmt->error . "\n", FILE_APPEND);
        echo 'error'; // Return error message
    }

    $stmt->close();
    exit();
}

// Fetch comments (this part remains the same)
if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];
    $stmt = $conn->prepare("SELECT comments.id, comments.comment, comments.user_id, users.email FROM comments JOIN users ON comments.user_id = users.id WHERE comments.blog_id = ?");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo "<div class='comment' data-comment-id='" . $row['id'] . "'>";
        echo "<strong>" . htmlspecialchars($row['email']) . "</strong>: ";
        echo "<span class='comment-text'>" . htmlspecialchars($row['comment']) . "</span>";
        
        // Show Edit and Delete buttons for the logged-in user
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
            echo " <button class='edit-comment btn btn-secondary btn-sm'>Edit</button>";
            echo " <button class='delete-comment btn btn-danger btn-sm'>Delete</button>";
        }
        
        echo "</div>";
    }

    $stmt->close();
}

// Handle deleting comments
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['delete_comment'])) {
    $comment_id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $comment_id, $_SESSION['user_id']);
    $stmt->execute();
    echo $stmt->affected_rows > 0 ? 'success' : 'error';
    $stmt->close();
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'], $_POST['id'], $_POST['user_id'], $_POST['blog_id'])) {
    $comment_id = $_POST['id'];
    $user_id = $_POST['user_id'];  // Fetch the user_id from POST
    $blog_id = $_POST['blog_id'];  // Fetch the blog_id from POST

    if (isset($_POST['comment'])) {
        $new_comment = $_POST['comment'];

        // Ensure that the comment belongs to the logged-in user
        $stmt = $conn->prepare("UPDATE comments SET comment = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $new_comment, $comment_id, $user_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }

        $stmt->close();
        exit();
    }


    $stmt->bind_param("sii", $new_comment, $comment_id, $_SESSION['user_id']);
    
    if (!$stmt->execute()) {
        // Log the SQL execution error for debugging purposes
        file_put_contents('debug.log', "SQL Error: " . $stmt->error . "\n", FILE_APPEND);
        echo 'error_execute';
    } else {
        echo 'success';
    }
    
    $stmt->close();
    exit();
}

?>