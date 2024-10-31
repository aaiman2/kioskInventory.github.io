<?php
// Include database connection
include 'db_connection.php';

// Fetch all suppliers from the database
$supplierQuery = "SELECT supplierName FROM suppliers";
$result = $conn->query($supplierQuery);
$suppliers = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $suppliers[] = $row['supplierName'];
    }
}
?>

<form id="addProductForm" method="POST" action="addProduct.php">
    <label for="productName">Product Name:</label>
    <input type="text" id="productName" name="productName" required>

    <label for="quantity">Quantity in Stock:</label>
    <input type="number" id="quantity" name="quantity" required>

    <label for="price">Selling Price (RM):</label>
    <input type="text" id="price" name="price" required>

    <label for="supplier">Supplier:</label>
    <select id="supplier" name="supplier">
        <option value="">--Select Supplier--</option>
        <?php foreach ($suppliers as $existingSupplier): ?>
            <option value="<?= $existingSupplier; ?>"><?= $existingSupplier; ?></option>
        <?php endforeach; ?>
    </select>

    <label for="newSupplier">Or Enter New Supplier:</label>
    <input type="text" id="newSupplier" name="newSupplier" placeholder="New supplier name">

    <button type="submit">Add Product</button>
</form>
