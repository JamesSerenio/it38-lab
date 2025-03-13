<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location:./index.php");
    exit;
}

// Database connection
require_once "config.php"; // Include your database connection file

// Prepare an insert statement
$sql = "INSERT INTO attendance (user_id, username, attendance_date, attendance_time) VALUES (:user_id, :username, :attendance_date, :attendance_time)";

try {
    // Prepare the statement
    $stmt = $pdo->prepare($sql);

    // Set parameters
    $param_user_id = $_SESSION["id"]; // Assuming user ID is stored in session
    $param_username = $_SESSION["username"]; // Assuming username is stored in session
    $param_date = date('Y-m-d'); // Today's date
    $param_time = date('Y-m-d H:i:s'); // Current date and time

    // Bind parameters
    $stmt->bindParam(':user_id', $param_user_id);
    $stmt->bindParam(':username', $param_username);
    $stmt->bindParam(':attendance_date', $param_date);
    $stmt->bindParam(':attendance_time', $param_time);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        // Redirect to user page with success message
        header("location: ../user/home.php?status=success");
        exit;
    } else {
        // Log error and show user-friendly message
        error_log("Database error: " . $stmt->errorInfo()[2]); // Log the error for debugging
        header("location: ../user/home.php?status=error");
        exit;
    }
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Prepare error: " . $e->getMessage()); // Log the error for debugging
    header("location: ../user/home.php?status=error");
    exit;
}

// Close the connection
$pdo = null;
?>