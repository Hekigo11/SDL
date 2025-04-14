<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['verify_email'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

include("dbconi.php");

$verification_code = $_POST['verification_code'];
$email = $_SESSION['verify_email'];
$current_time = date('Y-m-d H:i:s');

$query = "SELECT * FROM users WHERE email_add = ? AND OTPC = ? AND OTPE > ?";
$stmt = mysqli_prepare($dbc, $query);
mysqli_stmt_bind_param($stmt, "sss", $email, $verification_code, $current_time);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $update = "UPDATE users SET emailv = 1, OTPC = NULL, OTPE = NULL WHERE email_add = ?";
    $stmt = mysqli_prepare($dbc, $update);
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    if (mysqli_stmt_execute($stmt)) {
        unset($_SESSION['verify_email']);
        echo "success";
    } else {
        echo "Verification update failed";
    }
} else {
    echo "Invalid or expired verification code";
}

mysqli_close($dbc);
?>