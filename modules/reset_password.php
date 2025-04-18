<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

include("dbconi.php");

try {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    
    if (empty($otp) || empty($new_password)) {
        throw new Exception("All fields are required");
    }

    $current_time = date('Y-m-d H:i:s');
    
    // Verify OTP
    $query = "SELECT user_id FROM users 
              WHERE email_add = ? 
              AND OTPC = ? 
              AND OTPE > ?";
              
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "sss", $email, $otp, $current_time);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Hash new password and update user
        $hash_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_query = "UPDATE users 
                        SET password = ?, 
                            OTPC = NULL, 
                            OTPE = NULL 
                        WHERE user_id = ?";
                        
        $stmt = mysqli_prepare($dbc, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $hash_password, $user['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to update password");
        }
    } else {
        throw new Exception("Invalid or expired reset code");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($dbc);
?>