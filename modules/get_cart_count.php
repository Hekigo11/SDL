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

// Get total quantity from cart
$query = "SELECT SUM(quantity) as count FROM user_cart WHERE user_id = ?";
$stmt = $dbc->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'count' => $row['count'] ? intval($row['count']) : 0
]);
?>