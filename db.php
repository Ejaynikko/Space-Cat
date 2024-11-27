<?php
$servername = "localhost"; // Your database server (usually localhost)
$username = "root"; // Your database username (default is 'root' for XAMPP)
$password = ""; // Your database password (default is empty for XAMPP)
$dbname = "blog_db"; // Replace this with the name of your database

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
