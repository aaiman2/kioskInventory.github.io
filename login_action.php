<?php
// Include database connection
include 'db_connection.php';

// Start session
session_start();

// Get form data
$username = $_POST['username'];
$password = $_POST['password'];

// Prepare and bind SQL statement
$sql = $conn->prepare("SELECT * FROM user WHERE username = ?");
$sql->bind_param("s", $username);
$sql->execute();
$result = $sql->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    // Fetch user data
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Password is correct, log the user in
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");  // Redirect to dashboard
        exit();
    } else {
        // Password is incorrect
        echo "Invalid password. Please try again.";
    }
} else {
    // Username not found
    echo "Invalid username. Please try again.";
}

// Close connection
$conn->close();
?>
