<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<!-- Popper JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>	<title>Register</title>
	<link rel="stylesheet" href="regverif.css">
	<style>
		.password-requirements {
			background-color: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 5px;
			padding: 10px;
		}
		.requirement {
			display: block;
			margin: 2px 0;
			font-size: 0.875rem;
		}
		.requirement i {
			width: 12px;
			margin-right: 5px;
		}
		.password-strength {
			margin-top: 5px;
		}
		#password-strength-text {
			font-weight: 500;
		}
		.progress {
			background-color: #e9ecef;
		}
	</style>

	
  </head>

<body style="min-height: 100vh;  padding-top: 20px;">
<div class="container py-4" style="margin-top: 40vh">
      <div class="card mt-5 my-3"  style="border-radius: 30px; background-color: var(--background);">
          <div class="card-body" style="height: auto;">              
            <h5 class="card-title">Register</h5>
              <!-- <div class="alert alert-info" role="alert">
                  <strong>Password Security Tips:</strong>
                  <ul class="mb-0 mt-2" style="font-size: 0.875rem;">
                      <li>Use a unique password that you don't use elsewhere</li>
                      <li>Consider using a passphrase with multiple words</li>
                      <li>Avoid using personal information like your name or birthday</li>
                      <li>Mix uppercase, lowercase, numbers, and symbols</li>
                  </ul>
              </div> -->
              <div id="alert-container" class="mt-3"></div>
              <form id="frmstud" enctype="multipart/form-data">

			  	<div class="form-group">
                    <label for="txtstudno">Email</label>
                    <input type="text" class="form-control" id="txtemail" name="txtemail">
                </div>
            	<div class="form-group">
                    <label for="txtfname">First Name</label>
                    <input type="text" class="form-control" id="txtfname" name="txtfname">
                </div>
                <div class="form-group">
                    <label for="txtmname">Middle Name</label>
                    <input type="text" class="form-control" id="txtmname" name="txtmname">
                </div>
                <div class="form-group">
                    <label for="txtlname">Last Name</label>
                    <input type="text" class="form-control" id="txtlname" name="txtlname">
                </div>
                
                <div class="form-group">
                    <label for="txtstudno">Mobile Number</label>
                    <input type="text" class="form-control" id="txtmobilenum" name="txtmobilenum" pattern="^0\d{10}$" maxlength="11" placeholder="09XXXXXXXXX" required>
                    <small class="form-text text-muted">Enter 11-digit mobile number starting with 0 (e.g., 09123456789)</small>
                </div>                <div class="form-group">
                    <label for="txtpassword">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="txtpassword" name="txtpassword">
                        <div class="input-group-append">
                            <span class="input-group-text" id="togglePassword" style="cursor:pointer;">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                    <div class="password-requirements mt-2">
                        <small class="text-muted">Password must contain:</small>
                        <div class="requirement-list">
                            <small id="length-req" class="requirement text-danger">
                                <i class="fa fa-times"></i> At least 8 characters
                            </small><br>
                            <small id="uppercase-req" class="requirement text-danger">
                                <i class="fa fa-times"></i> At least one uppercase letter (A-Z)
                            </small><br>
                            <small id="lowercase-req" class="requirement text-danger">
                                <i class="fa fa-times"></i> At least one lowercase letter (a-z)
                            </small><br>
                            <small id="number-req" class="requirement text-danger">
                                <i class="fa fa-times"></i> At least one number (0-9)
                            </small><br>
                            <small id="special-req" class="requirement text-danger">
                                <i class="fa fa-times"></i> At least one special character (!@#$%^&*)
                            </small>
                        </div>
                    </div>                    <div class="password-strength mt-2">
                        <div class="progress" style="height: 5px;">
                            <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="password-strength-text" class="text-muted">Password strength: Very Weak</small>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            <strong>Example strong password:</strong> MySecure@Pass123
                        </small>
                    </div>
                </div><div class="form-group">
                    <label for="txtconfirmpassword">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="txtconfirmpassword" name="txtconfirmpassword">
                        <div class="input-group-append">
                            <span class="input-group-text" id="toggleConfirmPassword" style="cursor:pointer;">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                    <div id="password-match-indicator" class="mt-2" style="display: none;">
                        <small id="password-match-text" class="text-danger">
                            <i class="fa fa-times"></i> Passwords do not match
                        </small>
                    </div>
                </div>

                <div class="form-group d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-primary flex-fill mr-2" id="btnsave">Save</button>
                    <button type="button" class="btn btn-secondary flex-fill ml-2" id="btncancel">Cancel</button>
                </div>
              </form>
          </div>
      </div>

      </div>
</body>

<script>
function showAlert(message, type = 'warning') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    // Clear existing alerts
    document.getElementById('alert-container').innerHTML = '';
    document.getElementById('alert-container').appendChild(alertDiv);
}

$(document).ready(function(){
    // Name validation function
    function validateName(name) {
        // Only allow letters, spaces, hyphens, and apostrophes
        return /^[a-zA-Z\s\-']+$/.test(name);
    }
    
    // Real-time name validation
    function setupNameValidation(inputId, fieldName) {
        $(inputId).on('input', function() {
            const value = $(this).val();
            const errorId = inputId + '-error';
            
            // Remove existing error message
            $(errorId).remove();
            
            if (value && !validateName(value)) {
                $(this).addClass('is-invalid');
                $(this).after(`<div id="${errorId.substring(1)}" class="invalid-feedback">${fieldName} can only contain letters, spaces, hyphens, and apostrophes</div>`);
            } else {
                $(this).removeClass('is-invalid');
            }
        });
    }
    
    // Setup validation for all name fields
    setupNameValidation('#txtfname', 'First name');
    setupNameValidation('#txtmname', 'Middle name');
    setupNameValidation('#txtlname', 'Last name');

    // Password validation function
    function validatePassword(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };
        
        return requirements;
    }
    
    function updatePasswordStrength(password) {
        const requirements = validatePassword(password);
        const metRequirements = Object.values(requirements).filter(Boolean).length;
        
        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = $(`#${req}-req`);
            if (requirements[req]) {
                element.removeClass('text-danger').addClass('text-success');
                element.find('i').removeClass('fa-times').addClass('fa-check');
            } else {
                element.removeClass('text-success').addClass('text-danger');
                element.find('i').removeClass('fa-check').addClass('fa-times');
            }
        });
        
        // Update strength bar and text
        const strengthBar = $('#password-strength-bar');
        const strengthText = $('#password-strength-text');
        let strengthLevel = '';
        let barClass = '';
        let percentage = (metRequirements / 5) * 100;
        
        if (metRequirements === 0) {
            strengthLevel = 'Very Weak';
            barClass = 'bg-danger';
        } else if (metRequirements === 1) {
            strengthLevel = 'Weak';
            barClass = 'bg-danger';
        } else if (metRequirements === 2) {
            strengthLevel = 'Fair';
            barClass = 'bg-warning';
        } else if (metRequirements === 3) {
            strengthLevel = 'Good';
            barClass = 'bg-info';
        } else if (metRequirements === 4) {
            strengthLevel = 'Strong';
            barClass = 'bg-primary';
        } else {
            strengthLevel = 'Very Strong';
            barClass = 'bg-success';
        }
        
        strengthBar.removeClass('bg-danger bg-warning bg-info bg-primary bg-success').addClass(barClass);
        strengthBar.css('width', percentage + '%');
        strengthText.text(`Password strength: ${strengthLevel}`);
        
        return metRequirements === 5;
    }
      // Real-time password validation
    $("#txtpassword").on('input', function() {
        const password = $(this).val();
        updatePasswordStrength(password);
        
        // Check password match if confirm password has value
        const confirmPassword = $("#txtconfirmpassword").val();
        if (confirmPassword) {
            checkPasswordMatch(password, confirmPassword);
        }
    });
    
    // Password match validation
    function checkPasswordMatch(password, confirmPassword) {
        const indicator = $('#password-match-indicator');
        const text = $('#password-match-text');
        
        if (confirmPassword === '') {
            indicator.hide();
            return;
        }
        
        indicator.show();
        if (password === confirmPassword) {
            text.removeClass('text-danger').addClass('text-success');
            text.html('<i class="fa fa-check"></i> Passwords match');
        } else {
            text.removeClass('text-success').addClass('text-danger');
            text.html('<i class="fa fa-times"></i> Passwords do not match');
        }
    }
    
    $("#txtconfirmpassword").on('input', function() {
        const confirmPassword = $(this).val();
        const password = $("#txtpassword").val();
        checkPasswordMatch(password, confirmPassword);
    });

    // Mobile number validation
    $("#txtmobilenum").on('input', function() {
        let value = $(this).val();
        // Remove any non-digit characters
        value = value.replace(/\D/g, '');
        // Ensure it starts with 0
        if (value.length > 0 && value[0] !== '0') {
            value = '0' + value.substring(1);
        }
        // Limit to 11 digits
        value = value.substring(0, 11);
        $(this).val(value);
    });

    $("#btncancel").click(function(){
        document.location = "../index.php";
    });
      $("#btnsave").click(function(){
        // Clear previous alerts
        $('#alert-container').empty();
        
        // Validate name fields
        const nameFields = [
            { id: '#txtfname', name: 'First name' },
            { id: '#txtlname', name: 'Last name' }
        ];
        
        let nameValidationPassed = true;
        nameFields.forEach(field => {
            const value = $(field.id).val().trim();
            if (value && !validateName(value)) {
                showAlert(`${field.name} can only contain letters, spaces, hyphens, and apostrophes`, "danger");
                nameValidationPassed = false;
                return false;
            }
        });
        
        // Check middle name if provided
        const middleName = $('#txtmname').val().trim();
        if (middleName && !validateName(middleName)) {
            showAlert("Middle name can only contain letters, spaces, hyphens, and apostrophes", "danger");
            nameValidationPassed = false;
        }
        
        if (!nameValidationPassed) {
            return;
        }
        
        // Validate mobile number
        const mobileNum = $("#txtmobilenum").val();
        if (!/^0\d{10}$/.test(mobileNum)) {
            showAlert("Please enter a valid 11-digit mobile number starting with 0", "danger");
            return;
        }
        
        // Validate password strength
        const password = $("#txtpassword").val();
        const requirements = validatePassword(password);
        const metRequirements = Object.values(requirements).filter(Boolean).length;
        
        if (metRequirements < 5) {
            showAlert("Password does not meet all requirements. Please ensure your password is strong enough.", "danger");
            return;
        }
        
        // Validate password match
        const confirmPassword = $("#txtconfirmpassword").val();
        if (password !== confirmPassword) {
            showAlert("Passwords do not match", "danger");
            return;
        }
        
        // didisable na yung button after clicking, para di mag double click
        $("#btnsave").prop('disabled', true);
        
        $.post("<?php echo BASE_URL; ?>/modules/register_save.php", $("#frmstud").serialize(), function(response) {
            if(response.trim() === "success") {
                showAlert("Registration successful! Please check your email for verification. Redirecting...", "success");
            
                $("#frmstud")[0].reset();
                
                setTimeout(() => {
                    window.location.href = "<?php echo BASE_URL; ?>/modules/verify.php";
                }, 3000);
            } else {
                showAlert(response, "danger");
             
                $("#btnsave").prop('disabled', false);
            }
        }).fail(function() {
            showAlert("Failed to connect to server. Please try again.", "danger");
         
            $("#btnsave").prop('disabled', false);
        });
    });

    // Add FontAwesome for eye icon
    $("head").append('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">');

    // Toggle password visibility
    $('#togglePassword').on('click', function() {
        const input = $('#txtpassword');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    $('#toggleConfirmPassword').on('click', function() {
        const input = $('#txtconfirmpassword');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});
</script>
