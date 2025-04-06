<?php
session_start();
if (!isset($_SESSION['verify_email'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="../vendor/style1.css">
</head>
<body style="background: var(--primary1); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
    <div class="card mx-auto col-md-5 my-3" style="border-radius: 30px; background-color: var(--background);">
        <div class="card-body">
            <h5 class="card-title">Email Verification</h5>
            <p>We've sent a verification code to your email address. Please enter it below:</p>
            <form id="frmverify">
                <div class="form-group">
                    <input type="text" class="form-control" name="verification_code" placeholder="Enter 6-digit code" required>
                </div>
                <button type="button" class="btn btn-primary" id="btnverify">Verify Email</button>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        $("#btnverify").click(function(){
            $.post("verify_code.php", $("#frmverify").serialize(), function(response){
                if(response == 'success'){
                    alert("Email verified successfully!");
                    window.location.href = "../index.php";
                } else {
                    alert(response);
                }
            });
        });
    });
    </script>
</body>
</html>