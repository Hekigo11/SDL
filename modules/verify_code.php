<?php
session_start();
include("dbconi.php");

if (!isset($_POST['verification_code']) || !isset($_SESSION['verify_email'])) {
    echo "Invalid request";
    exit;
}

$code = $_POST['verification_code'];
$email = $_SESSION['verify_email'];
$current_time = date('Y-m-d H:i:s');

try {
    $stmt = mysqli_prepare($dbc, 
        "SELECT * FROM users WHERE email_add = ? AND OTPC = ? AND OTPE > ? AND emailv = 0"
    );
    mysqli_stmt_bind_param($stmt, "sss", $email, $code, $current_time);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Update user as verified
        $update_stmt = mysqli_prepare($dbc, 
            "UPDATE users SET emailv = 1, OTPC = NULL, OTPE = NULL WHERE email_add = ?"
        );
        mysqli_stmt_bind_param($update_stmt, "s", $email);
        
        if (mysqli_stmt_execute($update_stmt)) {
            unset($_SESSION['verify_email']);
            echo "success";
        } else {
            throw new Exception("Failed to update verification status");
        }
    } else {
        echo "Invalid or expired verification code";
    }
} catch (Exception $e) {
    error_log("Verification Error: " . $e->getMessage());
    echo "Verification failed. Please try again.";
} finally {
    mysqli_close($dbc);
}
?>