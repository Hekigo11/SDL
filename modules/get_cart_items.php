<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['loginok'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Get cart items with product details
$query = "SELECT c.*, p.prod_name as name, p.prod_price as price 
          FROM user_cart c 
          JOIN products p ON c.product_id = p.product_id 
          WHERE c.user_id = ?";
$stmt = $dbc->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id' => $row['product_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity']
    ];
}

echo json_encode([
    'success' => true,
    'items' => $items
]);
?>