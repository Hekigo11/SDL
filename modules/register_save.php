<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer-master/src/Exception.php';
require_once '../PHPMailer-master/src/PHPMailer.php';
require_once '../PHPMailer-master/src/SMTP.php';

session_start();
include("dbconi.php");

try {
    $error_messages = [];
    $isallok = true;
    
    // Sanitize and validate email
    $email = filter_var($_POST['txtemail'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_messages[] = "Invalid email format";
        $isallok = false;
    }

    // Validate mobile number
    $mobile_num = trim($_POST['txtmobilenum']);
    if (!preg_match('/^0\d{10}$/', $mobile_num)) {
        $error_messages[] = "Invalid mobile number format. Must be 11 digits starting with 0";
        $isallok = false;
    }

    // Check for existing email/mobile
    $stmt = mysqli_prepare($dbc, "SELECT email_add, mobile_num FROM users WHERE email_add = ? OR mobile_num = ?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $_POST['txtmobilenum']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['email_add'] == $email) {
            $error_messages[] = "Email already exists";
            $isallok = false;
        }
        if ($row['mobile_num'] == $_POST['txtmobilenum']) {
            $error_messages[] = "Mobile number already exists";
            $isallok = false;
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
            $error_messages[] = "Please enter your " . $label;
            $isallok = false;
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
            $_POST['txtmobilenum'],
            $hash_reg,
            $verification_code,
            $expiry_time
        );

        if (mysqli_stmt_execute($insert_stmt)) {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug = 2; // ito nagcacause delay
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

            if (!$mail->send()) {
                throw new Exception("Failed to send verification email");
            }

            mysqli_commit($dbc);
            $_SESSION['verify_email'] = $email;
            echo "success";
        } else {
            throw new Exception("Failed to register user");
        }
    } else {
        echo implode("<br>", $error_messages);
    }

} catch (Exception $e) {
    mysqli_rollback($dbc);
    error_log("Registration Error: " . $e->getMessage());
    echo "Registration failed. Please try again later.";
} finally {
    mysqli_close($dbc);
}
?>