<?php
session_start();
require_once '../config.php';

// Check admin authentication 
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {       
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

include("dbconi.php");

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="sales_report_' . $start_date . '_to_' . $end_date . '.xls"');

// Function to get detailed sales data
function getDetailedSalesData($dbc, $start_date, $end_date) {
    $data = [
        'delivery_orders' => [],
        'standard_catering' => [],
        'custom_catering' => []
    ];

    // Get delivery orders
    $query = "SELECT o.order_id, o.created_at, o.total_amount, o.status,
                     GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.prod_name) SEPARATOR ', ') as items,
                     u.email as customer
              FROM orders o
              JOIN order_items oi ON o.order_id = oi.order_id
              JOIN products p ON oi.product_id = p.product_id
              JOIN users u ON o.user_id = u.user_id
              WHERE o.created_at BETWEEN ? AND ?
              GROUP BY o.order_id
              ORDER BY o.created_at DESC";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $data['delivery_orders'][] = $row;
    }

    // Get standard catering orders
    $query = "SELECT co.catering_id, co.created_at, co.total_amount, co.status,
                     co.event_date, co.venue, co.num_persons,
                     u.email as customer
              FROM catering_orders co
              JOIN users u ON co.user_id = u.user_id
              WHERE co.created_at BETWEEN ? AND ?
              ORDER BY co.created_at DESC";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $data['standard_catering'][] = $row;
    }

    // Get custom catering orders
    $query = "SELECT co.*, u.email as customer,
                    (CASE WHEN needs_setup = 1 THEN 2000 ELSE 0 END + 
                     CASE WHEN needs_tablesandchairs = 1 THEN 3500 ELSE 0 END + 
                     CASE WHEN needs_decoration = 1 THEN 5000 ELSE 0 END + 
                     COALESCE(quote_amount, 0)) as total_with_services
              FROM custom_catering_orders co
              JOIN users u ON co.user_id = u.user_id
              WHERE co.created_at BETWEEN ? AND ?
              ORDER BY co.created_at DESC";
    
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $data['custom_catering'][] = $row;
    }

    return $data;
}

$sales_data = getDetailedSalesData($dbc, $start_date, $end_date);

// Create Excel content
echo "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
    td { mso-number-format:'\\₱#,##0.00'; }
    .text { mso-number-format:'@'; }
</style>
</head>
<body>
<h1>Sales Report ($start_date to $end_date)</h1>

<h2>Delivery Orders</h2>
<table border='1'>
    <tr>
        <th>Order ID</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Items</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>";

foreach ($sales_data['delivery_orders'] as $order) {
    echo "<tr>
        <td class='text'>#" . htmlspecialchars($order['order_id']) . "</td>
        <td class='text'>" . date('Y-m-d H:i', strtotime($order['created_at'])) . "</td>
        <td class='text'>" . htmlspecialchars($order['customer']) . "</td>
        <td class='text'>" . htmlspecialchars($order['items']) . "</td>
        <td>" . $order['total_amount'] . "</td>
        <td class='text'>" . htmlspecialchars($order['status']) . "</td>
    </tr>";
}

echo "</table>

<h2>Standard Catering Orders</h2>
<table border='1'>
    <tr>
        <th>Order ID</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Event Date</th>
        <th>Venue</th>
        <th>Persons</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>";

foreach ($sales_data['standard_catering'] as $order) {
    echo "<tr>
        <td class='text'>CTR-" . htmlspecialchars($order['catering_id']) . "</td>
        <td class='text'>" . date('Y-m-d H:i', strtotime($order['created_at'])) . "</td>
        <td class='text'>" . htmlspecialchars($order['customer']) . "</td>
        <td class='text'>" . date('Y-m-d', strtotime($order['event_date'])) . "</td>
        <td class='text'>" . htmlspecialchars($order['venue']) . "</td>
        <td>" . $order['num_persons'] . "</td>
        <td>" . $order['total_amount'] . "</td>
        <td class='text'>" . htmlspecialchars($order['status']) . "</td>
    </tr>";
}

echo "</table>

<h2>Custom Catering Orders</h2>
<table border='1'>
    <tr>
        <th>Order ID</th>
        <th>Date</th>
        <th>Customer</th>
        <th>Event Date</th>
        <th>Venue</th>
        <th>Persons</th>
        <th>Services</th>
        <th>Amount</th>
        <th>Status</th>
    </tr>";

foreach ($sales_data['custom_catering'] as $order) {
    $services = [];
    if ($order['needs_setup']) $services[] = 'Buffet Setup (₱2,000)';
    if ($order['needs_tablesandchairs']) $services[] = 'Tables & Chairs (₱3,500)';
    if ($order['needs_decoration']) $services[] = 'Decoration (₱5,000)';
    
    echo "<tr>
        <td class='text'>CSP-" . htmlspecialchars($order['custom_order_id']) . "</td>
        <td class='text'>" . date('Y-m-d H:i', strtotime($order['created_at'])) . "</td>
        <td class='text'>" . htmlspecialchars($order['customer']) . "</td>
        <td class='text'>" . date('Y-m-d', strtotime($order['event_date'])) . "</td>
        <td class='text'>" . htmlspecialchars($order['venue']) . "</td>
        <td>" . $order['num_persons'] . "</td>
        <td class='text'>" . implode(', ', $services) . "</td>
        <td>" . $order['total_with_services'] . "</td>
        <td class='text'>" . htmlspecialchars($order['status']) . "</td>
    </tr>";
}

echo "</table>
</body>
</html>";
