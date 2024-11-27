<?php
session_start();
require 'db.php'; // Include the database connection
require 'includes/Blog.php'; // Include the Blog class

$blog = new Blog($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blog_id = $_POST['blog_id'];
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action == 'like') {
        if ($blog->likePost($blog_id, $user_id)) {
            echo 'liked';
        } else {
            echo 'error';
        }
    } elseif ($action == 'unlike') {
        if ($blog->unlikePost($blog_id, $user_id)) {
            echo 'unliked';
        } else {
            echo 'error';
        }
    }
}
?>