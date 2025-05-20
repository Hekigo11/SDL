<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {        
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include("dbconi.php");

try {
    // Query to get all active packages and their base prices
    $query = "SELECT name, base_price FROM packages WHERE is_active = 1";
    $result = mysqli_query($dbc, $query);
    
    if (!$result) {
        throw new Exception("Database query error: " . mysqli_error($dbc));
    }
    
    $rates = [];
    
    // Process results into an associative array with package name as key and base_price as value
    while ($row = mysqli_fetch_assoc($result)) {
        $rates[$row['name']] = (float)$row['base_price'];
    }
    
    // Return the rates as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'rates' => $rates
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching package rates: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching package rates: ' . $e->getMessage()
    ]);
}
?> 