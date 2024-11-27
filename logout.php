<?php
require 'db.php'; // Include database connection
require 'User.php'; // Include the User class

// Create a User object and log the user out
$user = new User($conn);
$user->logout();

// Redirect to the homepage after logout
header("Location: index.php");
exit();
?>
