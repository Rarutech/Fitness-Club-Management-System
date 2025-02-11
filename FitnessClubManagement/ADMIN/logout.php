<?php
session_start();  // Start session to access session variables
session_unset();  // Remove all session variables
session_destroy();  // Destroy the session

// Redirect to login page
header("Location: login.php");
exit();
?>
