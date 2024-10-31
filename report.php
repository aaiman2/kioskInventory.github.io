<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory Pro</title>
    <link rel="stylesheet" href="dashboard.css">
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
            <section class="reports">
                <h2>Reports</h2>
                <p>Select a date range and supplier to generate reports.</p>
                <form method="post" action="">
                    <label for="startDate">Start Date:</label>
                    <input type="date" id="startDate" name="startDate" required>
                    
                    <label for="endDate">End Date:</label>
                    <input type="date" id="endDate" name="endDate" required>

                    <!-- Supplier dropdown filter -->
                    <label for="supplier">Supplier:</label>
                    <select id="supplier" name="supplier">
                        <option value="">All Suppliers</option>
                        <?php
                        // Fetch suppliers from the database
                        include 'db_connection.php';
                        $supplierResult = $conn->query("SELECT id, supplierName FROM suppliers");
                        while ($supplierRow = $supplierResult->fetch_assoc()) {
                            echo "<option value='" . $supplierRow['id'] . "'>" . $supplierRow['supplierName'] . "</option>";
                        }
                        ?>
                    </select>
                    
                    <button type="submit" name="generateReport">Generate Report</button>
                </form>

                <?php
                // Check if the form is submitted
                if (isset($_POST['generateReport'])) {
                    $startDate = $_POST['startDate'];
                    $endDate = $_POST['endDate'];
                    $supplier = $_POST['supplier'] ?? '';

                    // Updated SQL query to include sale_price from the products table
                    $sql = "SELECT p.productName, p.price AS sale_price, s.sale_date, s.quantity_sold, s.commission_amount, sup.supplierName
                            FROM sales s
                            JOIN products p ON s.product_id = p.id
                            JOIN suppliers sup ON s.supplier_id = sup.id
                            WHERE s.sale_date BETWEEN ? AND ?";

                    // Add supplier filter if a supplier is selected
                    if (!empty($supplier)) {
                        $sql .= " AND s.supplier_id = ?";
                    }

                    $stmt = $conn->prepare($sql);
                    if (!empty($supplier)) {
                        $stmt->bind_param("ssi", $startDate, $endDate, $supplier);
                    } else {
                        $stmt->bind_param("ss", $startDate, $endDate);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Variables to hold the total sales, quantity, and commission
                    $totalSales = 0;
                    $totalQuantity = 0;
                    $totalCommission = 0;

                    if ($result->num_rows > 0) {
                        echo "<h3>Report from $startDate to $endDate</h3>";
                        echo "<table>
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Sale Date</th>
                                        <th>Quantity Sold</th>
                                        <th>Sale Price (RM)</th>
                                        <th>Total Sales (RM)</th>
                                        <th>Commission per Unit (RM)</th>
                                        <th>Total Commission (RM)</th>
                                        <th>Supplier</th>
                                    </tr>
                                </thead>
                                <tbody>";

                        // Loop through the result set and calculate total sales and total commission
                        while ($row = $result->fetch_assoc()) {
                            $rowTotalSales = $row['quantity_sold'] * $row['sale_price'];
                            $totalSales += $rowTotalSales;
                            $totalQuantity += $row['quantity_sold'];
                        
                            $commissionPerUnit = $row['commission_amount'] / $row['quantity_sold']; // calculate commission per unit
                            $totalCommissionForThisRow = $row['commission_amount']; // total commission for this row
                            $totalCommission += $totalCommissionForThisRow; // add to total commission
                        
                            echo "<tr>
                                    <td>" . $row['productName'] . "</td>
                                    <td>" . $row['sale_date'] . "</td>
                                    <td>" . $row['quantity_sold'] . "</td>
                                    <td>RM " . number_format($row['sale_price'], 2) . "</td>
                                    <td>RM " . number_format($rowTotalSales, 2) . "</td>
                                    <td>RM " . number_format($commissionPerUnit, 2) . "</td>
                                    <td>RM " . number_format($totalCommissionForThisRow, 2) . "</td>
                                    <td>" . $row['supplierName'] . "</td>
                                  </tr>";
                        }
                        echo "</tbody>";

                        // Display the total quantity, total commission, and total sales at the bottom
                        echo "<tfoot>
                                <tr>
                                    <td colspan='3'><strong>Totals:</strong></td>
                                    <td></td>
                                    <td><strong>RM " . number_format($totalSales, 2) . "</strong></td>
                                    <td></td>
                                    <td><strong>RM " . number_format($totalCommission, 2) . "</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>";
                        echo "</table>";

                        // Print button
                        echo "<button onclick='printPDF(\"$startDate\", \"$endDate\", \"$supplier\")'>Print as PDF</button>";
                    } else {
                        echo "<p>No sales data found for the selected date range.</p>";
                    }
                    $stmt->close();
                }

                $conn->close();
                ?>
            </section>
        </main>
    </div>

    <script>
        function printPDF(startDate, endDate, supplier) {
        // Construct URL with the selected date range and supplier
        let url = 'generateReportPDF.php?startDate=' + startDate + '&endDate=' + endDate;
        if (supplier) {
            url += '&supplier=' + supplier;
        }
        window.location.href = url;
    }
    </script>
</body>
</html>
