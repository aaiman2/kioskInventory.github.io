<?php
require_once('TCPDF-main/tcpdf.php');
include 'db_connection.php';

// Get the parameters from the URL
$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$supplier = isset($_GET['supplier']) ? $_GET['supplier'] : '';

// Create new PDF document
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('KioskInventory');
$pdf->SetTitle('Sales Report');
$pdf->SetHeaderData('', 0, 'Sales Report', "From $startDate to $endDate");

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Add a page
$pdf->AddPage();

// Prepare the SQL query
$sql = "SELECT p.productName, p.price AS sale_price, s.sale_date, s.quantity_sold, s.commission_amount, sup.supplierName
        FROM sales s
        JOIN products p ON s.product_id = p.id
        JOIN suppliers sup ON s.supplier_id = sup.id
        WHERE s.sale_date BETWEEN ? AND ?";

// Add supplier filter if a supplier is selected
if (!empty($supplier)) {
    $sql .= " AND s.supplier_id = ?";
}

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!empty($supplier)) {
    $stmt->bind_param("ssi", $startDate, $endDate, $supplier);
} else {
    $stmt->bind_param("ss", $startDate, $endDate);
}
$stmt->execute();
$result = $stmt->get_result();

// Create HTML table for the PDF
$html = '<h2>Sales Report from ' . $startDate . ' to ' . $endDate . '</h2>';
$html .= '<table border="1" cellpadding="4">
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
            <tbody>';

$totalSales = 0;
$totalQuantity = 0;
$totalCommission = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rowTotalSales = $row['quantity_sold'] * $row['sale_price'];
        $totalSales += $rowTotalSales;
        $totalQuantity += $row['quantity_sold'];
        
        $commissionPerUnit = $row['commission_amount'] / $row['quantity_sold']; // calculate commission per unit
        $totalCommissionForThisRow = $row['commission_amount']; // total commission for this row
        $totalCommission += $totalCommissionForThisRow; // add to total commission
        
        $html .= '<tr>
                    <td>' . $row['productName'] . '</td>
                    <td>' . $row['sale_date'] . '</td>
                    <td>' . $row['quantity_sold'] . '</td>
                    <td>RM ' . number_format($row['sale_price'], 2) . '</td>
                    <td>RM ' . number_format($rowTotalSales, 2) . '</td>
                    <td>RM ' . number_format($commissionPerUnit, 2) . '</td>
                    <td>RM ' . number_format($totalCommissionForThisRow, 2) . '</td>
                    <td>' . $row['supplierName'] . '</td>
                  </tr>';
    }
    
    // Display the total quantity, total commission, and total sales at the bottom
    $html .= '</tbody>
              <tfoot>
                <tr>
                    <td colspan="3"><strong>Totals:</strong></td>
                    <td></td>
                    <td><strong>RM ' . number_format($totalSales, 2) . '</strong></td>
                    <td></td>
                    <td><strong>RM ' . number_format($totalCommission, 2) . '</strong></td>
                    <td></td>
                </tr>
              </tfoot>';
} else {
    $html .= '<tr><td colspan="8">No sales data found for the selected date range.</td></tr>';
}

$html .= '</table>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('sales_report.pdf', 'I');

// Close the statement and connection
$stmt->close();
$conn->close();
?>
