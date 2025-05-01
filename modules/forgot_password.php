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
                <div id="alert-container" class="mt-3"></div>
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
    function showAlert(message, type) {
        const alertContainer = $("#alert-container");
        const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                       </div>`;
        alertContainer.html(alertHtml);
    }

    $(document).ready(function(){
        $("#btnRequestReset").click(function(){
            let email = $("#email").val();
            if(!email) {
                showAlert("Please enter your email address", "warning");
                return;
            }

            $.post("<?php echo BASE_URL; ?>/modules/forgot_password_request.php", 
                { email: email }, 
                function(response){
                    if(response.success){
                        $("#email-form").hide();
                        $("#otp-form").show();
                        showAlert("Password reset code has been sent to your email", "success");
                    } else {
                        showAlert(response.message || "Error occurred", "danger");
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
                showAlert("Please fill in all fields", "warning");
                return;
            }

            if(newPassword !== confirmPassword) {
                showAlert("Passwords do not match", "warning");
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
                        showAlert("Password has been reset successfully! Redirecting...", "success");
                        setTimeout(() => {
                            window.location.href = "<?php echo BASE_URL; ?>/index.php";
                        }, 1500);
                    } else {
                        showAlert(response.message || "Error occurred", "danger");
                    }
                }, 'json'
            );
        });
    });
    </script>
</body>
</html>