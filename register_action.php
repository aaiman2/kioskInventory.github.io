<?php
// Include database connection
include 'db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$user = $_POST['username'];
$phone = $_POST['phone'];
$pass = $_POST['password'];

// Validate phone number (allowing between 10 and 15 digits)
if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
    die("Error: Invalid phone number format. Please enter a number between 10 and 15 digits.");
}

// Hash the password
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

// Insert into database
$sql = "INSERT INTO user (username, password, phone_number) VALUES ('$user', '$hashed_password', '$phone')";

if ($conn->query($sql) === TRUE) {
    echo "Registration successful! You can now <a href='login.php'>login</a>.";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
