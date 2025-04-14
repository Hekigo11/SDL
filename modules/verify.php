<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['verify_email'])) {
    header('Location: ' . BASE_URL . '/index.php');
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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/modules/regverif.css">
</head>
<body>
    <div class="card mx-auto col-md-5 my-3" style="border-radius: 30px; background-color: var(--background);">
        <div class="card-body">
            <a href="<?php echo BASE_URL; ?>/index.php" class="close-btn" style="position: absolute; right: 20px; top: 10px; text-decoration: none; font-size: 30px; color: #000;">&times;</a>
            <h5 class="card-title">Email Verification</h5>
            <p>We've sent a verification code to your email address. Please enter it below:</p>
            <form id="frmverify">
                <div class="form-group">
                    <input type="text" class="form-control" name="verification_code" placeholder="Enter 6-digit code" required>
                </div>
                <button type="button" class="btn rounded-pill btn-outline-primary btn-block" id="btnverify">Verify Email</button>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function(){
        $("#btnverify").click(function(){
            $.post("<?php echo BASE_URL; ?>/modules/verify_code.php", $("#frmverify").serialize(), function(response){
                if(response == 'success'){
                    alert("Email verified successfully!");
                    window.location.href = "<?php echo BASE_URL; ?>/index.php";
                } else {
                    alert(response);
                }
            });
        });
    });
    </script>
</body>
</html>