<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/dbconi.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['loginok'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    // Store order details in session for later use
    $_SESSION['temp_order'] = $_POST;
    
    echo json_encode([
        'success' => true,
        'message' => 'Order details stored temporarily'
    ]);

} catch (Exception $e) {
    error_log('Store Order Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}