<?php
// Start the session
session_start();

// Destroy all session data
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <link rel="stylesheet" href="logout.css">
    <script>
        // Redirect to the login page after 3 seconds
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000); // 3000 ms = 3 seconds
    </script>
</head>
<body>
    <h2>You have been logged out successfully!</h2>
    <p>Redirecting to the login page in 3 seconds...</p>
    <p>If you are not redirected, <a href="login.php">click here</a>.</p>
</body>
</html>
