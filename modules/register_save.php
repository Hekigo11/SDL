<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer-master/src/Exception.php';
require_once '../PHPMailer-master/src/PHPMailer.php';
require_once '../PHPMailer-master/src/SMTP.php';

// Initialize variables and validate POST data
session_start();
$isallok = true;
$msg = "";

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

include("dbconi.php");

try {
    // Sanitize inputs
    $email = filter_var($_POST['txtemail'], FILTER_SANITIZE_EMAIL);
    $mo_num = preg_replace('/[^0-9]/', '', $_POST['txtmobilenum']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $isallok = false;
        $msg .= "Invalid email format\n";
    }

    // Check for existing email/mobile using prepared statement
    $stmt = mysqli_prepare($dbc, "SELECT email_add, mobile_num FROM users WHERE email_add = ? OR mobile_num = ?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $mo_num);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['email_add'] == $email) {
            $isallok = false;
            $msg .= "Email Already Exists!\n";
        }
        if ($row['mobile_num'] == $mo_num) {
            $isallok = false;
            $msg .= "Mobile Number Already Exists!\n";
        }
    }

    // Validate required fields
    $required_fields = [
        'txtfname' => 'First Name',
        'txtlname' => 'Last Name',
        'txtemail' => 'Email',
        'txtmobilenum' => 'Mobile Number',
        'txtpassword' => 'Password'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty(trim($_POST[$field]))) {
            $isallok = false;
            $msg .= "Enter $label\n";
        }
    }

    if ($isallok) {
        mysqli_begin_transaction($dbc);

        // Generate verification data
        $hash_reg = password_hash($_POST['txtpassword'], PASSWORD_DEFAULT);
        $verification_code = sprintf("%06d", mt_rand(1, 999999));
        $expiry_time = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Insert user using prepared statement
        $insert_stmt = mysqli_prepare($dbc, 
            "INSERT INTO users (fname, mname, lname, email_add, mobile_num, password, role_id, emailv, OTPC, OTPE) 
             VALUES (?, ?, ?, ?, ?, ?, 2, 0, ?, ?)"
        );
        
        mysqli_stmt_bind_param($insert_stmt, "ssssssss",
            $_POST['txtfname'],
            $_POST['txtmname'],
            $_POST['txtlname'],
            $email,
            $mo_num,
            $hash_reg,
            $verification_code,
            $expiry_time
        );

        if (mysqli_stmt_execute($insert_stmt)) {
            try {
                // Configure PHPMailer with debug settings
                $mail = new PHPMailer(true);
                $mail->SMTPDebug = 2; // Enable verbose debug output
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer: $str");
                };
                
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp-relay.brevo.com';
                $mail->SMTPAuth = true;
                $mail->Username = '89af28001@smtp-brevo.com';
                $mail->Password = '2SgtjUQrEsRHwA0M';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->Timeout = 30; // Increased timeout
                $mail->CharSet = 'UTF-8'; // Set character encoding
                
                // Recipients
                $mail->setFrom('cateringservices69420@gmail.com', 'Marj catering services');
                $mail->addAddress($email, $_POST['txtfname'] . ' ' . $_POST['txtlname']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'SDL System - Email Verification';
                
                // Email template
                $mail->Body = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                        <h2>Email Verification</h2>
                        <p>Hello ' . htmlspecialchars($_POST['txtfname']) . ',</p>
                        <p>Your verification code is:</p>
                        <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0;">
                            <strong>' . $verification_code . '</strong>
                        </div>
                        <p>This code will expire in 15 minutes.</p>
                        <p style="color: #666; font-size: 12px; margin-top: 30px;">
                            If you did not request this verification code, please ignore this email.
                        </p>
                    </div>
                ';
                
                $mail->AltBody = "Hello {$_POST['txtfname']},\n\nYour verification code is: $verification_code\n\nThis code will expire in 15 minutes.";

                // Attempt to send email
                if (!$mail->send()) {
                    throw new Exception("Mailer Error: " . $mail->ErrorInfo);
                }

                // If email sent successfully
                mysqli_commit($dbc);
                $_SESSION['verify_email'] = $email;
                $_SESSION['verify_code'] = $verification_code; // Store code in session for verification
                $msg = "success";

            } catch (Exception $e) {
                mysqli_rollback($dbc);
                error_log("Email Error: " . $e->getMessage());
                $msg = "Registration successful but email verification failed. Please contact support.";
            }
        } else {
            throw new Exception("Database Error: " . mysqli_stmt_error($insert_stmt));
        }
    }

} catch (Exception $e) {
    mysqli_rollback($dbc);
    error_log("Registration Error: " . $e->getMessage());
    $msg = "Registration failed. Please try again later.";
} finally {
    mysqli_close($dbc);
    echo $msg;
}
?>