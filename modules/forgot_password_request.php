<?php
require_once __DIR__ . '/../config.php';
require_once '../PHPMailer-master/src/Exception.php';
require_once '../PHPMailer-master/src/PHPMailer.php';
require_once '../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

include("dbconi.php");

try {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    $query = "SELECT user_id, fname FROM users WHERE email_add = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Generate new OTP and expiry time
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        // Update user with new OTP
        $update_query = "UPDATE users SET OTPC = ?, OTPE = ? WHERE email_add = ?";
        $stmt = mysqli_prepare($dbc, $update_query);
        mysqli_stmt_bind_param($stmt, "sss", $otp, $expiry, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            // Send email with OTP
            $mail = new PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = 'smtp-relay.brevo.com';
            $mail->SMTPAuth = true;
            $mail->Username = '89af28001@smtp-brevo.com';
            $mail->Password = '2SgtjUQrEsRHwA0M';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->Timeout = 30;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('cateringservices69420@gmail.com', 'Marj catering services');
            $mail->addAddress($email);
            
            $mail->isHTML(true);
            $mail->Subject = 'SDL System - Password Reset Code';
            
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2>Password Reset</h2>
                    <p>Hello ' . htmlspecialchars($user['fname']) . ',</p>
                    <p>Your password reset code is:</p>
                    <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0;">
                        <strong>' . $otp . '</strong>
                    </div>
                    <p>This code will expire in 15 minutes.</p>
                    <p>If you did not request this code, please ignore this email.</p>
                </div>';

            $mail->send();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to generate reset code");
        }
    } else {
        throw new Exception("Email not found");
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($dbc);
?>