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

if (isset($_POST['clear_all'])) {
    // Clear all items from cart
    $sql = "DELETE FROM user_cart WHERE user_id = ?";
    $stmt = $dbc->prepare($sql);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
    }
} else {
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    $query = "DELETE FROM user_cart WHERE user_id = ? AND product_id = ?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param('ii', $userId, $productId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
}
?>