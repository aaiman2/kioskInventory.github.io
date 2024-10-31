<?php
include 'db_connection.php';

// Get form data
$productId = $_POST['productId'];
$supplierId = $_POST['supplierId'];
$quantitySold = $_POST['quantitySold'];
$saleDate = $_POST['saleDate'];
$commissionRatePerItem = $_POST['commissionRate']; // Commission per item

// Calculate total commission (commission per item * quantity sold)
$totalCommission = $commissionRatePerItem * $quantitySold;

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO sales (product_id, supplier_id, quantity_sold, sale_date, commission_amount) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiisd", $productId, $supplierId, $quantitySold, $saleDate, $totalCommission);

// Execute the statement
if ($stmt->execute()) {
    // Redirect back to the dashboard after a successful sale entry
    header("Location: dashboard.php");
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
