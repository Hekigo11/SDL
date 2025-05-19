<?php
require_once __DIR__ . '/../config.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Keep the original ID in the main login form -->
            <form id="loginForm" class="p-4" novalidate>
                <div class="form-group">
                    <label for="txtemail">Email</label>
                    <input type="email" class="form-control mb-2" id="txtemail" name="txtemail" placeholder="Enter Email" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group">
                    <label for="txtpassword">Password</label>
                    <input type="password" class="form-control mb-2" id="txtpassword" name="txtpassword" placeholder="Enter Password" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="text-center my-3">
                    <p><a href="<?php echo BASE_URL; ?>/modules/forgot_password.php">Forgot Password?</a></p>
                </div>
                <div class="text-center">
                    <button type="button" class="btn rounded-pill btn-primary btn-block" id="btnlogin">Login</button>
                    <p class="mt-3">Don't have an account? <a href="<?php echo BASE_URL; ?>/modules/register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="alert-container" class="mt-3"></div>

<script>
function showAlertlogin(message, type) {
    const alertContainer = $("#alert-container");
    const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            
                       </div>`;
    alertContainer.html(alertHtml);
}

function validateField(field) {
    const input = $(field);
    const value = input.val().trim();
    
    input.removeClass('is-invalid is-valid');
    input.next('.invalid-feedback').text('');
    
    if (!value) {
        input.addClass('is-invalid');
        input.next('.invalid-feedback').text(`Please enter your ${input.attr('placeholder').toLowerCase()}`);
        return false;
    }
    
    input.addClass('is-valid');
    return true;
}

$(document).ready(function(){
    // Validate on input
    $('#loginForm input').on('input', function() {
        validateField(this);
    });
    
    // GUMAGANA NA ENTER KEY
    $("#loginForm").on('keypress', function(e) {
        if(e.which === 13) {
            e.preventDefault();
            $("#btnlogin").click();
        }
    });

    $("#btnlogin").click(function(){
        let isValid = true;
        
        // Validate each field
        $('#loginForm input').each(function() {
            if (!validateField(this)) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            return;
        }

        // Pang clear ng alert
        $("#alert-container").empty();
        
        $.post("<?php echo BASE_URL; ?>/modules/login_req.php", $("#loginForm").serialize(), function(d){
            if(d == 'admin'){
                showAlertlogin("Login successful! Redirecting to admin dashboard...", "success");
                $("input[name='txtemail']").val('');
                $("input[name='txtpassword']").val('');
                $('#btnlogin').prop('disabled', true);
                setTimeout(() => {
                    $('#loginModal').modal('hide');
                    setTimeout(() => {
                        window.location.href = "<?php echo BASE_URL; ?>/modules/admindashboard.php";
                    }, 500);
                }, 2000);
            }
            else if(d == 'success'){
                showAlertlogin("Login successful! Redirecting...", "success");
                $("input[name='txtemail']").val('');
                $("input[name='txtpassword']").val('');
                $('#btnlogin').prop('disabled', true);
                setTimeout(() => {
                    $('#loginModal').modal('hide');
                    setTimeout(() => {
                        window.location.href = window.location.href;
                    }, 500);
                }, 2000);
            } 
            else if(d == 'verify_required') {
                showAlertlogin("Please verify your email first. Redirecting...", "warning");
                $('#btnlogin').prop('disabled', true);
                setTimeout(() => {
                    window.location.href = "<?php echo BASE_URL; ?>/modules/verify.php";
                }, 2000);
            } 
            else {
                showAlertlogin(d, "danger");
            }
        });
    });
});</script>


