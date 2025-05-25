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
    
    // Validate name fields
    function validateNameField($name, $fieldName) {
        // Only allow letters, spaces, hyphens, and apostrophes
        if (!preg_match('/^[a-zA-Z\s\-\']+$/', $name)) {
            return "$fieldName can only contain letters, spaces, hyphens, and apostrophes";
        }
        return null;
    }
    
    // Validate first name
    $fname = trim($_POST['txtfname']);
    if (!empty($fname)) {
        $nameError = validateNameField($fname, 'First name');
        if ($nameError) {
            $error_messages[] = $nameError;
            $isallok = false;
        }
    }
    
    // Validate middle name (optional)
    $mname = trim($_POST['txtmname']);
    if (!empty($mname)) {
        $nameError = validateNameField($mname, 'Middle name');
        if ($nameError) {
            $error_messages[] = $nameError;
            $isallok = false;
        }
    }
    
    // Validate last name
    $lname = trim($_POST['txtlname']);
    if (!empty($lname)) {
        $nameError = validateNameField($lname, 'Last name');
        if ($nameError) {
            $error_messages[] = $nameError;
            $isallok = false;
        }
    }
    
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
    }    // Validate password strength
    $password = $_POST['txtpassword'];
    $password_errors = [];
    
    // Check for common weak passwords
    $common_passwords = [
        'password', 'password123', '123456', '123456789', 'qwerty', 
        'abc123', 'password1', 'admin', 'letmein', 'welcome',
        '1234567890', 'iloveyou', 'princess', 'rockyou', '12345678'
    ];
    
    if (in_array(strtolower($password), $common_passwords)) {
        $password_errors[] = "This password is too common. Please choose a more unique password";
    }
    
    if (strlen($password) < 8) {
        $password_errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $password_errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $password_errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $password_errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $password_errors[] = "Password must contain at least one special character (!@#$%^&*)";
    }
    
    // Check if password contains user's personal information
    $fname_lower = strtolower($fname);
    $lname_lower = strtolower($lname);
    $email_parts = explode('@', strtolower($_POST['txtemail']));
    $email_username = $email_parts[0];
    
    if (stripos($password, $fname_lower) !== false && strlen($fname_lower) > 2) {
        $password_errors[] = "Password should not contain your first name";
    }
    if (stripos($password, $lname_lower) !== false && strlen($lname_lower) > 2) {
        $password_errors[] = "Password should not contain your last name";
    }
    if (stripos($password, $email_username) !== false && strlen($email_username) > 3) {
        $password_errors[] = "Password should not contain your email username";
    }
    
    if (!empty($password_errors)) {
        $error_messages = array_merge($error_messages, $password_errors);
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
        'txtpassword' => 'Password',
        'txtconfirmpassword' => 'Confirm Password'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty(trim($_POST[$field]))) {
            $error_messages[] = "Please enter your " . $label;
            $isallok = false;
        }
    }

    // Check if password and confirm password match
    if ($_POST['txtpassword'] !== $_POST['txtconfirmpassword']) {
        $error_messages[] = "Passwords do not match";
        $isallok = false;
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