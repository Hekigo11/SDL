<?php
require_once __DIR__ . '/../config.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <form id="frmlogin" class="p-4">
                        <div class="form-group">
                <label for="txtusername">Email or Mobile Number</label>
                <input type="text" class="form-control mb-4" name="txtemail" placeholder="Enter Email or Mobile Number" required>
            </div>
                <div class="form-group">
				<label for="txtpassword">Password</label>
                    <input type="password" class="form-control mb-4" name="txtpassword" placeholder="Enter Password" required>
                </div>
				<div class="text-center my-3"><p><a href="<?php echo BASE_URL; ?>/modules/forgot_password.php">Forgot Password?</a></p></div>
                <div class="text-center">
                    <button type="button" class="btn rounded-pill btn-primary btn-block" id="btnlogin">Login</button>
                    <p class="mt-3">Don't have an account? <a href="<?php echo BASE_URL; ?>/modules/register.php">Register here</a></p>
                    
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#btnlogin").click(function(){
            $.post("<?php echo BASE_URL; ?>/modules/login_req.php", $("form#frmlogin").serialize(), function(d){
                if(d == 'admin'){
                    alert("Admin Login Success");
                    $("input[name='txtemail']").val('');
                    $("input[name='txtpassword']").val('');
                    $('#loginModal').modal('hide');
                    window.location.href = "<?php echo BASE_URL; ?>/modules/admindashboard.php";
                }
                else if(d=='success'){
                    alert("Login Success");
                    $("input[name='txtemail']").val('');
                    $("input[name='txtpassword']").val('');
                    $('#loginModal').modal('hide');
                location.reload();
                    window.location.href = "<?php echo BASE_URL; ?>/";
                } else if(d == 'verify_required') {
                    alert("Please verify your email first");
                setTimeout(function() {
                    window.location.href = "<?php echo BASE_URL; ?>/modules/verify.php";
                }, 100);
                } else {
                    alert(d);
                }
            });
        });
    });
</script>


