<?php
// Include database connection
include 'db_connection.php';

// Handle form submission for adding a new sale
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $supplier_id = $_POST['supplier_id'];
    $quantity_sold = $_POST['quantity_sold'];
    $sale_date = $_POST['sale_date'];
    $commission_rate = $_POST['commission_rate'];


    // Insert the new sale into the database
    $sql = "INSERT INTO sales (product_id, supplier_id, quantity_sold, sale_date, commission_rate) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iiisd", $product_id, $supplier_id, $quantity_sold, $sale_date, $commission_rate);
        if ($stmt->execute()) {
            // Redirect to the same page with a success message
            header('Location: addSales.php?success=1');
            exit();
        } else {
            $error = "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sale - Inventory Pro</title>
    <link rel="stylesheet" href="addSales.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>KioskInventory</h1>
            <div class="user-info"></div>
        </header>
        <div class="dashboard-container">
                <div class="navbar">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="product.php">Product</a>
                    <a href="report.php">Report</a>
                    <a href="logout.php">Log Out</a>
                </div>
            </div>
        <main class="main-content">
            <section class="add-sale-form">
                <h2>Add New Sale</h2>

                <!-- Success Message -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                    <p style="color: green;">Sale added successfully!</p>
                <?php endif; ?>

                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <p style="color: red;"><?php echo $error; ?></p>
                <?php endif; ?>

                <form id="addSaleForm" action="addSales.php" method="POST">
                    <!-- Product Selection -->
                    <label for="productId">Product:</label>
                    <select id="productId" name="productId" required>
                        <option value="">-- Select Product --</option>
                        <?php
                        $result = $conn->query("SELECT id, productName FROM products");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['productName']}</option>";
                        }
                        ?>
                    </select>

                    <!-- Supplier Selection -->
                    <label for="supplierId">Supplier:</label>
                    <select id="supplierId" name="supplierId" required>
                        <option value="">-- Select Supplier --</option>
                        <?php
                        $supplierResult = $conn->query("SELECT id, supplierName FROM suppliers");
                        while ($row = $supplierResult->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['supplierName']}</option>";
                        }
                        ?>
                    </select>

                    <!-- Other Fields -->
                    <label for="quantitySold">Quantity Sold:</label>
                    <input type="number" id="quantitySold" name="quantitySold" required>

                    <label for="saleDate">Sale Date:</label>
                    <input type="date" id="saleDate" name="saleDate" required>

                    <label for="commissionRate">Commission Rate per Item (RM):</label>
                    <input type="number" id="commissionRate" name="commissionRate" step="0.01" required>

                    <button type="submit">Add Sale</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
