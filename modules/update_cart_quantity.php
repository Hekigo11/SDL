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
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if (!$productId || !in_array($action, ['increase', 'decrease'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Update quantity based on action
if ($action === 'increase') {
    $query = "UPDATE user_cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
} else {
    $query = "UPDATE user_cart SET quantity = GREATEST(quantity - 1, 1) WHERE user_id = ? AND product_id = ?";
}

$stmt = $dbc->prepare($query);
$stmt->bind_param('ii', $userId, $productId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
?>