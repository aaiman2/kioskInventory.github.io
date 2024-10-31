<?php
include 'db_connection.php';

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Check if all required fields are set
if (isset($data['id'], $data['productName'], $data['quantity'], $data['price'])) {
    $id = $data['id'];
    $productName = $data['productName'];
    $quantity = $data['quantity'];
    $price = $data['price'];

    // Prepare the SQL statement for updating the product
    $sql = "UPDATE products SET productName=?, quantity=?, price=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidi", $productName, $quantity, $price, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

$conn->close();
?>
