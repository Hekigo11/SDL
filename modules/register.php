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
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<title>Register</title>
	<link rel="stylesheet" href="regverif.css">

	
  </head>

<body>

      <div class="card mx-auto col-md-5 my-3"  style="border-radius: 30px; background-color: var(--background);">
          <div class="card-body">
              <h5 class="card-title">Register</h5>
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
                </div>
                <div class="form-group">
                    <label for="txtstudno">Password</label>
                    <input type="password" class="form-control" id="txtpassword" name="txtpassword">
                </div>

                <button type="button" class="btn btn-primary" id="btnsave">Save</button>
                <button type="button" class="btn btn-secondary" id="btncancel">Cancel</button>
              </form>
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
        
        // Validate mobile number
        const mobileNum = $("#txtmobilenum").val();
        if (!/^0\d{10}$/.test(mobileNum)) {
            showAlert("Please enter a valid 11-digit mobile number starting with 0", "danger");
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
});
</script>
