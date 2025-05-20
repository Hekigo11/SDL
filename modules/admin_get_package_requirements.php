<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['loginok']) || !in_array($_SESSION['role'], [1, 3])) {        
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

include("dbconi.php");

if (isset($_GET['package_id'])) {
    $package_id = intval($_GET['package_id']);
    
    $query = "SELECT * FROM package_products WHERE package_id = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "i", $package_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $requirements = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $requirements[] = [
            'category_id' => $row['category_id'],
            'amount' => $row['amount']
        ];
    }
    
    echo json_encode(['success' => true, 'requirements' => $requirements]);
} else {
    echo json_encode(['success' => false, 'error' => 'Missing package ID']);
}
?>
