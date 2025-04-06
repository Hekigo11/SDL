<?php
include("dbconi.php");

if(isset($_POST['email']) && isset($_POST['otp'])) {
    $email = mysqli_real_escape_string($dbc, $_POST['email']);
    $otp = mysqli_real_escape_string($dbc, $_POST['otp']);
    $current_time = date('Y-m-d H:i:s');

    $query = "SELECT * FROM users WHERE email_add = '$email' AND OTPC = '$otp' AND OTPE > '$current_time'";
    $result = mysqli_query($dbc, $query);

    if(mysqli_num_rows($result) > 0) {
        $update_query = "UPDATE users SET emailv = 1, OTPC = NULL, OTPE = NULL WHERE email_add = '$email'";
        mysqli_query($dbc, $update_query);
        echo "success";
    } else {
        echo "Invalid or expired verification code";
    }
    mysqli_close($dbc);
} else {
    echo "Invalid request";
}
?>