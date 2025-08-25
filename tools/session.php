<?php
session_start();

// Set session timeout duration (1 minute)
$timeout_duration = 300;

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ../login.php'); // Redirect if not logged in
    exit();
}

// Check last activity and auto logout if idle for 1 minute
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset(); // Unset session variables
    session_destroy(); // Destroy session
    header("Location: ../tools/logout.php"); // Redirect to logout
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity timestamp
?>
