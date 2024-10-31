<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Pro</title>
    <link rel="stylesheet" href="login.css">

</head>
<body>
    <div class="login-container">
        <h2>Login to Kiosk Inventory</h2>
        <form action="login_action.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-primary">Login</button>
            </div>
        </form>
        <div class="form-footer">
            <button onclick="window.location.href='register.php'" class="btn-primary">Register</button>
            <br>
            <br><a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</body>
</html>
