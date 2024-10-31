<?php
// Include the database connection file
include 'db_connection.php';

// Get the JSON input from the client-side
$data = json_decode(file_get_contents('php://input'), true);

// Check if all the required fields are available
if (isset($data['id'], $data['productName'], $data['quantity'], $data['price'], $data['supplier'])) {
    $id = $data['id'];
    $productName = $data['productName'];
    $quantity = $data['quantity'];
    $price = $data['price'];
    $supplier = $data['supplier'];

    // Prepare and execute the SQL update query
    $stmt = $conn->prepare("UPDATE products SET productName = ?, quantity = ?, price = ?, supplier = ? WHERE id = ?");
    $stmt->bind_param('sidsi', $productName, $quantity, $price, $supplier, $id);

    if ($stmt->execute()) {
        // Return a success response
        echo json_encode(['success' => true]);
    } else {
        // Return an error response
        echo json_encode(['success' => false, 'message' => 'Failed to update product.']);
    }
    
    $stmt->close();
} else {
    // If the necessary data is not provided, return an error
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}

// Close the database connection
$conn->close();
?>
