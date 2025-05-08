<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if (!isset($_SESSION['loginok'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

include("dbconi.php");

try {
    if (!isset($_POST['current_password']) || !isset($_POST['new_password'])) {
        throw new Exception("Missing required fields");
    }

    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Verify current password
    $query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (!$user || !password_verify($current_password, $user['password'])) {
        throw new Exception("Current password is incorrect");
    }

    // Hash and update new password
    $hash_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($dbc, $update_query);
    mysqli_stmt_bind_param($stmt, "si", $hash_password, $user_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to update password");
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($dbc);
?>