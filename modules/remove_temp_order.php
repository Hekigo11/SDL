<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

try {
    // Remove temporary order data from session
    if (isset($_SESSION['temp_order'])) {
        unset($_SESSION['temp_order']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Temporary order data removed'
    ]);

} catch (Exception $e) {
    error_log('Remove Temp Order Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}