<?php
// Enable error reporting to debug issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php'; // Ensure this path is correct

// Set the content type to JSON
header('Content-Type: application/json');

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Check if the ID is set
if (isset($data['id'])) {
    $id = $data['id'];

    // Prepare and execute the SQL delete query
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        // Return a success response
        echo json_encode(['success' => true]);
    } else {
        // Return an error response if the delete failed
        echo json_encode(['success' => false, 'message' => 'Failed to delete product.']);
    }

    $stmt->close();
} else {
    // Return an error if ID is not set
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}

$conn->close();
