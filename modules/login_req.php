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
    
    $query = "SELECT * FROM users 
              WHERE (email_add='".mysqli_real_escape_string($dbc, $_POST['txtemail'])."' 
              OR mobile_num='".mysqli_real_escape_string($dbc, $_POST['txtemail'])."') 
              AND role_id != 0";
              
    $result = mysqli_query($dbc, $query);
    
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        
        if(password_verify($_POST['txtpassword'], $row['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['loginok'] = true;
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['email'] = $row['email_add'];
            $_SESSION['name'] = $row['fname'] . ' ' . $row['lname'];
            $msg = "success";
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