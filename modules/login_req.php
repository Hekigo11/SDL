<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("dbconi.php");

$email = filter_input(INPUT_POST, 'txtemail', FILTER_SANITIZE_EMAIL);
$password = $_POST['txtpassword'];

if (empty($email) || empty($password)) {
    echo "Please fill in all fields";
    exit;
}

$query = "SELECT * FROM users WHERE email_add=? AND emailv=1";
$stmt = mysqli_prepare($dbc, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    if (password_verify($password, $row['password'])) {
        $_SESSION['loginok'] = true;
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['email'] = $row['email_add'];
        $_SESSION['name'] = $row['fname'] . ' ' . $row['lname'];
        echo "success";
    } else {
        echo "Invalid password";
    }
} else {
    echo "Email not found or not verified";
}

mysqli_close($dbc);
?>
