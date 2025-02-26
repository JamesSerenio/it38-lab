<?php
$servername = "localhost"; // Change if your database host is different
$username = "root";        // Your database username
$password = "";            // Your database password (empty if using XAMPP default)
$dbname = "it38c-2"; // Change to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
