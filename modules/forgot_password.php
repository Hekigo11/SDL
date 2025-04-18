<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/modules/regverif.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto col-md-5" style="border-radius: 30px; background-color: var(--background);">
            <div class="card-body">
                <h5 class="card-title">Reset Password</h5>
                <div id="email-form">
                    <div class="form-group">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
                    </div>
                    <button type="button" class="btn btn-primary btn-block" id="btnRequestReset">Request Password Reset</button>
                </div>
                
                <div id="otp-form" style="display: none;">
                    <div class="form-group">
                        <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter OTP code" maxlength="6" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    <button type="button" class="btn btn-primary btn-block" id="btnResetPassword">Reset Password</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        $("#btnRequestReset").click(function(){
            let email = $("#email").val();
            if(!email) {
                alert("Please enter your email address");
                return;
            }

            $.post("<?php echo BASE_URL; ?>/modules/forgot_password_request.php", 
                { email: email }, 
                function(response){
                    if(response.success){
                        $("#email-form").hide();
                        $("#otp-form").show();
                        alert("Password reset code has been sent to your email");
                    } else {
                        alert(response.message || "Error occurred");
                    }
                }, 'json'
            );
        });

        $("#btnResetPassword").click(function(){
            let email = $("#email").val();
            let otp = $("#otp").val();
            let newPassword = $("#new_password").val();
            let confirmPassword = $("#confirm_password").val();

            if(!otp || !newPassword || !confirmPassword) {
                alert("Please fill in all fields");
                return;
            }

            if(newPassword !== confirmPassword) {
                alert("Passwords do not match");
                return;
            }

            $.post("<?php echo BASE_URL; ?>/modules/reset_password.php", 
                {
                    email: email,
                    otp: otp,
                    new_password: newPassword
                }, 
                function(response){
                    if(response.success){
                        alert("Password has been reset successfully");
                        window.location.href = "<?php echo BASE_URL; ?>/index.php";
                    } else {
                        alert(response.message || "Error occurred");
                    }
                }, 'json'
            );
        });
    });
    </script>
</body>
</html>