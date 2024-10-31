<?php
// Include the database connection file
include 'db_connection.php';

// Check if the connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize success flags and message variables
$successSale = false; 
$successProduct = false; 
$message = ""; // Variable to hold success/error messages

// Handle form submission for adding a new sale and product
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetch values from the form for sales
    $product_id = $_POST['product_id'] ?? null;
    $supplier_id = $_POST['supplier_id'] ?? null;
    $quantity_sold = $_POST['quantity_sold'] ?? null;
    $sale_date = $_POST['sale_date'] ?? null;
    $commission_rate = $_POST['commission_rate'] ?? null;

    // Get product details
    $productName = $_POST['productName'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $price = $_POST['price'] ?? null;
    $supplier = $_POST['supplier'] ?? null;
    $newSupplier = $_POST['newSupplier'] ?? null;

    // Handle new supplier logic
    if (!empty($newSupplier)) {
        $supplier = $newSupplier;

        // Check if supplier already exists
        $checkSupplierQuery = "SELECT supplierName FROM suppliers WHERE supplierName = ?";
        if ($stmt = $conn->prepare($checkSupplierQuery)) {
            $stmt->bind_param("s", $supplier);
            $stmt->execute();
            $stmt->store_result();

            // If the supplier does not exist, insert the new supplier
            if ($stmt->num_rows == 0) {
                $insertSupplierQuery = "INSERT INTO suppliers (supplierName) VALUES (?)";
                if ($insertStmt = $conn->prepare($insertSupplierQuery)) {
                    $insertStmt->bind_param("s", $supplier);
                    $insertStmt->execute();
                    $insertStmt->close();
                }
            }
            $stmt->close();
        }
    }

    // Calculate the commission amount only if quantity sold and commission rate are provided
    if (!empty($quantity_sold) && !empty($commission_rate)) {
        // Check current product quantity before processing the sale
        $checkStockQuery = "SELECT quantity FROM products WHERE id = ?";
        if ($stockStmt = $conn->prepare($checkStockQuery)) {
            $stockStmt->bind_param("i", $product_id);
            $stockStmt->execute();
            $stockStmt->bind_result($current_quantity);
            $stockStmt->fetch();
            $stockStmt->close();

            // Ensure enough stock is available for the sale
            if ($current_quantity >= $quantity_sold) {
                $commission_amount = $quantity_sold * $commission_rate;

                // Insert the new sale into the sales table
                $sql = "INSERT INTO sales (product_id, sale_date, quantity_sold, commission_rate, commission_amount, supplier_id) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql)) {
                    // Bind the parameters
                    $stmt->bind_param("isiddi", $product_id, $sale_date, $quantity_sold, $commission_rate, $commission_amount, $supplier_id);
                  
                    // Execute the query
                    if ($stmt->execute()) {
                        $successSale = true; // Set success flag to true if the sale was added successfully

                        // Deduct the sold quantity from the product's quantity
                        $updateProductSql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
                        if ($updateStmt = $conn->prepare($updateProductSql)) {
                            $updateStmt->bind_param("ii", $quantity_sold, $product_id);
                            $updateStmt->execute();
                            $updateStmt->close();
                        }
                    }
                    $stmt->close();
                }
            } else {
                $message = "Not enough stock available for this sale."; // Handle insufficient stock case
            }
        }
    } 

    // Insert the new product only if product details are provided
    if (!empty($productName) && !empty($quantity) && !empty($price)) {
        $sql = "INSERT INTO products (productName, quantity, price, supplier) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sids", $productName, $quantity, $price, $supplier);
            if ($stmt->execute()) {
                $successProduct = true; // Product successfully added
            }
            $stmt->close();
        }
    } 

    // Redirect to the appropriate page based on the operation
    if ($successSale) {
        $message = "Sale added successfully!";
        header("Location: dashboard.php?message=" . urlencode($message)); 
        exit();
    }

    if ($successProduct) {
        $message = "Product added successfully!";
        header("Location: product.php?message=" . urlencode($message)); 
        exit();
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Kiosk Inventory</title>
    <link rel="stylesheet" href="dashboard.css">

</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>KioskInventory</h1>
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
            <section class="products-list">
                <h2>Products</h2>

                <!-- Add Product Button -->
                <button id="showAddProductBtn" style="margin-bottom: 20px;">Add Product</button>

                <!-- Add Product Form -->
                <div id="addProductForm" style="display: none;">
                    <form method="POST" action="product.php">
                        <label for="productName">Product Name:</label>
                        <input type="text" id="productName" name="productName" required>

                        <label for="quantity">Quantity in Stock:</label>
                        <input type="number" id="quantity" name="quantity" required>

                        <label for="price">Selling Price (RM):</label>
                        <input type="number" id="price" name="price" required step="0.01">

                        <label for="supplier">Supplier:</label>
                        <select id="supplier" name="supplier">
                            <option value="">--Select Supplier--</option>
                            <?php
                            // Fetch suppliers from the database
                            $supplierQuery = "SELECT supplierName FROM suppliers";
                            $result = $conn->query($supplierQuery);
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['supplierName'] . "'>" . $row['supplierName'] . "</option>";
                                }
                            }
                            ?>
                        </select>

                        <label for="newSupplier">Or Enter New Supplier:</label>
                        <input type="text" id="newSupplier" name="newSupplier" placeholder="New supplier name">

                        <button type="submit">Add Product</button>
                    </form>
                </div>

                <button id="addSalesButton" class="add-sales-btn">Add Sales</button>

                <div id="addSalesModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Add New Sale</h2>

                        <form id="addSaleForm" action="product.php" method="POST">
                            <!-- Product Selection -->
                            <label for="productId">Product:</label>
                            <select id="productId" name="product_id" required onchange="updateSupplier()">
                            <option value="">-- Select Product --</option>
                            <?php
                                // Populate dropdown with products from database
                                $result = $conn->query("SELECT id, productName, supplier_id FROM products");
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['id']}' data-supplier-id='{$row['supplier_id']}'>{$row['productName']}</option>";
                                }
                                ?>
                            </select>

                            <!-- Supplier Selection -->
                            <label for="supplierId">Supplier:</label>
                            <select id="supplierId" name="supplier_id" required>
                                <option value="">-- Select Supplier --</option>
                                <?php
                                // Populate dropdown with suppliers from database
                                $supplierResult = $conn->query("SELECT id, supplierName FROM suppliers");
                                while ($row = $supplierResult->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['supplierName']}</option>";
                                }
                                ?>
                            </select>

                            <!-- Other Fields -->
                            <label for="quantitySold">Quantity Sold:</label>
                            <input type="number" id="quantitySold" name="quantity_sold" required>

                            <label for="saleDate">Sale Date:</label>
                            <input type="date" id="saleDate" name="sale_date" required>

                            <label for="commissionRate">Commission Rate per Item (RM):</label>
                            <input type="number" id="commissionRate" name="commission_rate" step="0.01" required>

                            <button type="submit">Add Sale</button>
                        </form>
                    </div>
                </div>

                <!-- Products Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity in Stock</th>
                            <th>Selling Price (RM)</th>
                            <th>Supplier</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <body id="productTable">
                        <?php
                        // Fetch products from the database
                        $sql = "SELECT id, productName, quantity, price, supplier FROM products";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr data-id='" . $row['id'] . "'>
                                    <td>" . $row['productName'] . "</td>
                                    <td>" . $row['quantity'] . "</td>
                                    <td>RM " . $row['price'] . "</td>
                                    <td>" . $row['supplier'] . "</td>
                                    <td>
                                        <button onclick='editProduct(this)'>Edit</button>
                                        <button onclick='deleteProduct(this)'>Delete</button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No products found.</td></tr>";
                        }
                        ?>
                    </body>
                </table>
            </section>
        </main>
    </div>

    <div class="toast" id="toast" style="display: none;"></div>

    <script>
// Show/hide Add Product form
document.getElementById('showAddProductBtn').addEventListener('click', function() {
    var form = document.getElementById('addProductForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});

// Modal logic for adding sales
const modal = document.getElementById("addSalesModal");
const btn = document.getElementById("addSalesButton");
const span = document.getElementsByClassName("close")[0];

btn.onclick = function() {
    modal.style.display = "block";
};

span.onclick = function() {
    modal.style.display = "none";
};

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};

// Show success popup on successful sale addition
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('success') && urlParams.get('success') === 'true') {
    showToast("Sale added successfully!"); // Call the toast function
}

// Function to show the toast notification
function showToast(message) {
    const toast = document.createElement("div");
    toast.className = "toast success"; // Use the success class for styling
    toast.textContent = message;
    document.body.appendChild(toast);

    // Automatically hide the toast after 3 seconds
    setTimeout(() => {
        toast.style.opacity = "0"; // Fade out effect
        setTimeout(() => {
            document.body.removeChild(toast); // Remove from DOM
        }, 500); // Wait for the fade-out duration before removing
    }, 3000); // Show for 3 seconds
}

    function editProduct(button) {
        var row = button.parentNode.parentNode;
        var id = row.getAttribute('data-id');
        var productName = row.cells[0].innerHTML;
        var quantity = row.cells[1].innerHTML;
        var price = row.cells[2].innerHTML.replace('RM ', '');
        var supplier = row.cells[3].innerHTML;

        var newProductName = prompt("Edit Product Name:", productName);
        var newQuantity = prompt("Edit Quantity:", quantity);
        var newPrice = prompt("Edit Price (RM):", price);
        var newSupplier = prompt("Edit Supplier:", supplier);

        if (newProductName !== null && newQuantity !== null && newPrice !== null && newSupplier !== null) {
            // Update the row in the table
            row.cells[0].innerHTML = newProductName;
            row.cells[1].innerHTML = newQuantity;
            row.cells[2].innerHTML = "RM " + newPrice;
            row.cells[3].innerHTML = newSupplier;

            // Send the updated data to the server
            fetch('editProduct.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    productName: newProductName,
                    quantity: newQuantity,
                    price: newPrice,
                    supplier: newSupplier
                })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Product updated successfully!');
                } else {
                    showToast('Failed to update product.');
                }
            })
            .catch(error => {
                showToast('An error occurred: ' + error.message);
            });
        }
    }

    function deleteProduct(button) {
        var row = button.parentNode.parentNode;
        var id = row.getAttribute('data-id');

        if (confirm("Are you sure you want to delete this product?")) {
            // Remove the row from the table
            row.parentNode.removeChild(row);

            // Send the delete request to the server
            fetch('deleteProduct.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Product deleted successfully!');
                } else {
                    showToast('Failed to delete product.');
                }
            })
            .catch(error => {
                showToast('An error occurred: ' + error.message);
            });
        }
    }


    function updateSupplier() {
        const productSelect = document.getElementById('productId');
        const supplierSelect = document.getElementById('supplierId');
        const selectedOption = productSelect.options[productSelect.selectedIndex];

        const supplierId = selectedOption.getAttribute('data-supplier-id');
        console.log('Selected Supplier ID:', supplierId); // Log the supplier ID

        supplierSelect.value = supplierId;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('productId').addEventListener('change', updateSupplier);
        updateSupplier(); // Call on load to set the initial supplier
    });

</script>

</body>
</html>
