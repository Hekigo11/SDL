<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['verify_email'])) {
    echo "Invalid request";
    exit;
}

include("dbconi.php");

$current_time = date('Y-m-d H:i:s');
$email = $_SESSION['verify_email'];

// Check if previous OTP has expired
$check_query = "SELECT OTPE FROM users WHERE email_add = ? AND OTPE > ?";
$check_stmt = mysqli_prepare($dbc, $check_query);
mysqli_stmt_bind_param($check_stmt, "ss", $email, $current_time);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) > 0) {
    echo "Please wait for the current code to expire";
    exit;
}

// Generate new OTP only if previous one has expired
$new_otp = sprintf("%06d", mt_rand(1, 999999));
$new_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$query = "UPDATE users SET OTPC = ?, OTPE = ? WHERE email_add = ?";
$stmt = mysqli_prepare($dbc, $query);
mysqli_stmt_bind_param($stmt, "sss", $new_otp, $new_expiry, $email);

if(mysqli_stmt_execute($stmt)) {
    // TODO: Send new OTP via email
    echo "success";
} else {
    echo "Failed to generate new code";
}

mysqli_close($dbc);
?>