<?php
require_once __DIR__ . '/../config.php';
require_once 'dbconi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

session_start();
if (!isset($_SESSION['loginok'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$userId = $_SESSION['user_id'];
$productId = isset($input['product_id']) ? intval($input['product_id']) : 0;
$quantity = isset($input['quantity']) ? intval($input['quantity']) : 0;

if (!$productId || !$quantity) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Check if product already exists in cart
$query = "SELECT * FROM user_cart WHERE user_id = ? AND product_id = ?";
$stmt = $dbc->prepare($query);
$stmt->bind_param('ii', $userId, $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update quantity if product exists
    $query = "UPDATE user_cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param('iii', $quantity, $userId, $productId);
} else {
    // Insert new product into cart
    $query = "INSERT INTO user_cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param('iii', $userId, $productId, $quantity);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
?>