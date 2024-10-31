<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - KioskInventory</title>
    <link rel="stylesheet" href="resetpassword.css">

</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <form action="reset_password.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-primary">Reset Password</button>
            </div>
        </form>
        <div class="form-footer">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>
