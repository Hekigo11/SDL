<?php
require_once __DIR__ . '/../config.php';

$isallok = true;
$msg = "";

if(trim($_POST['txtemail']) == '') {
    $isallok = false;
    $msg .= "Enter Email or Mobile Number\n";
}
if(trim($_POST['txtpassword']) == '') {
    $isallok = false;
    $msg .= "Enter Password\n";
}

if($isallok) {
    include("dbconi.php");
    $current_time = date('Y-m-d H:i:s');
    $query = "SELECT * FROM users 
              WHERE (email_add='".mysqli_real_escape_string($dbc, $_POST['txtemail'])."' 
              OR mobile_num='".mysqli_real_escape_string($dbc, $_POST['txtemail'])."') 
              AND role_id != 0";
              
              $cleanup_query = "DELETE FROM users 
                     WHERE (emailv IS NULL OR emailv != 'Yes')
                     AND OTPE < ?";
    $cleanup_stmt = mysqli_prepare($dbc, $cleanup_query);
    mysqli_stmt_bind_param($cleanup_stmt, "s", $current_time);
    mysqli_stmt_execute($cleanup_stmt);


    $result = mysqli_query($dbc, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);

        if($row['emailv'] != 'Yes') {
            // Check if OTP is expired
            if($row['OTPE'] < $current_time) {
                // Generate new OTP and update expiration
                $new_otp = sprintf("%06d", mt_rand(1, 999999));
                $new_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                $update_query = "UPDATE users 
                               SET OTPC = '$new_otp', 
                                   OTPE = '$new_expiry' 
                               WHERE user_id = {$row['user_id']}";
                mysqli_query($dbc, $update_query);
            }
            $_SESSION['verify_email'] = $row['email_add'];
            $_SESSION['user_id'] = $row['user_id'];
            echo "verify_required";
            exit;
        }
        if(password_verify($_POST['txtpassword'], $row['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['loginok'] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['email'] = $row['email_add'];
            $_SESSION['name'] = $row['fname'] . ' ' . $row['lname'];
            $_SESSION['role'] = $row['role_id'];

            //role checker if admin or customer
            if($row['role_id'] == 1) {
                $msg = "admin"; // Admin role
            } else {
                $msg = "success"; // Customer role
            }
           
        } else {
            $msg = "Invalid password";
        }
    } else {
        $msg = "Account not found";
    }
    mysqli_close($dbc);
}

echo $msg;
?>