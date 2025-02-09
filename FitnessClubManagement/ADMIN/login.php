<?php
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Define valid login credentials (for demo purposes, ideally you fetch from a database)
    $valid_username = "Jonathan";  // Username
    $valid_password = "admin123";  // Password

    // Capture the form input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the credentials match
    if ($username == $valid_username && $password == $valid_password) {
        // Set session variables for the logged-in user
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'Administrator';  // You can change this to fetch from a database

        // Redirect to the dashboard page
        header("Location: index.php");
        exit();
    } else {
        $error_msg = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="loginStyle.css">
</head>
<body>

    <div class="login-container">
        <div class="login-form">
            <div class="logo">
                <img src="img/01.png" alt="Admin Logo">
            </div>
            <h2>Login</h2>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required placeholder="Enter your password">
                </div>

                <button type="submit" class="login-btn">Login</button>
                <?php if (isset($error_msg)) { echo "<p class='error-msg'>$error_msg</p>"; } ?>
            </form>
        </div>
    </div>

</body>
</html>
