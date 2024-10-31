<?php
session_start();

// Initialize error messages and success message
$password_error = '';
$success_message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db_connection.php'; // Include database connection

    // Retrieve form data
    $username = $_POST['username'] ?? '';  // Use null coalescing to avoid undefined key warning
    $new_password = $_POST['new_password'] ?? '';

    // Validate the username
    $sql = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $sql->bind_param("s", $username);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        // User exists, hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $update_sql = $conn->prepare("UPDATE user SET password = ? WHERE username = ?");
        $update_sql->bind_param("ss", $hashed_password, $username);

        if ($update_sql->execute()) {
            // Success message
            $success_message = "You can now <a href='login.php'>login</a>.";
        } else {
            $password_error = "Failed to reset password. Please try again.";
        }
    } else {
        $password_error = "No account found with that username.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - KioskInventory</title>
    <link rel="stylesheet" href="resetpassword.css">
</head>
<body>
    <div class="reset-password-container">
        <h2>Reset Your Password</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                <div class="error"><?php echo $password_error; ?></div>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-primary">Reset Password</button>
            </div>
        </form>
        <?php if (!empty($success_message)): ?>
            <div class="success" id="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
    </div>

    <script>
        // JavaScript to show the success message if it exists
        document.addEventListener("DOMContentLoaded", function() {
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.display = 'block'; // Show the success message
            }
        });
    </script>
</body>
</html>