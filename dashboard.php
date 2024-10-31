<?php
// Include database connection
include 'db_connection.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total products sold
$totalProductsSoldQuery = "SELECT SUM(quantity_sold) AS total_sold FROM sales";
$totalProductsSoldResult = $conn->query($totalProductsSoldQuery);
$totalProductsSold = $totalProductsSoldResult->fetch_assoc()['total_sold'] ?? 0;

// Fetch total sales (assuming you calculate total sales as quantity_sold * product price)
$totalSalesQuery = "
    SELECT SUM(s.quantity_sold * p.price) AS total_sales 
    FROM sales s 
    JOIN products p ON s.product_id = p.id";
$totalSalesResult = $conn->query($totalSalesQuery);
$totalSales = $totalSalesResult->fetch_assoc()['total_sales'] ?? 0;

// Fetch total commission
$totalCommissionQuery = "SELECT SUM(commission_amount) AS total_commission FROM sales";
$totalCommissionResult = $conn->query($totalCommissionQuery);
$totalCommission = $totalCommissionResult->fetch_assoc()['total_commission'] ?? 0;

// Fetch recent sales records including supplier
$recentSalesQuery = "
    SELECT s.sale_date AS date, p.productName AS product_name, s.quantity_sold, 
           (s.quantity_sold * p.price) AS total_sales, p.supplier AS supplier
    FROM sales s 
    JOIN products p ON s.product_id = p.id 
    ORDER BY s.sale_date DESC 
    LIMIT 5";
$recentSalesResult = $conn->query($recentSalesQuery);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Pro</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>KioskInventory</h1>
            <div class="user-info">
            </div>
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
            <section class="summary-cards">
                <div class="card">
                    <h2>Total Products Sold</h2>
                    <p><?php echo $totalProductsSold; ?></p> 
                </div>
                <div class="card">
                    <h2>Total Sales (RM)</h2>
                    <p>RM <?php echo number_format($totalSales, 2); ?></p>
                </div>
                <div class="card">
                    <h2>Total Commission (RM)</h2>
                    <p>RM <?php echo number_format($totalCommission, 2); ?></p> 
                </div>
            </section>
            <section class="recent-sales">
                <h2>Recent Sales</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product Name</th>
                            <th>Quantity Sold</th>
                            <th>Total Sales (RM)</th>
                            <th>Supplier</th> <!-- Add Supplier column here -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($recentSalesResult->num_rows > 0) {
                            while($row = $recentSalesResult->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$row['date']}</td>
                                    <td>{$row['product_name']}</td>
                                    <td>{$row['quantity_sold']}</td>
                                    <td>RM " . number_format($row['total_sales'], 2) . "</td>
                                    <td>{$row['supplier']}</td> <!-- Display supplier -->
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No recent sales available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
